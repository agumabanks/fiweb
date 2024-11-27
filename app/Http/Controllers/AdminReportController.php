<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\Cashflow;
use App\Models\LoanPayment;
use App\Models\SavingsTransaction;
use App\Models\UserLoan;
use App\Models\ExcessFund;
use App\Models\Membership;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use Illuminate\Support\Facades\Schema;

class AdminReportController extends Controller
{

    public function createActualCash(Request $request){
        $clients = Users::all();
        return view('admin-views.cashflow.actualCash', compact('clients'));
    }


    public function storeActualCash(Request $request)
        {
            // Validate input data
            $validated = $request->validate([
                // 'user_id' => 'required|exists:users,id', // Ensure this exists in the users table
                'amount' => 'required|numeric|min:0',
                // 'date_added' => 'required|date',
            ]);

            // Insert data directly into the actual_cash table
            DB::table('actual_cash')->insert([
                'user_id' => 61,
                'amount' => $validated['amount'],
                'date_added' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('admin.report.index')->with('success', 'Actual cash record added successfully.');
        }



    public function index(Request $request)
    {
        Log::info('Request received', ['request' => $request->all()]);
    
        // Validate the input period and dates
        $request->validate([
            'period'      => 'nullable|in:daily,weekly,monthly,custom',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);
    
        $period = $request->input('period', 'daily');
    
        // Fetch the start and end dates based on the selected period or custom dates
        if ($period === 'custom') {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate   = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            [$startDate, $endDate] = $this->getDateRange($period);
        }
    
        // Retrieve report data for the selected date range
        $financialSummary   = $this->getFinancialSummary($startDate, $endDate);
        $agentReportData    = $this->getAgentReportData($startDate, $endDate);
        $loanStatistics     = $this->getLoanStatistics($startDate, $endDate);
        $keyHighlights      = $this->getKeyHighlights($startDate, $endDate);
        $clientReportData   = $this->getClientReportData($startDate, $endDate);
    
        $pageTitle = ucfirst($period) . " Report";
    
        return view('admin-views.reports.index', compact(
            'financialSummary',
            'agentReportData',
            'loanStatistics',
            'keyHighlights',
            'clientReportData',
            'pageTitle',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Helper function to get date range for daily, weekly, and monthly reports.
     *
     * @param string $period
     * @return array
     */
    private function getDateRange($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'weekly':
                return [
                    $now->startOfWeek()->setTime(7, 0),
                    $now->endOfWeek()->setTime(23, 59, 59)
                ];
            case 'monthly':
                return [
                    $now->startOfMonth()->setTime(7, 0),
                    $now->endOfMonth()->setTime(23, 59, 59)
                ];
            case 'daily':
            default:
                if ($now->hour < 7) {
                    return [
                        $now->copy()->subDay()->setTime(7, 0),
                        $now->copy()->setTime(23, 59, 59)
                    ];
                } else {
                    return [
                        $now->copy()->setTime(7, 0),
                        $now->copy()->setTime(23, 59, 59)
                    ];
                }
        }
    }

    /**
     * Get financial summary for the specified date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getFinancialSummary($startDate, $endDate)
    {
        Log::info('Fetching financial summary', ['startDate' => $startDate, 'endDate' => $endDate]);

        // Cash Outflow: Loan disbursements + Expenses + Savings Withdrawals
        $loanDisbursements = UserLoan::whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        // Expenses by Category
        $expensesByCategory2 = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('category')
            ->get();


            $expensesByCategory = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Total Expenses Amount
        $totalExpensesAmount = $expensesByCategory; //expensesByCategory->sum('total');

        // Savings Withdrawals
        $savingsWithdrawals = SavingsTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 'withdrawal')
            ->sum('amount');


        // Cash Inflow
        $loanRepayments       = LoanPayment::whereBetween('created_at', [$startDate, $endDate])->where('is_reversed', false)->sum('amount');
        $processingFees       = UserLoan::whereBetween('created_at', [$startDate, $endDate])->sum('processing_fee');
        $savingsDeposits      = SavingsTransaction::whereBetween('created_at', [$startDate, $endDate])->where('type', 'deposit')->sum('amount');
        $capitalAdded         = Cashflow::whereBetween('created_at', [$startDate, $endDate])->sum('capital_added');
        $unknownFunds         = Cashflow::whereBetween('created_at', [$startDate, $endDate])->sum('unknown_funds');
        $totalExcessFunds     = ExcessFund::whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $membershipFunds      = Membership::whereBetween('created_at', [$startDate, $endDate])->sum('membership_fees');
        $membershipShareFunds = Membership::whereBetween('created_at', [$startDate, $endDate])->sum(DB::raw('shares * share_value'));
        $capitalBanked        = Cashflow::whereBetween('created_at', [$startDate, $endDate])->sum('cash_banked');

  // Loan Processing Fees
        $loanProcessingFees = $processingFees;
        // Corrected Opening Balance Calculation
        // $openingBalance = Cashflow::where('created_at', '<', $startDate)->orderBy('created_at', 'desc')->value('balance_bf') ?? 0;

        // $openingBalance = Cashflow::where('created_at', $startDate)->sum('balance_bf');

        $openingBalance =  Cashflow::whereBetween('created_at', [$startDate, $endDate])
            ->sum('balance_bf');

        // Exclude Opening Balance from Cash Inflow
        $cashInflow = $loanRepayments + $processingFees + $openingBalance + $savingsDeposits + $capitalAdded + $unknownFunds  + $membershipFunds + $membershipShareFunds;

 // Other Cash In's
        $OtherCashIn = $membershipFunds + $membershipShareFunds + $savingsDeposits + $totalExcessFunds;

        
        $actualCash =  DB::table('actual_cash')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('amount');

        $actualCash2 = DB::table('actual_cash')
        ->whereBetween('date_added', [$startDate, $endDate])
        ->pluck('amount');

        // $allExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])->get();

        // DB::table('actual_cash')->get([
        //     'user_id' => 61,
        //     'amount' => $validated['amount'],
        //     'date_added' => now(),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
        
        
       
        
        $CashIn = $loanRepayments  + $loanProcessingFees ;

        
        // total cashInflow  137,000 + 2,001,000  + 0  = 2,138,000
        $totalCashInflow   = $openingBalance + $OtherCashIn + $CashIn +   $capitalAdded ;
      



        // Cashouts
        $cashouts = $this->getCashouts($startDate, $endDate);

       
        // total exxpense
        $totalExpensesAmount = $expensesByCategory + $capitalBanked;

        // total expense with banking and mm cash_banked

        $cashOutflow = $loanDisbursements + $totalExpensesAmount ;//+ $savingsWithdrawals;

        // Closing Balance  27000 + 2,138,000 -  2,139,000
        $closingBalance =  $totalCashInflow - $cashOutflow ;

        // $excussF =  $closingBalance - $actualCash ;
        // $shotage = $actualCash - $closingBalance;

        
         // Retrieve Actual Cash
        // $actualCash = DB::table('actual_cash')
        // ->whereBetween('date_added', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        // ->sum('amount');

    // Calculate Excess Funds and Shortage
    $difference =  $closingBalance - $actualCash;

    if ($difference > 0) {
        // Excess Funds
        $excussF = $difference;
        $shotage = 0;
    } elseif ($difference < 0) {
        // Shortage
        $excussF = 0;
        $shotage = abs($difference);
    } else {
        // No Excess or Shortage
        $excussF = 0;
        $shotage = 0;
    }
        
 // Total Cash Flow
        $totalCashFlow = $cashInflow - $cashOutflow ;

      $allExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])->get();
       
        return compact(
            'cashInflow','membershipFunds','membershipShareFunds','totalCashInflow','allExpenses',
            'cashOutflow', 'CashIn',
            'loanProcessingFees','OtherCashIn','actualCash','excussF','shotage',
            'openingBalance','savingsDeposits',
            'closingBalance',
            'cashouts',
            'totalCashFlow',
            'expensesByCategory',
            'totalExpensesAmount','totalExpensesAmount','loanRepayments','loanDisbursements','capitalAdded'
        );
    }

    /**
     * Get total cashouts for the specified date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function getCashouts($startDate, $endDate)
    {
        Log::info('Fetching total cashouts', ['startDate' => $startDate, 'endDate' => $endDate]);

        // Loan Disbursements
        $loanDisbursements = UserLoan::whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        // Expenses
        $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        // Savings Withdrawals
        $savingsWithdrawals = SavingsTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 'withdrawal')
            ->sum('amount');

        // Cash Banked
        $cashBanked = Cashflow::whereBetween('created_at', [$startDate, $endDate])->sum('capital_added');

        // Total Cashouts
        $totalCashouts = $loanDisbursements + $totalExpenses + $savingsWithdrawals + $cashBanked;

        return $totalCashouts;
    }

    /**
     * Get agent report data for the specified date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getAgentReportData($startDate, $endDate)
    {
        // Retrieve all agents along with their clients
        $agents = User::where('type', 2)
            ->where('is_active', 1)
            ->with('clients')
            ->get();

        $agentPerformance = [];
        $totals = [
            'total_clients'             => 0,
            'total_money_out'           => 0,
            'total_expected_daily'      => 0,
            'total_amount_collected'    => 0,
            'total_clients_paid'        => 0,
            'total_clients_paid_today'  => 0,
            'total_performance'         => 0,
        ];

        $today = Carbon::today();

        foreach ($agents as $agent) {
            $clientCount = $agent->clients->count();
            $clientIds = $agent->clients->pluck('id');

            // Total Money Out
            $totalMoneyOut = UserLoan::whereIn('client_id', $clientIds)
                ->where('status', 1)
                ->sum('amount');

            // Expected Daily Collection
            $expectedDaily = UserLoan::whereIn('client_id', $clientIds)
                ->where('status', 1)
                ->sum('per_installment');

            // Amount Collected within the date range
            $amountCollected = LoanPayment::whereIn('client_id', $clientIds)->where('is_reversed', false)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            // Performance Percentage
            $performancePercentage = $expectedDaily > 0 ? ($amountCollected / $expectedDaily) * 100 : 0;

            // Clients Paid within the date range
            $clientsPaid = LoanPayment::whereIn('client_id', $clientIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->distinct('client_id')
                ->count('client_id');

            // Clients Paid Today
            $clientsPaidToday = LoanPayment::whereIn('client_id', $clientIds)
                ->whereDate('created_at', $today)
                ->distinct('client_id')
                ->count('client_id');

            $agentPerformance[] = [
                'agent'                 => $agent,
                'client_count'          => $clientCount,
                'total_money_out'       => $totalMoneyOut,
                'expected_daily'        => $expectedDaily,
                'amount_collected'      => $amountCollected,
                'clients_paid'          => $clientsPaid,
                'clients_paid_today'    => $clientsPaidToday,
                'performance_percentage'=> $performancePercentage,
            ];

            // Update totals
            $totals['total_clients']             += $clientCount;
            $totals['total_money_out']           += $totalMoneyOut;
            $totals['total_expected_daily']      += $expectedDaily;
            $totals['total_amount_collected']    += $amountCollected;
            $totals['total_clients_paid']        += $clientsPaid;
            $totals['total_clients_paid_today']  += $clientsPaidToday;
        }

        // Calculate total performance
        $totals['total_performance'] = $totals['total_expected_daily'] > 0
            ? ($totals['total_amount_collected'] / $totals['total_expected_daily']) * 100
            : 0;

        return compact('agentPerformance', 'totals');
    }

    /**
     * Get loan statistics for the specified date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getLoanStatistics2($startDate, $endDate)
    {
        Log::info('Fetching loan statistics', ['startDate' => $startDate, 'endDate' => $endDate]);

        // Total amount of loans disbursed within the period
        $loansDisbursed = UserLoan::whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        // Total amount of repayments received within the period
        $repaymentsReceived = LoanPayment::whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        // Interest earned within the period
        // Adjusted calculation based on available data
        $interestEarned = 0;

        // Option 1: If 'interest_amount' exists in 'user_loans' table
        if (Schema::hasColumn('user_loans', 'interest_amount')) {
            $interestEarned = UserLoan::whereBetween('created_at', [$startDate, $endDate])->sum('interest_amount');
        }
        // Option 2: If 'interest_rate' exists in 'user_loans' table
        elseif (Schema::hasColumn('user_loans', 'interest_rate')) {
            $interestEarned = UserLoan::whereBetween('created_at', [$startDate, $endDate])
                ->sum(DB::raw('amount * (interest_rate / 100)'));
        }
        // Option 3: Estimate based on default interest rate
        else {
            $defaultInterestRate = 20; // Replace with your actual default rate
            $interestEarned = $loansDisbursed * ($defaultInterestRate / 100);
        }

        // Rest of the method remains the same
        $overdueLoans = UserLoan::where('status', '<>', 2)->where('due_date', '<', now())->count();
        $totalActiveLoans = UserLoan::where('status', '<>', 2)->count();
        $delinquencyRate = $totalActiveLoans > 0 ? ($overdueLoans / $totalActiveLoans) * 100 : 0;

        $delinquencyMetrics = [
            'overdueLoans'      => $overdueLoans,
            'delinquencyRate'   => $delinquencyRate,
            'aging30Days'       => UserLoan::where('status', '<>', 2)->where('due_date', '<', now()->subDays(30))->count(),
            'aging60Days'       => UserLoan::where('status', '<>', 2)->where('due_date', '<', now()->subDays(60))->count(),
            'aging90Days'       => UserLoan::where('status', '<>', 2)->where('due_date', '<', now()->subDays(90))->count(),
        ];

        return [
            'loansDisbursed'        => $loansDisbursed,
            'repaymentsReceived'    => $repaymentsReceived,
            'interestEarned'        => $interestEarned,
            'delinquencyMetrics'    => $delinquencyMetrics,
        ];
    }


    private function getLoanStatistics($startDate, $endDate)
{
    Log::info('Fetching loan statistics', ['startDate' => $startDate, 'endDate' => $endDate]);

    // Define a closure to filter loans by active users
    $activeUserFilter = function ($query) {
        $query->where('is_active', 1); // Assuming 'status' equals 1 indicates an active user             ->where('is_active', 1)

    };

    // Total amount of loans disbursed within the period from active users
    $loansDisbursed = UserLoan::whereBetween('created_at', [$startDate, $endDate])
        ->whereHas('agent', $activeUserFilter)
        ->sum('amount');

    // Total amount of repayments received within the period from active users
    $repaymentsReceived = LoanPayment::whereBetween('created_at', [$startDate, $endDate])
        ->whereHas('loan.agent', $activeUserFilter)
        ->sum('amount');

    // Interest earned within the period from active users
    $interestEarned = 0;

    // Option 1: If 'interest_amount' exists in 'user_loans' table
    if (Schema::hasColumn('user_loans', 'interest_amount')) {
        $interestEarned = UserLoan::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('agent', $activeUserFilter)
            ->sum('interest_amount');
    }
    // Option 2: If 'interest_rate' exists in 'user_loans' table
    elseif (Schema::hasColumn('user_loans', 'interest_rate')) {
        $interestEarned = UserLoan::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('agent', $activeUserFilter)
            ->sum(DB::raw('amount * (interest_rate / 100)'));
    }
    // Option 3: Estimate based on default interest rate
    else {
        $defaultInterestRate = 20; // Replace with your actual default rate
        $interestEarned = $loansDisbursed * ($defaultInterestRate / 100);
    }

    // Calculate overdue loans from active users
    $overdueLoans = UserLoan::where('status', '<>', 2)
        ->where('due_date', '<', now())
        ->whereHas('agent', $activeUserFilter)
        ->count();

    // Total active loans from active users
    $totalActiveLoans = UserLoan::where('status', '<>', 2)
        ->whereHas('agent', $activeUserFilter)
        ->count();

    $delinquencyRate = $totalActiveLoans > 0 ? ($overdueLoans / $totalActiveLoans) * 100 : 0;

    $delinquencyMetrics = [
        'overdueLoans'    => $overdueLoans,
        'delinquencyRate' => $delinquencyRate,
        'aging30Days'     => UserLoan::where('status', '<>', 2)
            ->where('due_date', '<', now()->subDays(30))
            ->whereHas('agent', $activeUserFilter)
            ->count(),
        'aging60Days'     => UserLoan::where('status', '<>', 2)
            ->where('due_date', '<', now()->subDays(60))
            ->whereHas('agent', $activeUserFilter)
            ->count(),
        'aging90Days'     => UserLoan::where('status', '<>', 2)
            ->where('due_date', '<', now()->subDays(90))
            ->whereHas('agent', $activeUserFilter)
            ->count(),
    ];

    return [
        'loansDisbursed'     => $loansDisbursed,
        'repaymentsReceived' => $repaymentsReceived,
        'interestEarned'     => $interestEarned,
        'delinquencyMetrics' => $delinquencyMetrics,
    ];
}



    /**
     * Get key highlights for the specified date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getKeyHighlights($startDate, $endDate)
    {
        Log::info('Fetching key highlights', ['startDate' => $startDate, 'endDate' => $endDate]);

        // Count of new clients within the date range
        $newClientsCount = Client::whereBetween('created_at', [$startDate, $endDate])->count();

        // Total repayments received
        $totalRepayments = LoanPayment::whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        // Calculate the delinquency rate for the current period
        $totalLoans = UserLoan::count();
        $overdueLoans = UserLoan::where('status', '<>', 2)->where('due_date', '<', now())->count();
        $currentDelinquencyRate = $totalLoans > 0 ? ($overdueLoans / $totalLoans) * 100 : 0;

        // Get the top agents based on total collections in the given period
        $topAgents = User::where('type', 2)
        ->where('is_active', 1)
        ->with(['payments' => function ($query) use ($startDate, $endDate) {
            $createdAtColumn = $query->qualifyColumn('created_at');
            $query->whereBetween($createdAtColumn, [$startDate, $endDate]);
        }])
        ->get()
        ->map(function ($agent) {
            $agent->total_collected = $agent->payments->sum('amount');
            return $agent;
        })
        ->sortByDesc('total_collected')
        ->take(3);


        return compact(
            'newClientsCount',
            'totalRepayments',
            'currentDelinquencyRate',
            'topAgents'
        );
    }

    /**
     * Get client report data for the specified date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getClientReportData($startDate, $endDate)
    {
        Log::info('Fetching client report data', ['startDate' => $startDate, 'endDate' => $endDate]);

        $totalClients = Client::count();
        $clientsWithBalance = Client::where('credit_balance', '>', 0)->count();
        $totalCreditBalance = Client::sum('credit_balance');

        $clientsPaid = LoanPayment::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('client_id')
            ->count('client_id');

        $clientsUnpaid = $totalClients - $clientsPaid;

        $clientsPaidAdvance = LoanPayment::whereBetween('created_at', [$startDate, $endDate])
            ->where('is_advance', true)
            ->distinct('client_id')
            ->count('client_id');

        // Loans disbursed within the date range
        $loansDisbursed = UserLoan::with(['client'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 1) // Assuming status 1 means 'disbursed' or 'active'
            ->get();

        $clientLoans = $loansDisbursed->map(function ($loan) {
            $loanDate = $loan->created_at instanceof Carbon ? $loan->created_at : Carbon::parse($loan->created_at);
            $dueDate = $loan->due_date instanceof Carbon ? $loan->due_date : Carbon::parse($loan->due_date);

            $interestRate = $loan->interest_rate ?? 20; // Default to 20% if not set
            $profit = $loan->amount * ($interestRate / 100);

            $statusLabels = [
                0 => 'Pending',
                1 => 'Running',
                2 => 'Paid',
                3 => 'Rejected',
                // Add other status codes and labels as needed
            ];
            $statusName = $statusLabels[$loan->status] ?? 'Unknown';

            return [
                'client_name'       => $loan->client->name ?? 'N/A',
                'agent_name'        => $loan->agent->l_name ?? 'N/A',
                'amount_given'      => $loan->amount ?? 'N/A',
                'profit_to_be_made' => $profit ?? 'N/A',
                'loan_date'         => $loanDate->format('Y-m-d'),
                'due_date'          => $dueDate->format('Y-m-d'),
                'phone'             => $loan->client->phone ?? 'N/A',
                'status'            => $statusName,
            ];
        });

        return [
            'totalClients'          => $totalClients,
            'clientsWithBalance'    => $clientsWithBalance,
            'totalCreditBalance'    => $totalCreditBalance,
            'clientsPaid'           => $clientsPaid,
            'clientsUnpaid'         => $clientsUnpaid,
            'clientsPaidAdvance'    => $clientsPaidAdvance,
            'clientLoans'           => $clientLoans,
        ];
    }

    /**
     * Export the analytics report as a PDF.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
   

    public function exportAnalyticsPdf(Request $request)
    {
        $request->validate([
            'period'      => 'nullable|in:daily,weekly,monthly,custom',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $period = $request->input('period', 'daily');

        // Fetch the start and end dates based on the selected period or custom dates
        if ($period === 'custom') {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate   = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            [$startDate, $endDate] = $this->getDateRange($period);
        }

        // Retrieve report data for the selected date range
        $financialSummary   = $this->getFinancialSummary($startDate, $endDate);
        $agentReportData    = $this->getAgentReportData($startDate, $endDate);
        $loanStatistics     = $this->getLoanStatistics($startDate, $endDate);
        $keyHighlights      = $this->getKeyHighlights($startDate, $endDate);
        $clientReportData   = $this->getClientReportData($startDate, $endDate);

        $data = compact(
            'financialSummary',
            'agentReportData',
            'loanStatistics',
            'keyHighlights',
            'clientReportData',
            'startDate',
            'endDate',
            'period'
        );

        $pdf = PDF::loadView('admin-views.reports.analytics-dashboard-pdf', $data);

        return $pdf->download("{$period}_analytics_report_" . now()->format('Y-m-d') . '.pdf');
    }
}
