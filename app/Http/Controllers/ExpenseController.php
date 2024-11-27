<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Cashflow;
use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\PaymentTransaction;
use App\Models\Client;
use App\Models\User;
use App\Models\ExcessFund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // For auth()->id()

class ExpenseController extends Controller
{
    /**
     * Display a form to add a new Actual Cash record.
     */
    public function createActualSafeCash()
    {
        return view('admin-views.cashflow.actualCash');
    }

    /**
     * Store a new Actual Cash record in the database.
     */
    public function storeActualSafeCash(Request $request)
    {
        // Validate input data
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        // Insert data into the actual_cash table
        DB::table('actual_cash')->insert([
            'user_id' => auth()->id(),
            'amount' => $validated['amount'],
            'date_added' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.expense.expenses')->with('success', 'Actual cash record added successfully.');
    }

    /**
     * Display a form to edit an existing Actual Cash record.
     */
    public function editActualSafeCash($id)
    {
        // Retrieve the actual cash record by ID
        $actualCash = DB::table('actual_cash')->where('id', $id)->first();

        // Check if the record exists
        if (!$actualCash) {
            return redirect()->route('admin.expense.expenses')->with('error', 'Actual cash record not found.');
        }

        // Pass the record to the view
        return view('admin-views.cashflow.editActualCash', compact('actualCash'));
    }

    /**
     * Update the specified Actual Cash record in the database.
     */
    public function updateActualSafeCash(Request $request, $id)
    {
        // Validate input data
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        // Update the actual cash record
        $updated = DB::table('actual_cash')
            ->where('id', $id)
            ->update([
                'amount' => $validated['amount'],
                'updated_at' => now(),
            ]);

        if ($updated) {
            return redirect()->route('admin.expense.expenses')->with('success', 'Actual cash record updated successfully.');
        } else {
            return redirect()->route('admin.expense.expenses')->with('error', 'Failed to update actual cash record.');
        }
    }

    /**
     * Remove the specified Actual Cash record from the database.
     */
    public function destroyActualSafeCash($id)
    {
        $actualCash = DB::table('actual_cash')->where('id', $id)->first();

        if (!$actualCash) {
            return redirect()->route('admin.expense.expenses')->with('error', 'Actual cash record not found.');
        }

        DB::table('actual_cash')->where('id', $id)->delete();

        return redirect()->route('admin.expense.expenses')->with('success', 'Actual cash record deleted successfully.');
    }

    /**
     * Display a listing of all cashflows, expenses, excess funds, and actual cash records.
     */
    public function index()
    {
        $cashflows          = Cashflow::orderBy('created_at', 'desc')->get();
        $expenses           = Expense::with('agent')->orderBy('created_at', 'desc')->get();  // Eager load the agent
        $excessFunds        = ExcessFund::with('client')->orderBy('created_at', 'desc')->get();
        $actualCashRecords  = DB::table('actual_cash')->orderBy('date_added', 'desc')->get();

        return view('admin-views.cashflow.index', compact('cashflows', 'expenses', 'excessFunds', 'actualCashRecords'));
    }

    /**
     * Show the form to create a new expense.
     */
    public function create()
    {
        $agents = User::all();
        return view('admin-views.cashflow.addexpense', compact('agents'));
    }

    /**
     * Show the form to create a new cashflow.
     */
    public function createCashflow()
    {
        return view('admin-views.cashflow.addcashflow');
    }

    /**
     * Store a newly created expense in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0',
            'time'           => 'required|date_format:Y-m-d\TH:i',
            'category'       => 'nullable|string',
            'payment_method' => 'nullable|string',
            'notes'          => 'nullable|string',
            'agent_id'       => 'required|exists:users,id',
        ]);

        Expense::create([
            'transaction_id' => $this->generateTransactionId(),
            'description'    => $request->description,
            'amount'         => $request->amount,
            'time'           => date('Y-m-d H:i:s', strtotime($request->time)),
            'category'       => $request->category,
            'payment_method' => $request->payment_method,
            'notes'          => $request->notes,
            'added_by'       => auth()->id(),
            'status'         => 'completed', // Default status
            'user_id'        => $request->agent_id,
        ]);

        return redirect()->route('admin.expense.expenses')->with('success', 'Expense created successfully.');
    }

    /**
     * Store a newly created cashflow in the database.
     */
    public function storeCashflow(Request $request)
    {
        $request->validate([
            'balance_bf'    => 'required|numeric',
            'capital_added' => 'required|numeric',
            'cash_banked'   => 'required|numeric',
        ]);

        Cashflow::create([
            'balance_bf'     => $request->input('balance_bf'),
            'capital_added'  => $request->input('capital_added'),
            'cash_banked'    => $request->input('cash_banked'),
            'unknown_funds'  => $request->input('unknown_funds'),
        ]);

        return redirect()->route('admin.expense.expenses')->with('success', 'Cash flow created successfully.');
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense)
    {
        return view('admin-views.cashflow.edit', compact('expense'));
    }

    /**
     * Update the specified expense in the database.
     */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0',
            'time'           => 'required|date_format:Y-m-d\TH:i',
            'category'       => 'nullable|string',
            'payment_method' => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $expense->update([
            'description'    => $request->description,
            'amount'         => $request->amount,
            'time'           => date('Y-m-d H:i:s', strtotime($request->time)),
            'category'       => $request->category,
            'payment_method' => $request->payment_method,
            'notes'          => $request->notes,
            'status'         => $expense->is_reversed ? 'reversed' : 'completed',
        ]);

        return redirect()->route('admin.expense.expenses')->with('success', 'Expense updated successfully.');
    }

    /**
     * Reverse an expense entry.
     */
    public function reverse(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        // Ensure the expense is not already reversed
        if ($expense->is_reversed) {
            return redirect()->back()->with('error', 'This expense has already been reversed.');
        }

        // Mark the expense as reversed
        $expense->update([
            'is_reversed'     => true,
            'reversed_at'     => now(),
            'reversed_by'     => auth()->user()->id,
            'reversal_reason' => $request->reversal_reason,
            'status'          => 'reversed',
        ]);

        return redirect()->back()->with('success', 'Expense reversed successfully.');
    }

    /**
     * Remove the specified expense from the database.
     */
    public function destroy($id)
    {
        try {
            // Find the expense by ID
            $expense = Expense::findOrFail($id);

            // Check if the expense has been reversed
            if ($expense->is_reversed) {
                return redirect()->back()->with('error', 'Reversed expenses cannot be deleted.');
            }

            // Delete the expense
            $expense->delete();

            // Optional: Log the deletion (for auditing purposes)
            \Log::info('Expense deleted', ['expense_id' => $expense->id, 'deleted_by' => auth()->user()->id]);

            // Redirect with success message
            return redirect()->route('admin.expense.expenses')->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            // Log the error and return an error response
            \Log::error('Expense deletion failed', ['error' => $e->getMessage(), 'expense_id' => $id]);

            return redirect()->route('admin.expense.expenses')->with('error', 'An error occurred while deleting the expense.');
        }
    }

    /**
     * Handle the deletion of a cashflow entry.
     */
    public function destroyCashflow($id)
    {
        $cashflow = Cashflow::findOrFail($id);
        $cashflow->delete();

        Toastr::success('Cashflow record deleted successfully.');

        return back();
    }

    /**
     * Generate a unique transaction ID for each transaction.
     */
    protected function generateTransactionId()
    {
        do {
            $transactionId = 'abROi' . mt_rand(1000000000, 9999999999);
        } while (Expense::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    // Excess Funds Methods

    /**
     * Display a listing of the excess funds.
     */
    public function indexExcessfund()
    {
        $excessFunds = ExcessFund::with('client')->orderBy('created_at', 'desc')->get();
        return view('admin-views.excessfund.index', compact('excessFunds'));
    }

    /**
     * Show the form to create a new excess fund.
     */
    public function createExcessfund()
    {
        $clients = Client::all();
        return view('admin-views.cashflow.createExcessfund', compact('clients'));
    }

    /**
     * Store a newly created excess fund in the database.
     */
    public function storeExcessfund(Request $request)
    {
        $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'amount'     => 'required|numeric|min:0',
            'date_added' => 'required|date',
            'status'     => 'required|in:unallocated,allocated',
        ]);

        ExcessFund::create([
            'client_id'  => $request->client_id,
            'amount'     => $request->amount,
            'date_added' => $request->date_added,
            'status'     => $request->status,
        ]);

        Toastr::success('Excess fund added successfully.');

        return redirect()->route('admin.expense.expenses')->with('success', 'Excess fund created successfully.');
    }

    /**
     * Show the form for editing an existing excess fund.
     */
    public function editExcessfund(ExcessFund $excessFund)
    {
        $clients = Client::all();
        return view('admin-views.cashflow.editExcessfund', compact('excessFund', 'clients'));
    }

    /**
     * Update the specified excess fund in the database.
     */
    public function updateExcessfund(Request $request, ExcessFund $excessFund)
    {
        $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'amount'     => 'required|numeric|min:0',
            'date_added' => 'required|date',
            'status'     => 'required|in:unallocated,allocated',
        ]);

        $excessFund->update([
            'client_id'  => $request->client_id,
            'amount'     => $request->amount,
            'date_added' => $request->date_added,
            'status'     => $request->status,
        ]);

        Toastr::success('Excess fund updated successfully.');

        return redirect()->route('admin.expense.expenses')->with('success', 'Excess fund updated successfully.');
    }

    /**
     * Remove the specified excess fund from the database.
     */
    public function destroyExcessfund($id)
    {
        try {
            $excessFund = ExcessFund::findOrFail($id);
            $excessFund->delete();

            Toastr::success('Excess fund deleted successfully.');
        } catch (\Exception $e) {
            Toastr::error('An error occurred while deleting the excess fund.');
        }

        return redirect()->route('admin.expense.expenses')->with('success', 'Excess fund deleted successfully.');
    }
}
