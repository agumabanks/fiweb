<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membership;
use App\Models\ShareTransaction;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use App\Notifications\ShareTransactionNotification;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\MembershipsExport;

class MembershipController extends Controller
{
    /**
     * Display a listing of the memberships.
     */
    public function index()
    {
        $pageTitle = 'All Memberships';
        $memberships = Membership::with('client')
            ->paginate(20);

        return view('admin-views.memberships.index', compact('pageTitle', 'memberships'));
    }

    /**
     * Show the form for creating a new membership.
     */
    public function create2()
    {
        $pageTitle = 'Create New Membership';
        $users = Clients::all();
        $membership = new Membership();


        return view('admin-views.memberships.create', compact('pageTitle', 'users'));
    }
    
     public function create()
    {
        $pageTitle = 'Create New Membership';

        // Fetch necessary data
        $clients = Client::all(); // Assuming you have a Client model
        $agents = User::all();    // Assuming agents are users

        // Create an empty Membership instance
        $membership = new Membership();

        return view('admin-views.memberships.create', compact('pageTitle', 'clients', 'agents', 'membership'));
    }
    
public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id'         => 'required|exists:users,id',
            'membership_type' => 'required|in:Standard,Premium,Metal',
            'is_paid'         => 'required|boolean',
            'shares'          => 'required|integer|min:0',
            'share_value'     => 'required|numeric|min:0',
            'membership_fees' => 'required|numeric|min:0',
            'client_id'       => 'required|exists:clients,id',
            'is_shares_paid'  => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pageTitle', 'Create New Membership');
        }

        DB::beginTransaction();
        try {
            // Create the membership with validated data
            $membership = Membership::create([
                'user_id'         => $request->user_id,
                'membership_type' => $request->membership_type,
                'is_paid'         => $request->is_paid,
                'shares'          => $request->shares,
                'share_value'     => $request->share_value,
                'membership_fees' => $request->membership_fees,
                'client_id'       => $request->client_id,
                'is_shares_paid'  => $request->is_shares_paid,
                'membership_date' => now(), // Set the current timestamp as membership date
            ]);

            DB::commit();
            Toastr::success('Membership created successfully.');

            return redirect()->route('admin.memberships.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('An error occurred: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
    /**
     * Display the specified membership.
     */
    public function show($id)
    {
        $membership = Membership::with(['client'])->findOrFail($id);

        return view('admin-views.memberships.show', compact('membership'));
    }

    /**
     * Show the form for editing the specified membership.
     */
    public function edit($id)
    {
        $pageTitle = 'Edit Membership';
        $membership = Membership::findOrFail($id);
        $users = User::all();

        return view('admin-views.memberships.edit', compact('pageTitle', 'membership', 'users'));
    }

    /**
     * Update the specified membership in storage.
     */
    public function update(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'user_id'         => 'required|exists:users,id',
            'membership_type' => 'required|string|in:Standard,Premium,Metal',
            'is_paid'         => 'required|boolean',
        ]);

        // Update the membership
        $membership->update($validatedData);

        Toastr::success('Membership updated successfully.');

        return redirect()->route('memberships.index');
    }

    /**
     * Remove the specified membership from storage.
     */
    public function destroy($id)
    {
        try {
            $membership = Membership::findOrFail($id);

            // Delete all share transactions associated with this membership
            ShareTransaction::where('membership_id', $membership->id)->delete();

            // Delete the membership
            $membership->delete();

            Toastr::success('Membership and its share transactions deleted successfully.');

            return back();
        } catch (\Exception $e) {
            Toastr::error('Failed to delete the membership. Please try again.');
            return back();
        }
    }

    /**
     * Show the form for creating a share transaction.
     */
    public function createShareTransaction($membershipId)
    {
        $membership = Membership::findOrFail($membershipId);
        $pageTitle = 'Create Share Transaction for Membership #' . $membership->id;

        return view('admin-views.shares.create', compact('membership', 'pageTitle'));
    }

    /**
     * Store a new share transaction.
     */
    public function storeShareTransaction(Request $request, $membershipId)
    {
        $membership = Membership::findOrFail($membershipId);

        // Manually create a validator instance
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|string|in:create,reverse',
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'nullable|string|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Pass the membership variable back to the view
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('membership', $membership)
                ->with('pageTitle', 'Create Share Transaction for Membership #' . $membership->id);
        }

        $validatedData = $validator->validated();

        $amount = $validatedData['amount'];
        if ($validatedData['transaction_type'] === 'reverse') {
            $amount = -$amount;
        }

        DB::beginTransaction();
        try {
            // Update the membership shares
            $membership->shares += $amount;

            if ($membership->shares < 0) {
                throw new \Exception('Insufficient shares for this operation.');
            }

            $membership->save();

            // Record the share transaction
            $transaction = ShareTransaction::create([
                'membership_id'    => $membership->id,
                'transaction_type' => $validatedData['transaction_type'],
                'amount'           => $amount,
                'description'      => $validatedData['description'] ?? ucfirst($validatedData['transaction_type']),
            ]);

            // Send notification to the user
            $membership->user->notify(new ShareTransactionNotification($membership, $transaction));

            DB::commit();

            Toastr::success('Share transaction completed successfully.');

            return redirect()->route('memberships.show', $membership->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('An error occurred: ' . $e->getMessage());

            // Pass the membership variable back to the view
            return redirect()->back()
                ->withInput()
                ->with('membership', $membership)
                ->with('pageTitle', 'Create Share Transaction for Membership #' . $membership->id);
        }
    }

    /**
     * Show the form for transferring shares.
     */
    public function transferSharesForm($membershipId)
    {
        $membership = Membership::findOrFail($membershipId);
        $memberships = Membership::where('id', '!=', $membershipId)->get();
        $pageTitle = 'Transfer Shares from Membership #' . $membership->id;

        return view('admin-views.shares.transfer', compact('membership', 'memberships', 'pageTitle'));
    }

    /**
     * Handle the transfer of shares between memberships.
     */
    public function transferShares(Request $request, $membershipId)
    {
        $fromMembership = Membership::findOrFail($membershipId);

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'to_membership_id' => 'required|exists:memberships,id|different:from_membership_id',
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'nullable|string|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Pass variables back to the view
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('membership', $fromMembership)
                ->with('memberships', Membership::where('id', '!=', $membershipId)->get())
                ->with('pageTitle', 'Transfer Shares from Membership #' . $fromMembership->id);
        }

        $validatedData = $validator->validated();

        $toMembership = Membership::findOrFail($validatedData['to_membership_id']);

        if ($fromMembership->shares < $validatedData['amount']) {
            Toastr::error('Insufficient shares to transfer.');

            // Pass variables back to the view
            return redirect()->back()
                ->withInput()
                ->with('membership', $fromMembership)
                ->with('memberships', Membership::where('id', '!=', $membershipId)->get())
                ->with('pageTitle', 'Transfer Shares from Membership #' . $fromMembership->id);
        }

        DB::beginTransaction();
        try {
            // Deduct shares from the sender
            $fromMembership->shares -= $validatedData['amount'];
            $fromMembership->save();

            // Record the share transaction for sender
            $fromTransaction = ShareTransaction::create([
                'membership_id'        => $fromMembership->id,
                'transaction_type'     => 'transfer_out',
                'amount'               => -$validatedData['amount'],
                'related_membership_id' => $toMembership->id,
                'description'          => $validatedData['description'] ?? 'Transfer to Membership #' . $toMembership->id,
            ]);

            // Add shares to the receiver
            $toMembership->shares += $validatedData['amount'];
            $toMembership->save();

            // Record the share transaction for receiver
            $toTransaction = ShareTransaction::create([
                'membership_id'        => $toMembership->id,
                'transaction_type'     => 'transfer_in',
                'amount'               => $validatedData['amount'],
                'related_membership_id' => $fromMembership->id,
                'description'          => $validatedData['description'] ?? 'Transfer from Membership #' . $fromMembership->id,
            ]);

            // Send notifications
            $fromMembership->user->notify(new ShareTransactionNotification($fromMembership, $fromTransaction));
            $toMembership->user->notify(new ShareTransactionNotification($toMembership, $toTransaction));

            DB::commit();

            Toastr::success('Shares transferred successfully.');

            return redirect()->route('memberships.show', $fromMembership->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('An error occurred: ' . $e->getMessage());

            // Pass variables back to the view
            return redirect()->back()
                ->withInput()
                ->with('membership', $fromMembership)
                ->with('memberships', Membership::where('id', '!=', $membershipId)->get())
                ->with('pageTitle', 'Transfer Shares from Membership #' . $fromMembership->id);
        }
    }

    /**
     * Generate and download a share transaction receipt as PDF.
     */
    public function printTransactionReceiptPdf(Membership $membership, ShareTransaction $transaction)
    {
        // Ensure the transaction belongs to the membership
        if ($transaction->membership_id !== $membership->id) {
            Toastr::error('Transaction does not belong to this membership.');
            return redirect()->route('memberships.show', $membership->id);
        }

        // Load related user data
        $membership->load('user');

        // Pass data to the receipt view
        $data = [
            'membership' => $membership,
            'transaction' => $transaction,
        ];

        // Load the receipt view and generate PDF
        $pdf = PDF::loadView('shares.receipt_pdf', $data)
                   ->setPaper('a4', 'portrait');

        // Define a filename
        $filename = 'Receipt_' . $transaction->id . '.pdf';

        // Return the generated PDF for download
        return $pdf->download($filename);
    }

    /**
     * Display the reports selection form.
     */
    public function reportsIndex()
    {
        $pageTitle = 'Membership Reports';
        return view('admin-views.memberships.reports_index', compact('pageTitle'));
    }

    /**
     * Generate and display reports based on selected criteria.
     */
    public function generateReports(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'report_type' => 'required|in:total_shares,membership_activity',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        $reportType = $validatedData['report_type'];
        $startDate = Carbon::parse($validatedData['start_date'])->startOfDay();
        $endDate = Carbon::parse($validatedData['end_date'])->endOfDay();

        switch ($reportType) {
            case 'total_shares':
                $data = Membership::with('user')
                    ->select('id', 'user_id', 'membership_type', 'shares')
                    ->get();
                $reportTitle = 'Total Shares per Membership';
                break;

            case 'membership_activity':
                $data = ShareTransaction::with('membership.user')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                $reportTitle = 'Membership Activity Report';
                break;

            default:
                Toastr::error('Invalid report type selected.');
                return redirect()->back();
        }

        return view('admin-views.memberships.reports_result', compact('reportTitle', 'data', 'reportType', 'startDate', 'endDate'));
    }

    /**
     * Export reports to Excel or PDF.
     */
    public function exportReport(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'report_type' => 'required|in:total_shares,membership_activity',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'format'      => 'required|in:excel,pdf',
        ]);

        $reportType = $validatedData['report_type'];
        $startDate = Carbon::parse($validatedData['start_date'])->startOfDay();
        $endDate = Carbon::parse($validatedData['end_date'])->endOfDay();
        $format = $validatedData['format'];

        switch ($reportType) {
            case 'total_shares':
                $data = Membership::with('user')
                    ->select('id', 'user_id', 'membership_type', 'shares')
                    ->get();
                $reportTitle = 'Total Shares per Membership';
                $view = 'admin-views.memberships.reports_total_shares';
                break;

            case 'membership_activity':
                $data = ShareTransaction::with('membership.user')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                $reportTitle = 'Membership Activity Report';
                $view = 'admin-views.memberships.reports_membership_activity';
                break;

            default:
                Toastr::error('Invalid report type selected.');
                return redirect()->back();
        }

        if ($format === 'excel') {
            // Export to Excel using Maatwebsite\Excel
            return Excel::download(new MembershipsExport($data, $reportTitle), $reportTitle . '.xlsx');
        } elseif ($format === 'pdf') {
            // Export to PDF using Dompdf
            $pdf = PDF::loadView($view, compact('data', 'reportTitle', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($reportTitle . '.pdf');
        } else {
            Toastr::error('Invalid format selected.');
            return redirect()->back();
        }
    }
}
