<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

// Models
use App\Models\User;
use App\Models\Client;
use App\Models\UserLoan;
use App\Models\LoanPaymentInstallment;

/**
 * A polished LoanAnalysisController with powerful AJAX filtering
 * and supporting logic for summary, insights, and chart data.
 */
class LoanAnalysisController extends Controller
{
    /**
     * Display the main Loan Analysis Dashboard.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            // Example: load agents for filtering
            // $agents = User::where('role', 'agent')->get();
            $agents = User::where('type', 2)
            ->where('is_active', 1)
            ->get();

            // dd($agents);
            return view('admin-views.Loans.analysis.index', compact('agents'));
        } catch (\Exception $e) {
            Log::error('Loan Analysis Index Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            Toastr::error('Failed to load the Loan Analysis dashboard.');
            return back();
        }
    }

    /**
     * Fetch loan analysis data via AJAX, applying flexible filters:
     * - status
     * - aging
     * - partial_disbursed
     * - renewed
     * - min_amount, max_amount
     * - min_outstanding, max_outstanding
     * - from_disbursement, to_disbursement
     * - from_due, to_due
     * - agent
     * - pagination
     *
     * Returns JSON with table HTML, summary, insights, and chart data.
     */

     public function fetchDataX(Request $request)
{
    try {
        // Fetch all loans without filters
        $loans = UserLoan::with(['client', 'agent'])->paginate(15);

        // Render partial view
        $loansHtml = view('admin-views.Loans.analysis.partials.loan-table', compact('loans'))->render();

        // Dummy summary and insights
        $summary = [
            'total_loans' => $loans->total(),
            'total_outstanding' => $loans->sum('final_amount') - $loans->sum('paid_amount'),
            'average_repayment_time' => round($loans->avg('installment_interval'), 2),
            'default_rate' => 0.0,
            'overdue_count' => 0,
        ];

        $insights = [
            'high_risk_loans' => [],
            'top_performing_agents' => [],
        ];

        $chartData = [
            'statusData' => [],
            'agingBuckets' => [],
        ];

        return response()->json([
            'html'     => $loansHtml,
            'summary'  => $summary,
            'insights' => $insights,
            'charts'   => $chartData,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Loan Analysis Data Fetch Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'error' => 'An error occurred while fetching loan analysis data. Please try again later.'
        ], 500);
    }
}

// app/Http/Controllers/LoanAnalysisController.php

public function fetchData(Request $request)
{
    // Validate incoming filter params
    $validator = Validator::make($request->all(), [
        'status'            => 'nullable|string|in:all,pending,running,paid,overdue,defaulted',
        'aging'             => 'nullable|string|in:all,30,60,90+',
        'min_amount'        => 'nullable|numeric|min:0',
        'max_amount'        => 'nullable|numeric|min:0',
        'min_outstanding'   => 'nullable|numeric|min:0',
        'max_outstanding'   => 'nullable|numeric|min:0',
        'from_disbursement' => 'nullable|date',
        'to_disbursement'   => 'nullable|date',
        'from_due'          => 'nullable|date',
        'to_due'            => 'nullable|date',
        'agent_id'          => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) {
                if ($value !== 'all' && !User::where('id', $value)->exists()) {
                    $fail('The selected agent id is invalid.');
                }
            },
        ],
        'partial_disbursed' => 'nullable|boolean',
        'renewed'           => 'nullable|boolean',
        'per_page'          => 'nullable|integer|min:1|max:100',
        'page'              => 'nullable|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        // 1. Build the base query without pagination
        $query = UserLoan::with(['client', 'agent']);

        // 2. Apply filter conditions to the query
        $this->applyFilters($query, $request);

        // 3. Clone the query for summaries and insights
        $summaryQuery = clone $query;

        // 4. Calculate summaries, insights, and chart data
        $summary   = $this->calculateSummary($summaryQuery);
        $insights  = $this->generateInsights($summaryQuery);
        $chartData = $this->generateChartData($summaryQuery);

        // 5. Determine pagination parameters
        $perPage = $request->input('per_page', 15);
        $page    = $request->input('page', 1);

        // 6. Paginate the filtered query
        $loans = $query->paginate($perPage, ['*'], 'page', $page)->appends($request->except('page'));

        // 7. Render the partial view with the paginated loans
        $loansHtml = view('admin-views.Loans.analysis.partials.loan-table', compact('loans'))->render();

        // 8. Return the JSON response with all necessary data
        return response()->json([
            'html'     => $loansHtml,
            'summary'  => $summary,
            'insights' => $insights,
            'charts'   => $chartData,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Loan Analysis Data Fetch Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'input' => $request->all(),
        ]);
        return response()->json([
            'error' => 'An error occurred while fetching loan analysis data. Please try again later.'
        ], 500);
    }
}


public function fetchDataWorking(Request $request)
{
    // Validate incoming filter params
    $validator = Validator::make($request->all(), [
        'status'            => 'nullable|string|in:all,pending,running,paid,overdue,defaulted',
        'aging'             => 'nullable|string|in:all,30,60,90+',
        'min_amount'        => 'nullable|numeric|min:0',
        'max_amount'        => 'nullable|numeric|min:0',
        'min_outstanding'   => 'nullable|numeric|min:0',
        'max_outstanding'   => 'nullable|numeric|min:0',
        'from_disbursement' => 'nullable|date',
        'to_disbursement'   => 'nullable|date',
        'from_due'          => 'nullable|date',
        'to_due'            => 'nullable|date',
        'agent_id'          => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) {
                if ($value !== 'all' && !User::where('id', $value)->exists()) {
                    $fail('The selected agent id is invalid.');
                }
            },
        ],
        'partial_disbursed' => 'nullable|boolean',
        'renewed'           => 'nullable|boolean',
        'per_page'          => 'nullable|integer|min:1|max:100',
        'page'              => 'nullable|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        // 1. Build the base query without pagination
        $query = UserLoan::with(['client', 'agent']);

        // 2. Apply filter conditions to the query
        $this->applyFilters($query, $request);

        // 3. Clone the query for summaries and insights
        $summaryQuery = clone $query;

        // 4. Calculate summaries, insights, and chart data
        $summary   = $this->calculateSummary($summaryQuery);
        $insights  = $this->generateInsights($summaryQuery);
        $chartData = $this->generateChartData($summaryQuery);

        // 5. Determine pagination parameters
        $perPage = $request->input('per_page', 15);
        $page    = $request->input('page', 1);

        // 6. Paginate the filtered query
        $loans = $query->paginate($perPage, ['*'], 'page', $page)->appends($request->except('page'));

        // 7. Render the partial view with the paginated loans
        $loansHtml = view('admin-views.Loans.analysis.partials.loan-table', compact('loans'))->render();

        // 8. Return the JSON response with all necessary data
        return response()->json([
            'html'     => $loansHtml,
            'summary'  => $summary,
            'insights' => $insights,
            'charts'   => $chartData,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Loan Analysis Data Fetch Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'input' => $request->all(), // Optional: Include input for debugging
        ]);
        return response()->json([
            'error' => 'An error occurred while fetching loan analysis data. Please try again later.'
        ], 500);
    }
}


    public function fetchData2(Request $request)
    {
        // Validate incoming filter params
        $validator = Validator::make($request->all(), [
            'status'            => 'nullable|string|in:all,pending,running,paid,overdue,defaulted',
            'aging'             => 'nullable|string|in:all,30,60,90+',
            'min_amount'        => 'nullable|numeric|min:0',
            'max_amount'        => 'nullable|numeric|min:0',
            'min_outstanding'   => 'nullable|numeric|min:0',
            'max_outstanding'   => 'nullable|numeric|min:0',
            'from_disbursement' => 'nullable|date',
            'to_disbursement'   => 'nullable|date',
            'from_due'          => 'nullable|date',
            'to_due'            => 'nullable|date',
            'agent_id'          => 'nullable|exists:users,id',
            'partial_disbursed' => 'nullable|boolean',
            'renewed'           => 'nullable|boolean',
            'per_page'          => 'nullable|integer|min:1|max:100',
            'page'              => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Build base query, eager-load relationships if needed
            $query  = UserLoan::with(['client', 'agent'])->paginate(15);
//            = UserLoan::with(['client', 'agent'])->newQuery();

            // dd($query);

            // Apply filter conditions
            $this->applyFilters($query, $request);

            // Clone for summary/insights (so pagination doesn't affect counts)
            $summaryQuery = clone $query;

            // Summaries, Insights, Chart Data
            $summary  = $this->calculateSummary($summaryQuery);
            $insights = $this->generateInsights($summaryQuery);
            $chartData = $this->generateChartData($summaryQuery);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $loans = $query->paginate($perPage)->appends($request->except('page'));

            // Render partial for the loan table
            $loansHtml = view('admin-views.Loans.analysis.partials.loan-table', compact('loans'))->render();

            // Return JSON containing table HTML, summary, insights, chart data
            return response()->json([
                'html'     => $loansHtml,
                'summary'  => $summary,
                'insights' => $insights,
                'charts'   => $chartData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Loan Analysis Data Fetch Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'An error occurred while fetching loan analysis data. Please try again later.'
            ], 500);
        }
    }

    /**
     * Apply various filters to the query based on the request data.
     */
    private function applyFilters($query, Request $request)
    {
        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            // Example numeric statuses:
            // 0 => pending, 1 => running, 2 => paid, 3 => overdue, 4 => defaulted
            switch ($request->status) {
                case 'pending':
                    $query->where('status', 0);
                    break;
                case 'running':
                    $query->where('status', 1);
                    break;
                case 'paid':
                    $query->where('status', 2);
                    break;
                case 'overdue':
                    $query->where('status', 3);
                    break;
                case 'defaulted':
                    $query->where('status', 4);
                    break;
                default:
                    break;
            }
        }

        // Filter by aging
        if ($request->filled('aging') && $request->aging !== 'all') {
            $this->filterByAging($query, $request->aging);
        }

        // Filter by partial disbursed
        if ($request->filled('partial_disbursed') && $request->partial_disbursed == true) {
            $query->where('is_partial_disbursement', 1);
        }

        // Filter by renewed
        if ($request->filled('renewed') && $request->renewed == true) {
            $query->where('is_renewed', 1);
        }

        // Filter by agent
        if ($request->filled('agent_id') && $request->agent_id !== 'all') {
            $query->where('user_id', $request->agent_id);
        }

        // Filter by min/max principal amount
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Filter by disbursement date range
        if ($request->filled('from_disbursement') && $request->filled('to_disbursement')) {
            $from = Carbon::parse($request->from_disbursement)->startOfDay();
            $to   = Carbon::parse($request->to_disbursement)->endOfDay();
            $query->whereBetween('disbursed_at', [$from, $to]);
        }

        // Filter by due date range
        if ($request->filled('from_due') && $request->filled('to_due')) {
            $from = Carbon::parse($request->from_due)->startOfDay();
            $to   = Carbon::parse($request->to_due)->endOfDay();
            $query->whereBetween('due_date', [$from, $to]);
        }

        // Filter by min/max outstanding (final_amount - paid_amount)
        if ($request->filled('min_outstanding') || $request->filled('max_outstanding')) {
            $query->where(function($q) use ($request) {
                $outstandingExpr = DB::raw('(final_amount - paid_amount)');
                if ($request->filled('min_outstanding')) {
                    $q->where($outstandingExpr, '>=', $request->min_outstanding);
                }
                if ($request->filled('max_outstanding')) {
                    $q->where($outstandingExpr, '<=', $request->max_outstanding);
                }
            });
        }
    }

    /**
     * Filter by aging buckets (0-30, 31-60, 61-90, 90+).
     */

    private function filterByAging($query, $aging)
    {
        switch ($aging) {
            case '30':
                // Last 30 days
                $query->whereDate('loan_taken_date', '>=', Carbon::now()->subDays(30));
                break;
            case '60':
                // 31-60 days
                $query->whereBetween('loan_taken_date', [
                    Carbon::now()->subDays(60),
                    Carbon::now()->subDays(31)
                ]);
                break;
            case '90+':
                // Older than 90 days
                $query->whereDate('loan_taken_date', '<=', Carbon::now()->subDays(90));
                break;
            default:
                break;
        }
    }

    /**
     * Calculate summary stats: # of loans, total outstanding, default rate, etc.
     */
    private function calculateSummary($query)
    {
        $totalLoans       = $query->count();
        $totalFinalAmount = $query->sum('final_amount');
        $totalPaid        = $query->sum('paid_amount');
        $totalOutstanding = $totalFinalAmount - $totalPaid;

        // Example: average installment interval
        $averageRepaymentTime = round($query->avg('installment_interval'), 2);

        // Overdue (status=3)
        $overdueCount = (clone $query)->where('status', 3)->count();
        // Defaulted (status=4)
        $defaultedCount = (clone $query)->where('status', 4)->count();

        // Example default rate
        $defaultRate = ($totalLoans > 0)
            ? round(($defaultedCount / $totalLoans) * 100, 2)
            : 0.0;

        return [
            'total_loans'            => $totalLoans,
            'total_outstanding'      => $totalOutstanding,
            'average_repayment_time' => $averageRepaymentTime,
            'default_rate'           => $defaultRate,
            'overdue_count'          => $overdueCount,
        ];
    }

    /**
     * Generate insights (like high-risk loans, top agents, etc.).
     */
    private function generateInsights($query)
    {
        // High-risk => overdue or defaulted
        $highRisk = (clone $query)
            ->whereIn('status', [3, 4])
            ->take(5)
            ->get();

        // Example: top agents => those with the most running loans
        $topAgents = UserLoan::select('user_id', DB::raw('COUNT(*) as total'))
            ->where('status', 1) // running
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('agent')
            ->get();

        return [
            'high_risk_loans' => $highRisk,
            'top_agents'      => $topAgents,
        ];
    }

    /**
     * Generate chart data: status distribution, aging buckets, etc.
     */
    private function generateChartData($query)
    {
        // Status distribution
        $statusDistribution = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Aging distribution
        $agingBuckets = [
            '0-30 Days'  => (clone $query)->where('loan_taken_date', '>=', Carbon::now()->subDays(30))->count(),
            '31-60 Days' => (clone $query)->whereBetween('loan_taken_date', [Carbon::now()->subDays(60), Carbon::now()->subDays(31)])->count(),
            '61-90 Days' => (clone $query)->whereBetween('loan_taken_date', [Carbon::now()->subDays(90), Carbon::now()->subDays(61)])->count(),
            '90+ Days'   => (clone $query)->where('loan_taken_date', '<=', Carbon::now()->subDays(90))->count(),
        ];

        return [
            'statusData'   => $statusDistribution,
            'agingBuckets' => $agingBuckets,
        ];
    }
}
