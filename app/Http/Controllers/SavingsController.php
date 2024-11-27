<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\Client;
use App\Models\User;
use App\Models\AccountType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;
use PDF;
use App\Notifications\DepositNotification;
use App\Notifications\WithdrawalNotification;
use App\Notifications\LowBalanceAlert;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\TotalDepositsExport;
use Maatwebsite\Excel\Facades\Excel;

class SavingsController extends Controller
{
    /**
     * Display a listing of the savings accounts.
     */
    public function index()
{
    $pageTitle = 'All Savings Accounts';

    // Use with() to load related models to prevent null errors
    $savingsAccounts = SavingsAccount::with(['client', 'agent', 'accountType'])
        ->paginate(20);

    return view('admin-views.Savings.index', compact('pageTitle', 'savingsAccounts'));
}

    /**
     * Show the form for creating a new savings account.
     */
    public function create()
    {
        $pageTitle = 'Create New Savings Account';
        $clients = Client::all();
        $agents = User::all();
        $accountTypes = AccountType::all(); // Fetch account types

        return view('admin-views.Savings.create', compact('pageTitle', 'clients', 'agents', 'accountTypes'));
    }

    /**
     * Store a newly created savings account in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'agent_id' => 'nullable|exists:users,id',
            'initial_deposit' => 'required|numeric|min:0',
            'account_type_id' => 'required|exists:account_types,id',
        ]);

        // Generate a unique account number
        $accountNumber = $this->generateUniqueAccountNumber();

        // Fetch the selected account type
        $accountType = AccountType::findOrFail($validatedData['account_type_id']);

        // Create the savings account
        $savingsAccount = SavingsAccount::create([
            'client_id' => $validatedData['client_id'],
            'agent_id' => $validatedData['agent_id'],
            'account_number' => $accountNumber,
            'balance' => $validatedData['initial_deposit'],
            'account_type_id' => $validatedData['account_type_id'],
        ]);

        // Record the initial deposit as a transaction
        SavingsTransaction::create([
            'savings_account_id' => $savingsAccount->id,
            'type' => 'deposit',
            'amount' => $validatedData['initial_deposit'],
            'description' => 'Initial Deposit',
        ]);

        // Dispatch Deposit Notification if initial deposit > 0
        if ($validatedData['initial_deposit'] > 0) {
            $savingsAccount->client->notify(new DepositNotification($savingsAccount, SavingsTransaction::latest()->first()));
        }

        Toastr::success('Savings account created successfully.');

        return redirect()->route('admin.savings.index');
    }

    /**
     * Display the specified savings account.
     */
    public function show($id)
    {
        $savingsAccount = SavingsAccount::with(['client', 'agent', 'accountType', 'transactions'])->findOrFail($id);

        return view('admin-views.Savings.show', compact('savingsAccount'));
    }

    /**
     * Show the form for editing the specified savings account.
     */
    public function edit($id)
    {
        $pageTitle = 'Edit Savings Account';
        $savingsAccount = SavingsAccount::findOrFail($id);
        $clients = Client::all();
        $agents = User::all();
        $accountTypes = AccountType::all(); // Fetch account types

        return view('admin-views.Savings.edit', compact('pageTitle', 'savingsAccount', 'clients', 'agents', 'accountTypes'));
    }

    /**
     * Update the specified savings account in storage.
     */
    public function update(Request $request, $id)
    {
        $savingsAccount = SavingsAccount::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'agent_id' => 'nullable|exists:users,id',
            'account_type_id' => 'required|exists:account_types,id',
        ]);

        // Update the savings account
        $savingsAccount->update([
            'client_id' => $validatedData['client_id'],
            'agent_id' => $validatedData['agent_id'],
            'account_type_id' => $validatedData['account_type_id'],
        ]);

        Toastr::success('Savings account updated successfully.');

        return redirect()->route('admin.savings.index');
    }

    /**
     * Remove the specified savings account from storage.
     */
    public function destroy($id)
    {
        try {
            $savingsAccount = SavingsAccount::findOrFail($id);

            // Delete all transactions associated with this savings account
            SavingsTransaction::where('savings_account_id', $savingsAccount->id)->delete();

            // Delete the savings account
            $savingsAccount->delete();

            Toastr::success('Savings account and its transactions deleted successfully.');

            return back();
        } catch (\Exception $e) {
            Toastr::error('Failed to delete the savings account. Please try again.');
            return back();
        }
    }

    /**
     * Show the form for depositing funds into the savings account.
     */
    public function depositForm($id)
    {
        $savingsAccount = SavingsAccount::findOrFail($id);
        $pageTitle = 'Deposit Funds into ' . $savingsAccount->account_number;

        return view('admin-views.Savings.deposit', compact('savingsAccount', 'pageTitle'));
    }

    /**
     * Handle the deposit action.
     */
    public function deposit(Request $request, $id)
    {
        $savingsAccount = SavingsAccount::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        // Update the savings account balance
        $savingsAccount->balance += $validatedData['amount'];
        $savingsAccount->save();

        // Record the deposit transaction
        $transaction = SavingsTransaction::create([
            'savings_account_id' => $savingsAccount->id,
            'type' => 'deposit',
            'amount' => $validatedData['amount'],
            'description' => $validatedData['description'] ?? 'Deposit',
        ]);

        // Dispatch Deposit Notification
        $savingsAccount->client->notify(new DepositNotification($savingsAccount, $transaction));

        Toastr::success('Funds deposited successfully.');

        return redirect()->route('admin.savings.show', $savingsAccount->id);
    }

    /**
     * Show the form for withdrawing funds from the savings account.
     */
    public function withdrawForm($id)
    {
        $savingsAccount = SavingsAccount::findOrFail($id);
        $pageTitle = 'Withdraw Funds from ' . $savingsAccount->account_number;

        return view('admin-views.Savings.withdraw', compact('savingsAccount', 'pageTitle'));
    }

    /**
     * Handle the withdrawal action.
     */
    public function withdraw(Request $request, $id)
    {
        $savingsAccount = SavingsAccount::findOrFail($id);

        // Define minimum balance based on account type or set a default
        $minimumBalance = 100; // Example default, adjust as needed

        // Fetch account type to get specific minimum balance if defined
        $accountType = $savingsAccount->accountType;
        if ($accountType->min_balance !== null) {
            $minimumBalance = $accountType->min_balance;
        }

        // Validate the request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . ($savingsAccount->balance - $minimumBalance),
            'description' => 'nullable|string|max:255',
        ], [
            'amount.max' => 'Withdrawal would reduce balance below the minimum required of $' . $minimumBalance . '.',
        ]);

        // Update the savings account balance
        $savingsAccount->balance -= $validatedData['amount'];
        $savingsAccount->save();

        // Record the withdrawal transaction
        $transaction = SavingsTransaction::create([
            'savings_account_id' => $savingsAccount->id,
            'type' => 'withdrawal',
            'amount' => $validatedData['amount'],
            'description' => $validatedData['description'] ?? 'Withdrawal',
        ]);

        // Dispatch Withdrawal Notification
        $savingsAccount->client->notify(new WithdrawalNotification($savingsAccount, $transaction));

        // Check for Low Balance and dispatch alert if necessary
        if ($savingsAccount->balance < $minimumBalance) {
            $savingsAccount->client->notify(new LowBalanceAlert($savingsAccount));
        }

        Toastr::success('Funds withdrawn successfully.');

        return redirect()->route('admin.savings.show', $savingsAccount->id);
    }

    /**
     * Generate and download a transaction receipt as PDF.
     *
     * @param  SavingsAccount  $savings
     * @param  SavingsTransaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function printTransactionReceiptPdf(SavingsAccount $savings, SavingsTransaction $transaction)
    {
        // Ensure the transaction belongs to the savings account
        if ($transaction->savings_account_id !== $savings->id) {
            Toastr::error('Transaction does not belong to this savings account.');
            return redirect()->route('admin.savings.show', $savings->id);
        }

        // Load related client and agent data if not already loaded
        $savings->load(['client', 'agent', 'accountType']);

        // Pass data to the receipt view
        $data = [
            'savings' => $savings,
            'transaction' => $transaction,
        ];

        // Load the receipt view and generate PDF
        $pdf = PDF::loadView('admin-views.Savings.receipt_pdf', $data)
                   ->setPaper('a4', 'portrait');

        // Define a filename
        $filename = 'Receipt_' . $transaction->id . '.pdf';

        // Return the generated PDF for download
        return $pdf->download($filename);
    }

    /**
     * Render and display a transaction receipt optimized for thermal printing.
     *
     * @param  SavingsAccount  $savings
     * @param  SavingsTransaction  $transaction
     * @return \Illuminate\View\View
     */
    public function printTransactionReceiptThermal(SavingsAccount $savings, SavingsTransaction $transaction)
    {
        // Ensure the transaction belongs to the savings account
        if ($transaction->savings_account_id !== $savings->id) {
            Toastr::error('Transaction does not belong to this savings account.');
            return redirect()->route('admin.savings.show', $savings->id);
        }

        // Load related client and agent data if not already loaded
        $savings->load(['client', 'agent', 'accountType']);

        // Pass data to the receipt view
        $data = [
            'savings' => $savings,
            'transaction' => $transaction,
        ];

        // Return the thermal receipt view
        return view('admin-views.Savings.receipt_thermal', $data);
    }

    /**
     * Display the reports selection form.
     */
    public function reportsIndex()
    {
        $pageTitle = 'Savings Reports';
        return view('admin-views.Savings.reports_index', compact('pageTitle'));
    }

    /**
     * Generate and display reports based on selected criteria.
     */
    public function generateReports(Request $request)
    {
        // Validate input
        $request->validate([
            'report_type' => 'required|in:total_deposits,total_withdrawals,interest_earned,monthly_growth,top_performing',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $reportType = $request->report_type;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        switch ($reportType) {
            case 'total_deposits':
                $data = SavingsTransaction::where('type', 'deposit')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->select('savings_account_id', DB::raw('SUM(amount) as total_deposited'))
                            ->groupBy('savings_account_id')
                            ->with('savingsAccount.client')
                            ->get();
                $reportTitle = 'Total Deposits per Account';
                break;

            case 'total_withdrawals':
                $data = SavingsTransaction::where('type', 'withdrawal')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->select('savings_account_id', DB::raw('SUM(amount) as total_withdrawn'))
                            ->groupBy('savings_account_id')
                            ->with('savingsAccount.client')
                            ->get();
                $reportTitle = 'Total Withdrawals per Account';
                break;

            case 'interest_earned':
                // Calculate interest based on account types and transactions within the period
                $data = AccountType::with(['savingsAccounts' => function($query) use ($startDate, $endDate) {
                    $query->whereHas('transactions', function($q) use ($startDate, $endDate) {
                        $q->where('type', 'deposit')->whereBetween('created_at', [$startDate, $endDate]);
                    });
                }])->get();

                // Calculate total interest earned per account type
                $data = $data->map(function($type) use ($startDate, $endDate) {
                    $totalInterest = 0;
                    foreach ($type->savingsAccounts as $account) {
                        // Simplified interest calculation: amount * rate
                        $totalInterest += $account->balance * ($type->interest_rate / 100);
                    }
                    return [
                        'name' => $type->name,
                        'description' => $type->description,
                        'interest_rate' => $type->interest_rate,
                        'total_interest_earned' => $totalInterest,
                    ];
                });
                $reportTitle = 'Interest Earned per Account Type';
                break;

            case 'monthly_growth':
                $data = SavingsAccount::with(['transactions' => function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->get()
                ->map(function($account) {
                    $totalDeposits = $account->transactions->where('type', 'deposit')->sum('amount');
                    $totalWithdrawals = $account->transactions->where('type', 'withdrawal')->sum('amount');
                    $netGrowth = $totalDeposits - $totalWithdrawals;
                    return [
                        'account_number' => $account->account_number,
                        'client_name' => $account->client->name,
                        'total_deposits' => $totalDeposits,
                        'total_withdrawals' => $totalWithdrawals,
                        'net_growth' => $netGrowth,
                    ];
                });
                $reportTitle = 'Monthly Savings Growth';
                break;

            case 'top_performing':
                $data = SavingsAccount::withCount(['transactions as total_deposited' => function($query) use ($startDate, $endDate) {
                    $query->where('type', 'deposit')->whereBetween('created_at', [$startDate, $endDate]);
                }, 'transactions as total_withdrawn' => function($query) use ($startDate, $endDate) {
                    $query->where('type', 'withdrawal')->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->orderBy('total_deposited', 'desc')
                ->take(10)
                ->get();
                $reportTitle = 'Top Performing Accounts';
                break;

            default:
                return back()->withErrors(['report_type' => 'Invalid report type selected.']);
        }

        return view('admin-views.Savings.reports_result', compact('reportTitle', 'data', 'reportType', 'startDate', 'endDate'));
    }

    /**
     * Export reports to Excel.
     */
    public function exportReport(Request $request)
    {
        // Validate input
        $request->validate([
            'report_type' => 'required|in:total_deposits,total_withdrawals,interest_earned,monthly_growth,top_performing',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:excel,pdf',
        ]);

        $reportType = $request->report_type;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $format = $request->format;

        switch ($reportType) {
            case 'total_deposits':
                $export = new TotalDepositsExport($startDate, $endDate);
                $filename = 'Total_Deposits_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd');
                break;

            // Similarly, create and handle other export classes

            default:
                return back()->withErrors(['report_type' => 'Invalid report type selected.']);
        }

        if ($format === 'excel') {
            return Excel::download($export, $filename . '.xlsx');
        } elseif ($format === 'pdf') {
            // For PDF export, you can generate a view and stream it as PDF
            // Implement as needed
            Toastr::error('PDF export not implemented yet.');
            return back();
        }
    }

    /**
     * Generate a unique account number for the savings account.
     */
    private function generateUniqueAccountNumber()
    {
        do {
            // Example: SANV followed by 10 random uppercase letters and numbers
            $accountNumber = 'SANV' . strtoupper(Str::random(10));
        } while (SavingsAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }
}
