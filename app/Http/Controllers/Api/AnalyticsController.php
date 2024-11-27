<?php

// namespace App\Http\Controllers\API;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// class AnalyticsController extends Controller
// {
//     //
// }
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Agent;
use App\Models\Client;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get overall financial summary.
     */
    public function financialSummary(Request $request)
    {
        // Ensure the user is authenticated
        $this->authorize('viewAnalytics', User::class);

        // Calculate total loans disbursed
        $totalLoansDisbursed = Loan::sum('amount');

        // Calculate total repayments
        $totalRepayments = Payment::sum('amount');

        // Calculate outstanding balances
        $outstandingBalance = Loan::where('status', 'active')->sum('remaining_amount');

        // Prepare response
        return response()->json([
            'total_loans_disbursed' => $totalLoansDisbursed,
            'total_repayments' => $totalRepayments,
            'outstanding_balance' => $outstandingBalance,
        ], 200);
    }

    /**
     * Get agent performance metrics.
     */
    public function agentPerformance(Request $request, $agentId)
    {
        // Ensure the user is authorized
        $this->authorize('viewAgentPerformance', Agent::class);

        // Fetch agent data
        $agent = Agent::findOrFail($agentId);

        // Calculate metrics
        $loansManaged = $agent->loans()->count();
        $totalCollected = $agent->payments()->sum('amount');
        $collectionRate = $agent->calculateCollectionRate();

        // Prepare response
        return response()->json([
            'agent' => $agent->name,
            'loans_managed' => $loansManaged,
            'total_collected' => $totalCollected,
            'collection_rate' => $collectionRate,
        ], 200);
    }

    /**
     * Get loan portfolio analysis.
     */
    public function portfolioAnalysis(Request $request)
    {
        // Ensure the user is authorized
        $this->authorize('viewAnalytics', User::class);

        // Calculate portfolio data
        $totalLoans = Loan::count();
        $activeLoans = Loan::where('status', 'active')->count();
        $defaultedLoans = Loan::where('status', 'defaulted')->count();

        // Prepare response
        return response()->json([
            'total_loans' => $totalLoans,
            'active_loans' => $activeLoans,
            'defaulted_loans' => $defaultedLoans,
        ], 200);
    }
    
    
    public function agentReport(Request $request, $agentId)
{
    $agent = Agent::with('loans', 'payments')->findOrFail($agentId);

    // Calculate metrics
    $loansManaged = $agent->loans()->count();
    $totalDisbursed = $agent->loans()->sum('amount');
    $totalCollected = $agent->payments()->sum('amount');
    $collectionRate = $totalDisbursed > 0 ? ($totalCollected / $totalDisbursed) * 100 : 0;

    // Prepare response
    return response()->json([
        'agent' => $agent->name,
        'loans_managed' => $loansManaged,
        'total_disbursed' => $totalDisbursed,
        'total_collected' => $totalCollected,
        'collection_rate' => $collectionRate,
    ], 200);
}
}
