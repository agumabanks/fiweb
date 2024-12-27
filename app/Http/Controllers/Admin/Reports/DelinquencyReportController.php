<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLoan;
use App\Models\Client;
use App\Models\LoanPaymentInstallment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DelinquencyReportExport;
use Illuminate\Support\Facades\Log;

class DelinquencyReportController extends Controller
{
    /**
     * Display the Delinquency Metrics and Aging Report page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin-views.reports.delinquency_report');
    }

    /**
     * Fetch data for the Delinquency Report based on the provided date range (AJAX).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchData(Request $request): JsonResponse
    {
        // Validate Request
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            $this->logError('Delinquency Report Fetch Validation Failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();

            // Fetch Delinquent Loans
            $loans = UserLoan::with(['client', 'agent'])
                ->whereBetween('due_date', [$startDate, $endDate])
                ->where('status', '<>', 2) // Exclude fully paid loans
                ->get();

            $agingData = $this->calculateAging($loans);
            $trends = $this->calculateTrends($startDate, $endDate);
            $agentPerformance = $this->calculateAgentPerformance($startDate, $endDate);

            $totalLoans = $loans->count();
            $totalDelinquent = $loans->where('status', '<>', 2)->count();
            $loansData = $this->prepareDetailedLoansData($loans);

            return response()->json([
                'agingData'        => $agingData,
                'trends'           => $trends,
                'agentPerformance' => $agentPerformance,
                'summary'          => [
                    'totalLoans'      => $totalLoans,
                    'totalDelinquent' => $totalDelinquent,
                ],
                'loans'            => $loansData,
            ], 200);
        } catch (\Exception $e) {
            $this->logError('Delinquency Report Fetch Error', ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while generating the report. Please try again later.',
            ], 500);
        }
    }

    /**
     * Export the Delinquency Report as PDF.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPDF(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            $this->logError('Delinquency Report PDF Export Validation Failed', ['errors' => $validator->errors()]);
            return redirect()->back()->withErrors($validator->errors());
        }

        try {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();

            $loans = UserLoan::with(['client', 'agent'])
                ->whereBetween('due_date', [$startDate, $endDate])
                ->where('status', '<>', 2)
                ->get();

            $agingData = $this->calculateAging($loans);
            $trends = $this->calculateTrends($startDate, $endDate);
            $agentPerformance = $this->calculateAgentPerformance($startDate, $endDate);

            $totalLoans = $loans->count();
            $totalDelinquent = $loans->where('status', '<>', 2)->count();
            $loansData = $this->prepareDetailedLoansData($loans);

            $data = [
                'agingData'        => $agingData,
                'trends'           => $trends,
                'agentPerformance' => $agentPerformance,
                'summary'          => [
                    'totalLoans'      => $totalLoans,
                    'totalDelinquent' => $totalDelinquent,
                ],
                'loans'            => $loansData,
                'startDate'        => $startDate,
                'endDate'          => $endDate,
            ];

            $pdf = PDF::loadView('admin.reports.delinquency_report_pdf', $data);
            return $pdf->download('delinquency_report_' . now()->format('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            $this->logError('Delinquency Report PDF Export Error', ['exception' => $e]);
            return redirect()->back()->with('error', 'An error occurred while exporting the report. Please try again later.');
        }
    }

    /**
     * Export the Delinquency Report as Excel.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function exportExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            $this->logError('Delinquency Report Excel Export Validation Failed', ['errors' => $validator->errors()]);
            return redirect()->back()->withErrors($validator->errors());
        }

        try {
            $startDate = $request->start_date;
            $endDate   = $request->end_date;

            return Excel::download(new DelinquencyReportExport($startDate, $endDate), 'delinquency_report_' . now()->format('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            $this->logError('Delinquency Report Excel Export Error', ['exception' => $e]);
            return redirect()->back()->with('error', 'An error occurred while exporting the report. Please try again later.');
        }
    }

    /**
     * Calculate aging categories based on delinquent loans.
     *
     * @param \Illuminate\Support\Collection $loans
     * @return array
     */
    private function calculateAging($loans): array
    {
        $agingData = [
            '30-60 Days' => 0,
            '61-90 Days' => 0,
            '91+ Days'   => 0,
        ];

        foreach ($loans as $loan) {
            $daysOverdue = $this->calculateDaysOverdue($loan->due_date);
            if ($daysOverdue > 0 && $daysOverdue <= 60) {
                $agingData['30-60 Days']++;
            } elseif ($daysOverdue > 60 && $daysOverdue <= 90) {
                $agingData['61-90 Days']++;
            } elseif ($daysOverdue > 90) {
                $agingData['91+ Days']++;
            }
        }

        return $agingData;
    }

    /**
     * Calculate delinquency trends (monthly).
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    private function calculateTrends(Carbon $startDate, Carbon $endDate)
    {
        return UserLoan::whereBetween('due_date', [$startDate, $endDate])
            ->where('status', '<>', 2)
            ->selectRaw('YEAR(due_date) as year, MONTH(due_date) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                    'count' => $item->count,
                ];
            });
    }

    /**
     * Calculate agent performance metrics.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    private function calculateAgentPerformance(Carbon $startDate, Carbon $endDate)
    {
        return UserLoan::whereBetween('due_date', [$startDate, $endDate])
            ->where('status', '<>', 2)
            ->selectRaw('user_id, COUNT(*) as total, SUM(CASE WHEN status <> 2 THEN 1 ELSE 0 END) as delinquent')
            ->groupBy('user_id')
            ->with('agent')
            ->get()
            ->map(function ($item) {
                $agentName = $item->agent ? ($item->agent->f_name . ' ' . $item->agent->l_name) : 'N/A';
                return [
                    'agent'      => $agentName,
                    'delinquent' => $item->delinquent,
                    'total'      => $item->total,
                    'rate'       => $item->total > 0 ? round(($item->delinquent / $item->total) * 100, 2) : 0,
                ];
            });
    }

    /**
     * Prepare detailed loans data for the report.
     *
     * @param \Illuminate\Support\Collection $loans
     * @return \Illuminate\Support\Collection
     */
    private function prepareDetailedLoansData($loans)
    {
        return $loans->map(function ($loan) {
            $clientName = $loan->client ? $loan->client->name : 'N/A';
            $client_phone = $loan->client? $loan->client->phone : 'N/A';
            $agentName = $loan->agent ? ($loan->agent->f_name . ' ' . $loan->agent->l_name) : 'N/A';
            return [
                'client_name'      => $clientName,
                'client_phone'     => $client_phone,
                'loan_amount'      => $loan->amount,
                'due_date'         => $loan->due_date->format('Y-m-d'),
                'days_overdue'     => $this->calculateDaysOverdue($loan->due_date),
                'agent_name'       => $agentName,
                'follow_up_status' => $loan->follow_up_status ?? 'N/A',
            ];
        });
    }

    /**
     * Calculate days overdue.
     *
     * @param string|\Carbon\Carbon $dueDate
     * @return int
     */
    private function calculateDaysOverdue($dueDate): int
    {
        return Carbon::now()->diffInDays(Carbon::parse($dueDate), false);
    }

    /**
     * Log an error with context.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    private function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    /**
     * Generate a unique account number for the savings account.
     *
     * @return string
     */
    private function generateUniqueAccountNumber(): string
    {
        do {
            $accountNumber = 'SANV' . strtoupper(Str::random(10));
        } while (UserLoan::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }
}
