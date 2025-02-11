<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;

/**
 * LoanAnalysisController:
 * A comprehensive loan analysis with advanced filtering, summary, charts, and insights.
 */
class LoanAnalysisController extends Controller
{
    /**
     * Show the main Loan Analysis page (Blade view with filters, summary, charts).
     */
    public function index()
    {
        try {
            // Agents for a dropdown filter
            $agents = User::where('type', 2)
                ->where('is_active', 1)
                ->get();

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
     * Fetch loan analysis data via AJAX.
     * Returns:
     *  - HTML partial for table
     *  - summary stats
     *  - insights
     *  - chart data
     */
    public function fetchData(Request $request)
    {
        // 1) Validate filters
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
                        $fail('Invalid agent ID.');
                    }
                },
            ],
            'partial_disbursed' => 'nullable|boolean',
            'renewed'           => 'nullable|boolean',
            'per_page'          => 'nullable|integer|min:1|max:100',
            'page'              => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 2) Normalize 'page' => integer
            $pageParam = $request->input('page', 1);
            $page = filter_var($pageParam, FILTER_VALIDATE_INT, [
                'options' => ['default' => 1, 'min_range' => 1],
            ]);

            // 3) Build base query. Exclude orphaned loans:
            $query = UserLoan::with(['client', 'agent'])
                ->whereHas('client')
                ->whereHas('agent');

            // 4) Apply filters
            $this->applyFilters($query, $request);

            // 5) Clone for summary, insights, charts
            $summaryQuery = clone $query;

            // 6) Calculate
            $summary   = $this->calculateSummary($summaryQuery);
            $insights  = $this->generateInsights($summaryQuery);
            $chartData = $this->generateChartData($summaryQuery);

            // 7) Pagination
            $perPage = $request->input('per_page', 15);
            $loans   = $query->paginate($perPage, ['*'], 'page', $page)
                        ->appends($request->except('page'));

            // 8) Render partial
            $loansHtml = view('admin-views.Loans.analysis.partials.loan-table', compact('loans'))->render();

            // Return the combined JSON
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

    /**
     * Apply filters to the query (status, aging, amounts, date ranges, agent, etc.).
     */
    private function applyFilters($query, Request $request)
    {
        // Status
        if ($request->filled('status') && $request->status !== 'all') {
            switch ($request->status) {
                case 'pending':   $query->where('status', 0); break;
                case 'running':   $query->where('status', 1); break;
                case 'paid':      $query->where('status', 2); break;
                case 'overdue':   $query->where('status', 3); break;
                case 'defaulted': $query->where('status', 4); break;
            }
        }

        // Aging
        if ($request->filled('aging') && $request->aging !== 'all') {
            $this->filterByAging($query, $request->aging);
        }

        // Partial Disbursed
        if ($request->filled('partial_disbursed') && $request->partial_disbursed) {
            $query->where('is_partial_disbursement', 1);
        }

        // Renewed
        if ($request->filled('renewed') && $request->renewed) {
            $query->where('is_renewed', 1);
        }

        // Agent
        if ($request->filled('agent_id') && $request->agent_id !== 'all') {
            $query->where('user_id', $request->agent_id);
        }

        // Min/Max Principal
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Disbursement date range
        if ($request->filled('from_disbursement') && $request->filled('to_disbursement')) {
            $from = Carbon::parse($request->from_disbursement)->startOfDay();
            $to   = Carbon::parse($request->to_disbursement)->endOfDay();
            $query->whereBetween('disbursed_at', [$from, $to]);
        }

        // Due date range
        if ($request->filled('from_due') && $request->filled('to_due')) {
            $from = Carbon::parse($request->from_due)->startOfDay();
            $to   = Carbon::parse($request->to_due)->endOfDay();
            $query->whereBetween('due_date', [$from, $to]);
        }

        // Min/Max outstanding => (final_amount - paid_amount)
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
     * Filter by aging (0-30, 31-60, 61-90, 90+).
     */
    private function filterByAging($query, $aging)
    {
        switch ($aging) {
            case '30':
                $query->whereDate('loan_taken_date', '>=', Carbon::now()->subDays(30));
                break;
            case '60':
                $query->whereBetween('loan_taken_date', [
                    Carbon::now()->subDays(60),
                    Carbon::now()->subDays(31),
                ]);
                break;
            case '90+':
                $query->whereDate('loan_taken_date', '<=', Carbon::now()->subDays(90));
                break;
        }
    }

    /**
     * Calculate summary stats: total loans, total principal, total final, total outstanding, etc.
     */
    private function calculateSummary($query)
    {
        $totalLoans      = $query->count();
        $totalPrincipal  = $query->sum('amount');       // sum of original principal
        $totalFinal      = $query->sum('final_amount'); // sum of final (principal+interest)
        $totalPaid       = $query->sum('paid_amount');
        $totalOutstanding= $totalFinal - $totalPaid;

        // e.g. average installment interval
        $avgInterval     = round($query->avg('installment_interval'), 2);

        // Overdue => status=3
        $overdueCount    = (clone $query)->where('status', 3)->count();

        // Defaulted => status=4
        $defaultedCount  = (clone $query)->where('status', 4)->count();

        $defaultRate = ($totalLoans > 0)
            ? round(($defaultedCount / $totalLoans) * 100, 2)
            : 0.0;

        return [
            'total_loans'            => $totalLoans,
            'total_principal'        => $totalPrincipal,    // total money given (original principal)
            'total_final'            => $totalFinal,        // total final amounts
            'total_paid'             => $totalPaid,         // total paid so far
            'total_outstanding'      => $totalOutstanding,
            'average_repayment_time' => $avgInterval,
            'default_rate'           => $defaultRate,
            'overdue_count'          => $overdueCount,
        ];
    }

    /**
     * Generate insights: high-risk loans, top agents, top clients, etc.
     */
    private function generateInsights($query)
    {
        // High-risk => overdue or defaulted
        $highRisk = (clone $query)->whereIn('status', [3,4])
            ->orderByDesc('amount')
            ->take(5)
            ->get();

        // Top Agents => # of running loans
        $topAgents = DB::table('user_loans')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->where('status', 1) // running
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Top Clients => by total principal
        // (Optional) you can do sum of final_amount or # of loans
        $topClients = DB::table('user_loans')
            ->join('clients', 'clients.id', '=', 'user_loans.client_id')
            ->select('clients.name as client_name', DB::raw('SUM(user_loans.amount) as sum_principal'), DB::raw('COUNT(user_loans.id) as loan_count'))
            ->groupBy('clients.name')
            ->orderByDesc('sum_principal')
            ->take(5)
            ->get();

        return [
            'high_risk_loans' => $highRisk,
            'top_agents'       => $topAgents,
            'top_clients'      => $topClients,
        ];
    }

    /**
     * Generate chart data: status distribution, aging distribution, top-agents bar chart, etc.
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

        // Example: top-agents chart data
        $topAgentsData = DB::table('user_loans')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->where('status', 1)
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Prepare something like ["Agent Name"] => total
        // You can query their actual names if you prefer
        $agentLabels = [];
        $agentValues = [];
        foreach ($topAgentsData as $agent) {
            $user = User::find($agent->user_id);
            $agentLabels[] = $user ? ($user->f_name . ' ' . $user->l_name) : 'Agent#'.$agent->user_id;
            $agentValues[] = $agent->total;
        }

        return [
            'statusData'   => $statusDistribution,
            'agingBuckets' => $agingBuckets,
            'topAgents'    => [
                'labels' => $agentLabels,
                'data'   => $agentValues,
            ],
        ];
    }

    // Additional housekeeping methods, e.g., removeOrphanedLoans, etc., can go here
}
