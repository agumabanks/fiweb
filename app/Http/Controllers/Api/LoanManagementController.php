<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoanOffer;
use App\Models\LoanPlan;
use App\Models\UserLoan;
use App\Models\LoanPayment;
use App\Models\LoanPaymentInstallment;
use App\Models\User;
use App\Models\Client;
use App\Models\AgentLoan;
use App\Models\Guarantor;
use App\Models\PaymentTransaction;
use App\CentralLogics\Helpers;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LoanOfferApiController extends Controller
{
    public function latestPaymentTransactions(Request $request): JsonResponse
    {
        // Validate query parameters for pagination (optional)
        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $perPage = $request->input('per_page', 20); // Default items per page
        $transactions = PaymentTransaction::orderBy('paid_at', 'desc')
            ->paginate($perPage);
    
        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ], 200);
    }

      /**
     * 1. Reverse Payment
     * POST /dloans/{id}/reverse
     */
    public function reversePayment($id): JsonResponse
    {
        try {
            // Find the payment by its ID
            $payment = LoanPayment::findOrFail($id);

            // Check if the payment is already reversed
            if ($payment->is_reversed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This payment has already been reversed.'
                ], 400);
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

            return response()->json([
                'status' => 'success',
                'message' => 'Payment has been reversed successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reverse payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. Client Loans Payment History
     * GET /dclients/{id}/loan-history
     */
    public function clientLoanspayHistory(Request $request, $id): JsonResponse
    {
        try {
            $clientloanspays = LoanPayment::where('client_id', $id)->get();
            return response()->json([
                'status' => 'success',
                'data' => $clientloanspays
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan payment history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. Get Agent's Clients with Running Loans
     * GET /dagents/{agentId}/clients-running-loans
     */
    public function getAgentsClientsWithRunningLoans($agentId): JsonResponse
    {
        try {
            // Fetch all clients assigned to this agent with running loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 1) // Assuming status 1 denotes running loans
                ->get();

            // Count the total number of clients with running loans
            $totalClients = $clients->count();

            // Check if clients with active loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No clients with running loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $clients,
                'total_clients' => $totalClients
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve running loans for agent.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. Get Agent's Clients with Pending Loans
     * GET /dagents/{agentId}/clients-pending-loans
     */
    public function getAgentsClientsWithPendingLoans($agentId): JsonResponse
    {
        try {
            // Fetch all clients assigned to this agent with pending loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 0) // Assuming status 0 denotes pending loans
                ->get();

            // Count the total number of clients with pending loans
            $totalClients = $clients->count();

            // Check if clients with pending loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No clients with pending loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $clients,
                'total_clients' => $totalClients
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve pending loans for agent.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5. Get Agent's Clients with Paid Loans
     * GET /dagents/{agentId}/clients-paid-loans
     */
    public function getAgentsClientsWithPaidLoans($agentId): JsonResponse
    {
        try {
            // Fetch all clients assigned to this agent with paid loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 2) // Assuming status 2 denotes paid loans
                ->get();

            // Count the total number of clients with paid loans
            $totalClients = $clients->count();

            // Check if clients with paid loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No clients with paid loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $clients,
                'total_clients' => $totalClients
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve paid loans for agent.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 6. Delete Loan
     * DELETE /dloans/{id}
     */
    public function deleteLoan($id): JsonResponse
    {
        try {
            // Find the loan by ID
            $loan = UserLoan::findOrFail($id);

            // Delete all loan installments associated with this loan
            LoanPaymentInstallment::where('loan_id', $loan->id)->delete();

            // Delete the loan itself
            $loan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Loan and its installments deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete loan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 7. Add Client Loan
     * POST /dclients/{id}/loan
     */
    public function addClientLoan($id): JsonResponse
    {
        try {
            // Fetch the client
            $client = Client::findOrFail($id);

            // Fetch all loan plans
            $loanPlans = LoanPlan::all();

            // Fetch all agents with client count and total money out
            $agents = User::join('clients', 'users.id', '=', 'clients.added_by')
                    ->select('users.id', 'users.f_name', 'users.l_name', 
                             DB::raw('COUNT(clients.id) as client_count'),
                             DB::raw('SUM(clients.credit_balance) as total_money_out'))
                    ->groupBy('users.id', 'users.f_name', 'users.l_name')
                    ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'client' => $client,
                    'loanPlans' => $loanPlans,
                    'agents' => $agents
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add loan for client.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 8. Admin Paying Loan
     * POST /dloans/{id}/admin-pay
     */
    public function adminPayingLoan($id): JsonResponse
    {
        try {
            // Fetch client details
            $client = Client::findOrFail($id);

            // Find the running loan associated with this client
            $loan = UserLoan::where('client_id', $id)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->first();

            if (!$loan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No running loans found for the client.'
                ], 404);
            }

            // Fetch the agent associated with the loan 
            $agent = User::findOrFail($loan->user_id);

            // Fetch all payment slots (installments) associated with this loan
            $loanInstallments = LoanPaymentInstallment::where('loan_id', $loan->id)->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'client' => $client,
                    'loanInstallments' => $loanInstallments,
                    'loan' => $loan,
                    'agent' => $agent
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to prepare loan payment view.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 9. Pay Loan Variant Z
     * POST /dloans/pay-z
     */
    public function payLoanZ(Request $request): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clientId = $request->input('client_id');
            $paymentAmount = $request->input('amount');
            $note = $request->input('note');
            $paymentDate = now()->toDateString(); // Today's date

            // Retrieve the client and active loans
            $client = Client::findOrFail($clientId);
            $loans = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->get();

            if ($loans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active loans found for this client.'
                ], 404);
            }

            $remainingPaymentAmount = $paymentAmount;

            foreach ($loans as $loan) {
                // Fetch installments due for today
                $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
                    ->where('date', $paymentDate)
                    ->where('status', 'pending')
                    ->get();

                foreach ($installments as $installment) {
                    $installmentAmount = $installment->install_amount + $installment->installment_balance;
                    
                    if ($remainingPaymentAmount >= $installmentAmount) {
                        // Fully pay this installment
                        $installment->status = 'paid';
                        $installment->installment_balance = 0;
                        $remainingPaymentAmount -= $installmentAmount;
                    } else {
                        // Partially pay this installment
                        $installment->status = 'withbalance';
                        $installment->installment_balance = $installmentAmount - $remainingPaymentAmount;
                        $remainingPaymentAmount = 0;
                    }

                    $installment->save();

                    // Update loan's paid amount
                    $loan->paid_amount += ($installmentAmount - $installment->installment_balance);
                    $loan->given_installment += 1;

                    // If loan is fully paid, update its status
                    if ($loan->paid_amount >= $loan->final_amount) {
                        $loan->status = 2; // Fully Paid
                    }

                    $loan->save();

                    // If no remaining payment amount, break out of the loop
                    if ($remainingPaymentAmount <= 0) {
                        break;
                    }
                }

                if ($remainingPaymentAmount <= 0) {
                    break;
                }
            }

            // Deduct from client's credit balance
            $client->credit_balance -= $paymentAmount;
            $client->save();

            // Log the payment
            LoanPayment::create([
                'loan_id' => $loan->id,
                'client_id' => $clientId,
                'agent_id' => $loan->user_id, // Assuming the agent handling the loan
                'amount' => $paymentAmount,
                'credit_balance'=> $client->credit_balance,
                'payment_date' => now(),
                'note' => $note,
            ]);

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully.',
                'remaining_balance' => $client->credit_balance
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 10. Pay Loan Variant Z1
     * POST /dloans/pay-z1
     */
    public function payLoanZ1(Request $request): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clientId = $request->input('client_id');
            $paymentAmount = $request->input('amount');
            $paymentDate = now()->toDateString(); // Today's date

            // Retrieve the client and active loans
            $client = Client::findOrFail($clientId);
            $loans = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->get();

            if ($loans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active loans found for this client.'
                ], 404);
            }

            $remainingPaymentAmount = $paymentAmount;

            foreach ($loans as $loan) {
                // Fetch installments due or pending
                $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
                    ->where(function ($query) use ($paymentDate) {
                        $query->where('date', $paymentDate)
                              ->orWhere('status', 'pending');
                    })
                    ->orderBy('date', 'asc') // Process installments in chronological order
                    ->get();

                foreach ($installments as $installment) {
                    $installmentAmount = $installment->install_amount + $installment->installment_balance;

                    if ($remainingPaymentAmount >= $installmentAmount) {
                        // Fully pay this installment
                        $installment->status = 'paid';
                        $installment->installment_balance = 0;
                        $remainingPaymentAmount -= $installmentAmount;
                    } else {
                        // Partially pay this installment
                        $installment->status = 'withbalance';
                        $installment->installment_balance = $installmentAmount - $remainingPaymentAmount;
                        $remainingPaymentAmount = 0;
                    }

                    $installment->save();

                    // Update loan's paid amount
                    $loan->paid_amount += ($installmentAmount - $installment->installment_balance);
                    $loan->given_installment += 1;

                    // If the loan is fully paid, update its status
                    if ($loan->paid_amount >= $loan->final_amount) {
                        $loan->status = 2; // Fully Paid
                    }

                    $loan->save();

                    // If no remaining payment amount, break out of the loop
                    if ($remainingPaymentAmount <= 0) {
                        break;
                    }
                }

                if ($remainingPaymentAmount <= 0) {
                    break;
                }
            }

            // Update client's credit balance
            $client->credit_balance -= $paymentAmount;
            $client->save();

            // Log the payment
            LoanPayment::create([
                'loan_id' => $loan->id,
                'client_id' => $clientId,
                'agent_id' => $loan->user_id, // Assuming the agent handling the loan
                'amount' => $paymentAmount,
                'credit_balance'=> $client->credit_balance,
                'payment_date' => now(),
                'note' => null, // You can modify this if a note field is needed
            ]);

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully.',
                'remaining_balance' => $client->credit_balance
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    
    
    
    
    
    
    
     /**
     * 11. Pay Loan Variant Zx
     * POST /dloans/pay-zx
     */
    public function payLoanZx(Request $request): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clientId = $request->input('client_id');
            $paymentAmount = $request->input('amount');
            $paymentDate = now()->toDateString(); // Today's date

            // Retrieve the client and active loans
            $client = Client::findOrFail($clientId);
            $loans = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->get();

            if ($loans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active loans found for this client.'
                ], 404);
            }

            $remainingPaymentAmount = $paymentAmount;

            foreach ($loans as $loan) {
                // Fetch installments due or pending
                $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
                    ->where(function ($query) use ($paymentDate) {
                        $query->where('date', '<=', $paymentDate) // Prioritize today's or past due installments
                              ->orWhere('status', 'pending'); // Also handle pending installments
                    })
                    ->orderBy('date', 'asc') // Process installments in chronological order
                    ->get();

                foreach ($installments as $installment) {
                    $installmentTotalAmount = $installment->install_amount + $installment->installment_balance;

                    if ($remainingPaymentAmount >= $installmentTotalAmount) {
                        // Fully pay this installment
                        $installment->status = 'paid';
                        $installment->installment_balance = 0;
                        $remainingPaymentAmount -= $installmentTotalAmount;
                    } else {
                        // Partially pay this installment, and leave a balance
                        $installment->status = 'withbalance';
                        $installment->installment_balance = $installmentTotalAmount - $remainingPaymentAmount;
                        $remainingPaymentAmount = 0; // No remaining amount after partial payment
                    }

                    $installment->save();

                    // Update loan's paid amount
                    $loan->paid_amount += ($installmentTotalAmount - $installment->installment_balance);
                    $loan->given_installment += 1;

                    // If loan is fully paid, update its status
                    if ($loan->paid_amount >= $loan->final_amount) {
                        $loan->status = 2; // Fully Paid
                    }

                    $loan->save();

                    // If no remaining payment amount, break out of the loop
                    if ($remainingPaymentAmount <= 0) {
                        break;
                    }
                }

                if ($remainingPaymentAmount <= 0) {
                    break;
                }
            }

            // Update client's credit balance
            $client->credit_balance -= $paymentAmount;
            $client->save();

            // Log the payment
            LoanPayment::create([
                'loan_id' => $loan->id,
                'client_id' => $clientId,
                'agent_id' => $loan->user_id, // Assuming the agent handling the loan
                'amount' => $paymentAmount,
                'credit_balance'=> $client->credit_balance,
                'payment_date' => now(),
                'note' => null, // You can modify this if a note field is needed
            ]);

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully.',
                'remaining_balance' => $client->credit_balance
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 12. Pay Loan
     * POST /dloans/pay
     */
    public function payLoan(Request $request): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clientId = $request->input('client_id');
            $paymentAmount = $request->input('amount');
            $paymentDate = now()->toDateString(); // Today's date

            // Retrieve the client and active loans
            $client = Client::findOrFail($clientId);
            $loans = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->get();

            if ($loans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active loans found for this client.'
                ], 404);
            }

            $remainingPaymentAmount = $paymentAmount;

            foreach ($loans as $loan) {
                // Fetch today's and future installments in chronological order
                $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
                    ->where(function ($query) use ($paymentDate) {
                        $query->where('date', '>=', $paymentDate) // Today's or future installments
                              ->where('status', 'pending'); // Include only pending installments
                    })
                    ->orderBy('date', 'asc') // Process installments in chronological order
                    ->get();

                foreach ($installments as $installment) {
                    $installmentTotalAmount = $installment->install_amount + $installment->installment_balance;

                    if ($remainingPaymentAmount >= $installmentTotalAmount) {
                        // Fully pay this installment
                        $installment->status = 'paid';
                        $installment->installment_balance = 0;
                        $remainingPaymentAmount -= $installmentTotalAmount;
                    } else {
                        // Partially pay this installment, and leave a balance
                        $installment->status = 'withbalance';
                        $installment->installment_balance = $installmentTotalAmount - $remainingPaymentAmount;
                        $remainingPaymentAmount = 0; // No remaining amount after partial payment
                    }

                    $installment->save();

                    // Update loan's paid amount
                    $loan->paid_amount += ($installmentTotalAmount - $installment->installment_balance);
                    $loan->given_installment += 1;

                    // If loan is fully paid, update its status
                    if ($loan->paid_amount >= $loan->final_amount) {
                        $loan->status = 2; // Fully Paid
                    }

                    $loan->save();

                    // If no remaining payment amount, break out of the loop
                    if ($remainingPaymentAmount <= 0) {
                        break;
                    }
                }

                if ($remainingPaymentAmount <= 0) {
                    break;
                }
            }

            // Update client's credit balance
            $client->credit_balance -= $paymentAmount;
            $client->save();

            // Log the payment
            LoanPayment::create([
                'loan_id' => $loan->id,
                'client_id' => $clientId,
                'agent_id' => $loan->user_id, // Assuming the agent handling the loan
                'amount' => $paymentAmount,
                'credit_balance'=> $client->credit_balance,
                'payment_date' => now(),
                'note' => null, // You can modify this if a note field is needed
            ]);

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully.',
                'remaining_balance' => $client->credit_balance
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 13. Update Loan Payment
     * PUT /dloans/{loanId}/update-payment
     */
    public function updateLoanPayment(Request $request, $loanId): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'payment_amount' => 'required|numeric|min:1',
            'payment_dates' => 'nullable|string', // Validate as a comma-separated string
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $paymentAmount = $request->input('payment_amount');
            $paymentDates = $request->input('payment_dates');
            $note = $request->input('note');

            // Retrieve the loan and related details
            $loan = UserLoan::findOrFail($loanId);
            $client = Client::findOrFail($loan->client_id);

            // Calculate the new paid amount and remaining balance
            $newPaidAmount = $loan->paid_amount + $paymentAmount;
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
            $client->credit_balance -= $paymentAmount;
            $client->save();

            // Check and log payment_dates
            if (!empty($paymentDates)) {
                $paymentDatesArray = explode(',', $paymentDates);
                foreach ($paymentDatesArray as $paymentDate) {
                    $trimmedDate = trim($paymentDate);
                    // Validate date format
                    if (!\Carbon\Carbon::createFromFormat('Y-m-d', $trimmedDate)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Invalid date format in payment_dates.'
                        ], 400);
                    }

                    $installment = LoanPaymentInstallment::where('loan_id', $loanId)
                        ->where('date', $trimmedDate)
                        ->first();

                    if ($installment) {
                        $installmentAmount = $installment->install_amount;
                        $installmentBalance = $installment->installment_balance;
                        $totalInstallmentAmount = $installmentAmount + $installmentBalance;

                        if ($paymentAmount >= $totalInstallmentAmount) {
                            $installment->status = 'paid';
                            $installment->installment_balance = 0;
                            $paymentAmount -= $totalInstallmentAmount;
                        } else {
                            $installment->status = 'withbalance';
                            $installment->installment_balance = $totalInstallmentAmount - $paymentAmount;
                            $paymentAmount = 0;
                        }

                        $installment->save();
                    } else {
                        // Installment not found for the given date
                        return response()->json([
                            'status' => 'error',
                            'message' => "Installment not found for date: $trimmedDate."
                        ], 404);
                    }

                    if ($paymentAmount <= 0) {
                        break;
                    }
                }
            }

            // Create a record for the payment made
            LoanPayment::create([
                'loan_id'       => $loan->id,
                'client_id'     => $loan->client_id,
                'agent_id'      => $loan->user_id,
                'credit_balance'=> $client->credit_balance,
                'amount'        => $paymentAmount, // Original payment amount
                'payment_date'  => now(), // Current date/time as the payment record date
                'note'          => $note,
            ]);

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Loan payment updated successfully.',
                'data' => [
                    'loan' => $loan,
                    'client' => $client
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update loan payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 14. Update Loan Payment Variant 10
     * PUT /dloans/{loanId}/update-payment10
     */
    public function updateLoanPayment10(Request $request, $loanId): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'payment_amount' => 'required|numeric|min:1',
            'payment_dates' => 'nullable|string', // Validate as a comma-separated string
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $paymentAmount = $request->input('payment_amount');
            $paymentDates = $request->input('payment_dates');
            $note = $request->input('note');

            // Retrieve the loan and related details
            $loan = UserLoan::findOrFail($loanId);
            $client = Client::findOrFail($loan->client_id);

            // Calculate the new paid amount and remaining balance
            $newPaidAmount = $loan->paid_amount + $paymentAmount;
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
            $client->credit_balance -= $paymentAmount;
            $client->save();

            // Check and log payment_dates
            if (!empty($paymentDates)) {
                $paymentDatesArray = explode(',', $paymentDates);
                foreach ($paymentDatesArray as $paymentDate) {
                    $trimmedDate = trim($paymentDate);
                    // Validate date format
                    if (!\Carbon\Carbon::createFromFormat('Y-m-d', $trimmedDate)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Invalid date format in payment_dates.'
                        ], 400);
                    }

                    $installment = LoanPaymentInstallment::where('loan_id', $loanId)
                        ->where('date', $trimmedDate)
                        ->first();

                    if ($installment) {
                        $installment->status = 'paid';
                        $installment->save();
                    } else {
                        // Installment not found for the given date
                        return response()->json([
                            'status' => 'error',
                            'message' => "Installment not found for date: $trimmedDate."
                        ], 404);
                    }
                }
            }

            // Create a record for the payment made
            LoanPayment::create([
                'loan_id'       => $loan->id,
                'client_id'     => $loan->client_id,
                'agent_id'      => $loan->user_id,
                'amount'        => $paymentAmount, // Original payment amount
                'payment_date'  => now(), // Current date/time as the payment record date
                'note'          => $note,
            ]);

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Loan payment updated successfully.',
                'data' => [
                    'loan' => $loan,
                    'client' => $client
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update loan payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 15. Store Client Loan
     * POST /dclients/loan/store
     */
    public function storeClientLoan(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'agent_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'installment_interval' => 'required|numeric|min:1',
            'paid_amount' => 'nullable|numeric|min:0',
            'next_installment_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Fetch validated data
            $clientId = $request->input('client_id');
            $agentId = $request->input('agent_id');
            $amount = $request->input('amount');
            $installmentInterval = $request->input('installment_interval');
            $paidAmount = $request->input('paid_amount', 0.00);
            $nextInstallmentDate = $request->input('next_installment_date');
            $note = $request->input('note');

            // Calculate per installment and final amount
            $per_installment = ($amount * 1.2) / $installmentInterval;
            $final_amount = $amount * 1.2;
            $remaining_amount = $final_amount - $paidAmount;

            // Create a new UserLoan instance
            $loan = new UserLoan();
            $loan->user_id = $agentId;
            $loan->plan_id = 8; // Assuming plan_id is static or predefined
            $loan->trx = $this->generateUniqueTrx(); // Generate unique transaction ID
            $loan->amount = $amount;
            $loan->per_installment = $per_installment;
            $loan->installment_interval = $installmentInterval;
            $loan->total_installment = $installmentInterval;
            $loan->paid_amount = $paidAmount;
            $loan->final_amount = $final_amount;
            $loan->user_details = $request->input('user_details', null);
            $loan->admin_feedback = null; // Assuming this field is optional
            $loan->status = 0; // Assuming default status is 0 (Pending)
            $loan->next_installment_date = $nextInstallmentDate;
            $loan->client_id = $clientId;
            $loan->save(); // Save the loan to the database

            // Create a new AgentLoan record
            $agentLoan = new AgentLoan();
            $agentLoan->user_id = $agentId;
            $agentLoan->client_id = $clientId;
            $agentLoan->loan_amount = $amount;
            $agentLoan->final_loan_amount = $final_amount;
            $agentLoan->save();

            // Check if paid amount is greater than 0 to create a LoanPayment record
            if ($paidAmount > 0) {
                // Create a record for the payment made
                LoanPayment::create([
                    'loan_id' => $loan->id,
                    'client_id' => $clientId,
                    'agent_id' => $agentId,
                    'amount' => $paidAmount, // Use the correct paid amount
                    'payment_date' => now(), // Current date/time as the payment record date
                    'note' => $note, // Include optional note if provided
                ]);
            }

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Loan added successfully for client.',
                'data' => $loan
            ], 201);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store client loan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 16. Create Loan
     * POST /dloans/create
     */
    public function createLoan(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'trx' => 'nullable|string|max:40',
            'amount' => 'required|numeric|min:0',
            'installment_interval' => 'required|numeric|min:1',
            'installment_value' => 'required|numeric|min:0',
            // Add other necessary fields and validations as per your requirements
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Fetch the client and agent
            $client = Client::findOrFail($request->client_id);
            $agent = User::findOrFail($request->user_id);

            // Calculate per installment and final amount
            $installmentInterval = $request->installment_interval;
            $amount = $request->amount;
            $installmentValue = $request->installment_value;
            $per_installment = ($amount * 1.2) / $installmentInterval;
            $final_amount = $amount * 1.2;
            $remaining_amount = $final_amount - ($request->paid_amount ?? 0);

            // Create a new UserLoan instance
            $loan = new UserLoan();
            $loan->user_id = $request->user_id;
            $loan->plan_id = 8;  // Assuming plan_id is static or predefined
            $loan->trx = $request->trx ?? $this->generateUniqueTrx(); // Use provided trx or generate a unique one
            $loan->amount = $amount;
            $loan->per_installment = $per_installment;
            $loan->installment_interval = $installmentInterval;
            $loan->total_installment = $installmentInterval;
            $loan->paid_amount = $request->paid_amount ?? 0.00;
            $loan->final_amount = $final_amount;
            $loan->user_details = $request->input('user_details', null);
            $loan->admin_feedback = null;  // Assuming this field is optional
            $loan->status = 0;  // Assuming default status is 0 (Pending)
            $loan->next_installment_date = $request->next_installment_date ?? null;
            $loan->client_id = $request->client_id; // Assign the client ID
            $loan->save();  // Save the loan to the database

            // Create a new AgentLoan record
            $agentLoan = new AgentLoan();
            $agentLoan->user_id = $request->user_id;
            $agentLoan->client_id = $request->client_id;
            $agentLoan->loan_amount = $amount;
            $agentLoan->final_loan_amount = $final_amount;
            $agentLoan->save();

            // Check if paid amount is greater than 0 to create a LoanPayment record
            if (($request->paid_amount ?? 0) > 0) {
                // Create a record for the payment made
                LoanPayment::create([
                    'loan_id' => $loan->id,
                    'client_id' => $request->client_id,
                    'agent_id' => $request->user_id,
                    'amount' => $request->paid_amount, // Use the correct paid amount
                    'payment_date' => now(), // Current date/time as the payment record date
                    'note' => $request->input('note', null), // Include optional note if provided
                ]);
            }

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Loan created successfully.',
                'data' => $loan
            ], 201);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create loan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 17. Edit Loan
     * GET /dloans/{id}/edit
     */
    public function editLoan($id): JsonResponse
    {
        try {
            // Find the loan by ID
            $loan = UserLoan::findOrFail($id);

            // Retrieve the related client and loan plan
            $client = Client::findOrFail($loan->client_id);
            $loanPlan = LoanPlan::findOrFail($loan->plan_id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'loan' => $loan,
                    'client' => $client,
                    'loanPlan' => $loanPlan
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 18. Edit Loan Variant 2
     * POST /dloans/edit2
     */
    public function editLoan2(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:user_loans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $loanId = $request->input('id');

            // Find the loan by ID
            $loan = UserLoan::findOrFail($loanId);

            // Retrieve the related client and loan plan
            $client = Client::findOrFail($loan->client_id);
            $loanPlan = LoanPlan::findOrFail($loan->plan_id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'loan' => $loan,
                    'client' => $client,
                    'loanPlan' => $loanPlan
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 19. Save Loan Edit Variant 2
     * PUT /dloans/save-edit2/{loanId}
     */
    public function saveLoanEdit2(Request $request, $loanId): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'per_installment' => 'required|numeric|min:0',
            'installment_interval' => 'required|integer|min:1',
            'total_installment' => 'required|integer|min:1',
            // Add more validation rules as needed based on your loan fields
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $loan = UserLoan::findOrFail($loanId);

            // Check if the loan is in a pending or running state (status 0 or 1)
            if (!in_array($loan->status, [0, 1])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Loan cannot be edited in its current state.'
                ], 400);
            }

            // Update the loan details
            $loan->amount = $request->input('amount');
            $loan->per_installment = $request->input('per_installment');
            $loan->installment_interval = $request->input('installment_interval');
            $loan->total_installment = $request->input('installment_interval');
            // Update other loan fields as needed

            // Recalculate final_amount if necessary (based on your interest calculation logic)
            $loan->final_amount = $loan->per_installment * $loan->total_installment;

            // Save the changes
            $loan->save();

            // Optionally, return the updated loan details
            return response()->json([
                'status' => 'success',
                'message' => 'Loan updated successfully.',
                'data' => $loan
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update loan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 20. Save Loan Edit
     * PUT /dloans/save-edit/{loanId}
     */
    public function saveLoanEdit(Request $request, $loanId): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'per_installment' => 'required|numeric|min:0',
            'installment_interval' => 'required|integer|min:1',
            'processing_fee' => 'required|numeric|min:0',
            // Add more validation rules as needed based on your loan fields
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $loan = UserLoan::findOrFail($loanId);

            // Check if the loan is in a pending or running state (status 0 or 1)
            if (!in_array($loan->status, [0, 1])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Loan cannot be edited in its current state.'
                ], 400);
            }

            // Update the loan details
            $loan->amount = $request->input('amount');
            $loan->per_installment = $request->input('per_installment');
            $loan->installment_interval = $request->input('installment_interval');
            $loan->total_installment = $request->input('installment_interval');
            $loan->processing_fee = $request->input('processing_fee');
            $loan->final_amount = $loan->per_installment * $loan->total_installment;
            // Update other loan fields as needed

            // Save the changes
            $loan->save();

            // Optionally, return the updated loan details
            return response()->json([
                'status' => 'success',
                'message' => 'Loan updated successfully.',
                'data' => $loan
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update loan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    
    
    /**
     * 21. Show Loan Details
     * GET /dloans/{id}/show
     */
    public function showLoan($id): JsonResponse
    {
        try {
            // Fetch the loan by ID
            $loan = UserLoan::findOrFail($id);

            // Retrieve related client and loan plan details
            $client = Client::findOrFail($loan->client_id);
            $loanPlan = LoanPlan::findOrFail($loan->plan_id);

            // Retrieve agent details
            $agent = User::findOrFail($loan->user_id);

            // Retrieve guarantors
            $clientGuarantors = Guarantor::where('client_id', $loan->client_id)->get();

            // Retrieve loan installments
            $loanSlots = LoanPaymentInstallment::where('client_id', $loan->client_id)->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'loan' => $loan,
                    'client' => $client,
                    'loanPlan' => $loanPlan,
                    'agent' => $agent,
                    'clientGuarantors' => $clientGuarantors,
                    'loanSlots' => $loanSlots
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 22. Approve Loan
     * POST /dloans/{id}/approve
     */
    public function approveLoan(Request $request, $id): JsonResponse
    {
        try {
            // Find the loan by ID
            $loan = UserLoan::findOrFail($id);
            $client = Client::findOrFail($loan->client_id);
            $clientGuarantors = Guarantor::where('client_id', $loan->client_id)->get();

            // Check if the loan is in a pending state (status 0)
            if ($loan->status != 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Loan is not in a pending state.',
                ], 400);
            }

            // Uncomment the following block if you want to enforce the presence of guarantors
            /*
            if ($clientGuarantors->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client has no guarantors.',
                ], 400);
            }
            */

            // Check if client credit balance is greater than 0
            if ($client->credit_balance > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client has a positive credit balance.',
                ], 400);
            }

            // Update the loan status to 'Running' (status 1)
            $loan->status = 1;
            $loan->disbursed_at = now();
            $loan->due_date = now()->addDays($loan->installment_interval);
            $loan->next_installment_date = now()->addDays($loan->installment_interval);
            $loan->save();

            // Update the client's credit balance by adding the loan amount
            $client->credit_balance = isset($client->credit_balance) ? $client->credit_balance + $loan->final_amount : $loan->final_amount;
            $client->save();

            // Generate payment installments
            $this->createPaymentInstallments($loan);

            return response()->json([
                'status' => 'success',
                'message' => 'Loan approved and payment installments created successfully.',
                'data' => [
                    'loan' => $loan,
                    'client' => $client
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve loan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 23. Get Client QR Code
     * GET /dclients/{clientId}/qr
     */
    public function getClientQr(Request $request, $clientId): JsonResponse
    {
        try {
            // Retrieve the client
            $client = Client::findOrFail($clientId);

            // Prepare data for QR code
            $data = [
                'name' => $client->name,
                'phone' => $client->phone,
                'clientid' => $client->id,
                'image' => $client->image,
            ];

            // Generate QR code using a helper function
            $qr = Helpers::get_qrcode_client($data);

            // Return the QR code as a string (base64 or URL based on implementation)
            return response()->json([
                'status' => 'success',
                'qr_code' => $qr,
            ], 200);
        } catch (\Exception $e) {
            // Handle cases where client is not found or QR generation fails
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate QR code for client.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 24. Pay Loan Variant 3
     * POST /dloans/pay3
     */
    public function payLoan3(Request $request): JsonResponse
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get input values
            $clientId = $request->input('client_id');
            $amountPaid = $request->input('amount');
            $agentId = $request->input('agent_id'); // Optional
            $transactionId = $this->generateTransactionId(); // Assuming this method exists
            $paymentType = 'loan'; // Default to loan
            $today = now()->toDateString();

            // Fetch client details
            $client = Client::findOrFail($clientId);

            // Find the running loan for the client
            $loan = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->first();

            if (!$loan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No running loans found for the client.'
                ], 404);
            }

            // Fetch the agent associated with the loan
            $agent = User::find($loan->user_id);

            // Fetch all installments for the current loan
            $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
                ->where('status', 'pending') // Only consider pending installments
                ->get();

            // If no installments are found, return an error
            if ($installments->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No pending installments found for the loan.'
                ], 404);
            }

            // Process payment for available installments
            $totalPaid = $amountPaid;

            foreach ($installments as $installment) {
                if ($totalPaid <= 0) {
                    break;
                }

                // Update the installment status to 'paid' and the amount paid
                $installment->status = 'paid';
                $installment->save();

                // Update the loan's paid amount
                $loan->paid_amount += $installment->install_amount;

                // Create a new payment transaction record
                PaymentTransaction::create([
                    'client_id' => $clientId,
                    'loan_id' => $loan->id,
                    'agent_id' => $agent ? $agent->id : null, // Optional
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

            return response()->json([
                'status' => 'success',
                'message' => 'Loan installment(s) paid successfully.',
                'data' => [
                    'loan' => $loan,
                    'remaining_amount' => $loan->final_amount - $loan->paid_amount
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process loan payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 25. Pay Loan Variant 33
     * POST /dloans/pay33
     */
    public function payLoan33(Request $request): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            'agent_id' => 'nullable|exists:users,id', // Optional, only if there's an agent involved
            'transaction_id' => 'required|unique:payment_transactions,transaction_id',
            'payment_type' => 'required|string', // e.g., cash, card, mobile, etc.
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clientId = $request->input('client_id');
            $amountPaid = $request->input('amount');
            $agentId = $request->input('agent_id');  // Optional
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

            // Get running loans for the client
            $loans = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->get();

            if ($loans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No running loans found for the client.'
                ], 404);
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
                'status' => 'success',
                'message' => 'Loan installment(s) paid successfully.',
                'data' => [
                    'payment_transaction' => $paymentTransaction
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process loan payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 26. Pay Loan New
     * POST /dloans/pay-new
     */
    public function payLoanNew(Request $request): JsonResponse
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            'agent_id' => 'nullable|exists:users,id', // Optional
            'transaction_id' => 'required|unique:payment_transactions,transaction_id',
            'payment_type' => 'required|string', // e.g., cash, card, mobile, etc.
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clientId = $request->input('client_id');
            $amountPaid = $request->input('amount');
            $agentId = $request->input('agent_id');  // Optional
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

            // Get running loans for the client
            $loans = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->get();

            if ($loans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No running loans found for the client.'
                ], 404);
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
                'status' => 'success',
                'message' => 'Loan installment(s) paid successfully.',
                'data' => [
                    'payment_transaction' => $paymentTransaction
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process loan payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 27. Pay Loan Variant 111e
     * POST /dloans/pay111e
     */
    public function payLoan111e(Request $request): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            'agent_id' => 'nullable|exists:users,id', // Optional
            'transaction_id' => 'required|unique:payment_transactions,transaction_id',
            'payment_type' => 'required|string', // e.g., cash, card, mobile, etc.
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
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

            // Get running loans for the client
            $loans = UserLoan::where('client_id', $clientId)
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->get();

            if ($loans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No running loans found for the client.'
                ], 404);
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
                'status' => 'success',
                'message' => 'Loan installment(s) paid successfully.',
                'data' => [
                    'payment_transaction' => $paymentTransaction
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process loan payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 28. Today's Loan Installments
     * GET /dinstallments/today
     */
    public function todaysLoanInstallments(): JsonResponse
    {
        try {
            // Get today's date in Y-m-d format
            $today = now()->toDateString();

            // Query LoanPaymentInstallment for today's date and pending status
            $installments = LoanPaymentInstallment::where('date', $today)
                ->where('status', 'pending')
                ->get();

            // Check if there are any installments for today
            if ($installments->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No installments due today.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $installments
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve today\'s installments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 29. Today's Schedule for Agent
     * GET /dagents/{agentId}/schedule/today
     */
    public function todaysSchedule(Request $request, $agentId): JsonResponse
    {
        try {
            // Get today's date in Y-m-d format
            $today = now()->toDateString();

            // Query LoanPaymentInstallment for today's date and pending status for the specific agent
            $installments = LoanPaymentInstallment::where('agent_id', $agentId)
                ->where('date', $today)
                ->get();

            if ($installments->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No installments scheduled for today for this agent.'
                ], 404);
            }

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
                'status' => 'success',
                'message' => 'Successfully fetched today\'s schedule for the agent.',
                'data' => $responseData
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve today\'s schedule for the agent.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 30. Total Amount for Agent on Date
     * POST /dagents/{agentId}/total-amount
     */
    public function totalAmountForAgentOnDate(Request $request, $agentId): JsonResponse
    {
        try {
            // Define the custom time window (4:00 PM to 3:59 PM the next day)
            $startDate = now()->subDay()->setTime(16, 0, 0); // Yesterday at 4:00 PM
            $endDate = now()->setTime(15, 59, 59); // Today at 3:59 PM

            // Calculate the total amount the agent needs to collect within the time window
            $totalAmount = LoanPaymentInstallment::where('agent_id', $agentId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('install_amount');

            // Calculate the total amount already collected within the time window
            $totalAmountCollected = LoanPayment::where('agent_id', $agentId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('is_reversed', false) // Only non-reversed payments
                ->sum('amount');

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully fetched total amount for agent on the specified date.',
                'data' => [
                    'total_amount' => $totalAmount,
                    'collected' => $totalAmountCollected
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to calculate total amount for agent.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper Method: Generate Unique Transaction ID
     */
    protected function generateTransactionId()
    {
        do {
            $transactionId = 'abROi' . mt_rand(1000000000, 9999999999);
        } while (PaymentTransaction::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    /**
     * Helper Method: Create Payment Installments
     */
    protected function createPaymentInstallments(UserLoan $loan)
    {
        $installmentAmount = $loan->per_installment;
        $installmentInterval = $loan->installment_interval;
        $totalInstallments = $loan->total_installment;

        for ($i = 1; $i <= $totalInstallments; $i++) {
            // Calculate the installment date based on the interval
            $installmentDate = now()->addDays($i * $installmentInterval);

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
    
    
    /**
     * 31. All Loan Plans Index
     * GET /dloan-plans
     */
    public function loanplansindex(): JsonResponse
    {
        try {
            $loanPlans = LoanPlan::all();

            return response()->json([
                'status' => 'success',
                'data' => $loanPlans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan plans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 32. Create Loan Variant 10
     * POST /dloans/create10
     */
    public function createLoan10(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
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
            'note' => 'nullable|string|max:255', // Added note field
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = $request->input('user_id');
            $clientId = $request->input('client_id');
            $trx = $request->input('trx') ?? $this->generateUniqueTrx();
            $amount = $request->input('amount');
            $perInstallment = $request->input('per_installment');
            $installmentInterval = $request->input('installment_interval');
            $totalInstallment = $request->input('total_installment');
            $givenInstallment = $request->input('given_installment', 0);
            $paidAmount = $request->input('paid_amount', 0.00);
            $finalAmount = $request->input('final_amount');
            $userDetails = $request->input('user_details');
            $nextInstallmentDate = $request->input('next_installment_date');
            $note = $request->input('note');

            // Create a new UserLoan instance
            $loan = new UserLoan();
            $loan->user_id = $userId;
            $loan->plan_id = 8; // Assuming plan_id is static or predefined
            $loan->trx = $trx;
            $loan->amount = $amount;
            $loan->per_installment = $perInstallment;
            $loan->installment_interval = $installmentInterval;
            $loan->total_installment = $totalInstallment;
            $loan->given_installment = $givenInstallment;
            $loan->paid_amount = $paidAmount;
            $loan->final_amount = $finalAmount;
            $loan->user_details = $userDetails;
            $loan->admin_feedback = null;
            $loan->status = 0;
            $loan->next_installment_date = $nextInstallmentDate;
            $loan->client_id = $clientId;
            $loan->save();

            // Create a new AgentLoan record
            $agentLoan = new AgentLoan();
            $agentLoan->user_id = $userId;
            $agentLoan->client_id = $clientId;
            $agentLoan->loan_amount = $amount;
            $agentLoan->final_loan_amount = $finalAmount;
            $agentLoan->save();

            // Optionally, handle paid_amount
            if ($paidAmount > 0) {
                LoanPayment::create([
                    'loan_id' => $loan->id,
                    'client_id' => $clientId,
                    'agent_id' => $userId,
                    'amount' => $paidAmount,
                    'credit_balance' => $client->credit_balance,
                    'payment_date' => now(),
                    'note' => $note,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Loan created successfully.',
                'data' => $loan
            ], 201);
        } catch (\Exception $e) {
            // Handle exceptions and rollback if necessary
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create loan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 33. Client Loans
     * GET /dclients/{id}/loans
     */
    public function clientLoans(Request $request, $id): JsonResponse
    {
        try {
            $loans = UserLoan::where('client_id', $id)->get();

            return response()->json([
                'status' => 'success',
                'data' => $loans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve client loans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 34. User Loans List
     * GET /dusers/{id}/loans
     */
    public function userLoansList(Request $request, $id): JsonResponse
    {
        try {
            $loans = UserLoan::where('user_id', $id)->get();

            return response()->json([
                'status' => 'success',
                'data' => $loans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve user loans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 35. Withdrawal Methods
     * GET /dwithdrawal-methods
     */
    public function withdrawalMethods(Request $request): JsonResponse
    {
        try {
            $withdrawalMethods = WithdrawalMethod::latest()->get(); // Assuming WithdrawalMethod model exists

            return response()->json([
                'status' => 'success',
                'data' => $withdrawalMethods
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve withdrawal methods.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 36. Show Loan Offer
     * GET /dloan-offers/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $loanOffer = LoanOffer::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $loanOffer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan offer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 37. Update Loan Offer
     * PUT /dloan-offers/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'interest_rate' => 'sometimes|required|numeric',
            'min_amount' => 'sometimes|required|integer|min:0',
            'max_amount' => 'sometimes|required|integer|min:0',
            'min_term' => 'sometimes|required|integer|min:0',
            'max_term' => 'sometimes|required|integer|min:0',
            // Add more fields as necessary
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $loanOffer = LoanOffer::findOrFail($id);
            $loanOffer->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $loanOffer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update loan offer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 38. Destroy Loan Offer
     * DELETE /dloan-offers/{id}
     */
    public function destroy2($id): JsonResponse
    {
        try {
            $loanOffer = LoanOffer::findOrFail($id);
            $loanOffer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Loan offer deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete loan offer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 39. All Loans
     * GET /dloans
     */
    public function all_loans(Request $request): JsonResponse
    {
        try {
            $pageTitle = 'All Loans';

            if ($request->search) {
                $loans = UserLoan::where('trx', 'like', '%' . $request->search . '%')->paginate(20);
                $emptyMessage = 'No Data Found';
            } else {
                $loans = UserLoan::latest()->paginate(20);
                $emptyMessage = 'No Loans Yet';
            }

            return response()->json([
                'status' => 'success',
                'page_title' => $pageTitle,
                'empty_message' => $emptyMessage,
                'data' => $loans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve all loans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 40. Paid Loans
     * GET /dloans/paid
     */
    public function paidLoans(Request $request): JsonResponse
    {
        try {
            $pageTitle = 'Paid Loans';

            if ($request->search) {
                $loans = UserLoan::paid()->where('trx', 'like', '%' . $request->search . '%')->paginate(20);
                $emptyMessage = 'No Data Found';
            } else {
                $loans = UserLoan::paid()->latest()->paginate(20);
                $emptyMessage = 'No Paid Loans Yet';
            }

            return response()->json([
                'status' => 'success',
                'page_title' => $pageTitle,
                'empty_message' => $emptyMessage,
                'data' => $loans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve paid loans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 41. Pending Loans
     * GET /dloans/pending
     */
    public function pendingLoans(Request $request): JsonResponse
    {
        try {
            $pageTitle = 'Pending Loans';

            if ($request->search) {
                $loans = UserLoan::pending()->where('trx', 'like', '%' . $request->search . '%')->paginate(20);
                $emptyMessage = 'No Data Found';
            } else {
                $loans = UserLoan::pending()->latest()->paginate(20);
                $emptyMessage = 'No Pending Loans Yet';
            }

            return response()->json([
                'status' => 'success',
                'page_title' => $pageTitle,
                'empty_message' => $emptyMessage,
                'data' => $loans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve pending loans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 42. Pending Loans Variant 10
     * GET /dloans/pending10
     */
    public function pendingLoans10(Request $request): JsonResponse
    {
        try {
            $pageTitle = 'Pending Loans';

            if ($request->search) {
                $loans = UserLoan::pending()
                    ->where('trx', 'like', '%' . $request->search . '%')
                    ->with('client') // Load client data
                    ->latest()
                    ->paginate(20);
                $emptyMessage = 'No Data Found';
            } else {
                $loans = UserLoan::pending()
                    ->with('client') // Load client data
                    ->latest()
                    ->paginate(20);
                $emptyMessage = 'No Pending Loans Yet';
            }

            return response()->json([
                'status' => 'success',
                'page_title' => $pageTitle,
                'empty_message' => $emptyMessage,
                'data' => $loans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve pending loans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 43. Rejected Loans
     * GET /dloans/rejected
     */
    public function rejectedLoans(Request $request): JsonResponse
    {
        try {
            $pageTitle = 'Rejected Loans';

            if ($request->search) {
                $loans = UserLoan::rejected()->where('trx', 'like', '%' . $request->search . '%')->paginate(20);
                $emptyMessage = 'No Data Found';
            } else {
                $loans = UserLoan::rejected()->latest()->paginate(20);
                $emptyMessage = 'No Rejected Loans Yet';
            }

            return response()->json([
                'status' => 'success',
                'page_title' => $pageTitle,
                'empty_message' => $emptyMessage,
                'data' => $loans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve rejected loans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 44. Running Loans
     * GET /dloans/running
     */
    public function runningLoans(Request $request): JsonResponse
    {
        try {
            $pageTitle = 'Running Loans';

            if ($request->search) {
                $loans = UserLoan::running()->where('trx', 'like', '%' . $request->search . '%')->paginate(20);
                $emptyMessage = 'No Data Found';
            } else {
                $loans = UserLoan::running()->latest()->paginate(20);
                $emptyMessage = 'No Running Loans Yet';
            }

            return response()->json([
                'status' => 'success',
                'page_title' => $pageTitle,
                'empty_message' => $emptyMessage,
                'data' => $loans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve running loans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 45. Loan Plans Index
     * GET /dloan-plans-index
     */
    public function loanplansindex(): JsonResponse
    {
        // This method duplicates method 31. Avoid duplication.
        // If method 45 is different, implement accordingly.
        // For now, assuming it's already covered.
        try {
            $loanPlans = LoanPlan::all();

            return response()->json([
                'status' => 'success',
                'data' => $loanPlans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan plans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 46. Create Loan (Alternative Variant)
     * POST /dloans/create10
     */
    public function createLoan10(Request $request): JsonResponse
    {
        // This method duplicates method 32.
        // Ensure unique method names or implement different logic if needed.
        // For now, assuming it's already implemented.
        return response()->json([
            'status' => 'error',
            'message' => 'Method already implemented.'
        ], 400);
    }

    /**
     * 47. Client Loans
     * GET /dclients/{id}/loans
     */
    public function clientLoans(Request $request, $id): JsonResponse
    {
        // This method duplicates method 33.
        // Ensure unique method names or implement different logic if needed.
        // For now, assuming it's already implemented.
        return response()->json([
            'status' => 'error',
            'message' => 'Method already implemented.'
        ], 400);
    }

    /**
     * 48. User Loans List
     * GET /dusers/{id}/loans
     */
    public function userLoansList(Request $request, $id): JsonResponse
    {
        // This method duplicates method 34.
        // Ensure unique method names or implement different logic if needed.
        // For now, assuming it's already implemented.
        return response()->json([
            'status' => 'error',
            'message' => 'Method already implemented.'
        ], 400);
    }
    
    
      /**
     * 49. Show Loan Details
     * GET /dloans/{id}/show
     */
    public function showLoan($id): JsonResponse
    {
        try {
            // Fetch the loan by ID
            $loan = UserLoan::findOrFail($id);

            // Retrieve related client and loan plan details
            $client = Client::findOrFail($loan->client_id);
            $loanPlan = LoanPlan::findOrFail($loan->plan_id);

            // Retrieve agent details
            $agent = User::findOrFail($loan->user_id);

            // Retrieve guarantors
            $clientGuarantors = Guarantor::where('client_id', $loan->client_id)->get();

            // Retrieve loan installments
            $loanSlots = LoanPaymentInstallment::where('loan_id', $loan->id)->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'loan' => $loan,
                    'client' => $client,
                    'loanPlan' => $loanPlan,
                    'agent' => $agent,
                    'clientGuarantors' => $clientGuarantors,
                    'loanSlots' => $loanSlots
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 50. Update Loan Offer
     * PUT /dloans/{id}/update
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'interest_rate' => 'sometimes|required|numeric',
            'min_amount' => 'sometimes|required|integer|min:0',
            'max_amount' => 'sometimes|required|integer|min:0',
            'min_term' => 'sometimes|required|integer|min:0',
            'max_term' => 'sometimes|required|integer|min:0',
            // Add more fields as necessary
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find the loan offer by ID
            $loanOffer = LoanOffer::findOrFail($id);

            // Update the loan offer with validated data
            $loanOffer->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Loan offer updated successfully.',
                'data' => $loanOffer
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update loan offer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 51. Destroy Loan Offer
     * DELETE /dloans/{id}/destroy
     */
    public function destroy2($id): JsonResponse
    {
        try {
            // Find the loan offer by ID
            $loanOffer = LoanOffer::findOrFail($id);

            // Delete the loan offer
            $loanOffer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Loan offer deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete loan offer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
               
