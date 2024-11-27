<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLoan;
use App\Models\LoanPayment;
use App\Models\Client;
use App\Models\User;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DailyReportController extends Controller
{
    /**
     * Get the daily report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailyReport(Request $request)
    {
        // Retrieve the 'date' from the request, or set a default value if not provided
        $date = $request->input('date', now()->toDateString());

        $data = [
            'date' => $date,
            'getLoanStatistics' => $this->getLoanStatistics($request), // Pass the request object
            
        ];
        // 'getCashflowReport' => $this->getCashflowReport($request), // Pass the request object

        return response()->json($data, 200);
    }

    /**
     * Get the daily loan statistics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoanStatistics(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Fetch loan statistics
        $loansDisbursed = UserLoan::whereDate('created_at', $date)->sum('amount');
        $numberOfLoansDisbursed = UserLoan::whereDate('created_at', $date)->count();

        $loansRepaid = LoanPayment::whereDate('payment_date', $date)->sum('amount');
        $numberOfLoansRepaid = LoanPayment::whereDate('payment_date', $date)->count();

        $overdueLoans = UserLoan::where('status', '<>', 2)
            ->where('due_date', '<', $date)
            ->sum('final_amount'); // Changed from 'remaining_amount' to 'final_amount'

        $numberOfOverdueLoans = UserLoan::where('status', '<>', 2)
            ->where('due_date', '<', $date)
            ->count();

        $data = [
            'date' => $date,
            'loans_disbursed' => $loansDisbursed,
            'number_of_loans_disbursed' => $numberOfLoansDisbursed,
            'loans_repaid' => $loansRepaid,
            'number_of_loans_repaid' => $numberOfLoansRepaid,
            'overdue_loans_amount' => $overdueLoans,
            'number_of_overdue_loans' => $numberOfOverdueLoans,
        ];

        return response()->json($data, 200);
    }

    /**
     * Get the daily cash flow report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCashflowReport(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Cash inflows (loan repayments)
        $cashInflows = LoanPayment::whereDate('payment_date', $date)->sum('amount');

        // Cash outflows (loan disbursements and expenses)
        $cashOutflowsLoans = UserLoan::whereDate('disbursed_at', $date)->sum('amount');
        $cashOutflowsExpenses = Expense::whereDate('created_at', $date)->sum('amount');
        $cashOutflows = $cashOutflowsLoans + $cashOutflowsExpenses;

        // Net cash flow
        $netCashFlow = $cashInflows - $cashOutflows;

        $data = [
            'date' => $date,
            'cash_inflows' => $cashInflows,
            'cash_outflows' => $cashOutflows,
            'net_cash_flow' => $netCashFlow,
        ];

        return response()->json($data, 200);
    }

    /**
     * Get the agents report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgentsReport(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Fetch agents performance
        $agents = User::all();

        $agentsData = [];

        foreach ($agents as $agent) {
            $loansDisbursed = UserLoan::where('user_id', $agent->id)
                ->whereDate('created_at', $date)
                ->sum('amount');

            $numberOfLoansDisbursed = UserLoan::where('user_id', $agent->id)
                ->whereDate('created_at', $date)
                ->count();

            $paymentsCollected = LoanPayment::where('agent_id', $agent->id)
                ->whereDate('payment_date', $date)
                ->sum('amount');

            $numberOfPaymentsCollected = LoanPayment::where('agent_id', $agent->id)
                ->whereDate('payment_date', $date)
                ->count();

            $agentsData[] = [
                'agent_id' => $agent->id,
                'agent_name' => $agent->f_name . ' ' . $agent->l_name,
                'loans_disbursed' => $loansDisbursed,
                'number_of_loans_disbursed' => $numberOfLoansDisbursed,
                'payments_collected' => $paymentsCollected,
                'number_of_payments_collected' => $numberOfPaymentsCollected,
            ];
        }

        $data = [
            'date' => $date,
            'agents' => $agentsData,
        ];

        return response()->json($data, 200);
    }

    /**
     * Get the clients report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClientsReport(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        $newClients = Client::whereDate('created_at', $date)->count();
        $activeClients = Client::where('status', 'active')->count();

        $data = [
            'date' => $date,
            'new_clients' => $newClients,
            'total_active_clients' => $activeClients,
        ];

        return response()->json($data, 200);
    }

    /**
     * Get the new loans report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewLoansReport(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        $newLoans = UserLoan::with(['client', 'agent'])
            ->whereDate('created_at', $date)
            ->get();

        $data = [
            'date' => $date,
            'new_loans' => $newLoans,
        ];

        return response()->json($data, 200);
    }

    /**
     * Get the financial summary.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFinancialSummary(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Income Statement
        $interestIncome     = LoanPayment::whereDate('payment_date', $date)->sum('amount') * 0.2;
        $feeIncome          = UserLoan::whereDate('disbursed_at', $date)->sum('processing_fee');
        $totalRevenue       = $interestIncome + $feeIncome;
        $operatingExpenses  = Expense::whereDate('created_at', $date)->sum('amount');
        $netIncome          = $totalRevenue - $operatingExpenses;

        // Balance Sheet Components (as of the date)
        $cashBalance        = $this->getCashBalance($date);
        $loanReceivables    = UserLoan::where('status', '<>', 2)->sum('amount');
        $clientDeposits     = Client::sum('credit_balance');

        $totalAssets        = $cashBalance + $loanReceivables;
        $totalLiabilities   = $clientDeposits;
        $equity             = $totalAssets - $totalLiabilities;

        $data = [
            'date' => $date,
            'income_statement' => [
                'interest_income' => $interestIncome,
                'fee_income' => $feeIncome,
                'total_revenue' => $totalRevenue,
                'operating_expenses' => $operatingExpenses,
                'net_income' => $netIncome,
            ],
            'balance_sheet' => [
                'assets' => [
                    'cash_balance' => $cashBalance,
                    'loan_receivables' => $loanReceivables,
                    'total_assets' => $totalAssets,
                ],
                'liabilities' => [
                    'client_deposits' => $clientDeposits,
                    'total_liabilities' => $totalLiabilities,
                ],
                'equity' => $equity,
            ],
        ];

        return response()->json($data, 200);
    }

    /**
     * Get the cash balance as of a given date.
     *
     * @param string $date
     * @return float
     */
    private function getCashBalance($date)
    {
        // Starting cash balance
        $startingCashBalance = 100000; // Replace with actual starting balance

        // Cash inflows (loan repayments)
        $cashInflows = LoanPayment::whereDate('payment_date', '<=', $date)->sum('amount');

        // Cash outflows (loan disbursements and expenses)
        $cashOutflowsLoans = UserLoan::whereDate('created_at', '<=', $date)->sum('amount');
        $cashOutflowsExpenses = Expense::whereDate('created_at', '<=', $date)->sum('amount');
        $cashOutflows = $cashOutflowsLoans + $cashOutflowsExpenses;

        // Cash balance
        $cashBalance = $startingCashBalance + $cashInflows - $cashOutflows;

        return $cashBalance;
    }
}
