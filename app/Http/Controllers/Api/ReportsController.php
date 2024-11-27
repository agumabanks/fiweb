<?php

namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// class ReportsController extends Controller
// {
//     //
// }


 

// namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLoan;
use App\Models\Client;
use App\Models\User;
use App\Models\LoanPayment;
use App\Models\LoanPaymentInstallment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    /**
     * Get the daily report data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailyReport(Request $request)
    {
        // Ensure the user is authorized to view reports
        // $this->authorize('view_reports'); // Uncomment if using policies

        // Get date from request or default to today
        $date = $request->input('date', Carbon::today()->toDateString());
        $agentId = $request->input('agent_id'); // Optional agent filter

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Generate a unique cache key based on parameters
        $cacheKey = 'daily_report_' . md5(json_encode($request->all()));

        $reportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date, $agentId) {
            // Build queries with optional agent filter
            $loansQuery = UserLoan::whereDate('created_at', $date);
            $paymentsQuery = LoanPayment::whereDate('payment_date', $date);
            $installmentsQuery = LoanPaymentInstallment::whereDate('date', $date);

            if ($agentId) {
                $loansQuery->where('user_id', $agentId);
                $paymentsQuery->where('agent_id', $agentId);
                $installmentsQuery->where('agent_id', $agentId);
            }

            // Fetch data
            $totalLoansDisbursed = $loansQuery->sum('amount');
            $totalPaymentsCollected = $paymentsQuery->sum('amount');
            $totalInstallmentsDue = $installmentsQuery->sum('install_amount');
            $totalInstallmentsPaid = $installmentsQuery->where('status', 'paid')->sum('install_amount');

            // Additional metrics
            $numberOfLoansDisbursed = $loansQuery->count();
            $numberOfPaymentsCollected = $paymentsQuery->count();
            $numberOfInstallmentsDue = $installmentsQuery->count();
            $numberOfInstallmentsPaid = $installmentsQuery->where('status', 'paid')->count();

            // Prepare the data
            $reportData = [
                'date' => $date,
                'agent_id' => $agentId,
                'total_loans_disbursed' => $totalLoansDisbursed,
                'number_of_loans_disbursed' => $numberOfLoansDisbursed,
                'total_payments_collected' => $totalPaymentsCollected,
                'number_of_payments_collected' => $numberOfPaymentsCollected,
                'total_installments_due' => $totalInstallmentsDue,
                'number_of_installments_due' => $numberOfInstallmentsDue,
                'total_installments_paid' => $totalInstallmentsPaid,
                'number_of_installments_paid' => $numberOfInstallmentsPaid,
                // Additional data...
            ];

            return $reportData;
        });

        // Return JSON response
        return response()->json($reportData, 200);
    }

    /**
     * Get the agent report data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgentReport(Request $request)
    {
        // Ensure the user is authorized to view reports
        // $this->authorize('view_reports'); // Uncomment if using policies

        // Get agent ID and date range from request
        $agentId = $request->input('agent_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Validate agent ID
        if (!$agentId) {
            return response()->json(['error' => 'Agent ID is required'], 400);
        }

        // Validate date format
        try {
            $startDateObj = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDateObj = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Fetch agent data
        $agent = User::find($agentId);
        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        // Generate a unique cache key based on parameters
        $cacheKey = 'agent_report_' . md5(json_encode($request->all()));

        $reportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($agentId, $startDate, $endDate, $agent) {
            // Fetch data related to the agent
            $loansDisbursed = UserLoan::with('client')
                ->where('user_id', $agentId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $paymentsCollected = LoanPayment::with('client')
                ->where('agent_id', $agentId)
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->get();

            $totalLoansDisbursed = $loansDisbursed->sum('amount');
            $totalPaymentsCollected = $paymentsCollected->sum('amount');

            // Prepare the data
            $reportData = [
                'agent' => [
                    'id' => $agent->id,
                    'name' => $agent->f_name . ' ' . $agent->l_name,
                ],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_loans_disbursed' => $totalLoansDisbursed,
                'number_of_loans_disbursed' => $loansDisbursed->count(),
                'loans_disbursed' => $loansDisbursed,
                'total_payments_collected' => $totalPaymentsCollected,
                'number_of_payments_collected' => $paymentsCollected->count(),
                'payments_collected' => $paymentsCollected,
                // Additional data...
            ];

            return $reportData;
        });

        // Return JSON response
        return response()->json($reportData, 200);
    }

    // Additional methods for other reports can be added here

}
