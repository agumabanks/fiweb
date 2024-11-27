<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLoan;
use App\Models\Client;
use App\Models\User;
use App\Models\LoanPayment;
use App\Models\SanaaCard;
use App\Models\LoanPaymentInstallment;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    // Return all performance metrics in one function
    public function getAllPerformanceMetrics(Request $request)
    {
        // Loan Performance: Total loans issued
        $totalLoans = UserLoan::count();

        // Loan Performance: Loan repayment rate
        $repaidLoans = UserLoan::where('status', 'repaid')->count();
        $repaymentRate = ($totalLoans > 0) ? ($repaidLoans / $totalLoans) * 100 : 0;

        // Loan Performance: Loan default rate
        $defaultedLoans = UserLoan::where('status', 'defaulted')->count();
        $defaultRate = ($totalLoans > 0) ? ($defaultedLoans / $totalLoans) * 100 : 0;

        // Institutional Performance: Portfolio at Risk (PAR)
        $loansInArrears = UserLoan::where('status', 'in_arrears')->sum('balance');
        $totalPortfolio = UserLoan::sum('amount');
        $par = ($totalPortfolio > 0) ? ($loansInArrears / $totalPortfolio) * 100 : 0;

        // Institutional Performance: Liquidity metrics
        $cashReserves = 500000; // Example reserve data (you can make this dynamic)
        $liabilities = 200000;  // Example liability data (you can make this dynamic)
        $liquidity = $cashReserves - $liabilities;

        // Agent Performance: Agent ranking by successful collections
        $agentRankings = DB::table('loan_payment_installments')
            ->select(DB::raw('agent_id, count(*) as successful_collections'))
            ->where('status', 'paid')
            ->groupBy('agent_id')
            ->orderBy('successful_collections', 'desc')
            ->get();

        // Optionally, if specific agentId is passed, get agent-specific data
        $agentId = $request->query('agentId');
        if ($agentId) {
            $agentLoans = UserLoan::where('agent_id', $agentId)->count();
            $successfulCollections = LoanPaymentInstallment::where('agent_id', $agentId)->where('status', 'paid')->count();
        } else {
            $agentLoans = null;
            $successfulCollections = null;
        }

        // Optionally, if specific clientId is passed, get client-specific data
        $clientId = $request->query('clientId');
        if ($clientId) {
            $creditHistory = UserLoan::where('client_id', $clientId)->get();
            $clientActivity = LoanPayment::where('client_id', $clientId)->count();
        } else {
            $creditHistory = null;
            $clientActivity = null;
        }

        // Return all metrics in one JSON response
        return response()->json([
            'loan_performance' => [
                'total_loans' => $totalLoans,
                'repayment_rate' => $repaymentRate,
                'default_rate' => $defaultRate,
                'portfolio_at_risk' => $par,
            ],
            'institutional_performance' => [
                'liquidity' => $liquidity,
                'cash_reserves' => $cashReserves,
                'liabilities' => $liabilities,
            ],
            'agent_performance' => [
                'agent_loans' => $agentLoans,
                'successful_collections' => $successfulCollections,
                'agent_rankings' => $agentRankings,
            ],
            'client_performance' => [
                'credit_history' => $creditHistory,
                'client_activity' => $clientActivity,
            ],
        ]);
    }
}
