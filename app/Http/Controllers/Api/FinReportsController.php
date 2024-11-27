<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserLoan;
use App\Models\Client;
use App\Models\User;
use App\Models\LoanPayment;
use App\Models\LoanPaymentInstallment;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\AgentPerformance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FinReportsController extends Controller
{
    /**
     * Get the daily financial report data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailyFinancialReport(Request $request)
    {
        // Ensure the user is authorized to view reports
        // $this->authorize('view_reports'); // Uncomment if using policies

        // Get date from request or default to today
        $date = $request->input('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Generate a unique cache key based on parameters
        $cacheKey = 'daily_financial_report_' . $date;

        $reportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
            // **Income Statement Calculations**

            // Total Interest Income from loans
            $interestIncome = LoanPayment::whereDate('payment_date', $date)
                ->sum('interest_amount'); // Assume you have an 'interest_amount' field

            // Total Fee Income (e.g., processing fees)
            $feeIncome = Transaction::whereDate('created_at', $date)
                ->where('type', 'fee')
                ->sum('amount'); // Assume you have a 'Transaction' model recording fees

            // Total Revenues
            $totalRevenues = $interestIncome + $feeIncome;

            // Total Operating Expenses
            $operatingExpenses = Expense::whereDate('expense_date', $date)
                ->sum('amount'); // Assume you have an 'Expense' model

            // Loan Loss Provisions (e.g., 1% of total outstanding loans)
            $outstandingLoans = UserLoan::where('status', 1)->sum('remaining_amount');
            $loanLossProvisionRate = 0.01; // 1%
            $loanLossProvisions = $outstandingLoans * $loanLossProvisionRate;

            // Total Expenses
            $totalExpenses = $operatingExpenses + $loanLossProvisions;

            // Net Income
            $netIncome = $totalRevenues - $totalExpenses;

            // **Balance Sheet Calculations**

            // Assets
            $cashAndCashEquivalents = $this->getCashBalance($date);
            $loanReceivables = UserLoan::where('status', '<>', 2)->sum('remaining_amount');
            $equipmentValue = $this->getEquipmentValue(); // Implement this method as needed

            $totalAssets = $cashAndCashEquivalents + $loanReceivables + $equipmentValue;

            // Liabilities
            $clientDeposits = Client::sum('deposit_balance'); // Assume clients have 'deposit_balance'
            $borrowings = $this->getBorrowings(); // Implement this method if you have borrowings

            $totalLiabilities = $clientDeposits + $borrowings;

            // Equity
            $equity = $totalAssets - $totalLiabilities;

            // **Cash Flow Statement Calculations**

            // Cash Flows from Operating Activities
            $cashFromLoanRepayments = LoanPayment::whereDate('payment_date', $date)->sum('amount');
            $cashPaidForExpenses = Expense::whereDate('expense_date', $date)->sum('amount');

            $netCashFromOperatingActivities = $cashFromLoanRepayments - $cashPaidForExpenses;

            // Cash Flows from Investing Activities
            $cashUsedInInvestingActivities = $this->getCashUsedInInvesting($date); // Implement as needed

            // Cash Flows from Financing Activities
            $cashFromBorrowings = $this->getCashFromBorrowings($date); // Implement as needed
            $cashUsedForDebtRepayment = $this->getCashUsedForDebtRepayment($date); // Implement as needed

            $netCashFromFinancingActivities = $cashFromBorrowings - $cashUsedForDebtRepayment;

            // Net Increase in Cash and Cash Equivalents
            $netIncreaseInCash = $netCashFromOperatingActivities
                                + $cashUsedInInvestingActivities
                                + $netCashFromFinancingActivities;

            // **Closing Balances**

            // Closing Cash Balance
            $closingCashBalance = $cashAndCashEquivalents + $netIncreaseInCash;

            // Prepare the report data
            $reportData = [
                'date' => $date,
                'income_statement' => [
                    'interest_income' => $interestIncome,
                    'fee_income' => $feeIncome,
                    'total_revenues' => $totalRevenues,
                    'operating_expenses' => $operatingExpenses,
                    'loan_loss_provisions' => $loanLossProvisions,
                    'total_expenses' => $totalExpenses,
                    'net_income' => $netIncome,
                ],
                'balance_sheet' => [
                    'assets' => [
                        'cash_and_cash_equivalents' => $cashAndCashEquivalents,
                        'loan_receivables' => $loanReceivables,
                        'equipment_value' => $equipmentValue,
                        'total_assets' => $totalAssets,
                    ],
                    'liabilities' => [
                        'client_deposits' => $clientDeposits,
                        'borrowings' => $borrowings,
                        'total_liabilities' => $totalLiabilities,
                    ],
                    'equity' => $equity,
                ],
                'cash_flow_statement' => [
                    'cash_from_operating_activities' => $netCashFromOperatingActivities,
                    'cash_used_in_investing_activities' => $cashUsedInInvestingActivities,
                    'cash_from_financing_activities' => $netCashFromFinancingActivities,
                    'net_increase_in_cash' => $netIncreaseInCash,
                    'closing_cash_balance' => $closingCashBalance,
                ],
                'closing_balances' => [
                    'cash_balance' => $closingCashBalance,
                    'loan_balance' => $loanReceivables,
                    'client_deposit_balance' => $clientDeposits,
                ],
            ];

            return $reportData;
        });

        // Return JSON response
        return response()->json($reportData, 200);
    }

    /**
     * Get loans statistics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoansStatistics(Request $request)
    {
        // Ensure the user is authorized
        // $this->authorize('view_reports');

        $startDate = $request->input('start_date', Carbon::today()->subMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        // Validate date formats
        try {
            $startDateObj = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDateObj = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Generate cache key
        $cacheKey = 'loans_statistics_' . md5($startDate . $endDate);

        $reportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($startDate, $endDate) {
            $totalLoansDisbursed = UserLoan::whereBetween('created_at', [$startDate, $endDate])->sum('amount');
            $numberOfLoansDisbursed = UserLoan::whereBetween('created_at', [$startDate, $endDate])->count();

            $loansByStatus = UserLoan::select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('status')
                ->get();

            // Prepare the data
            $reportData = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_loans_disbursed' => $totalLoansDisbursed,
                'number_of_loans_disbursed' => $numberOfLoansDisbursed,
                'loans_by_status' => $loansByStatus,
            ];

            return $reportData;
        });

        return response()->json($reportData, 200);
    }

    /**
     * Get the cashflow report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCashflowReport(Request $request)
    {
        // Ensure the user is authorized
        // $this->authorize('view_reports');

        $startDate = $request->input('start_date', Carbon::today()->subMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        // Validate date formats
        try {
            $startDateObj = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDateObj = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Generate cache key
        $cacheKey = 'cashflow_report_' . md5($startDate . $endDate);

        $reportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($startDate, $endDate) {
            // Cash Flows from Operating Activities
            $cashFromLoanRepayments = LoanPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
            $cashPaidForExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');

            $netCashFromOperatingActivities = $cashFromLoanRepayments - $cashPaidForExpenses;

            // Cash Flows from Investing Activities
            $cashUsedInInvestingActivities = $this->getCashUsedInInvestingPeriod($startDate, $endDate); // Implement as needed

            // Cash Flows from Financing Activities
            $cashFromBorrowings = $this->getCashFromBorrowingsPeriod($startDate, $endDate); // Implement as needed
            $cashUsedForDebtRepayment = $this->getCashUsedForDebtRepaymentPeriod($startDate, $endDate); // Implement as needed

            $netCashFromFinancingActivities = $cashFromBorrowings - $cashUsedForDebtRepayment;

            // Net Increase in Cash and Cash Equivalents
            $netIncreaseInCash = $netCashFromOperatingActivities
                                + $cashUsedInInvestingActivities
                                + $netCashFromFinancingActivities;

            // Prepare the data
            $reportData = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'cash_flow_statement' => [
                    'cash_from_operating_activities' => $netCashFromOperatingActivities,
                    'cash_used_in_investing_activities' => $cashUsedInInvestingActivities,
                    'cash_from_financing_activities' => $netCashFromFinancingActivities,
                    'net_increase_in_cash' => $netIncreaseInCash,
                ],
            ];

            return $reportData;
        });

        return response()->json($reportData, 200);
    }

    /**
     * Get agents report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgentsReport(Request $request)
    {
        // Ensure the user is authorized
        // $this->authorize('view_reports');

        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->endOfMonth()->toDateString());

        // Validate date formats
        try {
            $startDateObj = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDateObj = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Generate cache key
        $cacheKey = 'agents_report_' . md5($startDate . $endDate);

        $reportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($startDate, $endDate) {
            // Fetch agents
            $agents = User::whereHas('roles', function ($query) {
                $query->where('name', 'agent');
            })->get();

            $agentsData = [];

            foreach ($agents as $agent) {
                $loansDisbursed = UserLoan::where('user_id', $agent->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');

                $numberOfLoansDisbursed = UserLoan::where('user_id', $agent->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $paymentsCollected = LoanPayment::where('agent_id', $agent->id)
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount');

                $agentsData[] = [
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->f_name . ' ' . $agent->l_name,
                    'total_loans_disbursed' => $loansDisbursed,
                    'number_of_loans_disbursed' => $numberOfLoansDisbursed,
                    'total_payments_collected' => $paymentsCollected,
                ];
            }

            // Prepare the data
            $reportData = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'agents' => $agentsData,
            ];

            return $reportData;
        });

        return response()->json($reportData, 200);
    }

    /**
     * Get financial summary.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFinancialSummary(Request $request)
    {
        // Ensure the user is authorized
        // $this->authorize('view_reports');

        // Use the existing methods to compile the financial summary
        $date = $request->input('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use Y-m-d'], 400);
        }

        // Generate cache key
        $cacheKey = 'financial_summary_' . $date;

        $reportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
            // You can call the methods directly or compile the summary here
            $dailyFinancialReport = $this->getDailyFinancialReport(new Request(['date' => $date]))->getData();

            // Extract needed data
            $incomeStatement = $dailyFinancialReport->income_statement ?? null;
            $balanceSheet = $dailyFinancialReport->balance_sheet ?? null;
            $cashFlowStatement = $dailyFinancialReport->cash_flow_statement ?? null;

            // Prepare the financial summary
            $reportData = [
                'date' => $date,
                'income_statement' => $incomeStatement,
                'balance_sheet' => $balanceSheet,
                'cash_flow_statement' => $cashFlowStatement,
            ];

            return $reportData;
        });

        return response()->json($reportData, 200);
    }

    // Helper methods

    private function getCashBalance($date)
    {
        // Implement logic to calculate cash balance as of the given date
        // This may include starting balance + cash inflows - cash outflows
        // For simplicity, return a dummy value
        return 100000; // Replace with actual calculation
    }

    private function getEquipmentValue()
    {
        // Implement logic to get current value of equipment
        // This may involve depreciation calculations
        // For simplicity, return a dummy value
        return 50000; // Replace with actual calculation
    }

    private function getBorrowings()
    {
        // Implement logic to get total borrowings
        // For simplicity, return a dummy value
        return 20000; // Replace with actual calculation
    }

    private function getCashUsedInInvesting($date)
    {
        // Implement logic to calculate cash used in investing activities on the given date
        // For simplicity, return a dummy value
        return -10000; // Negative value indicates cash outflow
    }

    private function getCashFromBorrowings($date)
    {
        // Implement logic to calculate cash from borrowings on the given date
        // For simplicity, return a dummy value
        return 0; // Replace with actual calculation
    }

    private function getCashUsedForDebtRepayment($date)
    {
        // Implement logic to calculate cash used for debt repayment on the given date
        // For simplicity, return a dummy value
        return -5000; // Negative value indicates cash outflow
    }

    // Methods for period-based calculations
    private function getCashUsedInInvestingPeriod($startDate, $endDate)
    {
        // Implement logic for the period
        return -20000; // Replace with actual calculation
    }

    private function getCashFromBorrowingsPeriod($startDate, $endDate)
    {
        // Implement logic for the period
        return 50000; // Replace with actual calculation
    }

    private function getCashUsedForDebtRepaymentPeriod($startDate, $endDate)
    {
        // Implement logic for the period
        return -10000; // Replace with actual calculation
    }
}
