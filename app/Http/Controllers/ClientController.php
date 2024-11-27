<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentTransactionsRequest;
// use App\Models\LoanPayment;
// use App\Models\User;
// use App\Models\Branch;
// use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;



use App\Models\Client;
use Illuminate\Http\Request;

use App\Models\LoanOffer;
use App\Models\LoanPlan;
use App\Models\UserLoan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Guarantor;
use App\CentralLogics\Helpers;
use Stevebauman\Location\Facades\Location;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use App\Models\LoanPaymentInstallment;

use App\Models\SanaaCard;
use Illuminate\Support\Facades\DB;
use PDF;

use App\Models\LoanPayment;
use App\Models\Branch;

use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientsExport;
// use Maatwebsite\Excel\Facades\Excel;



use Brian2694\Toastr\Facades\Toastr;

class ClientController extends Controller
{
    
    public function agentDash(Request $request)
        {
            // Validate the input period and dates
            $request->validate([
                'period' => 'nullable|in:daily,weekly,monthly,custom',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);
        
            // Get the reporting period ('daily', 'weekly', 'monthly', 'custom'), defaulting to 'daily'
            $period = $request->input('period', 'daily');
        
            // Fetch the start and end dates based on the selected period or custom dates
            if ($period === 'custom') {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            } else {
                [$startDate, $endDate] = $this->getDateRange2($period);
            }
        
            // Total Installments Collected in the Date Range
            $totalInstallmentsCollected = LoanPaymentInstallment::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'paid')
                ->count();
        
            // Total Amount Collected in the Date Range
            $totalAmountCollected = LoanPaymentInstallment::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'paid')
                ->sum('install_amount');
        
            // Total Overdue Installments as of End Date
            $totalOverdueInstallments = LoanPaymentInstallment::where('status', 'overdue')
                ->where('date', '<=', $endDate)
                ->count();
        
            // New Clients Added in the Date Range
            $newClients = Client::whereBetween('created_at', [$startDate, $endDate])->count();
        
            // Agent Financial Summary: Get total amount and transaction count per agent
            $agentTransactions = LoanPayment::with('agent:id,f_name,l_name')
                ->select('agent_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as transaction_count'))
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->groupBy('agent_id')
                ->get();
        
            // Real-Time Installment Payments: Get the latest 50 payments with agent and client details
            $installmentPayments = LoanPayment::with(['agent:id,f_name,l_name', 'client:id,name'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        
            // Loans Overview: Get counts and collections
            $totalActiveLoans = UserLoan::where('status', 1)->count();
            $activeLoans = UserLoan::where('status', 1)->with('client:id,name')->get();
        
            $totalPaidLoans = UserLoan::where('status', 2)->count();
            $paidLoans = UserLoan::where('status', 2)->with('client:id,name')->get();
        
            $totalOverdueLoans = UserLoan::whereHas('loanPaymentInstallments', function ($query) {
                $query->where('status', 'overdue');
            })->count();
            $overdueLoans = UserLoan::whereHas('loanPaymentInstallments', function ($query) {
                $query->where('status', 'overdue');
            })->with('client:id,name')->get();
        
            // Client Overview: Get the total number of clients and the collection
            $totalClients = Client::count();
            $clients = Client::with(['userLoans' => function ($query) {
                $query->select('client_id', 'status', 'amount', 'next_installment_date');
            }])->get();
        
            // Agent Performance Data
            $agentReportData = $this->getAgentReportData($startDate, $endDate);
        
            // Pass data to the view
            return view('admin-views.reports.agent-report-today', compact(
                'totalInstallmentsCollected',
                'totalAmountCollected',
                'totalOverdueInstallments',
                'totalActiveLoans',
                'activeLoans',
                'totalPaidLoans',
                'paidLoans',
                'totalOverdueLoans',
                'overdueLoans',
                'newClients',
                'agentTransactions',
                'installmentPayments',
                'totalClients',
                'clients',
                'agentReportData',
                'startDate',
                'endDate',
                'period'
            ));
        }
        

        public function getTransactionHistory(Client $client)
{
    // Fetch the latest transaction history
    $clientLoanPayHistroy = LoanPayment::where('client_id', $client->id)
        ->orderBy('payment_date', 'desc')
        ->get();

    // Return the HTML view for the transaction history
    $html = view('admin-views.clients.partials.transaction-history', compact('clientLoanPayHistroy'))->render();

    return response()->json(['html' => $html]);
}

        /**
         * Get the date range based on the period.
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
                        $now->copy()->startOfWeek()->setTime(12, 0),
                        $now->copy()->endOfWeek()->addDay()->setTime(11, 59, 59)
                    ];
                case 'monthly':
                    return [
                        $now->copy()->startOfMonth()->setTime(12, 0),
                        $now->copy()->endOfMonth()->addDay()->setTime(11, 59, 59)
                    ];
                case 'daily':
                default:
                    if ($now->hour < 12) {
                        // If current time is before 8 AM, set start to yesterday 8 AM
                        return [
                            $now->copy()->subDay()->setTime(12, 0),
                            $now->copy()->setTime(11, 59, 59)
                        ];
                    } else {
                        // If current time is after 8 AM, set start to today 8 AM
                        return [
                            $now->copy()->setTime(12, 0),
                            $now->copy()->addDay()->setTime(11, 59, 59)
                        ];
                    }
            }
        }
        
        /**
         * Get agent performance data for the specified date range.
         *
         * @param Carbon $startDate
         * @param Carbon $endDate
         * @return array
         */
        private function getAgentReportData($startDate, $endDate)
        {
            $agents = User::where('type', 2)->where('is_active', 1)->with('clients')->get(); // Assuming type 2 indicates agents
        
            $agentPerformance = [];
            $totals = [
                'total_clients' => 0,
                'total_money_out' => 0,
                'total_expected_daily' => 0,
                'total_amount_collected' => 0,
                'total_performance' => 0,
            ];
        
            foreach ($agents as $agent) {
                $clientCount = $agent->clients->count();
        
                $totalMoneyOut = UserLoan::where('user_id', $agent->id)
                    ->where('status', 1)
                    ->sum('amount');
        
                // Expected daily is the sum of 'per_installment' amounts from 'user_loans' for this agent's clients
                $expectedDaily = UserLoan::where('user_id', $agent->id)
                    ->where('status', 1)
                    ->sum('per_installment');
        
                $amountCollected = LoanPayment::where('agent_id', $agent->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');
        
                $performancePercentage = $expectedDaily > 0 ? ($amountCollected / $expectedDaily) * 100 : 0;
        
                $agentPerformance[] = [
                    'agent' => $agent,
                    'client_count' => $clientCount,
                    'total_money_out' => $totalMoneyOut,
                    'expected_daily' => $expectedDaily,
                    'amount_collected' => $amountCollected,
                    'performance_percentage' => $performancePercentage,
                ];
        
                $totals['total_clients'] += $clientCount;
                $totals['total_money_out'] += $totalMoneyOut;
                $totals['total_expected_daily'] += $expectedDaily;
                $totals['total_amount_collected'] += $amountCollected;
            }
        
            $totals['total_performance'] = $totals['total_expected_daily'] > 0
                ? ($totals['total_amount_collected'] / $totals['total_expected_daily']) * 100
                : 0;
        
            return [
                'agentPerformance' => $agentPerformance,
                'totals' => $totals,
            ];
        }

    
    public function exportClientsToExcel(Request $request)
    {
        return Excel::download(new ClientsExport($request->all()), 'clients.xlsx');
    }

    
    public function getClientsWhoPaidToday(Request $request): JsonResponse
        {
            // Get today's date
            $today = Carbon::today();
        
            // Determine the number of results per page (default to 20 if not specified)
            $perPage = $request->input('per_page', 20);
        
            // Fetch clients who have made payments today, using pagination
            $clients = Client::whereHas('loanPayments', function ($query) use ($today) {
                $query->whereDate('created_at', $today);
            })->paginate($perPage);
        
            // Return the clients as a JSON response
            return response()->json([
                'status' => 'success',
                'message' => 'Clients who have made payments today retrieved successfully.',
                'data' => $clients->items(), // Returns the paginated items
                'current_page' => $clients->currentPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
                'last_page' => $clients->lastPage(),
            ], 200);
        } 








    
   public function getClientsWhoHaventPaidToday(Request $request): JsonResponse
    {
        // Get today's date
        $today = Carbon::today();
    
        // Set the pagination limit, defaulting to 20 if not provided
        $perPage = $request->input('per_page', 20);
    
        // Get clients who haven't made any payments today with pagination
        $clients = Client::whereDoesntHave('loanPayments', function ($query) use ($today) {
            $query->whereDate('created_at', $today);
        })->paginate($perPage);
    
        // Return the paginated clients as a JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Clients who have not paid today retrieved successfully.',
            'data' => $clients->items(),
            'current_page' => $clients->currentPage(),
            'per_page' => $clients->perPage(),
            'total' => $clients->total(),
            'last_page' => $clients->lastPage(),
        ], 200);
    }
    
    /**
     * Generate the Agents Report.
     *
     * @param array $dateRange
     * @return array
     */
    private function getAgentPerformance($dateRange)
    {
        // Fetch agents with client counts and total money out
        $agents = User::join('clients', 'users.id', '=', 'clients.added_by')
            ->select(
                'users.id',
                'users.f_name',
                'users.l_name',
                DB::raw('COUNT(clients.id) as client_count'),
                DB::raw('SUM(clients.credit_balance) as total_money_out')
            )
            ->groupBy('users.id', 'users.f_name', 'users.l_name')
            ->get();

        $agentPerformance = [];

        $totalClients = 0;
        $totalMoneyOut = 0;
        $totalAmountCollected = 0;
        $totalExpectedDaily = 0;

        foreach ($agents as $agent) {
            // Calculate expected daily amount from total money out
            // Assuming 30 days in a month for calculation
            $agent->expected_daily = $agent->total_money_out / 30;

            // Calculate amount collected from cleared payment installments within the date range
            $agent->amount_collected = LoanPayment::where('agent_id', $agent->id)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->sum('amount');

            // Calculate performance percentage
            $expectedTotal = $agent->expected_daily * 30; // This equals total_money_out
            if ($expectedTotal > 0) {
                $agent->performance_percentage = ($agent->amount_collected / $expectedTotal) * 100;
            } else {
                $agent->performance_percentage = 0;
            }

            // Update totals
            $totalClients += $agent->client_count;
            $totalMoneyOut += $agent->total_money_out;
            $totalAmountCollected += $agent->amount_collected;
            $totalExpectedDaily += $agent->expected_daily;

            // Prepare data for agentPerformance array
            $agentPerformance[] = [
                'agent' => $agent,
                'client_count' => $agent->client_count,
                'total_money_out' => $agent->total_money_out,
                'expected_daily' => $agent->expected_daily,
                'amount_collected' => $agent->amount_collected,
                'performance_percentage' => $agent->performance_percentage,
            ];
        }

        // Calculate total performance percentage
        if ($totalMoneyOut > 0) {
            $totalPerformance = ($totalAmountCollected / $totalMoneyOut) * 100;
        } else {
            $totalPerformance = 0;
        }

        // Prepare totals array
        $totals = [
            'total_clients' => $totalClients,
            'total_money_out' => $totalMoneyOut,
            'total_amount_collected' => $totalAmountCollected,
            'total_expected_daily' => $totalExpectedDaily,
            'total_performance' => $totalPerformance,
        ];

        return [
            'agentPerformance' => $agentPerformance,
            'totals' => $totals,
        ];
    }
    
     public function agentTransactions(Request $request)
    {
        // Fetch date range from request or default to the current month
        $dateRange = $request->get('date_range', [
            'start' => Carbon::now()->startOfMonth(),  // Default start of the current month
            'end' => Carbon::now()->endOfMonth()       // Default end of the current month
        ]);
    
        // Ensure valid start and end dates
        $startDate = $dateRange['start'] ?? Carbon::now()->startOfMonth();
        $endDate = $dateRange['end'] ?? Carbon::now()->endOfMonth();
    
        // Retrieve additional filters from request
        $agentId = $request->get('agent_id');         // Filter for agent transactions
        $clientId = $request->get('client_id');       // Filter by client
        $status = $request->get('status');            // Filter by payment status (paid, pending, etc.)
        $minAmount = $request->get('min_amount');     // Filter by minimum payment amount
        $maxAmount = $request->get('max_amount');     // Filter by maximum payment amount
    
        // Query LoanPayment model, eager load agent and client relationships
        $query = LoanPayment::with(['agent', 'client'])
            ->whereBetween('created_at', [$startDate, $endDate]);  // Date range filter
    
        // Apply filters based on user input
        if ($agentId) {
            $query->where('agent_id', $agentId);  // Filter by specific agent
        }
    
        if ($clientId) {
            $query->where('client_id', $clientId);  // Filter by specific client
        }
    
        if ($status) {
            $query->where('status', $status);  // Filter by payment status
        }
    
        if ($minAmount) {
            $query->where('amount', '>=', $minAmount);  // Minimum amount filter
        }
    
        if ($maxAmount) {
            $query->where('amount', '<=', $maxAmount);  // Maximum amount filter
        }
    
        // Fetch results with pagination (20 per page)
        $installmentPayments = $query->orderBy('created_at', 'desc')->paginate(60);
    
        // Fetch agent performance data (date range or additional logic from Sanaa Finance SaaS)
        $agentReportData = $this->getAgentPerformance($dateRange);
    
        // Fetch agents for filter dropdowns
        $agents = User::all();  // Assuming you have an Agent model
    
        // Return filtered data to the view
        return view('admin-views.reports.real-Time-Payments', compact('installmentPayments', 'agentReportData', 'agents'));
    }

    
    public function agentTransactionsLast(Request $request)
        {
            // Fetch date range from request or default to the current month
            $dateRange = $request->get('date_range', [
                'start' => Carbon::now()->startOfMonth(),  // Default start of the current month
                'end' => Carbon::now()->endOfMonth()       // Default end of the current month
            ]);
        
            // Ensure valid start and end dates
            $startDate = $dateRange['start'] ?? Carbon::now()->startOfMonth();
            $endDate = $dateRange['end'] ?? Carbon::now()->endOfMonth();
        
            // Retrieve additional filters from request
            $agentId = $request->get('agent_id');         // Filter for agent transactions
            $clientId = $request->get('client_id');       // Filter by client
            $status = $request->get('status');            // Filter by payment status (paid, pending, etc.)
            $branchId = $request->get('branch_id');       // Filter by branch ID (as Sanaa operates with branches)
            $minAmount = $request->get('min_amount');     // Filter by minimum payment amount
            $maxAmount = $request->get('max_amount');     // Filter by maximum payment amount
        
            // Query LoanPayment model, eager load agent, client, and branch relationships
            $query = LoanPayment::with(['agent', 'client', 'loan.branch'])
                ->whereBetween('created_at', [$startDate, $endDate]);  // Date range filter
        
            // Apply filters based on user input
            if ($agentId) {
                $query->where('agent_id', $agentId);  // Filter by specific agent
            }
        
            if ($clientId) {
                $query->where('client_id', $clientId);  // Filter by specific client
            }
        
            if ($branchId) {
                $query->whereHas('loan.branch', function($q) use ($branchId) {
                    $q->where('id', $branchId);  // Filter by specific branch
                });
            }
        
            if ($status) {
                $query->where('status', $status);  // Filter by payment status
            }
        
            if ($minAmount) {
                $query->where('amount', '>=', $minAmount);  // Minimum amount filter
            }
        
            if ($maxAmount) {
                $query->where('amount', '<=', $maxAmount);  // Maximum amount filter
            }
        
            // Fetch results with pagination (20 per page)
            $installmentPayments = $query->orderBy('created_at', 'desc')->paginate(20);
        
            // Fetch agent performance data (date range or additional logic from Sanaa Finance SaaS)
            $agentReportData = $this->getAgentPerformance($dateRange);
        
            // Fetch agents and branches for filter dropdowns
            $agents = User::all();  // Assuming you have an Agent model
            $branches = Branch::all();  // Assuming you have a Branch model
        
            // Return filtered data to the view
            return view('admin-views.reports.real-Time-Payments', compact('installmentPayments', 'agentReportData', 'agents', 'branches'));
        }

    
   
   
   
        public function agentTransactionsX(Request $request)
{
    // Fetch date range from request or set default values (start and end of current month)
    $dateRange = $request->get('date_range', [
        'start' => Carbon::now()->startOfMonth(),  // Default to the start of the current month
        'end' => Carbon::now()->endOfMonth()       // Default to the end of the current month
    ]);

    // Ensure start and end dates are valid
    $startDate = $dateRange['start'] ?? Carbon::now()->startOfMonth();
    $endDate = $dateRange['end'] ?? Carbon::now()->endOfMonth();

    // Retrieve filters from request
    $agentId = $request->get('agent_id');         // Optional filter for agent
    $clientId = $request->get('client_id');       // Optional filter for client
    $status = $request->get('status');            // Optional filter for payment status (e.g., 'paid', 'pending')
    $branchId = $request->get('branch_id');       // Optional filter for branch (as Sanaa Finance operates with branches)
    $minAmount = $request->get('min_amount');     // Optional filter for minimum payment amount
    $maxAmount = $request->get('max_amount');     // Optional filter for maximum payment amount

    // Query the LoanPayment model with agent and client details
    $query = LoanPayment::with(['agent', 'client', 'loan.branch']) // Eager load agent, client, and branch relationships
        ->whereBetween('created_at', [$startDate, $endDate]); // Filter by date range

    // Apply filters if they are present in the request
    if ($agentId) {
        $query->where('agent_id', $agentId); // Filter by agent ID
    }

    if ($clientId) {
        $query->where('client_id', $clientId); // Filter by client ID
    }

    if ($branchId) {
        $query->whereHas('loan.branch', function($q) use ($branchId) {
            $q->where('id', $branchId); // Filter by branch ID
        });
    }

    if ($status) {
        $query->where('status', $status); // Filter by payment status (e.g., 'paid', 'pending')
    }

    if ($minAmount) {
        $query->where('amount', '>=', $minAmount); // Filter by minimum amount
    }

    if ($maxAmount) {
        $query->where('amount', '<=', $maxAmount); // Filter by maximum amount
    }

    // Get the filtered results with pagination (20 records per page)
    $installmentPayments = $query->orderBy('created_at', 'desc')->paginate(20);

    // Get agent report data (depending on date range or other filters)
    $agentReportData = $this->getAgentPerformance($dateRange);

    // Return the filtered data to the view
    return view('admin-views.reports.real-Time-Payments', compact('installmentPayments', 'agentReportData'));
}


    
  public function agentTransactions100(Request $request)
{
    // Fetch date range from request or set default values
    $dateRange = $request->get('date_range', [
        'start' => Carbon::now()->startOfMonth(),  // Default to the start of the current month
        'end' => Carbon::now()->endOfMonth()       // Default to the end of the current month
    ]);

    // Ensure that the date range array has valid 'start' and 'end' values
    $startDate = $dateRange['start'] ?? Carbon::now()->startOfMonth(); // Fallback to current month's start
    $endDate = $dateRange['end'] ?? Carbon::now()->endOfMonth();       // Fallback to current month's end

    // Fetch the agent report data based on the date range
    $agentReportData = $this->getAgentPerformance($dateRange);

    // Real-Time Installment Payments with agent and client details
    $installmentPayments = LoanPayment::with(['agent', 'client']) // Eager load agent and client details
        ->whereBetween('created_at', [$startDate, $endDate]) // Safely use $startDate and $endDate
        ->orderBy('created_at', 'desc') // Order by the latest created records
        ->paginate(20); // Paginate the results to 20 per page

    return view('admin-views.reports.real-Time-Payments', compact('installmentPayments', 'agentReportData'));
}

    /**
     * Determine the date range based on the selected period.
     *
     * @param string $period
     * @return array
     */
    private function getDateRange($period)
    {
        switch ($period) {
            case 'weekly':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'monthly':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            default: // daily
                $startDate = Carbon::now()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
        }
        return ['start' => $startDate, 'end' => $endDate];
    }
    
     public function agentDashX(Request $request)
        {
            $today = Carbon::today();
            $period = $request->input('period', 'daily'); // Options: daily, weekly, monthly
            $dateRange = $this->getDateRange($period);
            $agentReportData = $this->getAgentPerformance($dateRange);
        
        
            // Total Installments Collected Today
            $totalInstallmentsCollectedToday = LoanPaymentInstallment::whereDate('date', $today)
                ->where('status', 'paid')
                ->count();
        
            // Total Amount Collected Today
            $totalAmountCollectedToday = LoanPaymentInstallment::whereDate('date', $today)
                ->where('status', 'paid')
                ->sum('install_amount');
        
            // Total Overdue Installments
            $totalOverdueInstallments = LoanPaymentInstallment::whereDate('date', '<', $today)
                ->where('status', 'overdue')
                ->count();
        
            // Total Active Loans (Running loans)
            $totalActiveLoans = UserLoan::where('status', 1)->count();
        
            // New Clients Added Today
            $newClientsToday = Client::whereDate('created_at', $today)->count();
        
            // Agent Financial Summary: Eager load agent details
            $agentTransactions = LoanPayment::with('agent') // Load agent details
                ->select('agent_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as transaction_count'))
                ->whereDate('payment_date', $today)
                ->groupBy('agent_id')
                ->get();
        
        
        
        // Real-Time Installment Payments: Eager load agent and client details using LoanPayment model
            $installmentPayments = LoanPayment::with(['agent', 'client']) // Load both agent and client details
                ->orderBy('created_at', 'desc') // Order by the latest created records
                ->take(50) // Limit to 50 records
                ->get();
        
        
            // Loans Overview: Active, Paid, and Overdue Loans Managed by Agents
            $activeLoans = UserLoan::where('status', 1)->get();  // Active loans
            $paidLoans = UserLoan::where('status', 2)->get();    // Paid loans
        
            // Fetch loans with overdue installments
            $overdueLoans = UserLoan::whereHas('loanPaymentInstallments', function ($query) {
                $query->where('status', 'overdue');
            })->get();
        
            // Client Overview: Clients assigned to each agent
            $clients = Client::with(['userLoans' => function ($query) {
                $query->select('client_id', 'status', 'amount', 'next_installment_date');
            }])->get();
        
            return view('admin-views.reports.agent-report-today', compact(
                'totalInstallmentsCollectedToday', 'totalAmountCollectedToday', 'totalOverdueInstallments',
                'totalActiveLoans', 'newClientsToday', 'agentTransactions', 'installmentPayments', 
                'activeLoans', 'paidLoans', 'overdueLoans', 'clients','agentReportData'
            ));
        }

      public function agentDash1017(Request $request)
{
    // Set the current date
    $today = Carbon::today();

    // Get the reporting period ('daily', 'weekly', 'monthly'), defaulting to 'daily'
    $period = $request->input('period', 'daily');

    // Validate the period input
    if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
        $period = 'daily';
    }

    // Get the date range based on the selected period
    $dateRange = $this->getDateRange($period);

    // Get agent performance data for the date range
    $agentReportData = $this->getAgentPerformance($dateRange);

    // Total Installments Collected Today
    $totalInstallmentsCollectedToday = LoanPaymentInstallment::whereDate('date', $today)
        ->where('status', 'paid')
        ->count();

    // Total Amount Collected Today
    $totalAmountCollectedToday = LoanPaymentInstallment::whereDate('date', $today)
        ->where('status', 'paid')
        ->sum('install_amount');

    // Total Overdue Installments
    $totalOverdueInstallments = LoanPaymentInstallment::whereDate('date', '<', $today)
        ->where('status', 'overdue')
        ->count();

    // Loans Overview: Get counts and collections
    $totalActiveLoans = UserLoan::where('status', 1)->count(); // Count of active loans
    $activeLoans = UserLoan::where('status', 1)->get();        // Collection of active loans

    $totalPaidLoans = UserLoan::where('status', 2)->count();   // Count of paid loans
    $paidLoans = UserLoan::where('status', 2)->get();          // Collection of paid loans

    $totalOverdueLoans = UserLoan::whereHas('loanPaymentInstallments', function ($query) {
        $query->where('status', 'overdue');
    })->count(); // Count of overdue loans

    $overdueLoans = UserLoan::whereHas('loanPaymentInstallments', function ($query) {
        $query->where('status', 'overdue');
    })->get(); // Collection of overdue loans

    // New Clients Added Today
    $newClientsToday = Client::whereDate('created_at', $today)->count();

    // Agent Financial Summary: Get total amount and transaction count per agent
    $agentTransactions = LoanPayment::with('agent:id,f_name,l_name') // Load agent details with selected fields
        ->select('agent_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as transaction_count'))
        ->whereDate('payment_date', $today)
        ->groupBy('agent_id')
        ->get();

    // Real-Time Installment Payments: Get the latest 50 payments with agent and client details
    $installmentPayments = LoanPayment::with(['agent:id,f_name,l_name', 'client:id,name']) // Select specific fields
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->get();

    // Client Overview: Get the total number of clients and the collection
    $totalClients = Client::count();
    $clients = Client::with(['userLoans' => function ($query) {
        $query->select('client_id', 'status', 'amount', 'next_installment_date');
    }])->get();

    // Pass data to the view
    return view('admin-views.reports.agent-report-today', compact(
        'totalInstallmentsCollectedToday',
        'totalAmountCollectedToday',
        'totalOverdueInstallments',
        'totalActiveLoans',
        'activeLoans',
        'totalPaidLoans',
        'paidLoans',
        'totalOverdueLoans',
        'overdueLoans',
        'newClientsToday',
        'agentTransactions',
        'installmentPayments',
        'totalClients',
        'clients',
        'agentReportData'
    ));
}



    public function getUserAgentByPhone(Request $request)
    {
        // Validate the request input
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->input('phone');

        // Query the user by phone number
        $user = User::where('phone', $phone)->first();

        if ($user) {
            // Return the user data as JSON
            return response()->json($user);
        } else {
            // Return a 404 error if user not found
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    
    public function search(Request $request)
    {
        // Get query parameters
        $searchQuery = $request->input('q'); // For general search (name, ID)
        $sortBy = $request->input('sortBy', 'id'); // Default to sorting by 'id'
        $sortOrder = $request->input('sortOrder', 'asc'); // Default to ascending order
        $perPage = $request->input('perPage', 10); // Default to 10 results per page
    
        // Build the query
        $query = Client::query();
    
        // Search by name or ID (modify this based on your database columns)
        if ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('id', 'like', '%' . $searchQuery . '%');
        }
    
        // Add filters (if any specific filtering is needed, e.g., by status)
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
    
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
    
        // Paginate the results
        $clients = $query->paginate($perPage);
    
        // Return the paginated result in JSON format
        return response()->json($clients);
    }

  
    
    

    
  


    
    public function agentReport()
            {
                // Fetch agents with client counts and total money out
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
            
                // Initialize totals
                $totalClients = 0;
                $totalMoneyOut = 0;
                $totalAmountCollected = 0;
                $totalExpectedDaily = 0;
            
                foreach ($agents as $agent) {
                    // Calculate expected daily amount from total money out
                    $agent->expected_daily = $agent->total_money_out / 30;
            
                    // Calculate amount collected from cleared payment slots
                    $agent->amount_collected = LoanPaymentInstallment::where('agent_id', $agent->id)
                        ->where('status', 'cleared')
                        ->sum('install_amount');
            
                    // Calculate performance percentage
                    $expectedTotal = $agent->expected_daily * 30; // This equals total_money_out
                    if ($expectedTotal > 0) {
                        $agent->performance_percentage = ($agent->amount_collected / $expectedTotal) * 100;
                    } else {
                        $agent->performance_percentage = 0;
                    }
            
                    // Update totals
                    $totalClients += $agent->client_count;
                    $totalMoneyOut += $agent->total_money_out;
                    $totalAmountCollected += $agent->amount_collected;
                    $totalExpectedDaily += $agent->expected_daily; // Sum expected daily across all agents
                }
            
                // Calculate total performance percentage
                if ($totalMoneyOut > 0) {
                    $totalPerformance = ($totalAmountCollected / $totalMoneyOut) * 100;
                } else {
                    $totalPerformance = 0;
                }
            
                // Prepare totals array for the view
                $totals = [
                    'total_clients' => $totalClients,
                    'total_money_out' => $totalMoneyOut,
                    'total_amount_collected' => $totalAmountCollected,
                    'total_expected_daily' => $totalExpectedDaily, // Add this line
                    'total_performance' => $totalPerformance,
                ];
            
                return view('admin-views.reports.agent-report', compact('agents', 'totals'));
            }
   
//   printCard
  public function printCard($clientId)
{
    $client = Client::findOrFail($clientId);
    $card = SanaaCard::where('client_id', $clientId)->firstOrFail();

    // Generate HTML for both front and back sides, passing the $client variable
    $frontHtml = view('admin-views.clients.print-front', compact('client', 'card'))->render();
    $backHtml = view('admin-views.clients.print-back', compact('client', 'card'))->render();

    $combinedHtml = $frontHtml . '<div style="page-break-after: always;"></div>' . $backHtml;

    $customPaper = array(0,0,317.74,199.8);
    $pdf = PDF::loadHTML($combinedHtml)->setPaper($customPaper)->setWarnings(false);

    return $pdf->download('card_' . $client->id . '.pdf'); 
}
  
  
  
  

   
    public function edit($id)
    {
        // Fetch the client by ID
        $client = Client::findOrFail($id);
        $users = User::all();
    
        // Pass the client data to the view
        return view('admin-views.clients.edit', compact('client', 'users'));
    }
    

public function createClient(Request $request)
{
    $ip = env('APP_MODE') == 'live' ? $request->ip() : '61.247.180.82';
    $currentUserInfo = Location::get($ip);
    $users = User::all();

    // Retrieve all branches to display in the client creation form
    $branches = Branch::all();

    return view('admin-views.clients.create', compact('currentUserInfo', 'users', 'branches'));
}

  public function store(Request $request)
{ 
    // dd($request->all());
    // Add 'branch_id' to validation rules to ensure the client is linked to a branch.
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|string|email|max:255|unique:clients',
        'phone' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'status' => 'required|string|max:255',
        'kyc_verified_at' => 'nullable|date',
        'dob' => 'nullable|date',
        'business' => 'nullable|string|max:255',
        'nin' => 'nullable|string|max:255',
        'recommenders' => 'nullable|json',
        'added_by' => 'nullable|string|max:255',
        'next_of_kin' => 'nullable|string|max:255',
        'next_of_kin_phone' => 'nullable|string|max:255',
        'next_of_kin_relationship' => 'nullable|string|max:255',

        'branch_id' => 'required|uuid|exists:branches,branch_id', // Validate branch_id
    ]);

    // Generate email if it's not provided
    if (empty($validatedData['email'])) {
        // Create an email from the name (make it lowercase and remove any spaces)
        $validatedData['email'] = strtolower(str_replace(' ', '', $validatedData['name'])) . '@sanaa.co';
    }

    // Assign the branch ID from the request or the logged-in user if not provided
    if (!isset($validatedData['branch_id'])) {
        $validatedData['branch_id'] = auth()->user()->branch_id; // Assuming the user has a branch assigned
    }

    // Create the client with branch_id
    Client::create($validatedData);

    // Flash a success message
    return redirect()->route('admin.allclients')->with('success', 'Client added successfully.');
}


  // add guarantors
  
  
  public function addClientGuarantor(Request $request): JsonResponse
        {
            try {
                // Handle file uploads
                $photoPath = $request->file('photo') ? $request->file('photo')->store('guarantors/photos', 'public') : null;
                $nationalIdPhotoPath = $request->file('national_id_photo') ? $request->file('national_id_photo')->store('guarantors/national_id_photos', 'public') : null;
        
                // Create the guarantor
                $guarantor = Guarantor::create([
                    'name' => $request->input('name'),
                    'nin' => $request->input('nin'),
                    'phone_number' => $request->input('phone_number'),
                    'address' => $request->input('address'),
                    'relationship' => $request->input('client_relationship'),
                    'job' => $request->input('job'),
                    'photo' => $photoPath,
                    'national_id_photo' => $nationalIdPhotoPath,
                    'added_by' => $request->input('added_by'),
                    'client_id' => $request->input('client_id'),
                ]);
        
                // Return success response with guarantor data
                return response()->json(response_formatter(DEFAULT_200, $guarantor, null), 200);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(response_formatter(DEFAULT_500, null, 'An error occurred while adding the guarantor.'), 500);
            }
        }

  
  
  
    public function addClientGuarantor10(Request $request): JsonResponse
    {
  
       
        $photoPath = $request->file('photo') ? $request->file('photo')->store('guarantors/photos', 'public') : null;
        $nationalIdPhotoPath = $request->file('national_id_photo') ? $request->file('national_id_photo')->store('guarantors/national_id_photos', 'public') : null;

        // Create the guarantor
        $guarantor = Guarantor::create([
            'name' => $request->name,
            'nin' => $request->nin,
            'photo' => $photoPath,
            'national_id_photo' => $nationalIdPhotoPath,
            'added_by' => $request->added_by,
            'client_id' => $request->client_id,
        ]);

        return response()->json(response_formatter(DEFAULT_200, $guarantor, null), 200);

    }

    public function agentClientDetails_old($agentId)
                {
                   
                    $queryParams = [];
                    $agent =        User::findOrFail($agentId);
                    // $clients =      Client::where('added_by', $agentId)->get();
                    $clientsQuery = Client::where('added_by', $agentId)->get(); // SanaaCard::query();
                    
                    
                    // get client details
                    $clientsData = [];
                    $clients = $clientsQuery->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);
                
                    // Loop through the clients and collect the required data
                    foreach ($clients as $client) {
                        $clientGuarantorName = Guarantor::find($client->client_id);  
                        
                        $clientsData[] = [
                            'client' => $client,
                            'client_name' => $clientName ? $clientName->name : 'N/A',
                        ];
                    }
                
                    // Calculate total credit balance and total loan balance
                    $totalCreditBalance = $clients->sum('credit_balance');
                    $totalLoanBalance = $clients->sum('loan_balance');
                
                    return view('admin-views.reports.agentClientDetails', compact('agent', 'clients', 'totalCreditBalance', 'totalLoanBalance'));
        
          }
          
          
    public function agentClientDetails($agentId)
            {
                $queryParams = [];
                $agent = User::findOrFail($agentId);
            
                $clientsQuery = Client::where('added_by', $agentId); 
                $clients = $clientsQuery->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);
            
                $clientsData = [];
            
                foreach ($clients as $client) {
                    // The main issue is here: You're trying to find a Guarantor by the client_id, 
                    // but client_id in the Guarantor model likely refers to the Client it's associated with. 
                    // You need to find the Guarantor based on the Client's id.
                    $clientGuarantor = Guarantor::where('client_id', $client->id)->first(); 
            
                    $clientsData[] = [
                        'client' => $client,
                        // Use the guarantor's name if found, otherwise 'N/A'
                        'guarantor_name' => $clientGuarantor ? $clientGuarantor->name : 'N/A', 
                    ];
                }
            
                $totalCreditBalance = $clients->sum('credit_balance');
                $totalLoanBalance = $clients->sum('loan_balance');
            
                return view('admin-views.reports.agentClientDetails', compact('agent', 'clients', 'totalCreditBalance', 'totalLoanBalance', 'clientsData')); 
            }
  
  public function show13($id)
    {
        // Fetch the client by ID
        $client = Client::findOrFail($id);
        
        $clientLoanPayment = LoanPayment::where();

        
    
        // Pass the client data to the view
        return view('admin-views.clients.profile', compact('client'));
    }
  
  
  
  // In your ClientController
    public function show($id)
{
    // Fetch the client by ID
    $client = Client::findOrFail($id);

    // Get the agent who added the client
    $agent = User::find($client->added_by);

    // Get all guarantors associated with this client
    $guarantors = Guarantor::where('client_id', $client->id)->get();

    // Get client loan history
    $clientLoanPayHistroy = LoanPayment::where('client_id', $client->id)
                            ->orderBy('created_at', 'desc') // Order by created_at in descending order (latest first)
                            ->get();

    // Client loans
    $clientLoans = UserLoan::where('client_id', $client->id)->get();

    $collaterals = DB::table('client_collaterals')
    ->where('client_id', $client->id)
    ->orderBy('created_at', 'desc')
    ->get();

    // Get the client's branch
    $branch = Branch::find($client->branch_id);

    // Pass all the data to the view
    return view('admin-views.clients.profile', compact('client', 'agent', 'guarantors', 'clientLoanPayHistroy', 'clientLoans', 'branch','collaterals'));
}





function generateUniquePAN() {
    do {
        // Generate a random 16-digit number
        $pan = '';
        for ($i = 0; $i < 4; $i++) {
            $pan .= str_pad(random_int(0, 5959), 4, '0', STR_PAD_LEFT);
        }
        
        // Check if the generated PAN already exists in the database
        $panExists = SanaaCard::where('pan', $pan)->exists();
    } while ($panExists); // Repeat if the PAN is not unique
    
    return $pan;
}



function generateSanaaCardData() 
    {
        // Fetch clients from the database
        $clients = Client::all();
    
        foreach ($clients as $client) {
            // Generate a unique PAN (Primary Account Number) - typically a 16-digit number
            $pan = $this-> generateUniquePAN();
    
            // Generate other card fields
            $cvv = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT); // 3-digit CVV
            $iin = '123456'; // Issuer Identification Number (you may want to use your own logic)
            $expiryDate = now()->addYears(5)->toDateString(); // Card expiry 5 years from now
            $issueDate = now()->toDateString(); // Today's date as the issue date
            $balance = '0'; // Random balance for example
            $pinCode = bcrypt('1234'); // Example: bcrypt the PIN for security
            
            // Create a new SanaaCard entry
            SanaaCard::create([
                'client_id' => $client->id,
                'pan' => $pan,
                'cvv' => $cvv,
                'card_status' => 'active',
                'is_printed' => false, // Default to not printed
                'card_type' => 'phy',
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
                'balance' => $balance,
                'currency' => 'UGX', // Default to UGX
                'pin_code' => $pinCode,
                'emv_chip' => null, // Placeholder
                'magnetic_stripe_data' => null, // Placeholder
                'hologram' => null, // Placeholder
                'signature_panel' => null, // Placeholder
                'iin' => $iin,
                'nfc_enabled' => false,
            ]);
        }
    }
    
   // Add guarantors
   public function addClientGuarantorWeb(Request $request, $client_id)
        {
            // Validate the request data for adding a guarantor
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'nin' => 'required|string|max:255|unique:guarantors,nin',
                'phone_number' => 'required|string|max:15',
                'address' => 'nullable|string|max:255',
                'client_relationship' => 'required|string|max:255',
                'job' => 'nullable|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'national_id_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'added_by' => 'nullable|exists:users,id',
            ]);
        
            // Handle file uploads
            $photoPath = $request->file('photo') ? $request->file('photo')->store('guarantors/photos', 'public') : null;
            $nationalIdPhotoPath = $request->file('national_id_photo') ? $request->file('national_id_photo')->store('guarantors/national_id_photos', 'public') : null;
        
            // Create guarantor
            Guarantor::create([
                'name' => $validatedData['name'],
                'nin' => $validatedData['nin'],
                'phone_number' => $validatedData['phone_number'],
                'address' => $validatedData['address'],
                'relationship' => $validatedData['client_relationship'],
                'job' => $validatedData['job'],
                'photo' => $photoPath,
                'national_id_photo' => $nationalIdPhotoPath,
                'added_by' => $validatedData['added_by'] ?? auth()->user()->id,
                'client_id' => $client_id,
            ]);
        
            // Redirect with success message
            return redirect()->route('admin.clients.edit', $client_id)->with('success', 'Guarantor added successfully.');
        }
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients,email,' . $id, // Exclude current client from unique check
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'kyc_verified_at' => 'nullable|date',
            'dob' => 'nullable|date',
            'business' => 'nullable|string|max:255',
            'nin' => 'nullable|string|max:255',
            'recommenders' => 'nullable|json',
            'credit_balance' => 'required|numeric|min:0',
            'savings_balance' => 'required|numeric|min:0',
            'added_by' => 'nullable|string|max:255',
            'next_of_kin' => 'nullable|string|max:255',
        ]);
    
        // Find the client by ID and update with validated data
        $client = Client::findOrFail($id);
        $client->update($validatedData);
    
        // Redirect to clients list with a success message
        return redirect()->route('admin.allclients')->with('success', 'Client updated successfully.');
    }


    public function index(): JsonResponse
    {
        
        $clients = Client::all();
        // return response()->json($clients);
        $customers = $customers->latest()->customer()->paginate(Helpers::pagination_limit())->appends($queryParams);

        return response()->json(response_formatter(DEFAULT_200, $clients, null), 200);
    }
    
    // agents clints
      //  get client profile 
   public function getAgentClient(Request $request): JsonResponse
    {
        $clients = Client::where('added_by', $request -> agentId)->get();
        return response()->json(response_formatter(DEFAULT_200, $clients, null), 200);
        
    }
    
    public function clientsCards(Request $request): Factory|View|Application
        {
            $pageTitle = 'All Clients Cards';
            $emptyMessage = 'No clients found';
        
            $queryParams = [];
            $search = $request->get('search');
            
            $clientsQuery = SanaaCard::query();
            
            if ($search) {
                $key = explode(' ', $search);
                $clientsQuery->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%")
                          ->orWhere('nin', 'like', "%{$value}%")
                          ->orWhere('email', 'like', "%{$value}%")
                          ->orWhere('phone', 'like', "%{$value}%");
                    }
                });
                $queryParams['search'] = $search;
            }
        
            $clientsCardData = [];
            $clients = $clientsQuery->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);
        
            // Loop through the clients and collect the required data
            foreach ($clients as $client) {
                $clientName = Client::find($client->client_id); // Fetch the user who added this client
                
                $clientsCardData[] = [
                    'client' => $client,
                    'client_name' => $clientName ? $clientName->name : 'N/A',
                ];
            }
        
            return view('admin-views.clients.clients-cards', compact('clientsCardData', 'search', 'pageTitle', 'emptyMessage', 'clients'));
        }

    
    // all clients
    public function clients10(Request $request): Factory|View|Application
    {
        $pageTitle = 'All Clients';
        $emptyMessage = 'No clients found';
    
        $queryParams = [];
        $search = $request->get('search');
    
        // Start building the query
        $clientsQuery = Client::query();
    
        // Apply search filters
        if (!empty($search)) {
            $key = explode(' ', $search);
            $clientsQuery->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                      ->orWhere('nin', 'like', "%{$value}%")
                      ->orWhere('email', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParams['search'] = $search;
        }
    
        // Fetch agents with UUIDs
        $agents = User::where('type', 2) // Adjus  t 'type' as per your application
            ->select('id', 'f_name', 'l_name')
            ->get();
    
        // Filter by agent using UUID
        $agentUuid = $request->get('agent_id');
        if (!empty($agentUuid) && $agentUuid != 'all') {
            $clientsQuery->where('added_by', $agentUuid);
            $queryParams['agent_id'] = $agentUuid;
        }
    
        // Filter by payment status
        $paymentStatus = $request->get('payment_status');
        if (!empty($paymentStatus) && $paymentStatus != 'all') {
            if ($paymentStatus == 'paid') {
                $clientsQuery->whereDoesntHave('userLoans', function ($query) {
                    $query->where('status', '!=', 2); // Assuming status 2 means 'paid'
                });
            } elseif ($paymentStatus == 'unpaid') {
                $clientsQuery->whereHas('userLoans', function ($query) {
                    $query->where('status', '!=', 2); // Not 'paid'
                });
            }
            $queryParams['payment_status'] = $paymentStatus;
        }
    
        // Filter by client status
        $status = $request->get('status');
        if (!empty($status) && $status != 'all') {
            $clientsQuery->where('status', $status);
            $queryParams['status'] = $status;
        }
    
        // Date range filter
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
    
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDateParsed = Carbon::parse($fromDate)->startOfDay();
            $toDateParsed = Carbon::parse($toDate)->endOfDay();
            $clientsQuery->whereBetween('created_at', [$fromDateParsed, $toDateParsed]);
            $queryParams['from_date'] = $fromDate;
            $queryParams['to_date'] = $toDate;
        }
    
        // Export to Excel
        if ($request->has('export') && $request->export == 'excel') {
            $clients = $clientsQuery->get();
            return Excel::download(new ClientsExport($clients), 'clients.xlsx');
        }
    
        // Paginate the results
        $clients = $clientsQuery->latest()->paginate(20)->appends($queryParams);
    
        // Collect data for the view
        $clientsData = [];
        foreach ($clients as $client) {
            $agent = $client->addedBy; // Use the relationship
            $addedByName = $agent ? $agent->f_name . ' ' . $agent->l_name : 'N/A';
    
            $hasUnpaidLoans = $client->userLoans()->where('status', '!=', 2)->exists();
            $paymentStatusLabel = $hasUnpaidLoans ? 'Unpaid' : 'Paid';
    
            $clientsData[] = [
                'client' => $client,
                'added_by_name' => $addedByName,
                'payment_status' => $paymentStatusLabel,
            ];
        }
    
        return view('admin-views.clients.allClients', compact(
            'clientsData',
            'search',
            'pageTitle',
            'emptyMessage',
            'clients',
            'agents',
            'queryParams'
        ));
    }


    public function clientsNice(Request $request)
    {
        $pageTitle = 'All Clients';
        $emptyMessage = 'No clients found';
    
        $queryParams = [];
        $search = $request->get('search');
        $clientsQuery = Client::query();
    
        // Apply search filters
        if (!empty($search)) {
            $key = explode(' ', $search);
            $clientsQuery->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                      ->orWhere('nin', 'like', "%{$value}%")
                      ->orWhere('email', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParams['search'] = $search;
        }
    
        // Fetch agents
        $agents = User::where('type', 2)
            ->select('id', 'f_name', 'l_name')
            ->get();
    
        // Filter by agent using ID
        $agentId = $request->get('agent_id');
        if (!empty($agentId) && $agentId != 'all') {
            $clientsQuery->where('added_by', $agentId);
            $queryParams['agent_id'] = $agentId;
        }
    
        // Filter by payment status
        $paymentStatus = $request->get('payment_status');
        if (!empty($paymentStatus) && $paymentStatus != 'all') {
            if ($paymentStatus == 'paid') {
                $clientsQuery->whereDoesntHave('userLoans', function ($query) {
                    $query->where('status', '!=', 2);
                });
            } elseif ($paymentStatus == 'unpaid') {
                $clientsQuery->whereHas('userLoans', function ($query) {
                    $query->where('status', '!=', 2);
                });
            }
            $queryParams['payment_status'] = $paymentStatus;
        }
    
        // Filter by client status
        $status = $request->get('status');
        if (!empty($status) && $status != 'all') {
            $clientsQuery->where('status', $status);
            $queryParams['status'] = $status;
        }
    
        // Date range filter
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
    
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDateParsed = Carbon::parse($fromDate)->startOfDay();
            $toDateParsed = Carbon::parse($toDate)->endOfDay();
            $clientsQuery->whereBetween('created_at', [$fromDateParsed, $toDateParsed]);
            $queryParams['from_date'] = $fromDate;
            $queryParams['to_date'] = $toDate;
        }
    
        // Export to Excel
        if ($request->has('export') && $request->export == 'excel') {
            $clients = $clientsQuery->get();
            return Excel::download(new ClientsExport($clients), 'clients.xlsx');
        }
    
        // Paginate the results
        $page = $request->get('page', 1);
        $clients = $clientsQuery->latest()->paginate(20, ['*'], 'page', $page)->appends($queryParams);
    
        // Collect data for the view
        $clientsData = [];
        foreach ($clients as $client) {
            $agent = $client->addedBy; // Ensure 'addedBy' relationship is defined
            $addedByName = $agent ? $agent->f_name . ' ' . $agent->l_name : 'N/A';
    
            $hasUnpaidLoans = $client->userLoans()->where('status', '!=', 2)->exists();
            $paymentStatusLabel = $hasUnpaidLoans ? 'Unpaid' : 'Paid';
    
            $clientsData[] = [
                'client' => $client,
                'added_by_name' => $addedByName,
                'payment_status' => $paymentStatusLabel,
            ];
        }
    
        // Check if the request is an AJAX request
        if ($request->ajax()) {
            // Return the partial view as JSON for AJAX requests
            $html = view('admin-views.clients.partials.clients-table', compact('clientsData', 'clients', 'emptyMessage'))->render();
            return response()->json(['html' => $html]);
        } else {
            // Return the full view for normal requests
            return view('admin-views.clients.allClients', compact(
                'clientsData',
                'search',
                'pageTitle',
                'emptyMessage',
                'clients',
                'agents',
                'queryParams'
            ));
        }
    }


    public function clientsXXXX(Request $request)
{
    $pageTitle = 'All Clients';
    $emptyMessage = 'No clients found';

    $queryParams = [];
    $search = $request->get('search');
    $clientsQuery = Client::query();

    // Apply search filters
    if (!empty($search)) {
        $key = explode(' ', $search);
        $clientsQuery->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%")
                  ->orWhere('nin', 'like', "%{$value}%")
                  ->orWhere('email', 'like', "%{$value}%")
                  ->orWhere('phone', 'like', "%{$value}%");
            }
        });
        $queryParams['search'] = $search;
    }

    // Fetch agents
    $agents = User::where('type', 2)
        ->select('id', 'f_name', 'l_name')
        ->get();

    // Filter by agent ID
    $agentId = $request->get('agent_id');
    if (!empty($agentId) && $agentId != 'all') {
        $clientsQuery->where('added_by', $agentId);
        $queryParams['agent_id'] = $agentId;
    }

    // Filter by payment status
    $paymentStatus = $request->get('payment_status');
    if (!empty($paymentStatus) && $paymentStatus != 'all') {
        if ($paymentStatus == 'paid') {
            $clientsQuery->whereDoesntHave('userLoans', function ($query) {
                $query->where('status', '!=', 2);
            });
        } elseif ($paymentStatus == 'unpaid') {
            $clientsQuery->whereHas('userLoans', function ($query) {
                $query->where('status', '!=', 2);
            });
        }
        $queryParams['payment_status'] = $paymentStatus;
    }

    // Filter by client status
    $status = $request->get('status');
    if (!empty($status) && $status != 'all') {
        $clientsQuery->where('status', $status);
        $queryParams['status'] = $status;
    }

    // Date range filter
    $fromDate = $request->get('from_date');
    $toDate = $request->get('to_date');

    if (!empty($fromDate) && !empty($toDate)) {
        $fromDateParsed = Carbon::parse($fromDate)->startOfDay();
        $toDateParsed = Carbon::parse($toDate)->endOfDay();
        $clientsQuery->whereBetween('created_at', [$fromDateParsed, $toDateParsed]);
        $queryParams['from_date'] = $fromDate;
        $queryParams['to_date'] = $toDate;
    }

    // Export to Excel
    if ($request->has('export') && $request->export == 'excel') {
        $clients = $clientsQuery->get();
        return Excel::download(new ClientsExport($clients), 'clients.xlsx');
    }

    // Download PDF
    if ($request->has('download') && $request->download == 'pdf') {
        $clients = $clientsQuery->with(['addedBy'])->get();
        $clientsData = [];
        foreach ($clients as $client) {
            $agent = $client->addedBy;
            $addedByName = $agent ? $agent->f_name . ' ' . $agent->l_name : 'N/A';
            $clientsData[] = [
                'client_name' => $client->name,
                'credit_balance' => $client->credit_balance,
                'phone' => $client->phone,
                'agent_name' => $addedByName,
            ];
        }
        // Generate PDF
        $pdf = PDF::loadView('admin-views.clients.client-report-pdf', [
            'clientsData' => $clientsData,
            'filters' => $queryParams,
        ]);
        return $pdf->download('client_report.pdf');
    }

    // Paginate the results
    $page = $request->get('page', 1);
    $clients = $clientsQuery->latest()->paginate(20, ['*'], 'page', $page)->appends($queryParams);

    // Collect data for the view
    $clientsData = [];
    foreach ($clients as $client) {
        $agent = $client->addedBy; // Ensure 'addedBy' relationship is defined
        $addedByName = $agent ? $agent->f_name . ' ' . $agent->l_name : 'N/A';

        $hasUnpaidLoans = $client->userLoans()->where('status', '!=', 2)->exists();
        $paymentStatusLabel = $hasUnpaidLoans ? 'Unpaid' : 'Paid';

        $clientsData[] = [
            'client' => $client,
            'added_by_name' => $addedByName,
            'payment_status' => $paymentStatusLabel,
        ];
    }

    // Check if the request is an AJAX request
    if ($request->ajax()) {
        // Return the partial view as JSON for AJAX requests
        $html = view('admin-views.clients.partials.clients-table', compact('clientsData', 'clients', 'emptyMessage'))->render();
        return response()->json(['html' => $html]);
    } else {
        // Return the full view for normal requests
        return view('admin-views.clients.allClients', compact(
            'clientsData',
            'search',
            'pageTitle',
            'emptyMessage',
            'clients',
            'agents',
            'queryParams'
        ));
    }
}


public function clients(Request $request)
{
    $pageTitle = 'All Clients';
    $emptyMessage = 'No clients found';

    $queryParams = [];
    $search = $request->get('search');
    $clientsQuery = Client::query();

    // Apply search filters
    if (!empty($search)) {
        $key = explode(' ', $search);
        $clientsQuery->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%")
                  ->orWhere('nin', 'like', "%{$value}%")
                  ->orWhere('email', 'like', "%{$value}%")
                  ->orWhere('phone', 'like', "%{$value}%");
            }
        });
        $queryParams['search'] = $search;
    }

    // Fetch agents
    $agents = User::where('type', 2)
        ->select('id', 'f_name', 'l_name')
        ->get();

    // Filter by agent ID
    $agentId = $request->get('agent_id');
    if (!empty($agentId) && $agentId != 'all') {
        $clientsQuery->where('added_by', $agentId);
        $queryParams['agent_id'] = $agentId;
    }

    // Filter by payment status
    $paymentStatus = $request->get('payment_status');
    if (!empty($paymentStatus) && $paymentStatus != 'all') {
        if ($paymentStatus == 'paid') {
            $clientsQuery->whereDoesntHave('userLoans', function ($query) {
                $query->where('status', '!=', 2);
            });
        } elseif ($paymentStatus == 'unpaid') {
            $clientsQuery->whereHas('userLoans', function ($query) {
                $query->where('status', '!=', 2);
            });
        }
        $queryParams['payment_status'] = $paymentStatus;
    }

    // Filter by client status
    $status = $request->get('status');
    if (!empty($status) && $status != 'all') {
        $clientsQuery->where('status', $status);
        $queryParams['status'] = $status;
    }

    // Date range filter
    $fromDate = $request->get('from_date');
    $toDate = $request->get('to_date');

    if (!empty($fromDate) && !empty($toDate)) {
        $fromDateParsed = Carbon::parse($fromDate)->startOfDay();
        $toDateParsed = Carbon::parse($toDate)->endOfDay();
        $clientsQuery->whereBetween('created_at', [$fromDateParsed, $toDateParsed]);
        $queryParams['from_date'] = $fromDate;
        $queryParams['to_date'] = $toDate;
    }

    // Filter clients who paid or didn't pay today
    $paidTodayFilter = $request->get('paid_today');
    if (!empty($paidTodayFilter) && $paidTodayFilter != 'all') {
        $today = Carbon::today();
        if ($paidTodayFilter === 'paid') {
            $clientsQuery->whereHas('loanPayments', function ($query) use ($today) {
                $query->whereDate('payment_date', $today);
            });
        } elseif ($paidTodayFilter === 'not_paid') {
            $clientsQuery->whereDoesntHave('loanPayments', function ($query) use ($today) {
                $query->whereDate('payment_date', $today);
            });
        }
        $queryParams['paid_today'] = $paidTodayFilter;
    }

    // Export to Excel
    if ($request->has('export') && $request->export == 'excel') {
        $clients = $clientsQuery->get();
        return Excel::download(new ClientsExport($clients), 'clients.xlsx');
    }

    // Download PDF
    if ($request->has('download') && $request->download == 'pdf') {
        $clients = $clientsQuery->with(['addedBy'])->get();
        $clientsData = [];
        foreach ($clients as $client) {
            $agent = $client->addedBy;
            $addedByName = $agent ? $agent->f_name . ' ' . $agent->l_name : 'N/A';
            $clientsData[] = [
                'client_name' => $client->name,
                'credit_balance' => $client->credit_balance,
                'phone' => $client->phone,
                'agent_name' => $addedByName,
            ];
        }
        // Generate PDF
        $pdf = PDF::loadView('admin-views.clients.client-report-pdf', [
            'clientsData' => $clientsData,
            'filters' => $queryParams,
        ]);
        return $pdf->download('client_report.pdf');
    }

    // Paginate the results
    $page = $request->get('page', 1);
    $clients = $clientsQuery->latest()->paginate(20, ['*'], 'page', $page)->appends($queryParams);

    // Collect data for the view
    $clientsData = [];
    $today = Carbon::today();
    foreach ($clients as $client) {
        $agent = $client->addedBy; // Ensure 'addedBy' relationship is defined
        $addedByName = $agent ? $agent->f_name . ' ' . $agent->l_name : 'N/A';

        $hasUnpaidLoans = $client->userLoans()->where('status', '!=', 2)->exists();
        $paymentStatusLabel = $hasUnpaidLoans ? 'Unpaid' : 'Paid';

        // Check if client has made a payment today
        $hasPaidToday = $client->loanPayments()->whereDate('payment_date', $today)->exists();

        $clientsData[] = [
            'client' => $client,
            'added_by_name' => $addedByName,
            'payment_status' => $paymentStatusLabel,
            'has_paid_today' => $hasPaidToday,
        ];
    }

    // Check if the request is an AJAX request
    if ($request->ajax()) {
        // Return the partial view as JSON for AJAX requests
        $html = view('admin-views.clients.partials.clients-table', compact('clientsData', 'clients', 'emptyMessage'))->render();
        return response()->json(['html' => $html]);
    } else {
        // Return the full view for normal requests
        return view('admin-views.clients.allClients', compact(
            'clientsData',
            'search',
            'pageTitle',
            'emptyMessage',
            'clients',
            'agents',
            'queryParams'
        ));
    }
}

    
    
    
    
    public function clients222(Request $request): Factory|View|Application
    {
        $pageTitle = 'All Clients';
        $emptyMessage = 'No clients found';
    
        // Retrieve query parameters
        $queryParams = $request->all();
        $search = $request->get('search');
    
        // Start building the query
        $clientsQuery = Client::query();
    
        // Apply search filters
        if ($search) {
            $key = explode(' ', $search);
            $clientsQuery->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                      ->orWhere('nin', 'like', "%{$value}%")
                      ->orWhere('email', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%");
                }
            });
        }
    
        // Fetch agents with UUIDs
        $agents = User::where('type', 2)
            ->select('id', 'f_name', 'l_name')
            ->get();

            // ddd
    
        // Filter by agent using UUID
        $agentUuid = $request->get('agent_id');
        if ($agentUuid && $agentUuid != 'all') {
            $clientsQuery->where('added_by', $agentUuid);
        }
    
        // Filter by payment status
        $paymentStatus = $request->get('payment_status');
        if ($paymentStatus == 'paid') {
            // Clients who have paid all their loans
            $clientsQuery->whereDoesntHave('userLoans', function ($query) {
                $query->where('status', '!=', 2); // Assuming status 2 means 'paid'
            });
        } elseif ($paymentStatus == 'unpaid') {
            // Clients who have unpaid loans
            $clientsQuery->whereHas('userLoans', function ($query) {
                $query->where('status', '!=', 2); // Not 'paid'
            });
        }
    
        // Additional filters (e.g., client status)
        if ($request->has('status') && $request->status != 'all') {
            $clientsQuery->where('status', $request->status);
        }
    
        // Date range filter
        if ($request->has('from_date') && $request->has('to_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            $clientsQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }
    
        // Export to Excel
        if ($request->has('export') && $request->export == 'excel') {
            $clients = $clientsQuery->get();
            return Excel::download(new ClientsExport($clients), 'clients.xlsx');
        }
    
        // Paginate the results
        $clients = $clientsQuery->latest()->paginate(20)->appends($queryParams);
    
        // Collect data for the view
        $clientsData = [];
        foreach ($clients as $client) {
            $agent = $client->addedBy; // Use the relationship
            $addedByName = $agent ? $agent->f_name . ' ' . $agent->l_name : 'N/A';
    
            $hasUnpaidLoans = $client->userLoans()->where('status', '!=', 2)->exists();
            $paymentStatus = $hasUnpaidLoans ? 'Unpaid' : 'Paid';
    
            $clientsData[] = [
                'client' => $client,
                'added_by_name' => $addedByName,
                'payment_status' => $paymentStatus,
            ];
        }
    
        return view('admin-views.clients.allClients', compact(
            'clientsData',
            'search',
            'pageTitle',
            'emptyMessage',
            'clients',
            'agents',
            'queryParams'
        ));
    }
    

    public function clientsX(Request $request): Factory|View|Application
    {
        $pageTitle = 'All Clients';
        $emptyMessage = 'No clients found';
    
        // Retrieve query parameters
        $queryParams = $request->all();
        $search = $request->get('search');
    
        // Start building the query
        $clientsQuery = Client::query();
    
        // Apply search filters
        if ($search) {
            $key = explode(' ', $search);
            $clientsQuery->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                      ->orWhere('nin', 'like', "%{$value}%")
                      ->orWhere('email', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%");
                }
            });
        }
    
        // Filter by agent
        $agentId = $request->get('agent_id');
        if ($agentId && $agentId != 'all') {
            $clientsQuery->where('added_by', $agentId);
        }
    
        // Filter by payment status
        $paymentStatus = $request->get('payment_status');
        if ($paymentStatus == 'paid') {
            // Clients who have paid all their loans
            $clientsQuery->whereDoesntHave('userLoans', function ($query) {
                $query->where('status', '!=', 2); // Assuming status 2 means 'paid'
            });
        } elseif ($paymentStatus == 'unpaid') {
            // Clients who have unpaid loans
            $clientsQuery->whereHas('userLoans', function ($query) {
                $query->where('status', '!=', 2); // Not 'paid'
            });
        }
    
        // Additional filters (e.g., other client table variables)
        // Example: Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $clientsQuery->where('status', $request->status);
        }
    
        // Date range filter
        if ($request->has('from_date') && $request->has('to_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            $clientsQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }
    
        // Export to Excel
        if ($request->has('export') && $request->export == 'excel') {
            $clients = $clientsQuery->get();
            return Excel::download(new ClientsExport($clients), 'clients.xlsx');
        }
    
        // Paginate the results
        $clients = $clientsQuery->latest()->paginate(20)->appends($queryParams);
    
        // Collect data for the view
        $clientsData = [];
        foreach ($clients as $client) {
            $addedByUser = User::find($client->added_by);
            $hasUnpaidLoans = $client->userLoans()->where('status', '!=', 2)->exists();
            $paymentStatus = $hasUnpaidLoans ? 'Unpaid' : 'Paid';
            $clientsData[] = [
                'client' => $client,
                'added_by_name' => $addedByUser ? $addedByUser->f_name . ' ' . $addedByUser->l_name : 'N/A',
                'payment_status' => $paymentStatus, // Ensure this key is set
            ];
        }
    
        // Get list of agents for filter dropdown
        $agents = User::where('type', 2)->get(); // Adjust 'type' field as per your User model
    
        return view('admin-views.clients.allClients', compact(
            'clientsData',
            'search',
            'pageTitle',
            'emptyMessage',
            'clients',
            'agents',
            'queryParams'
        ));
    }
    

     public function clients2(Request $request): Factory|View|Application
    {
        // $this -> generateSanaaCardData();
        $pageTitle = 'All Clients';
        $emptyMessage = 'No clients found';
    
        $queryParams = [];
        $search = $request->get('search');
        
        $clientsQuery = Client::query();
        
        if ($search) {
            $key = explode(' ', $search);
            $clientsQuery->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                      ->orWhere('nin', 'like', "%{$value}%")
                      ->orWhere('email', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParams['search'] = $search;
        }
    
        $clientsData = [];
        $clients = $clientsQuery->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);
    
        // Loop through the clients and collect the required data
        foreach ($clients as $client) {
            $addedByUser = User::find($client->added_by); // Fetch the user who added this client
            
            $clientsData[] = [
                'client' => $client,
               'added_by_name' => $addedByUser ? $addedByUser->f_name . ' ' . $addedByUser->l_name : 'N/A',

            ];
        }
    
        return view('admin-views.clients.allClients', compact('clientsData', 'search', 'pageTitle', 'emptyMessage', 'clients'));
    }


    
    // active clients
     public function activeClients(Request $request): Factory|View|Application
        {
            
            
            
            
            // $this -> generateSanaaCardData();
        $pageTitle = 'Active Clients';
        $emptyMessage = 'No clients found';
    
        $queryParams = [];
        $search = $request->get('search');
        
        $clientsQuery = Client::where('status', 'active');
        
        if ($search) {
            $key = explode(' ', $search);
            $clientsQuery->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                      ->orWhere('nin', 'like', "%{$value}%")
                      ->orWhere('email', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParams['search'] = $search;
        }
    
        $clientsData = [];
        $clients = $clientsQuery->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);
    
        // Loop through the clients and collect the required data
        foreach ($clients as $client) {
            $addedByUser = User::find($client->added_by); // Fetch the user who added this client
            
            $clientsData[] = [
                'client' => $client,
               'added_by_name' => $addedByUser ? $addedByUser->f_name . ' ' . $addedByUser->l_name : 'N/A',

            ];
        }
    
        return view('admin-views.clients.index', compact('clientsData', 'search', 'pageTitle', 'emptyMessage', 'clients'));
        }

    
    
 public function agentReport3333()
{
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

    // Initialize totals
    $totalClients = 0;
    $totalMoneyOut = 0;
    $totalAmountCollected = 0;
    $totalPerformance = 0;

    foreach ($agents as $agent) {
        // Calculate expected daily amount from total money out
        $agent->expected_daily = $agent->total_money_out / 30;

        // Calculate amount collected from cleared payment slots
        $agent->amount_collected = LoanPaymentInstallment::where('agent_id', $agent->id)
            ->where('status', 'cleared')
            ->sum('install_amount');

        // Calculate performance percentage
        if ($agent->expected_daily > 0) {
            $agent->performance_percentage = ($agent->amount_collected / ($agent->expected_daily * 30)) * 100;
        } else {
            $agent->performance_percentage = 0;
        }

        // Update totals
        $totalClients += $agent->client_count;
        $totalMoneyOut += $agent->total_money_out;
        $totalAmountCollected += $agent->amount_collected;
        $totalPerformance += $agent->performance_percentage;
    }

    // Calculate average performance
    $averagePerformance = count($agents) > 0 ? $totalPerformance / count($agents) : 0;

    // Pass totals to the view
    $totals = [
        'total_clients' => $totalClients,
        'total_money_out' => $totalMoneyOut,
        'total_amount_collected' => $totalAmountCollected,
        'average_performance' => $averagePerformance,
    ];

    return view('admin-views.reports.agent-report', compact('agents', 'totals'));
}

    
    
  

  
    
    
    public function distroy(Request $request)
    {
        
        
       $client = Client::findOrFail($request->id);
        $client->delete();
        Toastr::success(translate('Message Delete successfully'));
        
        // Redirect back to the client list with a success message
        return back(); //redirect()->route('admin.allclients');

    }


    
    // with balance
    public function clientsWithBalance()
{
    $pageTitle = 'Clients With Balance';
    $query = Client::where('credit_balance', '>', 0); // Updated to check credit_balance
    $emptyMessage = 'No Clients With Balance Yet';
    
    if (request()->search) {
        $query = $query->where('trx', request()->search);
        $emptyMessage = 'No Data Found';
    }
    
    $clients = $query->paginate(20);
    
    return view('admin-views.clients.index', compact('pageTitle', 'emptyMessage', 'clients'));
}


// banned
      public function bannedClients(Request $request): Factory|View|Application
        {
            $pageTitle = 'Banned Clients';
            $queryParams = [];
            $search = $request->input('search');
        
            $query = Client::where('status', 'banned');
            $emptyMessage = 'No Banned Clients Yet';
        
            if ($request->has('search')) {
                $query = $query->where('trx', 'like', "%{$search}%");
                $emptyMessage = 'No Data Found';
                $queryParams['search'] = $search;
            }
        
            $clients = $query->paginate(Helpers::pagination_limit())->appends($queryParams);
        
            return view('admin-views.clients.index', compact('pageTitle', 'emptyMessage', 'clients', 'search'));
        }

            
    // verified
    public function verifiedClients(Request $request): Factory|View|Application
                {
                    $pageTitle = 'Verified Clients';
                    $queryParams = [];
                    $search = $request->input('search');
                
                    $query = Client::where('verified', true);
                    $emptyMessage = 'No Verified Clients Yet';
                
                    if ($request->has('search')) {
                        $query = $query->where('trx', 'like', "%{$search}%");
                        $emptyMessage = 'No Data Found';
                        $queryParams['search'] = $search;
                    }
                
                    $clients = $query->paginate(Helpers::pagination_limit())->appends($queryParams);
                
                    return view('admin-views.clients.index', compact('pageTitle', 'emptyMessage', 'clients', 'search'));
                }




    /**
     * Store a newly created client in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     
    //  get client profile 
   public function getClientProfile(Request $request): JsonResponse
    {
        $client = Client::where('id', $request -> id)->get();
        return response()->json(response_formatter(DEFAULT_200, $client, null), 200);
        
    }
     

    
    public function addClient(Request $request): JsonResponse
    {
        

        $client = Client::create($request->all());

        return response()->json(response_formatter(DEFAULT_200, $client, null), 200);

    }
    
    // get client guarantors
    public function clientguarantorsList(Request $request): JsonResponse
    {
        $clientguarantors = Guarantor::where('client_id', $request -> id)->get();
        return response()->json(response_formatter(DEFAULT_200, $clientguarantors, null), 200);
        
    }
    
  
    
    
    // add photos
    public function addClientPhotos(Request $request): JsonResponse
        {
            // Validate the request
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'photo' => 'nullable|file|mimes:jpeg,png,jpg',
                'national_id_photo' => 'nullable|file|mimes:jpeg,png,jpg',
            ]);
        
    
        
            $photoPath = $request->file('photo') ? $request->file('photo')->store('clients/photos', 'public') : null;
            $nationalIdPhotoPath = $request->file('national_id_photo') ? $request->file('national_id_photo')->store('clients/national_id_photos', 'public') : null;

            
            
            $client = Client::find($request->client_id);
        
            if ($client) {
                $client->client_photo = $photoPath;
                $client->national_id_photo = $nationalIdPhotoPath;
                $client->save();
            }
        
            // Return a JSON response with the updated client data
            return response()->json(response_formatter(DEFAULT_200, $client, null), 200);
        }

    
    
    
    
      public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'gender' => 'required',
            'occupation' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $this->user->find($request->user()->id);
        $user->f_name = $request->f_name;
        $user->l_name = $request->l_name;
        $user->email = $request->email;
        $user->image = $request->has('image') ? Helpers::update('customer/', $user->image, 'png', $request->image) : $user->image;
        $user->gender = $request->gender;
        $user->occupation = $request->occupation;
        $user->save();
        return response()->json(['message' => 'Profile successfully updated'], 200);
    }
    
   

}
