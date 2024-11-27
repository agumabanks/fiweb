<?php
namespace App\Http\Controllers;

use App\Models\LoanPayment;
use App\Models\UserLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentLoanTransactionController extends Controller
{
    
    // get agent clients total
    public function getAgentClientsSum($agentId)
    {
        // Validate if agent exists
        $agentExists = DB::table('users')->where('id', $agentId)->exists();
        if (!$agentExists) {
            return response()->json(['status' => false, 'message' => 'Agent not found'], 404);
        }
    
        // Count the total number of unique clients associated with this agent
        $totalClients = UserLoan::where('user_id', $agentId)  // Assuming user_id is the agent's ID
            ->join('clients', 'user_loans.client_id', '=', 'clients.id') // Join with clients table if necessary
            ->distinct('client_id') // Ensure only unique clients are counted
            ->count('client_id');  // Count unique clients
    
        // Return the total number of clients
        return response()->json([
            'status' => true,
            'message' => 'Total number of unique clients fetched successfully',
            'total_clients' => $totalClients,
        ], 200);
    }

    
    public function getLoanTransactions2($agentId)
    {
        // Validate if agent exists (optional, but recommended)
        $agentExists = DB::table('users')->where('id', $agentId)->exists();
        if (!$agentExists) {
            return response()->json(['status' => false, 'message' => 'Agent not found'], 404);
        }

        // Fetch the loan transactions for the agent's clients
        $loanTransactions = LoanPayment::whereHas('loan', function($query) use ($agentId) {
            $query->where('user_id', $agentId);
        })
        ->select('loan_id', 'amount', 'payment_date', 'is_reversed')
        ->orderBy('payment_date', 'desc')
        ->get();

        // Return the loan transaction history in a structured JSON response
        return response()->json([
            'status' => true,
            'message' => 'Loan transaction history fetched successfully',
            'data' => $loanTransactions,
        ], 200);
    }
    
    public function getLoanTransactions($agentId)
    {
        // Validate if agent exists (optional, but recommended)
        $agentExists = DB::table('users')->where('id', $agentId)->exists();
        if (!$agentExists) {
            return response()->json(['status' => false, 'message' => 'Agent not found'], 404);
        }
    
        // Fetch loan transactions with eager loading for loans and clients
        $loanTransactions = LoanPayment::with(['loan.client']) // Eager load loan and client relations
            ->whereHas('loan', function($query) use ($agentId) {
                $query->where('user_id', $agentId); // Assuming user_id is the agent's ID
            })
            ->where('is_reversed', 0) // Exclude reversed payments
            ->select('loan_id', 'amount', 'payment_date')
            ->orderBy('payment_date', 'desc')
            ->get();
    
        // Loop through the transactions and structure the response data
        $responseData = $loanTransactions->map(function($transaction) {
            return [
                'client_name' => $transaction->loan->client->name ?? 'Unknown', // Client name
                'client_id' => $transaction->loan->client->id ?? 'Unknown',     // Client ID
                'amount_paid' => $transaction->amount,                          // Amount paid
                'payment_date' => $transaction->payment_date,                    // Payment date
                'is_reversed' => $transaction->is_reversed == 1 ? true : false, 
            ];
        });
    
        // Return the loan transaction history in a structured JSON response
        return response()->json([
            'status' => true,
            'message' => 'Loan transaction history fetched successfully',
            'data' => $responseData,
        ], 200);
    }

}
