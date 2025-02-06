<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanOffer;
use App\Models\LoanPlan;
use App\Models\UserLoan;
use App\Models\ClientFine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // If using Validator

use App\Notifications\FineAddedNotification;
use Illuminate\Support\Facades\DB;

use App\Models\LoanAdvance;


use Illuminate\Support\Facades\Storage;
use App\Models\ClientCollateral;



use App\Http\Requests\StoreClientFineRequest;


use App\Models\LoanPayment;
use Illuminate\Support\Str;


use Illuminate\Http\JsonResponse;
// use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;

use App\Models\User;
use App\Models\Client;
use App\Models\AgentLoan;
use App\Models\Guarantor;

use App\Models\PaymentTransaction;

use App\CentralLogics\Helpers;
use App\Models\LoanPaymentInstallment;

// use Illuminate\Support\Facades\DB;

use Carbon\Carbon;


class LoanOfferController extends Controller
{

    public function loanArrearsIndex()
    {
        // Fetch all active agents or all users who are considered "agents".
        // Adjust the query conditions based on your actual "Agent" logic.
        $agents = User::where('is_active', 1)->get();

        // Return the Blade view, passing agents for the dropdown.
        return view('admin-views.Loans.arrears.index', compact('agents'));
    }
 





    public function removeOrphanedLoans()
        {
            // 1) Find all loans that do NOT have a matching client
            $orphanedLoans = UserLoan::whereDoesntHave('client')->get();

            // 2) Delete them
            foreach ($orphanedLoans as $loan) {
                // Optionally delete installments, payments, etc. first
                // e.g. $loan->installments()->delete();

                // Then delete the loan
                $loan->delete();
                $loan->loanPaymentInstallments()->delete();
                // $loan->loanPayments()->delete();
                
            }

            return back()->with('success', 'Successfully removed orphaned loans with missing clients.');
        }



    public function loanArrearsData(Request $request)
{
    $currentHour = Carbon::now()->hour;

    // Only show running loans (status = 1)
    $query = DB::table('user_loans')
        ->join('clients', 'clients.id', '=', 'user_loans.client_id')
        ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
        ->join('loan_payment_installments', 'loan_payment_installments.loan_id', '=', 'user_loans.id')
        ->where('user_loans.status', 1)
        ->whereIn('loan_payment_installments.status', ['pending','withbalance'])
        ->select(
            'clients.id as client_id',
            'clients.name as client_name',
            'clients.phone as client_phone',
            'clients.credit_balance as client_balance', // add the client's current balance
            'user_loans.id as loan_id',
            'user_loans.trx as loan_transaction_id',
            // We'll group by the earliest overdue date, missed installments, etc.
            DB::raw('COUNT(loan_payment_installments.id) as total_overdue_installments'),
            DB::raw('SUM(loan_payment_installments.install_amount) as total_overdue_amount'),
            DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
        )
        ->groupBy(
            'user_loans.id',
            'clients.id',
            'clients.name',
            'clients.phone',
            'clients.credit_balance',
            'user_loans.trx',
            'agents.f_name',
            'agents.l_name'
        );

    // Overdue logic: after 8 AM, only overdue installments < today
    if ($currentHour >= 8) {
        $query->where('loan_payment_installments.date', '<', Carbon::now()->startOfDay());
    } else {
        $query->where('loan_payment_installments.date', '<', Carbon::now()->startOfDay());
    }

    // ============= FILTERS ==============

    // 1) Agent filter
    if ($request->filled('agent_id') && $request->agent_id !== 'all') {
        $query->where('user_loans.user_id', $request->agent_id);
    }

    // 2) Search filter
    $searchValue = $request->input('search.value');
    if ($searchValue) {
        $query->where(function ($q) use ($searchValue) {
            $q->where('clients.name', 'like', "%{$searchValue}%")
              ->orWhere('clients.phone', 'like', "%{$searchValue}%")
              ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
        });
    }

    // 3) Overdue months
    // If "1 month" -> earliest_overdue_date <= now()->subMonths(1)
    // If "2 months" -> earliest_overdue_date <= now()->subMonths(2)
    // etc.
    if ($request->filled('overdue_months')) {
        $months = (int) $request->overdue_months;
        $cutoffDate = Carbon::now()->subMonths($months)->startOfDay();
        // Because we do grouping, we can use having() or filter after we get results.
        // But let's do a "havingRaw" approach:
        // We want: MIN(installments.date) <= $cutoffDate
        // This is tricky with Query Builder. Let's do a simpler approach:
        $query->havingRaw("MIN(loan_payment_installments.date) <= ?", [$cutoffDate]);
    }

    // ========== GET RESULTS FOR DATATABLES ==========

    // Step 1: get all results from the DB
    $allResults = $query->get();

    // Step 2: DataTables normally does ordering in the DB,
    // but because we have grouping and some custom logic,
    // we can do an in-memory sort. For large data sets, consider advanced solutions.
    $order   = $request->input('order', []);
    $columns = $request->input('columns', []);
    if (!empty($order)) {
        foreach ($order as $o) {
            $columnIndex = $o['column'];
            $columnName  = $columns[$columnIndex]['data'] ?? 'client_name';
            $dir         = $o['dir'] === 'asc' ? 'asc' : 'desc';

            if ($dir === 'asc') {
                $allResults = $allResults->sortBy($columnName)->values();
            } else {
                $allResults = $allResults->sortByDesc($columnName)->values();
            }
        }
    } else {
        // Default sort by client name asc
        $allResults = $allResults->sortBy('client_name')->values();
    }

    $recordsTotal = $allResults->count();

    // Step 3: pagination
    $start  = (int) $request->input('start', 0);
    $length = (int) $request->input('length', 20);
    $pagedResults = $allResults->slice($start, $length)->values();

    // ========== SUMMARY DATA ==========
    // We want to show in the blade: 
    //   - number of unique clients
    //   - total overdue amount
    $uniqueClients           = $allResults->unique('client_id')->count();
    $totalOverdueAmount      = $allResults->sum('total_overdue_amount');

    // Return DataTables JSON plus the summary
    return response()->json([
        'draw'            => (int) $request->input('draw'),
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data'            => $pagedResults,
        'summary'         => [
            'client_count'         => $uniqueClients,
            'total_overdue_amount' => $totalOverdueAmount
        ]
    ]);
}

    
    public function loanArrearsDataXX(Request $request)
    {
        $currentHour = Carbon::now()->hour;
        
        // If before 8 AM, consider all running loans as in arrears:
        // otherwise, only those with overdue installments
        $query = DB::table('user_loans')
            ->join('clients', 'clients.id', '=', 'user_loans.client_id')
            ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
            ->where('user_loans.status', 1) // Running loans
            ->select(
                'clients.id as client_id',
                'clients.name as client_name',
                'clients.phone as client_phone',
                'user_loans.id as loan_id',
                'user_loans.trx as loan_transaction_id',
                'agents.f_name as agent_first_name',
                'agents.l_name as agent_last_name'
            );
    
        if ($currentHour >= 8) {
            // After 8 AM: Show only loans with overdue installments
            $query->join('loan_payment_installments', 'loan_payment_installments.loan_id', '=', 'user_loans.id')
                ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                ->where('loan_payment_installments.date', '<', Carbon::now()->startOfDay())
                ->addSelect(
                    DB::raw('COUNT(loan_payment_installments.id) as total_overdue_installments'),
                    DB::raw('SUM(loan_payment_installments.install_amount) as total_overdue_amount'),
                    DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
                )
                ->groupBy('user_loans.id', 'clients.id', 'clients.name', 'clients.phone', 'user_loans.trx', 'agents.f_name', 'agents.l_name');
        } else {
            // Before 8 AM: Assume all running loans are in arrears
            // In a real scenario, you may want to still require installments to be due.
            // For simplicity, let's assume total_overdue_installments = total_installment - installments paid (if you store that).
            // Here, we might need a different logic: 
            // Let's say all installments up to yesterday are overdue:
            $query->join('loan_payment_installments', 'loan_payment_installments.loan_id', '=', 'user_loans.id')
                ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                ->where('loan_payment_installments.date', '<', Carbon::now()->startOfDay())
                ->addSelect(
                    DB::raw('COUNT(loan_payment_installments.id) as total_overdue_installments'),
                    DB::raw('SUM(loan_payment_installments.install_amount) as total_overdue_amount'),
                    DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
                )
                ->groupBy('user_loans.id', 'clients.id', 'clients.name', 'clients.phone', 'user_loans.trx', 'agents.f_name', 'agents.l_name');
        }
    
        // Searching
        $searchValue = $request->input('search.value');
        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('clients.name', 'LIKE', "%{$searchValue}%")
                  ->orWhere('clients.phone', 'LIKE', "%{$searchValue}%")
                  ->orWhere('user_loans.trx', 'LIKE', "%{$searchValue}%");
            });
        }
    
        // Count total records (before filtering)
        $recordsTotal = $query->count();
    
        // Ordering
        $order = $request->input('order', []);
        $columns = $request->input('columns', []);
        if (!empty($order)) {
            foreach ($order as $o) {
                $columnIndex = $o['column'];
                $columnName = $columns[$columnIndex]['data'] ?? 'client_name';
                $dir = $o['dir'] == 'asc' ? 'asc' : 'desc';
                $query->orderBy($columnName, $dir);
            }
        } else {
            $query->orderBy('client_name', 'asc');
        }
    
        // Pagination (display 20 per page)
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        if ($length != -1) {
            $query->skip($start)->take($length);
        }
    
        $arrears = $query->get();
    
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $arrears
        ]);
    }
    

   public function deleteLoanNow($id)
    {
        try {
            // Find the loan by ID
            $loan = UserLoan::findOrFail($id);
    
            // Delete all loan installments associated with this loan
            LoanPaymentInstallment::where('loan_id', $loan->id)->delete();
    
            // Delete the loan itself
            $loan->delete();
    
            // Redirect back with success message
            return back()->with('success', 'Loan and its installments deleted successfully.');
        } catch (\Exception $e) {
            // Handle any errors (e.g., loan not found)
            return back()->withErrors(['error' => 'Failed to delete the loan. Please try again.']);
        }
    }
    
    
  
    public function reversePayment($id)
    {
        // Find the payment by its ID
        $payment = LoanPayment::findOrFail($id);
    
        // Check if the payment is already reversed
        if ($payment->is_reversed) {
            return redirect()->back()->withErrors(['error' => 'This payment has already been reversed.']);
        }
    
        // Find the client and update their credit balance
        $client = $payment->client;
        if ($client) {
            // Decrease the credit balance by the payment amount
            $client->credit_balance += $payment->amount;
            $client->save();
        }
    
        // Mark the payment as reversed
        $payment->is_reversed = true;
        $payment->save();
    
        // Update the installment and loan statuses accordingly
        $loan = $payment->loan;
        $loanInstallment = $loan->loanPaymentInstallments()
                                ->where('status', 'paid')
                                ->orderBy('date', 'desc')
                                ->first();
        
        if ($loanInstallment) {
            $loanInstallment->status = 'pending';
            $loanInstallment->save();
        }
    
        // Adjust the loan's paid amount
        $loan->paid_amount -= $payment->amount;
        $loan->save();
    
        return redirect()->back()->with('success', 'Payment has been reversed successfully.');
    }


    // client loan payment history api
    public function clientLoanspayHistory(Request $request): JsonResponse
    {
        try {
            $clientloanspays = LoanPayment::where('client_id', $request->id)->get();
            return response()->json(response_formatter(DEFAULT_200, $clientloanspays, null), 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan payment history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    // clients with running loans api
    public function getAgentsClientsWithRunningLoans($agentId)
        {
            // Fetch all clients assigned to this agent with running loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 1) // Adjust 'running' based on your actual status
                ->get();
        
            // Count the total number of clients with running loans
            $totalClients = $clients->count();
        
            // Check if clients with active loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No clients with running loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }
        
            return response()->json([
                'status' => true,
                'data' => $clients,
                'total_clients' => $totalClients
            ]);
        }


   
        // clients with pending loans api
    public function getAgentsClientsWithPendingLoans($agentId)
        {
            // Fetch all clients assigned to this agent with running loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 0) // Adjust 'running' based on your actual status
                ->get();
        
            // Count the total number of clients with running loans
            $totalClients = $clients->count();
        
            // Check if clients with active loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No clients with pending loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }
        
            return response()->json([
                'status' => true,
                'data' => $clients,
                'total_clients' => $totalClients
            ]);
        }
        
        
        
        
        
           // clients with paid loans api
    public function getAgentsClientsWithPaidLoans($agentId)
        {
            // Fetch all clients assigned to this agent with running loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 2) // Adjust 'running' based on your actual status
                ->get();
        
            // Count the total number of clients with running loans
            $totalClients = $clients->count();
        
            // Check if clients with active loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No clients with running loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }
        
            return response()->json([
                'status' => true,
                'data' => $clients,
                'total_clients' => $totalClients
            ]);
        }
    
    // delete loan
    public function deleteLoan($id)
        {
            // Find the loan by ID
            $loan = UserLoan::findOrFail($id);
        
            // Delete all loan installments associated with this loan
            LoanPaymentInstallment::where('loan_id', $loan->id)->delete();
        
            // Delete the loan itself
            $loan->delete();
        
            // Redirect back with success message
            return back()->with('success', 'Loan and its installments deleted successfully.');
        }

        public function addClientLoan($id)
        {
            // Fetch the necessary data
            $client = Client::with('guarantors')->find($id); // Load client with related guarantors
            $loanPlans = LoanPlan::all();
        
            // Check if client or loanPlans is null and handle appropriately
            if (!$client || !$loanPlans) {
                return redirect()->back()->withErrors(['message' => 'Invalid Client or Loan Plans']);
            }
        
            // Fetch agents who manage clients
            $agents = User::join('clients', 'users.id', '=', 'clients.added_by')
                ->select(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    \DB::raw('COUNT(clients.id) as client_count'),
                    \DB::raw('SUM(clients.credit_balance) as total_money_out')
                )
                ->groupBy('users.id', 'users.f_name', 'users.l_name')
                ->get();
        
            // Return the view with the data
            return view('admin-views.Loans.addClientLoan', compact('client', 'loanPlans', 'agents'));
        }
    
    public function addClientLoanNov($id)
        {
            // Fetch the necessary data
             $client = Client::find($id); // Assuming $loan has a client_id
            
            $loanPlans = LoanPlan::all();
        
            // Check if loan, client, or loanPlan is null and handle appropriately
            if (!$client || !$loanPlans) {
                return redirect()->back()->withErrors(['message' => 'Invalid Loan, Client, or Plan ID']);
            }
            
            $agents = User::join('clients', 'users.id', '=', 'clients.added_by')
                    ->select('users.id', 'users.f_name', 'users.l_name', 
                             \DB::raw('COUNT(clients.id) as client_count'),
                             \DB::raw('SUM(clients.credit_balance) as total_money_out'))
                    ->groupBy('users.id', 'users.f_name', 'users.l_name')
                    ->get();
                    
        
            // Return the view with the data
            return view('admin-views.Loans.addClientLoan', compact( 'client', 'loanPlans', 'agents'));
        }
        

// pay admin loan
    public function adminPayingLoan($id)
    {
        // Fetch client details
        $client = Client::find($id);
    
        if (!$client) {
            return response()->json(['errors' => 'Client not found'], 404);
        }
    
        // Find the running loan associated with this client
        $loan = UserLoan::where('client_id', $id)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->first();
    
        if (!$loan) {
            return response()->json(['errors' => 'No running loans found for the client'], 404);
        }
    
        // Fetch the agent associated with the loan 
        $agent = User::find($loan->user_id);
    
        // Fetch all payment slots (installments) associated with this loan
        $loanInstallments = LoanPaymentInstallment::where('loan_id', $loan->id)->get();
    
        // Return the view with the fetched data admin-views.Loans.pay-loan?
        return view('admin-views.Loans.pay-loan', compact('client', 'loanInstallments', 'loan', 'agent'));
    }


// agent pay loan

    
 


public function payLoan11Nov(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    $clientId = $request->input('client_id');
    $paymentAmount = $request->input('amount');
    $remainingPayment = $paymentAmount;

    // Start a database transaction to ensure atomicity
    DB::beginTransaction();
    
    try {
        $client = Client::findOrFail($clientId);

        // Fetch all pending loans for the client
        $loans = UserLoan::where('client_id', $clientId)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->get();

        foreach ($loans as $loan) {
            $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
                ->where('status', 'pending')
                ->orderBy('date', 'asc')
                ->get();

            foreach ($installments as $installment) {
                $installmentTotal = $installment->install_amount + $installment->installment_balance;

                if ($remainingPayment >= $installmentTotal) {
                    $installment->status = 'paid';
                    $installment->installment_balance = 0;
                    $remainingPayment -= $installmentTotal;
                } else {
                    $installment->status = 'withbalance';
                    $installment->installment_balance = $installmentTotal - $remainingPayment;
                    $remainingPayment = 0;
                }

                $installment->save();

                // Update the loan's paid amount
                $loan->paid_amount += $installmentTotal - $installment->installment_balance;

                if ($loan->paid_amount >= $loan->final_amount) {
                    $loan->status = 2; // Fully Paid
                }

                $loan->save();

                // Exit the loop if all the payment amount is exhausted
                if ($remainingPayment <= 0) {
                    break;
                }
            }

            if ($remainingPayment <= 0) {
                break;
            }
        }

        // Deduct from client's credit balance
        $client->credit_balance -= $paymentAmount;
        $client->save();

        // Commit transaction
        DB::commit();

        return response()->json([
            'response_code' => 'default_200',
            'message' => 'Payment processed successfully',
            'remaining_balance' => $client->credit_balance,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['errors' => 'Error processing payment: ' . $e->getMessage()], 500);
    }
}


public function payLoan11Nov2(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount'    => 'required|numeric|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    $clientId       = $request->input('client_id');
    $paymentAmount  = $request->input('amount');
    $remainingPayment = $paymentAmount;

    // Start a database transaction to ensure atomicity
    DB::beginTransaction();

    try {
        $client = Client::findOrFail($clientId);

        // Fetch all pending loans for the client
        $loans = UserLoan::where('client_id', $clientId)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->get();

        $totalOwed = $loans->sum(function ($loan) {
            return max($loan->final_amount - $loan->paid_amount, 0);
        });

        // If paymentAmount exceeds total owed, calculate excess
        $excessPayment  = max($paymentAmount - $totalOwed, 0);
        $paymentToProcess = $paymentAmount - $excessPayment;

        // Process payment towards loans
        foreach ($loans as $loan) {
            $remainingLoanAmount = $loan->final_amount - $loan->paid_amount;

            if ($remainingLoanAmount <= 0) {
                continue; // Loan is fully paid
            }

            $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
                ->where('status', 'pending')
                ->orderBy('date', 'asc')
                ->get();

            foreach ($installments as $installment) {
                $installmentTotal = $installment->install_amount + $installment->installment_balance;
                $amountToPay = min($remainingPayment, $installmentTotal);

                if ($amountToPay >= $installmentTotal) {
                    $installment->status = 'paid';
                    $installment->installment_balance = 0;
                } else {
                    $installment->status = 'withbalance';
                    $installment->installment_balance = $installmentTotal - $amountToPay;
                }

                $installment->save();

                // Update the loan's paid amount
                $loan->paid_amount = min($loan->paid_amount + $amountToPay, $loan->final_amount);

                if ($loan->paid_amount >= $loan->final_amount) {
                    $loan->status = 2; // Fully Paid
                }

                $loan->save();

                $remainingPayment -= $amountToPay;

                // Exit the loop if all the payment amount is exhausted
                if ($remainingPayment <= 0) {
                    break;
                }
            }

            if ($remainingPayment <= 0) {
                break;
            }
        }

        // Deduct from client's credit balance the amount applied to loans
        $client->credit_balance -= ($paymentAmount - $excessPayment);

        // If there's excess payment, add it to savings_balance
        if ($excessPayment > 0) {
            $client->savings_balance += $excessPayment;
        }

        $client->save();

        // Record the payment in LoanPayment
        LoanPayment::create([
            'loan_id'        => $loan->id ?? null,
            'client_id'      => $clientId,
            'agent_id'       => $loan->user_id ?? null,
            'amount'         => $paymentAmount,
            'credit_balance' => $client->credit_balance,
            'payment_date'   => now(),
            'note'           => $request->input('note') ?? null,
        ]);

        // Commit transaction
        DB::commit();

        return response()->json([
            'response_code'    => 'default_200',
            'message'          => 'Payment processed successfully',
            'remaining_balance'=> $client->credit_balance,
            'savings_balance'  => $client->savings_balance,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['errors' => 'Error processing payment: ' . $e->getMessage()], 500);
    }
}

public function payLoan222222222(Request $request): JsonResponse
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount'    => 'required|numeric|min:1',
        'note'      => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $clientId      = $request->input('client_id');
    $paymentAmount = $request->input('amount');

    // Fetch the client and check their credit balance
    $client = Client::findOrFail($clientId);

    if ($paymentAmount > $client->credit_balance) {
        return response()->json([
            'errors' => 'Payment amount exceeds client\'s credit balance.',
        ], 400);
    }

    // Start a database transaction for atomicity
    DB::beginTransaction();

    try {
        // Fetch all active loans for the client
        $loans = UserLoan::where('client_id', $clientId)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->orderBy('id', 'asc') // Process loans in the order they were taken
            ->get();

        if ($loans->isEmpty()) {
            return response()->json(['errors' => 'No active loans found for this client.'], 404);
        }

        $remainingPayment = $paymentAmount;
        $excessPayment    = 0;



           // add loan advance
        $installmentAmount = $loan->per_installment;
        // $paymentAmount  = $request->input('payment_amount');
        $advanceAmount = $paymentAmount - $installmentAmount;
        $installmentsCovered = floor($advanceAmount / $installmentAmount);
   
   
    
        if ($installmentsCovered > 0) {
            // Create Loan Advance Record
            $loanAdance =    DB::table('loan_advances');

            $loanAdvance = new $loanAdance;
            $loanAdvance->loan_id = $loan->id;
            $loanAdvance->client_id = $client->id;
            $loanAdvance->total_advance_amount = $advanceAmount;
            $loanAdvance->remaining_advance_amount = $advanceAmount;
            $loanAdvance->total_installments = $installmentsCovered;
            $loanAdvance->remaining_installments = $installmentsCovered;
            $loanAdvance->save();

        }
   

        // Calculate total amount owed across all loans
        $totalOwed = $loans->sum(function ($loan) {
            return max($loan->final_amount - $loan->paid_amount, 0);
        });

        // If payment amount exceeds total owed, calculate excess
        if ($paymentAmount > $totalOwed) {
            $excessPayment    = $paymentAmount - $totalOwed;
            $remainingPayment = $totalOwed;
        }

        // Process payment towards loans
        foreach ($loans as $loan) {
            $loanBalance = $loan->final_amount - $loan->paid_amount;

            if ($loanBalance <= 0) {
                continue; // Skip if loan is already fully paid
            }

            $paymentForLoan = min($remainingPayment, $loanBalance);

            // Update loan's paid amount
            $loan->paid_amount += $paymentForLoan;

            // Update loan status if fully paid, with rounding to handle floating-point precision
            if (round($loan->paid_amount, 2) >= round($loan->final_amount, 2)) {
                $loan->status = 2; // Fully Paid
            }

            $loan->save();

            // Apply payment to installments
            $this->applyPaymentToInstallments($loan->id, $paymentForLoan);

            // Check if loan is fully paid after payment
            if ($loan->status == 2) {
                // Mark all remaining installments as 'paid'
                LoanPaymentInstallment::where('loan_id', $loan->id)
                    ->whereIn('status', ['pending', 'withbalance'])
                    ->update([
                        'status' => 'paid',
                        'installment_balance' => 0,
                    ]);
            }

            // Record the payment for this loan
            LoanPayment::create([
                'loan_id'        => $loan->id,
                'client_id'      => $clientId,
                'agent_id'       => $loan->user_id,
                'amount'         => $paymentForLoan,
                'credit_balance' => $client->credit_balance,
                'payment_date'   => now(),
                'note'           => $request->input('note') ?? null,
            ]);

            $remainingPayment -= $paymentForLoan;

            if ($remainingPayment <= 0) {
                break;
            }
        }

     
        // Update client's credit balance
        $client->credit_balance -= ($paymentAmount - $excessPayment);

        // If there's excess payment, add it to the client's savings balance
        if ($excessPayment > 0) {
            $client->savings_balance += $excessPayment;
        }

        $client->save();

        // Commit the transaction
        DB::commit();

        return response()->json([
            'response_code'     => 'default_200',
            'message'           => 'Payment processed successfully.',
            'remaining_balance' => $client->credit_balance,
            'savings_balance'   => $client->savings_balance,
        ], 200);

    } catch (\Exception $e) {
        // Rollback the transaction on error
        DB::rollBack();
        return response()->json(['errors' => 'Error processing payment: ' . $e->getMessage()], 500);
    }
}

public function payLoan(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount'    => 'required|numeric|min:1',
            'note'      => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $clientId      = $request->input('client_id');
        $paymentAmount = $request->input('amount');
        $note          = $request->input('note');

        // Fetch the client and check their credit balance
        $client = Client::findOrFail($clientId);

        if ($paymentAmount > $client->credit_balance) {
            return response()->json([
                'errors' => ['amount' => "Payment amount exceeds client's credit balance."],
            ], 400);
        }

        // Start a database transaction for atomicity
        DB::beginTransaction();

        try {
            // Fetch all active loans for the client, ordered by oldest first
            $loans = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->orderBy('id', 'asc')
                ->lockForUpdate() // Lock the rows for update to prevent race conditions
                ->get();

            if ($loans->isEmpty()) {
                return response()->json(['errors' => ['loan' => 'No active loans found for this client.']], 404);
            }

            $remainingPayment = $paymentAmount;
            $excessPayment    = 0;

            // Initialize total owed across all loans
            $totalOwed = $loans->sum(function ($loan) {
                return max($loan->final_amount - $loan->paid_amount, 0);
            });

            // If payment amount exceeds total owed, calculate excess
            if ($paymentAmount > $totalOwed) {
                $excessPayment    = $paymentAmount - $totalOwed;
                $remainingPayment = $totalOwed;
            }

            // Process payment towards loans
            foreach ($loans as $loan) {
                if ($remainingPayment <= 0) {
                    break;
                }

                $loanBalance = $loan->final_amount - $loan->paid_amount;

                if ($loanBalance <= 0) {
                    continue; // Skip if loan is already fully paid
                }

                // Determine the payment amount for this loan
                $paymentForLoan = min($remainingPayment, $loanBalance);

                // Update loan's paid amount
                $loan->paid_amount += $paymentForLoan;

                // Update loan status if fully paid
                if (round($loan->paid_amount, 2) >= round($loan->final_amount, 2)) {
                    $loan->status = 2; // Fully Paid

                    // Mark all remaining installments as 'paid'
                    LoanPaymentInstallment::where('loan_id', $loan->id)
                        ->whereIn('status', ['pending', 'withbalance'])
                        ->update([
                            'status' => 'paid',
                            'installment_balance' => 0,
                        ]);
                }

                $loan->save();

                // Apply payment to installments
                $this->applyPaymentToInstallments($loan->id, $paymentForLoan);

                // Record the payment for this loan
                LoanPayment::create([
                    'loan_id'        => $loan->id,
                    'client_id'      => $clientId,
                    'agent_id'       => $loan->user_id,
                    'amount'         => $paymentForLoan,
                    'credit_balance' => $client->credit_balance,
                    'payment_date'   => now(),
                    'note'           => $note,
                ]);

                $remainingPayment -= $paymentForLoan;
            }

            // Handle advance payment if there's excess
            if ($excessPayment > 0) {
                // Assuming 'per_installment' is a property of UserLoan
                // and remains consistent across loans
                // Here, we'll use the first loan's 'per_installment' for calculation
                $firstLoan = $loans->first();
                $installmentAmount = $firstLoan->per_installment;
                $installmentsCovered = floor($excessPayment / $installmentAmount);
                $advanceAmount = $installmentsCovered * $installmentAmount;

                if ($installmentsCovered > 0) {
                    // Create Loan Advance Record
                    LoanAdvance::create([
                        'loan_id'                   => $firstLoan->id,
                        'client_id'                 => $client->id,
                        'total_advance_amount'      => $advanceAmount,
                        'remaining_advance_amount'  => $advanceAmount,
                        'total_installments'        => $installmentsCovered,
                        'remaining_installments'    => $installmentsCovered,
                    ]);
                }

                // Update excess payment to be added to savings
                $client->savings_balance += ($excessPayment - $advanceAmount);
            }

            // Update client's credit balance
            $client->credit_balance -= ($paymentAmount - $excessPayment);
            $client->save();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'response_code'     => 'default_200',
                'message'           => 'Payment processed successfully.',
                'remaining_balance' => number_format($client->credit_balance, 2),
                'savings_balance'   => number_format($client->savings_balance, 2),
            ], 200);

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error processing loan payment: ' . $e->getMessage(), [
                'client_id' => $clientId,
                'amount'    => $paymentAmount,
                'note'      => $note,
            ]);

            return response()->json(['errors' => ['server' => 'Error processing payment. Please try again later.']], 500);
        }
    }




    public function topup(Request $request, $clientId)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'amount' => 'required|numeric|min:1',
        'note'   => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Fetch the client
    $client = Client::findOrFail($clientId);

    $paymentAmount = $request->input('amount');

    // Check if the amount being paid is equal to the client's current credit balance
    if (round($paymentAmount, 2) != round($client->credit_balance, 2)) {
        return response()->json([
            'errors' => ['amount' => ['Payment amount must be equal to the client\'s credit balance.']],
        ], 422);
    }

    // Begin transaction
    DB::beginTransaction();

    try {
        // Fetch all active loans for the client
        $loans = UserLoan::where('client_id', $clientId)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->orderBy('id', 'asc')
            ->get();

        if ($loans->isEmpty()) {
            return response()->json(['errors' => ['No active loans found for this client.']], 404);
        }

        // Process each loan
        foreach ($loans as $loan) {
            $loanBalance = $loan->final_amount - $loan->paid_amount;

            if ($loanBalance <= 0) {
                continue; // Skip if loan is already fully paid
            }

            // Update loan's paid amount to final amount (fully paid)
            $loan->paid_amount = $loan->final_amount;
            $loan->status = 2; // Fully Paid
            $loan->save();

            // Mark all remaining installments as 'paid'
            LoanPaymentInstallment::where('loan_id', $loan->id)
                ->whereIn('status', ['pending', 'withbalance'])
                ->update([
                    'status' => 'paid',
                    'installment_balance' => 0,
                ]);

            // Record the payment for this loan
            LoanPayment::create([
                'loan_id'        => $loan->id,
                'client_id'      => $clientId,
                'agent_id'       => $loan->user_id,
                'amount'         => $loanBalance,
                'credit_balance' => 0, // Will be updated after
                'payment_date'   => now(),
                'note'           => $request->input('note') ?? null,
            ]);
        }

        // Clear the client's credit balance
        $client->credit_balance = 0;
        $client->save();

        // Commit transaction
        DB::commit();

        return response()->json([
            'message'           => 'Top-up payment successful.',
            'remaining_balance' => $client->credit_balance,
            // Optionally, include updated loan history or other data
        ], 200);

    } catch (\Exception $e) {
        // Rollback transaction
        DB::rollBack();
        return response()->json(['errors' => ['An error occurred while processing the top-up.']], 500);
    }
}

public function updateLoanPayment(Request $request, $loanId)
{
    $validator = Validator::make($request->all(), [
        'payment_amount' => 'required|numeric|min:1',
        'note'           => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            return redirect()->back()->withErrors($validator->errors());
        }
    }

    DB::beginTransaction();
    try {
        $loan = UserLoan::findOrFail($loanId);
        $client = Client::findOrFail($loan->client_id);

        $paymentAmount = $request->input('payment_amount');
        $note = $request->input('note');

        // Check if Payment Amount Exceeds Client's Credit Balance
        if ($paymentAmount > $client->credit_balance) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['payment_amount' => 'Payment amount exceeds client\'s credit balance.']], 400);
            } else {
                return redirect()->back()->withErrors(['payment_amount' => 'Payment amount exceeds client\'s credit balance.']);
            }
        }

        // Calculate Total Owed for this loan
        $loanBalance = max($loan->final_amount - $loan->paid_amount, 0);

        $amountAppliedToLoan = min($paymentAmount, $loanBalance);
        $excessAmount = $paymentAmount - $amountAppliedToLoan;

        // Update Loan's Paid Amount
        $loan->paid_amount += $amountAppliedToLoan;

        // If fully paid
        if (round($loan->paid_amount, 2) >= round($loan->final_amount, 2)) {
            $loan->status = 2; // Fully Paid
            // Mark all remaining installments as 'paid'
            LoanPaymentInstallment::where('loan_id', $loan->id)
                ->whereIn('status', ['pending', 'withbalance'])
                ->update([
                    'status' => 'paid',
                    'installment_balance' => 0,
                ]);
        }

        $loan->save();

        // Apply Payment to Installments
        $this->applyPaymentToInstallments($loan->id, $amountAppliedToLoan);

        // If there's excess payment, convert it to advance installments
        if ($excessAmount > 0 && $loan->status != 2) {
            $installmentAmount = $loan->per_installment;
            $installmentsCovered = floor($excessAmount / $installmentAmount);
            $advanceAmount = $installmentsCovered * $installmentAmount;

            if ($installmentsCovered > 0) {
                // Check if there's an existing advance record for this loan
                $advance = LoanAdvance::where('loan_id', $loan->id)->first();

                if (!$advance) {
                    $advance = new LoanAdvance();
                    $advance->loan_id = $loan->id;
                    $advance->client_id = $client->id;
                    $advance->total_advance_amount = $advanceAmount;
                    $advance->remaining_advance_amount = $advanceAmount;
                    $advance->total_installments = $installmentsCovered;
                    $advance->remaining_installments = $installmentsCovered;
                } else {
                    // Update existing advance
                    $advance->total_advance_amount += $advanceAmount;
                    $advance->remaining_advance_amount += $advanceAmount;
                    $advance->total_installments += $installmentsCovered;
                    $advance->remaining_installments += $installmentsCovered;
                }

                $advance->save();

                // If there's any leftover after forming full installments, add to savings
                $remainingExcess = $excessAmount - $advanceAmount;
                if ($remainingExcess > 0) {
                    $client->savings_balance += $remainingExcess;
                }
            } else {
                // If no full installment could be formed, add the entire excess to savings
                $client->savings_balance += $excessAmount;
            }

        }

        // Deduct full payment amount from client's credit balance
        $client->credit_balance -= $paymentAmount;
        $client->save();

        // Record the Payment
        LoanPayment::create([
            'loan_id'        => $loan->id,
            'client_id'      => $loan->client_id,
            'agent_id'       => $loan->user_id,
            'credit_balance' => $client->credit_balance,
            'amount'         => $paymentAmount,
            'payment_date'   => now(),
            'note'           => $note,
        ]);

        DB::commit();

        if ($request->ajax()) {
            return response()->json(['message' => 'Payment processed successfully.'], 200);
        } else {
            Toastr::success('Loan payment updated successfully');
            return redirect()->route('admin.loans.show', $loanId)->with('success', 'Payment processed successfully!');
        }

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error updating loan payment: ' . $e->getMessage(), [
            'loan_id' => $loanId,
            'amount'  => $request->input('payment_amount'),
        ]);

        if ($request->ajax()) {
            return response()->json(['errors' => ['server' => 'An error occurred while processing the payment.']], 500);
        } else {
            return redirect()->back()->withErrors(['server' => 'An error occurred while processing the payment.']);
        }
    }
}


public function updateLoanPayment2DEC6(Request $request, $loanId)
{
    // **Validation**
    $validator = Validator::make($request->all(), [
        'payment_amount' => 'required|numeric|min:1',
        'payment_dates'  => 'nullable|string', // Validate as a string
        'note'           => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            return redirect()->back()->withErrors($validator->errors());
        }
    }

    // **Process Payment Dates**
    $paymentDates = [];
    if ($request->has('payment_dates') && !empty($request->input('payment_dates'))) {
        $dates = explode(',', $request->input('payment_dates'));
        foreach ($dates as $date) {
            try {
                $parsedDate = \Carbon\Carbon::createFromFormat('Y-m-d', trim($date));
                $paymentDates[] = $parsedDate->format('Y-m-d');
            } catch (\Exception $e) {
                if ($request->ajax()) {
                    return response()->json(['errors' => ['payment_dates' => 'Invalid date format.']], 422);
                } else {
                    return redirect()->back()->withErrors(['payment_dates' => 'Invalid date format.']);
                }
            }
        }
    }

    // **Start Database Transaction**
    DB::beginTransaction();

    try {
        // **Retrieve Loan and Client**
        $loan = UserLoan::findOrFail($loanId);
        $client = Client::findOrFail($loan->client_id);

        $paymentAmount = $request->input('payment_amount');

        // **Check if Payment Amount Exceeds Client's Credit Balance**
        if ($paymentAmount > $client->credit_balance) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['payment_amount' => 'Payment amount exceeds client\'s credit balance.']], 400);
            } else {
                return redirect()->back()->withErrors(['payment_amount' => 'Payment amount exceeds client\'s credit balance.']);
            }
        }

        // **Calculate Remaining Loan Balance**
        $loanBalance = $loan->final_amount - $loan->paid_amount;

        if ($loanBalance <= 0) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['loan' => 'This loan is already fully paid.']], 400);
            } else {
                return redirect()->back()->withErrors(['loan' => 'This loan is already fully paid.']);
            }
        }

        // **Determine Amount Applied to Loan and Excess**
        $amountAppliedToLoan = min($paymentAmount, $loanBalance);
        $excessAmount = $paymentAmount - $amountAppliedToLoan;

        // **Update Loan's Paid Amount**
        $loan->paid_amount += $amountAppliedToLoan;

        // **Update Loan Status if Fully Paid**
        if (round($loan->paid_amount, 2) >= round($loan->final_amount, 2)) {
            $loan->status = 2; // Fully Paid

            // **Update Installments to 'paid'**
            LoanPaymentInstallment::where('loan_id', $loan->id)
                ->whereIn('status', ['pending', 'withbalance'])
                ->update([
                    'status' => 'paid',
                    'installment_balance' => 0,
                ]);
        }

        $loan->save();

        // **Apply Payment to Installments**
        $this->applyPaymentToInstallments($loan->id, $amountAppliedToLoan);

        // **Update Client's Credit Balance**
        $client->credit_balance -= $paymentAmount; // Deduct the entire payment amount

        // **Handle Excess Amount by Adding to Advances**
        if ($excessAmount > 0) {
            $installmentAmount = $loan->per_installment;
            $installmentsCovered = floor($excessAmount / $installmentAmount);
            $advanceAmount = $installmentsCovered * $installmentAmount;

            if ($installmentsCovered > 0) {
                // **Create Loan Advance Record**
                LoanAdvance::create([
                    'loan_id'                   => $loan->id,
                    'client_id'                 => $client->id,
                    'total_advance_amount'      => $advanceAmount,
                    'remaining_advance_amount'  => $advanceAmount,
                    'total_installments'        => $installmentsCovered,
                    'remaining_installments'    => $installmentsCovered,
                ]);
            }

            // **Handle Any Remaining Excess Amount**
            $remainingExcess = $excessAmount - $advanceAmount;

            if ($remainingExcess > 0) {
                // Optionally, add to savings or handle accordingly
                $client->savings_balance += $remainingExcess;
            }
        }

        $client->save();

        // **Log the Payment Dates**
        if (!empty($paymentDates)) {
            \Log::info('Payment Dates for Loan ID ' . $loanId . ':', $paymentDates);
        } else {
            \Log::info('No payment dates provided for Loan ID ' . $loanId . '.');
        }

        // **Record the Payment**
        LoanPayment::create([
            'loan_id'        => $loan->id,
            'client_id'      => $loan->client_id,
            'agent_id'       => $loan->user_id,
            'credit_balance' => $client->credit_balance,
            'amount'         => $paymentAmount, // Original payment amount
            'payment_date'   => now(),
            'note'           => $request->input('note') ?? null,
        ]);

        // **Commit the Transaction**
        DB::commit();

        // **Return Success Response**
        if ($request->ajax()) {
            return response()->json(['message' => 'Payment processed successfully.'], 200);
        } else {
            Toastr::success('Loan payment updated successfully');
            return redirect()->route('admin.loans.show', $loanId)->with('success', 'Payment processed successfully!');
        }

    } catch (\Exception $e) {
        // **Rollback the Transaction on Error**
        DB::rollBack();

        // **Log the Error**
        \Log::error('Error updating loan payment: ' . $e->getMessage(), [
            'loan_id'   => $loanId,
            'client_id' => $loan->client_id,
            'amount'    => $request->input('payment_amount'),
        ]);

        if ($request->ajax()) {
            return response()->json(['errors' => ['server' => 'An error occurred while processing the payment. Please try again later.']], 500);
        } else {
            return redirect()->back()->withErrors(['server' => 'An error occurred while processing the payment. Please try again later.']);
        }
    }
}

public function loanAdvancesIndex()
{
    // Return the blade view that lists the loan advances.
    // Make sure 'admin-views.Loans.advances.index' is the correct path to your view.
    return view('admin-views.Loans.advances.index');
}

public function listLoanAdvances(Request $request)
{
    $query = DB::table('loan_advances')
        ->join('user_loans', 'user_loans.id', '=', 'loan_advances.loan_id')
        ->join('clients', 'clients.id', '=', 'loan_advances.client_id')
        ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
        ->where('user_loans.status', 1) // Running loans
        ->select(
            'clients.id as client_id',
            'clients.name as client_name',
            'clients.phone as client_phone',
            'user_loans.id as loan_id',
            'user_loans.trx as loan_transaction_id',
            'agents.f_name as agent_first_name',
            'agents.l_name as agent_last_name',
            'loan_advances.total_advance_amount',
            'loan_advances.remaining_advance_amount',
            'loan_advances.total_installments',
            'loan_advances.remaining_installments'
        );

    // Searching
    if ($searchValue = $request->input('search.value')) {
        $query->where(function($q) use ($searchValue) {
            $q->where('clients.name', 'like', "%{$searchValue}%")
              ->orWhere('clients.phone', 'like', "%{$searchValue}%")
              ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
        });
    }

    $recordsTotal = $query->count();

    // Ordering
    $order = $request->input('order', []);
    $columns = $request->input('columns', []);
    if (!empty($order)) {
        foreach ($order as $o) {
            $columnIndex = $o['column'];
            $columnName = $columns[$columnIndex]['data'] ?? 'client_name';
            $dir = $o['dir'] == 'asc' ? 'asc' : 'desc';
            $query->orderBy($columnName, $dir);
        }
    } else {
        $query->orderBy('client_name', 'asc');
    }

    // Pagination: 20 per page
    $start = $request->input('start', 0);
    $length = $request->input('length', 20);
    if ($length != -1) {
        $query->skip($start)->take($length);
    }

    $advances = $query->get();

    return response()->json([
        'draw' => $request->input('draw'),
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $advances
    ]);
}











public function storeClientCollateral(Request $request, $clientId)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string',
        'file'        => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // Adjust file types and size as needed
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        // Begin transaction
        DB::beginTransaction();

        // Fetch the client
        $client = Client::findOrFail($clientId);

        // Handle the file upload
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            // Generate a unique file name
            $filename = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();

            // Store the file securely
            $filePath = $uploadedFile->storeAs('collaterals/' . $client->id, $filename, 'public');

            // Create the collateral record
            $collateral = ClientCollateral::create([
                'client_id'         => $client->id,
                'title'             => $request->input('title'),
                'description'       => $request->input('description'),
                'file_path'         => $filePath,
                'file_type'         => $uploadedFile->getClientMimeType(),
                'mime_type'         => $uploadedFile->getClientMimeType(),
                'original_filename' => $uploadedFile->getClientOriginalName(),
            ]);

            // Commit transaction
            DB::commit();

            return response()->json([
                'message'    => 'Collateral added successfully.',
                'collateral' => $collateral,
            ], 200);
        } else {
            return response()->json(['errors' => ['file' => ['No file uploaded.']]], 422);
        }
    } catch (\Exception $e) {
        // Rollback transaction
        DB::rollBack();

        // Log the error
        Log::error("Failed to add collateral for client ID {$clientId}: " . $e->getMessage(), [
            'client_id'     => $clientId,
            'user_id'       => Auth::id(),
            'error_message' => $e->getMessage(),
            'stack_trace'   => $e->getTraceAsString(),
        ]);

        return response()->json(['errors' => ['An error occurred while adding the collateral.']], 500);
    }
}

public function collateralsList($clientId)
{
    try {
        // Fetch the client
        $client = Client::findOrFail($clientId);

        // Load collaterals
        $collaterals = $client->collaterals()->orderBy('created_at', 'desc')->get();

        // Render the collaterals list partial view
        $collateralsHtml = view('admin-views.clients.partials.collaterals-list', compact('collaterals'))->render();

        return response()->json(['html' => $collateralsHtml], 200);
    } catch (\Exception $e) {
        // Log the error
        Log::error("Failed to fetch collaterals for client ID {$clientId}: " . $e->getMessage(), [
            'client_id'     => $clientId,
            'user_id'       => Auth::id(),
            'error_message' => $e->getMessage(),
            'stack_trace'   => $e->getTraceAsString(),
        ]);

        return response()->json(['errors' => ['An error occurred while fetching the collaterals.']], 500);
    }
}


public function storeClientFine(Request $request, $clientId)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'amount' => 'required|numeric|min:0.01',
        'reason' => 'required|string|max:255',
        'note'   => 'nullable|string|max:500',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Fetch the client
    $client = Client::findOrFail($clientId);

    // Begin transaction
    DB::beginTransaction();

    try {
        // Create the fine
        $fine = ClientFine::create([
            'client_id' => $client->id,
            'added_by'  => 55,
            'amount'    => $request->input('amount'),
            'reason'    => $request->input('reason'),
         ]);

        // Update client's credit balance
        $client->increment('credit_balance', $request->input('amount'));

        // Commit transaction
        DB::commit();

        return response()->json([
            'message'            => 'Fine added successfully.',
            'fine_id'            => $fine->id,
            'new_credit_balance' => $client->credit_balance,
        ], 200);

    } catch (\Exception $e) {
        // Rollback transaction
        DB::rollBack();

        // Log the error for debugging
        Log::error("Failed to add fine to client ID {$clientId}: " . $e->getMessage(), [
            'client_id'     => $clientId,
            'user_id'       => 55,
            'error_message' => $e->getMessage(),
            'stack_trace'   => $e->getTraceAsString(),
        ]);

        return response()->json(['errors' => ['An error occurred while adding the fine.']], 500);
    }
}

    

    public function finesList(Client $client)
    {
        // Load fines with the user who added them
        $client->load('fines.addedBy');

        // Render the fines list partial view
        $finesHtml = view('admin-views.clients.partials.fines-list', compact('client'))->render();

        return response()->json(['html' => $finesHtml], 200);
    }

    // banks the great
public function renewLoan(Request $request, $loanId)
{
    // Validate the incoming request
    $validatedData = $request->validate([
        'client_id' => 'required|exists:clients,id',
    ]);

    $clientId = $validatedData['client_id'];

    // Fetch the client
    $client = Client::findOrFail($clientId);

    // Fetch the loan to renew
    $loan = UserLoan::where('client_id', $clientId)
        ->where('id', $loanId)
        ->where('status', 1) // Assuming status 1 is Running
        ->first();

    if (!$loan) {
        return response()->json(['error' => 'Loan not found or not eligible for renewal.'], 404);
    }

    // Check if loan is already renewed
    if ($loan->is_renewed) {
        return response()->json(['error' => 'Loan has already been renewed.'], 400);
    }

    // Get client's current credit balance
    $currentCreditBalance = $client->credit_balance;

    if ($currentCreditBalance <= 0) {
        return response()->json(['error' => 'Client has insufficient credit balance for renewal.'], 400);
    }

    // Begin a transaction
    DB::beginTransaction();

    try {
        // Update the loan's renewal fields
        $loan->is_renewed = true;
        $loan->renewed_amount = $currentCreditBalance;
        $loan->renewed_date = now()->toDateString();

        // Calculate the new final amount based on renewal logic (e.g., 20% interest)
        $interestRate = 1.2; // 20% interest
        $loan->final_renewed_to_amount = $currentCreditBalance * $interestRate;
        $loan->final_amount = $currentCreditBalance * $interestRate;
        // Optionally, update other fields as necessary
        // For example, resetting paid_amount or updating due_date
        $loan->paid_amount = 0; // Reset paid amount for the renewed loan
        $loan->status = 1; // Keep the status as Running
        $loan->disbursed_at = now();
        $loan->due_date = now()->addDays($loan->installment_interval ?? 30); // Default to 30 days if interval is null

        $loan->save();

        // Create a PaymentTransaction record for the renewal
        PaymentTransaction::create([
            'client_id' => $clientId,
            'loan_id' => $loan->id,
            'agent_id' => $loan->user_id,
            'transaction_id' => $this->generateTransactionId(),
            'payment_type' => 'renewal',
            'amount' => $currentCreditBalance,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Deduct the renewal amount from the client's credit balance
        $client->credit_balance = $currentCreditBalance * $interestRate;
        $client->save();

        // Optionally, create new installments based on the renewed amount
        // This depends on your application logic

        DB::commit();

        return redirect()->back();
        
        // response()->json([
        //     'message' => 'Loan renewed successfully.',
        //     'loan' => $loan,
        //     'client' => $client,
        // ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        // Log the error for debugging
        \Log::error('Loan Renewal Failed: ' . $e->getMessage());

        return response()->json(['error' => 'Failed to renew the loan. Please try again later.'], 500);
    }
}

protected function applyPaymentToInstallments($loanId, $paymentAmount)
{
    $paymentAmountRemaining = $paymentAmount;

    // **Retrieve Pending Installments**
    $pendingInstallments = LoanPaymentInstallment::where('loan_id', $loanId)
        ->whereIn('status', ['pending', 'withbalance'])
        ->orderBy('date', 'asc')
        ->get();

    foreach ($pendingInstallments as $installment) {
        if ($paymentAmountRemaining <= 0) {
            break;
        }

        $installmentBalance = $installment->installment_balance ?? $installment->install_amount;

        if ($paymentAmountRemaining >= $installmentBalance) {
            // **Full Payment**
            $installment->status = 'paid';
            $paymentAmountRemaining -= $installmentBalance;
            $installment->installment_balance = 0;
        } else {
            // **Partial Payment**
            $installment->status = 'withbalance';
            $installment->installment_balance = $installmentBalance - $paymentAmountRemaining;
            $paymentAmountRemaining = 0;
        }

        $installment->save();
    }
}



private function applyPaymentToInstallmentsXX($loanId, $paymentAmount)
{
    $remainingPayment = $paymentAmount;

    // Fetch pending or withbalance installments for the loan, ordered by date
    $installments = LoanPaymentInstallment::where('loan_id', $loanId)
        ->whereIn('status', ['pending', 'withbalance'])
        ->orderBy('date', 'asc')
        ->get();

    foreach ($installments as $installment) {
        // Calculate the total amount due for the installment
        $installmentDue = $installment->install_amount + $installment->installment_balance;

        // Determine how much to pay towards this installment
        $amountToPay = min($remainingPayment, $installmentDue);

        // Update installment status and balance
        if ($amountToPay >= $installmentDue) {
            // Fully paid installment
            $installment->status = 'paid';
            $installment->installment_balance = 0;
        } else {
            // Partially paid installment
            $installment->status = 'withbalance';
            $installment->installment_balance = $installmentDue - $amountToPay;
        }

        $installment->save();

        // Deduct the amount paid towards this installment from the remaining payment
        $remainingPayment -= $amountToPay;

        if ($remainingPayment <= 0) {
            break;
        }
    }
}

 private function applyPaymentToInstallmentsNov($loanId, $paymentAmount)
{
    $remainingPayment = $paymentAmount;

    // Fetch pending or withbalance installments for the loan, ordered by date
    $installments = LoanPaymentInstallment::where('loan_id', $loanId)
        ->whereIn('status', ['pending', 'withbalance'])
        ->orderBy('date', 'asc')
        ->get();

    foreach ($installments as $installment) {
        // Calculate the total amount due for the installment
        $installmentDue = $installment->install_amount + $installment->installment_balance;

        // Determine how much to pay towards this installment
        $amountToPay = min($remainingPayment, $installmentDue);

        // Update installment status and balance
        if ($amountToPay >= $installmentDue) {
            // Fully paid installment
            $installment->status = 'paid';
            $installment->installment_balance = 0;
        } else {
            // Partially paid installment
            $installment->status = 'withbalance';
            $installment->installment_balance = $installmentDue - $amountToPay;
        }

        $installment->save();

        // Deduct the amount paid towards this installment from the remaining payment
        $remainingPayment -= $amountToPay;

        // If there's no remaining payment, exit the loop
        if ($remainingPayment <= 0) {
            break;
        }
    }
}


private function applyPaymentToInstallments11Nov($loanId, $paymentAmount)
{
    $remainingPayment = $paymentAmount;

    // Fetch pending installments for the loan, ordered by date
    $installments = LoanPaymentInstallment::where('loan_id', $loanId)
        ->whereIn('status', ['pending', 'withbalance'])
        ->orderBy('date', 'asc')
        ->get();

    foreach ($installments as $installment) {
        $installmentDue = $installment->install_amount + $installment->installment_balance;

        $amountToPay = min($remainingPayment, $installmentDue);

        // Update installment status and balance
        if ($amountToPay >= $installmentDue) {
            $installment->status = 'paid';
            $installment->installment_balance = 0;
        } else {
            $installment->status = 'withbalance';
            $installment->installment_balance = $installmentDue - $amountToPay;
        }

        $installment->save();

        $remainingPayment -= $amountToPay;

        if ($remainingPayment <= 0) {
            break;
        }
    }
}


public function payLoan11Nov3(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount'    => 'required|numeric|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    $clientId      = $request->input('client_id');
    $paymentAmount = $request->input('amount');

    // Fetch the client and check credit balance
    $client = Client::findOrFail($clientId);

    if ($paymentAmount > $client->credit_balance) {
        return response()->json([
            'errors' => 'Payment amount exceeds client\'s credit balance.',
        ], 400);
    }

    $remainingPayment = $paymentAmount;

    // Start a database transaction to ensure atomicity
    DB::beginTransaction();

    try {
        // Fetch all pending loans for the client
        $loans = UserLoan::where('client_id', $clientId)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->get();

        $totalOwed = $loans->sum(function ($loan) {
            return max($loan->final_amount - $loan->paid_amount, 0);
        });

        // If paymentAmount exceeds total owed, calculate excess
        $excessPayment   = max($paymentAmount - $totalOwed, 0);
        $paymentToProcess = $paymentAmount - $excessPayment;

        // Process payment towards loans
        foreach ($loans as $loan) {
            $remainingLoanAmount = $loan->final_amount - $loan->paid_amount;

            if ($remainingLoanAmount <= 0) {
                continue; // Loan is fully paid
            }

            $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
                ->where('status', 'pending')
                ->orderBy('date', 'asc')
                ->get();

            foreach ($installments as $installment) {
                $installmentTotal = $installment->install_amount + $installment->installment_balance;
                $amountToPay = min($remainingPayment, $installmentTotal);

                if ($amountToPay >= $installmentTotal) {
                    $installment->status = 'paid';
                    $installment->installment_balance = 0;
                } else {
                    $installment->status = 'withbalance';
                    $installment->installment_balance = $installmentTotal - $amountToPay;
                }

                $installment->save();

                // Update the loan's paid amount
                $loan->paid_amount = min($loan->paid_amount + $amountToPay, $loan->final_amount);

                if ($loan->paid_amount >= $loan->final_amount) {
                    $loan->status = 2; // Fully Paid
                }

                $loan->save();

                $remainingPayment -= $amountToPay;

                // Exit the loop if all the payment amount is exhausted
                if ($remainingPayment <= 0) {
                    break;
                }
            }

            if ($remainingPayment <= 0) {
                break;
            }
        }

        // Deduct from client's credit balance the amount applied to loans
        $client->credit_balance -= ($paymentAmount - $excessPayment);

        // If there's excess payment, add it to savings_balance
        if ($excessPayment > 0) {
            $client->savings_balance += $excessPayment;
        }

        $client->save();

        // Record the payment in LoanPayment
        LoanPayment::create([
            'loan_id'        => $loan->id ?? null,
            'client_id'      => $clientId,
            'agent_id'       => $loan->user_id ?? null,
            'amount'         => $paymentAmount,
            'credit_balance' => $client->credit_balance,
            'payment_date'   => now(),
            'note'           => $request->input('note') ?? null,
        ]);

        // Commit transaction
        DB::commit();

        return response()->json([
            'response_code'    => 'default_200',
            'message'          => 'Payment processed successfully',
            'remaining_balance'=> $client->credit_balance,
            'savings_balance'  => $client->savings_balance,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['errors' => 'Error processing payment: ' . $e->getMessage()], 500);
    }
}





    public function updateLoanPayment15Oct(Request $request, $loanId)
    {
        // Validate the request inputs
        $validatedData = $request->validate([
            'payment_amount' => 'required|numeric|min:1',
            'payment_dates' => 'nullable|string', // Validate as a string
            'note' => 'nullable|string|max:255',
        ]);
    
        // If payment_dates is present, convert it to an array
        if ($request->has('payment_dates') && !empty($validatedData['payment_dates'])) {
            $validatedData['payment_dates'] = explode(',', $validatedData['payment_dates']);
            
            // Validate the dates after conversion
            foreach ($validatedData['payment_dates'] as $date) {
                if (!\Carbon\Carbon::createFromFormat('Y-m-d', trim($date))) {
                    return redirect()->back()->withErrors(['payment_dates' => 'Invalid date format.']);
                }
            }
        } else {
            $validatedData['payment_dates'] = [];
        }
    
        // Retrieve the loan and related details
        $loan = UserLoan::findOrFail($loanId);
        $client = Client::find($loan->client_id);
    
        // Calculate the new paid amount and remaining balance
        $newPaidAmount = $loan->paid_amount + $validatedData['payment_amount'];
        $remainingAmount = $loan->final_amount - $newPaidAmount;
    
        // Update the loan payment details
        $loan->paid_amount = $newPaidAmount;
    
        // If the loan is fully paid, update the status
        if ($remainingAmount <= 0) {
            $loan->status = 2; // Fully Paid
        }
    
        // Save the loan
        $loan->save();
    
        // Update client's credit balance
        $client->credit_balance -= $validatedData['payment_amount'];
        $client->save();
    
        // Check and log payment_dates
        if (!empty($validatedData['payment_dates'])) {
            \Log::info('Payment Dates:', $validatedData['payment_dates']);
            $paymentAmountRemaining = $validatedData['payment_amount'];
    
            foreach ($validatedData['payment_dates'] as $paymentDate) {
                $installment = LoanPaymentInstallment::where('loan_id', $loanId)
                    ->where('date', trim($paymentDate))
                    ->first();
    
                if ($installment) {
                    $installmentAmount = $installment->install_amount;
                    $installmentBalance = $installment->installment_balance;
    
                    $totalInstallmentAmount = $installmentAmount + $installmentBalance;
    
                    if ($paymentAmountRemaining >= $totalInstallmentAmount) {
                        $installment->status = 'paid';
                        $installment->installment_balance = 0;
                        $paymentAmountRemaining -= $totalInstallmentAmount;
                    } else {
                        $installment->status = 'withbalance';
                        $installment->installment_balance = $totalInstallmentAmount - $paymentAmountRemaining;
                        $paymentAmountRemaining = 0;
                    }
    
                    $installment->save();
                } else {
                    \Log::warning("Installment not found for loan_id: $loanId and date: $paymentDate");
                }
    
                if ($paymentAmountRemaining <= 0) {
                    break;
                }
            }
        } else {
            \Log::info('No payment dates provided.');
        }
    
        // Create a record for the payment made
        LoanPayment::create([
            'loan_id'       => $loan->id,
            'client_id'     => $loan->client_id,
            'agent_id'      => $loan->user_id,
            'credit_balance'=> $client->credit_balance,
            'amount'        => $validatedData['payment_amount'], // Original payment amount
            'payment_date'  => now(), // Current date/time as the payment record date
            'note'          => $validatedData['note'] ?? null,
        ]);
    
        // Provide feedback to the user
        Toastr::success('Loan payment updated successfully');
    
        // Redirect back to the loan details page
        return redirect()->route('admin.loans.show', $loanId)->with('success', 'Payment processed successfully!');
    }


   


    public function updateLoanPayment10(Request $request, $loanId)
    {
        // dd($request->all());
    
        // Validate the request inputs
        $validatedData = $request->validate([
            'payment_amount' => 'required|numeric|min:1',
            'payment_dates' => 'nullable|string', // Validate as a string
            'note' => 'nullable|string|max:255',
        ]);
    
        // If payment_dates is present, convert it to an array
        if ($request->has('payment_dates') && !empty($validatedData['payment_dates'])) {
            $validatedData['payment_dates'] = explode(',', $validatedData['payment_dates']);
            
            // Validate the dates after conversion
            foreach ($validatedData['payment_dates'] as $date) {
                if (!\Carbon\Carbon::createFromFormat('Y-m-d', trim($date))) {
                    return redirect()->back()->withErrors(['payment_dates' => 'Invalid date format.']);
                }
            }
        } else {
            $validatedData['payment_dates'] = [];
        }
    
        // Retrieve the loan and related details
        $loan = UserLoan::findOrFail($loanId);
        $client = Client::find($loan->client_id);
    
    
        // Calculate the new paid amount and remaining balance
        $newPaidAmount = $loan->paid_amount + $validatedData['payment_amount'];
        $remainingAmount = $loan->final_amount - $newPaidAmount;
    
        // Update the loan payment details
        $loan->paid_amount = $newPaidAmount;
    
        // If the loan is fully paid, update the status
        if ($remainingAmount <= 0) {
            $loan->status = 2; // Fully Paid
        }
    
        // Save the loan
        $loan->save();
        
        
         // Update client's credit balance
        $client->credit_balance -= $validatedData['payment_amount'];
        $client->save();
    
        // Check and log payment_dates
        if (!empty($validatedData['payment_dates'])) {
            \Log::info('Payment Dates:', $validatedData['payment_dates']);
            foreach ($validatedData['payment_dates'] as $paymentDate) {
                $installment = LoanPaymentInstallment::where('loan_id', $loanId)
                    ->where('date', trim($paymentDate))
                    ->first();
                if ($installment) {
                    $installment->status = 'paid';
                    $installment->save();
                } else {
                    \Log::warning("Installment not found for loan_id: $loanId and date: $paymentDate");
                }
            }
        } else {
            \Log::info('No payment dates provided.');
        }
    
        // Create a record for the payment made
        LoanPayment::create([
            'loan_id'       => $loan->id,
            'client_id'     => $loan->client_id,
            'agent_id'      => $loan->user_id,
            'amount'        => $validatedData['payment_amount'], // Original payment amount
            'payment_date'  => now(), // Current date/time as the payment record date
            'note'          => $validatedData['note'] ?? null,
            
        ]);
    
        // Provide feedback to the user
        Toastr::success('Loan payment updated successfully');
    
        // Redirect back to the loan details page
        // return redirect()->route('admin.loans.show', $loanId);
            return redirect()->route('admin.loans.show', $loanId)->with('success', 'Payment processed successfully!');
    
    }



    public function storeClientLoan22(Request $request)
    {
        // dd($request->all());

        // Validate the request data
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'agent_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'installment_interval' => 'required|numeric|min:1',
            'paid_amount' => 'nullable|numeric|min:0',
            'next_installment_date' => 'nullable|date', // Ensure date validation if present
            'note' => 'nullable|string|max:255', // Adding note validation if provided
            'taken_date'  => 'nullable|date',
        ]);
    
        // Calculate per installment and final amount
        $per_installment = ($validatedData['amount'] * 1.2) / $validatedData['installment_interval'];
        $final_amount = $validatedData['amount'] * 1.2;
    
        // Calculate remaining amount
        $remaining_amount = $final_amount - ($validatedData['paid_amount'] ?? 0);
    
        // Create a new UserLoan instance
        $loan = new UserLoan();
        $loan->user_id = $validatedData['agent_id'];
        $loan->plan_id = 8; // Assuming plan_id is static or predefined
        $loan->trx = $this->generateUniqueTrx(); // Generate unique transaction ID
        $loan->amount = $validatedData['amount'];
        $loan->per_installment = $per_installment;
        $loan->installment_interval = $validatedData['installment_interval'];
        $loan->total_installment = $validatedData['installment_interval'];
        $loan->paid_amount = $validatedData['paid_amount'] ?? 0.00;
        $loan->final_amount = $final_amount;
        $loan->loan_taken_date = $validatedData['taken_date'];
        $loan->user_details = $request->user_details ?? null;
        $loan->admin_feedback = null; // Assuming this field is optional
        $loan->status = 0; // Assuming default status is 0
        $loan->next_installment_date = $validatedData['next_installment_date'] ?? null;
        $loan->client_id = $validatedData['client_id']; // Assign the client ID
        $loan->save(); // Save the loan to the database
    
        // Check if paid amount is greater than 0 to create a LoanPayment record
        if ($validatedData['paid_amount'] > 0) {
            // Create a record for the payment made
            LoanPayment::create([
                'loan_id' => $loan->id,
                'client_id' => $validatedData['client_id'],
                'agent_id' => $validatedData['agent_id'],
                'amount' => $validatedData['paid_amount'], // Use the correct paid amount
                'payment_date' => now(), // Current date/time as the payment record date
                'note' => $validatedData['note'] ?? null, // Include optional note if provided
            ]);
        }
    
        // Redirect back with success message
        return redirect()->route('admin.loan-pendingLoans')->with('success', 'Loan added successfully for client ');
    }
    
     public function storeClientLoan(Request $request)
        {
            // Validate the request data
            $validatedData = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'agent_id' => 'required|exists:users,id',
                'plan_id' => 'required|exists:loan_plans,id', // Add validation for loan plan ID
                'amount' => 'required|numeric|min:0',
                'installment_interval' => 'required|numeric|min:1',
                'paid_amount' => 'nullable|numeric|min:0',
                'next_installment_date' => 'nullable|date', // Ensure date validation if present
                'note' => 'nullable|string|max:255', // Adding note validation if provided
                'taken_date' => 'nullable|date',
            ]);
        
            // Fetch the loan plan based on the plan_id
            $loanPlan = LoanPlan::findOrFail($validatedData['plan_id']); // Fetch the loan plan details
        
            // Dynamic calculation based on the loan plan details
            $per_installment = ($validatedData['amount'] * 1.2) / $validatedData['installment_interval'];

            $per_installment = ($validatedData['amount'] * (1 + ($loanPlan->installment_value / 100))) / $validatedData['installment_interval'];
            $final_amount = $validatedData['amount'] * (1 + ($loanPlan->installment_value / 100));
        
            // Calculate the remaining amount after the paid amount
            $remaining_amount = $final_amount - ($validatedData['paid_amount'] ?? 0);
        
            // Create a new UserLoan instance
            $loan = new UserLoan();
            $loan->user_id = $validatedData['agent_id'];
            $loan->plan_id = $loanPlan->id; // Set the plan ID dynamically
            $loan->trx = $this->generateUniqueTrx(); // Generate unique transaction ID
            $loan->amount = $validatedData['amount'];
            $loan->per_installment = $per_installment;
            $loan->installment_interval = $validatedData['installment_interval'];
            $loan->total_installment = $validatedData['installment_interval']; // Assuming installment interval is the number of installments
            $loan->paid_amount = $validatedData['paid_amount'] ?? 0.00;
            $loan->final_amount = $final_amount;
            $loan->loan_taken_date = $validatedData['taken_date'];
            $loan->user_details = $request->user_details ?? null;
            $loan->admin_feedback = null; // Assuming this field is optional
            $loan->status = 0; // Assuming default status is 0
            $loan->next_installment_date = $validatedData['next_installment_date'] ?? null;
            $loan->client_id = $validatedData['client_id']; // Assign the client ID
            $loan->save(); // Save the loan to the database
        
            // Check if paid amount is greater than 0 to create a LoanPayment record
            if ($validatedData['paid_amount'] > 0) {
                // Create a record for the payment made
                LoanPayment::create([
                    'loan_id' => $loan->id,
                    'client_id' => $validatedData['client_id'],
                    'agent_id' => $validatedData['agent_id'],
                    'amount' => $validatedData['paid_amount'], // Use the correct paid amount
                    'payment_date' => now(), // Current date/time as the payment record date
                    'note' => $validatedData['note'] ?? null, // Include optional note if provided
                ]);
            }
        
            // Redirect back with success message
            return redirect()->route('admin.loan-pendingLoans')->with('success', 'Loan added successfully for client ');
        }

    
    public function createLoan(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'trx' => 'nullable|string|max:40',
            'amount' => 'required|numeric|min:0',
             
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }
    
        // Fetch the client and agent
        $client = Client::find($request->client_id);
        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }
    
        // $agent = User::findOrFail($request->user_id)->where('is_active', 1)->get();
        $agent = User::where('id', $request->user_id)->where('is_active', 1)->firstOrFail();

    
        // Calculate per installment and final amount
        $per_installment = ($request->amount * 1.2) / $request->installment_interval;
        $final_amount = $request->amount * 1.2;
    
        // Calculate remaining amount
        $remaining_amount = $final_amount - ($request->paid_amount ?? 0);
    
        // Create a new UserLoan instance
        $loan = new UserLoan();
        $loan->user_id = $request->user_id;
        $loan->plan_id = 8;  // Assuming plan_id is static or predefined
        $loan->trx = $request->trx; // Generate unique transaction ID
        $loan->amount = $request->amount;
        $loan->per_installment = $per_installment;
        $loan->installment_interval = $request->installment_interval;
        $loan->total_installment = $request->installment_interval;
        $loan->paid_amount = $request->paid_amount ?? 0.00;
        $loan->final_amount = $final_amount;
        $loan->user_details = $request->user_details ?? null;
        $loan->admin_feedback = null;  // Assuming this field is optional
        $loan->status = 0;  // Assuming default status is 0
        $loan->next_installment_date = $request->next_installment_date ?? null;
        $loan->client_id = $request->client_id; // Assign the client ID
        $loan->save();  // Save the loan to the database
    
        // Create a new agent loan record
        $agentLoan = new AgentLoan();
        $agentLoan->user_id = $request->user_id;
        $agentLoan->client_id = $request->client_id;
        $agentLoan->loan_amount = $request->amount;
        $agentLoan->final_loan_amount = $final_amount;
        $agentLoan->save();
        
        
         // Check if paid amount is greater than 0 to create a LoanPayment record
        if ($request->paid_amount > 0) {
            // Create a record for the payment made
            LoanPayment::create([
                'loan_id' => $loan->id,
                'client_id' => $request->client_id,
                'agent_id' => $request->agent_id,
                'amount' => $request->paid_amount, // Use the correct paid amount
                'payment_date' => now(), // Current date/time as the payment record date
                // 'note' => $validatedData['note'] ?? null, // Include optional note if provided
            ]);
        }
    
        return response()->json(response_formatter(DEFAULT_200, $loan, null), 200);
    }
        
        
    
    function generateUniqueTrx()
        {
            do {
                // Generate a random transaction ID
                $trx = 'TRX' . Str::random(8);
                
                // Check if the trx already exists in the UserLoan table
                $exists = UserLoan::where('trx', $trx)->exists();
            } while ($exists);
        
            return $trx;
        }
    
    public function editLoan($id) {
            // Find the loan by ID
            $loan = UserLoan::findOrFail($id);
        
            // Retrieve the related client and loan plan
            $client = Client::find($loan->client_id);
            $loanPlan = LoanPlan::find($loan->plan_id);
        
            // Pass the loan, client, and loan plan data to the view
            return view('admin-views.Loans.edit', compact('loan', 'client', 'loanPlan'));
        }

    public function editLoan2(Request $request){
        $loan = UserLoan::findOrFail($request->id);
        $client = Client::find($loan->client_id);
         $loanPlan = LoanPlan::find($loan->plan_id);
         return view('admin-views.Loans.edit', compact('loan', 'client', 'loanPlan'));
    }
    
    public function saveLoanEdit2(Request $request)
        {
            // Validate the request
            $request->validate([
                'id' => 'required|exists:user_loans,id', // Ensure the loan ID exists
                'amount' => 'required|numeric|min:0',
                'per_installment' => 'required|numeric|min:0',
                'installment_interval' => 'required|integer|min:1',
                'total_installment' => 'required|integer|min:1',
                // Add more validation rules as needed based on your loan fields
            ]);
            
            $loan = UserLoan::findOrFail($request->id);
        
            // Check if the loan is in a pending or running state (status 0 or 1)
            if ($loan->status != 0 && $loan->status != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan cannot be edited in its current state.',
                ]);
            }
        
            // Update the loan details
            $loan->amount = $request->amount;
            $loan->per_installment = $request->per_installment;
            $loan->installment_interval = $request->installment_interval;
            $loan->total_installment = $request->installment_interval;
            // Update other loan fields as needed
        
            // Recalculate final_amount if necessary (based on your interest calculation logic)
        
            // Save the changes
            $loan->save();
        
            return this-> showLoan( $loan->id);
        }
        
        
        
        
        public function saveLoanEdit(Request $request)
            {
                
                $loan = UserLoan::findOrFail($request->id);
            
                // Check if the loan is in a pending or running state (status 0 or 1)
                if ($loan->status != 0 && $loan->status != 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Loan cannot be edited in its current state.',
                    ]);
                }
            
                // Update the loan details
                $loan->amount = $request->amount;
                $loan->per_installment = $request->per_installment;
                $loan->installment_interval = $request->installment_interval;
                $loan->total_installment = $request->installment_interval;
                $loan->final_amount = $loan->per_installment * $loan->total_installment;
                $loan->processing_fee = $request->processing_fee;

               
                // Save the changes
                $loan->save();
            
                // Redirect to the loan details view after saving the changes
                return $this->showLoan($loan->id);
            }

        
     public function showLoan($id)
    {
        // Fetch the loan by ID
        $loan = UserLoan::findOrFail($id);
    
        // Retrieve client details using the client_id
        $client = Client::find($loan->client_id);
        
        $agent = User::find($loan->user_id); 
    
        // Retrieve loan plan details using the plan_id
        $loanPlan = LoanPlan::find($loan->plan_id);
        
        // CLIENT guarantors
        $clientGuarantors =   Guarantor::where('client_id', $loan->client_id)->get();
        $loanSlots =  LoanPaymentInstallment::where('client_id', $loan->client_id)->get();


    
        // Pass the loan, client, and loan plan data to the view
        return view('admin-views.Loans.view', [
            'loan' => $loan,
            'client' => $client,
            'loanPlan' => $loanPlan,
            'agent' => $agent,
            'clientGuarantors' => $clientGuarantors,
            'loanSlots' => $loanSlots
        ]);
    }
    
    
    // on loan approval, create payment slots/plan
    public function approveLoan(Request $request)
        {
           
            $loan = UserLoan::findOrFail($request->id);
            $client = Client::find($loan->client_id);
            $clientGuarantors =   Guarantor::where('client_id', $loan->client_id)->get();
        
            // Check if the loan is in a pending state (status 0)
            if ($loan->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan is not in a pending state.',
                ]);
            }
            
            
             // Check if the client has no guarantors 
            if ($clientGuarantors->isEmpty()) {
                    //   return response()->warning('Client has no guarantors.'); // Assuming you have a 'warning' response helper
                    Toastr::error(translate('Client has no guarantors.'));
                  return back();
            }
        
            // Check if client credit balance is greater than 0 
            if ($client->credit_balance != 0) { // Assuming you have a 'credit_balance' column on your Client model
                Toastr::error(translate('Client has a positive credit balance.'));
                return back();
            }
             
            if ($loan->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan is not in a pending state.',
                ]);
            }

          // Loan taken at
            $loanTakenDate = Carbon::parse($loan->loan_taken_date); //$loan->loan_taken_date;

            // Update the loan status to 'Running' (status 1)
            $loan->status = 1;
            $loan->disbursed_at = $loanTakenDate;
            $loan->due_date = $loanTakenDate->copy()->addDays($loan->installment_interval);

            // set the client credit balance to the add the loan
        
            // Set the next installment date (assuming today is the approval date)
            $loan->next_installment_date = $loanTakenDate->copy()->addDays($loan->installment_interval);
        
            // Save the changes
            $loan->save();
            
            
           // Set the next installment date (assuming today is the approval date)
            $loan->next_installment_date = $loanTakenDate->copy()->addDays($loan->installment_interval);
            $loan->save();
        
            // Update the client's credit balance by adding the loan amount
            $client->credit_balance = isset($client->credit_balance) ? $client->credit_balance + $loan->final_amount : $loan->final_amount;
            $client->save();

            
            // Generate payment installments
            $this->createPaymentInstallments($loan);
        
            return back();
        }
     
     
         
    // payment slots created after the loan is approved
    protected function createPaymentInsta(UserLoan $loan)
        {
            $installmentAmount = $loan->per_installment;
            $totalInstallments = $loan->total_installment;
        
            for ($i = 0; $i < $totalInstallments; $i++) {
                $installmentDate = now()->addDays($i); // Add days incrementally for daily installments
        
                LoanPaymentInstallment::create([
                    'loan_id' => $loan->id,
                    'agent_id' => $loan->user_id,
                    'client_id' => $loan->client_id,
                    'install_amount' => $installmentAmount,
                    'date' => $installmentDate,
                    'status' => 'pending', // Initially set status as 'pending'
                ]);
            }
        }


protected function createPaymentInstallments(UserLoan $loan)
{
    $installmentAmount = $loan->per_installment;
    $installmentInterval = $loan->installment_interval;
    $totalInstallments = $loan->total_installment;
    $loanTakenDate = Carbon::parse($loan->loan_taken_date); //$loan->loan_taken_date;


    for ($i = 1; $i <= $totalInstallments; $i++) {
        // Calculate the base installment date
        $installmentDate = $loanTakenDate->copy()->addDays($i);

        // Adjust the time to 11 AM to ensure it falls within the business day
        $installmentDate->setTime(11, 0);

        // Save the installment
        LoanPaymentInstallment::create([
            'loan_id' => $loan->id,
            'agent_id' => $loan->user_id,
            'client_id' => $loan->client_id,
            'install_amount' => $installmentAmount,
            'date' => $installmentDate,
            'status' => 'pending', // Initially set status as 'pending'
        ]);
    }
}

     
   
     
     
     
     
     
     
     
   
    
   public function getClientQr(Request $request): JsonResponse
{
    // Retrieve a single client record
    $customer = Client::where('id', $request->client_id)->first();
    
    // Check if the customer exists
    if ($customer) {
        $data = [];
        $data['name'] = $customer->name;
        $data['phone'] = $customer->phone;
        $data['clientid'] = $customer->id;
        $data['image'] = $customer->image;
        
        $qr = Helpers::get_qrcode_client($data); 
        
        

        // Return the response with customer data
        return response()->json([
            'qr_code' => strval($qr),
        ], 200);
    } else {
        // Handle case where the customer was not found
        return response()->json([
            'message' => 'Client not found',
        ], 404);
    }
}

    
    
    // pay loan 
  public function payLoan3(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    $clientId = $request->input('client_id');
    $amountPaid = $request->input('amount');
    $today = now()->toDateString();

    // Get running loans for the client
    $loans = UserLoan::where('client_id', $clientId)
        ->where('status', '<>', 2) // Exclude fully paid loans
        ->get();

    if ($loans->isEmpty()) {
        return response()->json(['errors' => 'No running loans found for the client'], 404);
    }

    $totalPaid = $amountPaid;

    foreach ($loans as $loan) {
        // Get today's installments for the current loan
        $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
            ->where('date', $today)
            ->where('status', 'pending')
            ->get();

        if ($installments->isEmpty()) {
            continue;
        }

        foreach ($installments as $installment) {
            // Update the installment status to 'paid' and the amount paid
            $installment->status = 'paid';
            $installment->save();

            // Update the loan's paid amount
            $loan->paid_amount += $installment->install_amount;

            // If the total paid amount is equal to or exceeds the final amount, update the loan status
            if ($loan->paid_amount >= $loan->final_amount) {
                $loan->status = 2; // Status 2 indicates a fully paid loan
            }

            $loan->save();

            // Deduct the installment amount from the total amount paid
            $totalPaid -= $installment->install_amount;

            // If the total amount paid covers the current installment, continue to the next
            if ($totalPaid <= 0) {
                break;
            }
        }

        // If the total amount paid covers all installments, break the loop
        if ($totalPaid <= 0) {
            break;
        }
    }

    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Loan installment(s) paid successfully'], 200);
}


public function payLoan33(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0',
        'agent_id' => 'nullable|exists:agents,id', // Optional, only if there's an agent involved
        'transaction_id' => 'required|unique:payment_transactions,transaction_id',
        'payment_type' => 'required|string', // e.g., cash, card, mobile, etc.
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    $clientId = $request->input('client_id');
    $amountPaid = $request->input('amount');
    $agentId = $request->input('agent_id');  // Optional
    $transactionId = $request->input('transaction_id');
    $paymentType = $request->input('payment_type');
    $today = now()->toDateString();

    // Get running loans for the client
    $loans = UserLoan::where('client_id', $clientId)
        ->where('status', '<>', 2) // Exclude fully paid loans
        ->get();

    if ($loans->isEmpty()) {
        return response()->json(['errors' => 'No running loans found for the client'], 404);
    }

    $totalPaid = $amountPaid;

    foreach ($loans as $loan) {
        // Get today's installments for the current loan
        $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
            ->where('date', $today)
            ->where('status', 'pending')
            ->get();

        if ($installments->isEmpty()) {
            continue;
        }

        foreach ($installments as $installment) {
            // Update the installment status to 'paid' and the amount paid
            $installment->status = 'paid';
            $installment->save();

            // Update the loan's paid amount
            $loan->paid_amount += $installment->install_amount;

            // Create a new payment transaction record
            PaymentTransaction::create([
                'client_id' => $clientId,
                'loan_id' => $loan->id,
                'agent_id' => $agentId, // Optional
                'transaction_id' => $transactionId,
                'payment_type' => $paymentType,
                'amount' => $installment->install_amount,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // If the total paid amount is equal to or exceeds the final amount, update the loan status
            if ($loan->paid_amount >= $loan->final_amount) {
                $loan->status = 2; // Status 2 indicates a fully paid loan
            }

            $loan->save();

            // Deduct the installment amount from the total amount paid
            $totalPaid -= $installment->install_amount;

            // If the total amount paid covers the current installment, continue to the next
            if ($totalPaid <= 0) {
                break;
            }
        }

        // If the total amount paid covers all installments, break the loop
        if ($totalPaid <= 0) {
            break;
        }
    }

    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Loan installment(s) paid successfully'], 200);
}




    protected function generateTransactionId()
        {
            do {
                $transactionId = 'abROi' . mt_rand(1000000000, 9999999999);
            } while (PaymentTransaction::where('transaction_id', $transactionId)->exists());
        
            return $transactionId;
        }


// pay loan tests





public function payLoanNew(Request $request): JsonResponse
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    // Get input values
    $clientId = $request->input('client_id');
    $amountPaid = $request->input('amount');
    $agentId = $request->input('agent_id'); // Optional
    $transactionId = $this->generateTransactionId();
    $paymentType = 'loan'; // Default to loan
    $today = now()->toDateString();

    // Fetch client details
    $client = Client::find($clientId);
    if (!$client) {
        return response()->json(['errors' => 'Client not found'], 404);
    }

    // Find the running loan for the client
    $loan = UserLoan::where('client_id', $clientId)
        ->where('status', '<>', 2) // Exclude fully paid loans
        ->first();

    if (!$loan) {
        return response()->json(['errors' => 'No running loans found for the client'], 404);
    }

    // Fetch the agent associated with the loan
    $agent = User::find($loan->user_id);

    // Fetch all installments for the current loan
    $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
        ->where('status', 'pending') // Only consider pending installments
        ->get();

    // If no installments are found, return an error
    if ($installments->isEmpty()) {
        return response()->json(['errors' => 'No pending installments found for the loan'], 404);
    }

    // Process payment for available installments
    $totalPaid = $amountPaid;

    foreach ($installments as $installment) {
        if ($totalPaid <= 0) {
            break;
        }

        // Mark the installment as paid and deduct from the total paid amount
        $installmentAmount = min($installment->install_amount, $totalPaid);
        $installment->status = 'paid';
        $installment->save();

        // Update the loan's paid amount
        $loan->paid_amount += $installmentAmount;

        // Create payment transaction record
        PaymentTransaction::create([
            'client_id' => $clientId,
            'loan_id' => $loan->id,
            'agent_id' => $agentId, // Optional
            'transaction_id' => $transactionId,
            'payment_type' => $paymentType,
            'amount' => $installmentAmount,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Deduct from the total paid amount
        $totalPaid -= $installmentAmount;

        // If the loan is fully paid, update the status
        if ($loan->paid_amount >= $loan->final_amount) {
            $loan->status = 2; // Fully paid loan
            $loan->save();
            break;
        }
    }

    // Return success response
    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Loan installment(s) paid successfully',
    ], 200);
}






public function payLoan111e(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0',
        'agent_id' => 'nullable|exists:agents,id', // Optional, only if there's an agent involved
        'transaction_id' => 'required|unique:payment_transactions,transaction_id',
        'payment_type' => 'required|string', // e.g., cash, card, mobile, etc.
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    $clientId = $request->input('client_id');
    $amountPaid = $request->input('amount');
    $agentId = $request->input('agent_id');
    $transactionId = $request->input('transaction_id');
    $paymentType = $request->input('payment_type');
    $today = now()->toDateString();

    // Record the payment transaction
    $paymentTransaction = PaymentTransaction::create([
        'client_id' => $clientId,
        'amount' => $amountPaid,
        'agent_id' => $agentId,
        'transaction_id' => $transactionId,
        'payment_type' => $paymentType,
        'status' => 'completed',
        'paid_at' => now(),
    ]);

    // The rest of the payLoan logic remains the same
    // Get running loans for the client
    $loans = UserLoan::where('client_id', $clientId)
        ->where('status', '<>', 2) // Exclude fully paid loans
        ->get();

    if ($loans->isEmpty()) {
        return response()->json(['errors' => 'No running loans found for the client'], 404);
    }

    $totalPaid = $amountPaid;

    foreach ($loans as $loan) {
        // Get today's installments for the current loan
        $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
            ->where('date', $today)
            ->where('status', 'pending')
            ->get();

        if ($installments->isEmpty()) {
            continue;
        }

        foreach ($installments as $installment) {
            // Update the installment status to 'paid' and the amount paid
            $installment->status = 'paid';
            $installment->save();

            // Update the loan's paid amount
            $loan->paid_amount += $installment->install_amount;

            // Associate the payment transaction with the loan
            $paymentTransaction->loan_id = $loan->id;
            $paymentTransaction->save();

            // If the total paid amount is equal to or exceeds the final amount, update the loan status
            if ($loan->paid_amount >= $loan->final_amount) {
                $loan->status = 2; // Status 2 indicates a fully paid loan
            }

            $loan->save();

            // Deduct the installment amount from the total amount paid
            $totalPaid -= $installment->install_amount;

            // If the total amount paid covers the current installment, continue to the next
            if ($totalPaid <= 0) {
                break;
            }
        }

        // If the total amount paid covers all installments, break the loop
        if ($totalPaid <= 0) {
            break;
        }
    }

    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Loan installment(s) paid successfully'], 200);
}

    
    
    
   public function todaysLoanInstallments(): JsonResponse
    {
        // Get today's date in Y-m-d format
        $today = now()->toDateString();
        
        // Query LoanPaymentInstallment for today's date and pending status todaysLoanInstallments
    $installments = LoanPaymentInstallment::where('date', $today)
        ->where('status', 'pending')
        ->get();
        
        // Check if there are any installments for today
        if ($installments->isEmpty()) {
            return response()->json(['message' => 'No installments due today'], 404);
        }
    
        // Return the installments
        return response()->json(response_formatter(DEFAULT_200, $installments, null), 200);
        
        
        
    }
    
    
    // today schedule
    public function todaysSchedule2(Request $request): JsonResponse
    {
        // Get today's date in Y-m-d format
        $today = now()->toDateString();
        
        $agentId = $request->input('agent_id');
        
        // Query LoanPaymentInstallment for today's date and pending status todaysLoanInstallments
        $installments = LoanPaymentInstallment::where('agent_id', $agentId)
            ->where('date', $today)
            ->get();
    
    
           
                    
            // Prepare the response data with client details
    $responseData = [];

    foreach ($installments as $installment) {
        // Get client details for each installment
        $client = Client::find($installment->client_id);
        $clientBalance =  $client->credit_balance;

        $responseData[] = [
            'id' => $installment->id,
            'loan_id' => $installment->loan_id,
            'agent_id' => $installment->agent_id,
            'client_id' => $installment->client_id,
            'client_name' => $client ? $client->name : null, // Assuming 'name' is a field in the clients table
            'client_phone' => $client ? $client->phone : null, // Assuming 'phone' is a field in the clients table
            'install_amount' => $installment->install_amount,
            'date' => $installment->date,
            'balance' => $clientBalance,
            'status' => $installment->status,
            'created_at' => $installment->created_at,
            'updated_at' => $installment->updated_at,
        ];
    }
    
     return response()->json([
        'response_code' => 'default_200',
        'message' => 'Successfully fetched data',
        'DataContent' => $responseData
    ], 200);
    
     // Return the installments with client details
    // return response()->json(response_formatter(DEFAULT_200, $responseData, null), 200);
    }
    
    public function todaysSchedule(Request $request): JsonResponse
        {
            // Get the start and end date-time for today using getDateRange2
            [$startDate, $endDate] = $this->getDateRange2('daily');
            
            $agentId = $request->input('agent_id');
            
            // Query LoanPaymentInstallment for today's date range
            $installments = LoanPaymentInstallment::where('agent_id', $agentId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
        
            // Prepare the response data with client details
            $responseData = [];
        
            foreach ($installments as $installment) {
                // Get client details for each installment
                $client = Client::find($installment->client_id);
                $clientBalance = $client ? $client->credit_balance : null;
        
                $responseData[] = [
                    'id' => $installment->id,
                    'loan_id' => $installment->loan_id,
                    'agent_id' => $installment->agent_id,
                    'client_id' => $installment->client_id,
                    'client_name' => $client ? $client->name : null,
                    'client_phone' => $client ? $client->phone : null,
                    'install_amount' => $installment->install_amount,
                    'date' => $installment->date,
                    'balance' => $clientBalance,
                    'status' => $installment->status,
                    'created_at' => $installment->created_at,
                    'updated_at' => $installment->updated_at,
                ];
            }
            
            return response()->json([
                'response_code' => 'default_200',
                'message' => 'Successfully fetched data',
                'DataContent' => $responseData
            ], 200);
        }
        
        /**
         * Get the start and end date-time based on the period.
         *
         * @param string $period
         * @return array
         */
        private function getDateRange2($period)
        {
            $now = Carbon::now();
        
            switch ($period) {
                case 'weekly':
                    return [
                        $now->copy()->startOfWeek()->setTime(8, 0),
                        $now->copy()->endOfWeek()->addDay()->setTime(7, 59, 59)
                    ];
                case 'monthly':
                    return [
                        $now->copy()->startOfMonth()->setTime(8, 0),
                        $now->copy()->endOfMonth()->addDay()->setTime(7, 59, 59)
                    ];
                case 'daily':
                default:
                    if ($now->hour < 8) {
                        // If current time is before 8 AM, set start to yesterday 8 AM
                        return [
                            $now->copy()->subDay()->setTime(8, 0),
                            $now->copy()->setTime(7, 59, 59)
                        ];
                    } else {
                        // If current time is after 8 AM, set start to today 8 AM
                        return [
                            $now->copy()->setTime(8, 0),
                            $now->copy()->addDay()->setTime(7, 59, 59)
                        ];
                    }
            }
        }

    
    // private function getDateRange2($period)
    //     {
    //         $now = Carbon::now();
        
    //         switch ($period) {
    //             case 'weekly':
    //                 return [
    //                     $now->copy()->startOfWeek()->setTime(8, 0),
    //                     $now->copy()->endOfWeek()->addDay()->setTime(7, 59, 59)
    //                 ];
    //             case 'monthly':
    //                 return [
    //                     $now->copy()->startOfMonth()->setTime(8, 0),
    //                     $now->copy()->endOfMonth()->addDay()->setTime(7, 59, 59)
    //                 ];
    //             case 'daily':
    //             default:
    //                 if ($now->hour < 8) {
    //                     // If current time is before 8 AM, set start to yesterday 8 AM
    //                     return [
    //                         $now->copy()->subDay()->setTime(8, 0),
    //                         $now->copy()->setTime(7, 59, 59)
    //                     ];
    //                 } else {
    //                     // If current time is after 8 AM, set start to today 8 AM
    //                     return [
    //                         $now->copy()->setTime(8, 0),
    //                         $now->copy()->addDay()->setTime(7, 59, 59)
    //                     ];
    //                 }
    //         }
    //     }
        
    
    
    // collected 
    
    
    // daily total for agent for today
  public function totalAmountForAgentOnDate(Request $request): JsonResponse
{
    // Get today's date and time at 11 am
    $startOfBusinessDay = now()->setTime(11, 0, 0);
    
    // Get the agent_id from the request
    $agentId = $request->input('agent_id');

    // Query the installments for the given agent from 11 am today
    $totalAmount = LoanPaymentInstallment::where('agent_id', $agentId)
                    ->whereDate('date', now()->toDateString()) // Ensure only the date part is considered
                    ->sum('install_amount');
                    
    $totalAmountCollected2 = LoanPaymentInstallment::where('agent_id', $agentId)
                    ->whereDate('date', now()->toDateString()) // Ensure only the date part is considered
                    ->where('status', 'paid')
                    ->sum('install_amount');
                    
                    
                    
                    
    // Set the custom time window (4:00 PM to 3:59 PM the next day)
        $startDate = now()->subDay()->setTime(16, 0, 0); // Yesterday at 4:00 PM
        $endDate = now()->setTime(15, 59, 59); // Today at 3:59 PM
        
        
        $totalAmountCollected = LoanPayment::where('agent_id', $agentId)
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->where('is_reversed', false) // Only non-reversed payments
                                ->sum('amount');
                    
                    
                    
                    

    // Return the total amount the agent needs to collect
    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Successfully fetched data',
        'total_amount' => $totalAmount,
        'collected' => $totalAmountCollected
    ], 200);
}


    
    public function totalAmountForAgentOnDate1000(Request $request): JsonResponse
    {
        // Get today's date in Y-m-d format
        $today = now()->toDateString();
        
   

    // Get the agent_id and date from the request
    $agentId = $request->input('agent_id');
    // $date = $request->input('date');

    // Query the installments for the given agent and date
    $totalAmount = LoanPaymentInstallment::where('agent_id', $agentId)
                    ->where('date', $today)
                    ->sum('install_amount');
                    
    $totalAmountCollected = LoanPaymentInstallment::where('agent_id', $agentId)
                    ->where('date', $today)
                    ->where('status', 'paid')
                    ->sum('install_amount');

    // Return the total amount the agent needs to collect
    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Successfully fetched data',
        'total_amount' => $totalAmount,
        'collected' => $totalAmountCollected
    ], 200);
}


    
     // loan Plans
     public function allplans(){
        //  get all the available plans
        $loanPlans = LoanPlan::all();
        
         return view('admin-views.Loans.plan.index', compact('loanPlans'));
     }
    // add 
    public function addplan(){
         return view('admin-views.Loans.plan.create');
    }
    
     // create 
    public function createplan(Request $request){
        $request->validate([
            'plan_name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'installment_value' => 'required|numeric|min:0',
            'installment_interval' => 'required|numeric|min:0',
            'total_installments' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
        ]);

        LoanPlan::create($request->all());
        
        return view('admin-views.Loans.plan.create');
    }
    
    
    // edit 
       public function editplan($id){
         $loanPlan = LoanPlan::findOrFail($id);
         return view('admin-views.Loans.plan.edit',compact('loanPlan'));
    }
     
     
    //  delet paln 
    public function destroyNow($id)
    {
         
        $loanPlan = LoanPlan::findOrFail($id);
        $loanPlan->delete();

        return redirect()->route('admin.loan-plans')->with('success', 'Loan Plan deleted successfully');
    }
    
    
    
    
     public function updateNow(Request $request, $id)
    {
        $request->validate([
            'plan_name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'installment_value' => 'required|numeric|min:0',
            'installment_interval' => 'required|numeric|min:0',
            'total_installments' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
        ]);

        $loanPlan = LoanPlan::findOrFail($id);
        $loanPlan->update($request->all());

        return redirect()->route('admin.loan-plans')->with('success', 'Loan Plan updated successfully');
    }
     
     
     // all loans
     public function all_loans(){
        //  get all the available plans
        // $loanPlans = LoanPlan::all();
        $pageTitle      = 'All Loans';

        if(request()->search){
            $query          = UserLoan::where('trx', request()->search);
            $emptyMessage   = 'No Data Found';
        }else{
            $query          = UserLoan::latest();
            $emptyMessage   = 'No Loan Yet';
        }
 $totalLoans = $query->count();
        $loans = $query->paginate(20);
        
         return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
     }
     
        
        
        
    // paid Loans
        
    public function paidLoans()
     {
                $pageTitle      = 'Paid Loans';
        
        
                if(request()->search){
                    $query          = UserLoan::paid()->where('trx', request()->search);
                    $emptyMessage   = 'No Data Found';
                }else{
                    $query          = UserLoan::paid()->latest();
                    $emptyMessage   = 'No Paid Loan Yet';
                }
         $totalLoans = $query->count();
                $loans = $query->paginate(20);
        
                return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
            }
     
public function dueLoans()
{
    $pageTitle = 'Due Loans';
    $emptyMessage = 'No Due Loans Found';

    // Initialize query for due loans
    $query = UserLoan::due()->latest();

    // Search functionality
    if ($search = request('search')) {
        $query->where('trx', 'LIKE', '%' . $search . '%');
        $emptyMessage = 'No loans found matching your search criteria.';
    }

    // Get total number of due loans (before pagination)
    $totalLoans = $query->count();

    // Paginate the result
    $loans = $query->paginate(20);

    // Pass the totalDueLoans count to the view along with the loans data
    return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
}


     
     
            
    // pending loans
    public function pendingLoans()
    {
                $pageTitle      = 'Pending Loans';
        
                if(request()->search){
                    $query          = UserLoan::pending()->where('trx', request()->search);
                    $emptyMessage   = 'No Data Found';
                }else{
                    $query          = UserLoan::pending()->latest();
                    $emptyMessage   = 'No Pending Loan Yet';
                }
        $totalLoans = $query->count();
                $loans = $query->paginate(20);
              
        
                return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
            }

        
    public function pendingLoans10()
{
    $pageTitle = 'Pending Loans';

    if (request()->search) {
        $query = UserLoan::pending()
            ->where('trx', request()->search)
            ->with('client') // Load client data based on client_id
            ->latest();
        $emptyMessage = 'No Data Found';
    } else {
        $query = UserLoan::pending()
            ->with('client') // Load client data based on client_id
            ->latest();
        $emptyMessage = 'No Pending Loan Yet';
    }

    $loans = $query->paginate(20);

    return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans'));
}

    
    
    
    // rejected loans
    public function rejectedLoans()
            {
                $pageTitle      = 'Rejected Loans';
        
                if(request()->search){
                    $query          = UserLoan::rejected()->where('trx', request()->search);
                    $emptyMessage   = 'No Data Found';
                }else{
                    $query          = UserLoan::rejected()->latest();
                    $emptyMessage   = 'No Rejected Loan Yet';
                }
        $totalLoans = $query->count();
                $loans = $query->paginate(20);
        
                return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
            }

    

    
    // running loans 
    public function runningLoans()
    {
                $pageTitle      = 'Running Loans';
        
                if(request()->search){
                    $query          = UserLoan::running()->where('trx', request()->search);
                    $emptyMessage   = 'No Data Found';
                }else{
                    $query          = UserLoan::running()->latest();
                    $emptyMessage   = 'No Running Loan Yet';
                }
        $totalLoans = $query->count();
                $loans = $query->paginate(20);
        
                return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
            }

    
    
    
    public function loanplansindex()
    {
        
        $loanPlans = LoanPlan::all();
        return response()->json($loanPlans);
    }

    
    
    




// create loan.
    public function createLoan10(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            
            'trx' => 'nullable|string|max:40',
            'amount' => 'required|numeric|min:0',
            'per_installment' => 'required|numeric|min:0',
            'installment_interval' => 'required|integer|min:1',
            'total_installment' => 'required|integer|min:1',
            'given_installment' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'user_details' => 'nullable|string',
            'next_installment_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }

        $user = User::find($request->user_id);
        $plan = LoanPlan::find($request->plan_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

      

        $loan = new UserLoan();
        $loan->user_id = $request->user_id;
        $loan->plan_id = $request->plan_id;
        $loan->trx = $request->trx;
        $loan->amount = $request->amount;
        $loan->per_installment = $request->per_installment;
        $loan->installment_interval = $request->installment_interval;
        $loan->total_installment = $request->total_installment;
        $loan->given_installment = $request->given_installment ?? 0;
        $loan->paid_amount = $request->paid_amount ?? 0.00;
        $loan->final_amount = $request->final_amount;
        $loan->user_details = $request->user_details ?? null;
        $loan->admin_feedback = null;
        $loan->status = 0;
        $loan->next_installment_date = $request->next_installment_date ?? null;
        $loan->client_id = $request->client_id;
        $loan->save();
        
        
        // Create a new agent loan record
                $agentLoan = new AgentLoan();
                $agentLoan->user_id = $request->user_id;
                $agentLoan->client_id = $request->client_id;
                $agentLoan->loan_amount = $request->amount;
                $agentLoan->final_loan_amount = $request->final_amount;
                $agentLoan->save();
        
        // $client = Client::where('client_id', $request->client_id)
        
        
        
      

        
        return response()->json(response_formatter(DEFAULT_200, $loan, null), 200);
    }
    
    
    
    
    
   

    
    // client loans
    public function clientLoans(Request $request): JsonResponse
    {
        $loans = UserLoan::where('client_id', $request -> id)->get();
        return response()->json(response_formatter(DEFAULT_200, $loans, null), 200);
    }

// user loans 
   public function userLoansList(Request $request): JsonResponse
    {
        $loans = UserLoan::where('user_id', $request -> id)->get();
        return response()->json(response_formatter(DEFAULT_200, $loans, null), 200);
        
    }



 public function withdrawalMethods(Request $request): JsonResponse
    {
        $withdrawalMethods = $this->withdrawalMethod->latest()->get();
        return response()->json(response_formatter(DEFAULT_200, $withdrawalMethods, null), 200);
    }



    public function show($id)
    {
        $loanOffer = LoanOffer::findOrFail($id);
        return response()->json($loanOffer);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'interest_rate' => 'sometimes|required|numeric',
            'min_amount' => 'sometimes|required|integer|min:0',
            'max_amount' => 'sometimes|required|integer|min:0',
            'min_term' => 'sometimes|required|integer|min:0',
            'max_term' => 'sometimes|required|integer|min:0',
        ]);

        $loanOffer = LoanOffer::findOrFail($id);
        $loanOffer->update($request->all());
        return response()->json($loanOffer);
    }

    public function destroy2($id)
    {
        $loanOffer = LoanOffer::findOrFail($id);
        $loanOffer->delete();
        return response()->json(['message' => 'Loan offer deleted successfully']);
    }
    
    
    // 
}

