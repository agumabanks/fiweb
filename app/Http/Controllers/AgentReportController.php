<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Agent model
use App\Models\Expense;
use App\Models\LoanPayment;
use Carbon\Carbon;
use PDF;

class AgentReportController extends Controller
{
    /**
     * Display the enhanced report of a specific agent for a given period.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $agentId
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function index(Request $request, $agentId)
    {
        // Get the period and custom dates from the request
        $period = $request->input('period', 'daily'); // default to 'daily'

        // Fetch the start and end dates based on the selected period or custom dates
        if ($period === 'custom') {
            $startDateInput = $request->input('start_date');
            $endDateInput = $request->input('end_date');

            if (!$startDateInput || !$endDateInput) {
                // If custom period is selected but dates are missing, default to today
                $startDateInput = Carbon::now()->format('Y-m-d');
                $endDateInput = Carbon::now()->format('Y-m-d');
            }

            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
        } else {
            [$startDate, $endDate] = $this->getDateRange($period);
            $startDateInput = $startDate->format('Y-m-d');
            $endDateInput = $endDate->format('Y-m-d');
        }

        // Prepare data using a helper method
        $data = $this->prepareReportData($agentId, $startDate, $endDate, $period, $startDateInput, $endDateInput);

        // Pass data to the view
        return view('admin-views.agent.agent-report', $data);
    }

    /**
     * Download the agent report as a PDF.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $agentId
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(Request $request, $agentId) 
    {
        try {
            // Get the period and dates from the request
            $period = $request->input('period', 'daily');
            $startDateInput = $request->input('start_date');
            $endDateInput = $request->input('end_date');

            if ($period === 'custom' && (!$startDateInput || !$endDateInput)) {
                $startDateInput = Carbon::now()->format('Y-m-d');
                $endDateInput = Carbon::now()->format('Y-m-d');
            }

            $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : null;
            $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : null;

            // Prepare data
            $data = $this->prepareReportData($agentId, $startDate, $endDate, $period, $startDateInput, $endDateInput);

            // Generate the PDF
            $pdf = PDF::loadView('admin-views.agent.agent-report-pdf', $data);
            return $pdf->download("agent_report_" . now()->format('Y-m-d') . '.pdf'); 
        } catch (\Exception $e) {
            // Log the error
            \Log::error('PDF Generation Error: ' . $e->getMessage());

            // Display the exception message for debugging
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Prepare the report data to be used in both the web view and PDF.
     *
     * @param int $agentId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param string $period
     * @param string $startDateInput
     * @param string $endDateInput
     * @return array
     */
    private function prepareReportData($agentId, $startDate, $endDate, $period, $startDateInput, $endDateInput)
    {
        // Fetch the agent's details, including clients and their loans
        $agent = User::with(['clients.loans'])->findOrFail($agentId);

        // Fetch the agent's expenses within the given timeframe
        $agentExpenses = Expense::where('user_id', $agentId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Calculate total expenses
        $totalAgentExpenses = $agentExpenses->sum('amount');

        // Initialize variables for financial data and performance metrics
        $totalClients = 0;
        $totalClientsPaid = 0;
        $totalClientsUnpaid = 0;
        $totalClientsAdvancePaid = 0;
        $totalClientsPartialPaid = 0;
        $totalLoanAmount = 0; // Sum of running loans' final amounts
        $totalCollected = 0;
        $totalOutstandingAmount = 0;
        $totalExpectedPayments = 0;
        $interestLostOnArrears = 0;
        $clientDetails = [];

        // Collect all loan IDs for the agent's clients
        $loanIds = $agent->clients->pluck('loans')->flatten()->pluck('id')->unique();

        // Fetch all payments made to those loans within the date range
        $payments = LoanPayment::whereIn('loan_id', $loanIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Group payments by loan_id for efficient access
        $paymentsByLoan = $payments->groupBy('loan_id');

        // Fetch all loans disbursed within the date range
        $loansDisbursed = $agent->clients->pluck('loans')->flatten()->filter(function ($loan) use ($startDate, $endDate) {
            $loanDisbursedAt = Carbon::parse($loan->disbursed_at);
            return $loanDisbursedAt->between($startDate, $endDate);
        });

        foreach ($agent->clients as $client) {
            $totalClients++;
            $clientLoanAmount = 0;
            $clientExpectedPayment = 0;
            $clientTotalPaid = 0;
            $clientOutstandingAmount = 0;

            foreach ($client->loans as $loan) {
                // Parse loan dates
                $loanDisbursedAt = Carbon::parse($loan->disbursed_at);
                $loanDueDate = Carbon::parse($loan->due_date);

                // Check if the loan is running
                if ($loan->status == 1) {
                    // Sum the running loan's final amount
                    $clientLoanAmount += $loan->final_amount;
                    $totalLoanAmount += $loan->final_amount;

                    // Calculate expected payments within the date range
                    $expectedStartDate = $startDate->copy()->max($loanDisbursedAt);
                    $expectedEndDate = $endDate->copy()->min($loanDueDate);

                    if ($expectedStartDate->lte($expectedEndDate)) {
                        $numberOfInstallments = $expectedStartDate->diffInDays($expectedEndDate) + 1;

                        $expectedPayment = $loan->per_installment * $numberOfInstallments;

                        $clientExpectedPayment += $expectedPayment;
                        $totalExpectedPayments += $expectedPayment;
                    }

                    // Get payments made to this loan within the date range
                    $loanPayments = $paymentsByLoan->get($loan->id, collect());
                    $totalPaid = $loanPayments->sum('amount');
                    $clientTotalPaid += $totalPaid;
                    $totalCollected += $totalPaid;

                    // Calculate outstanding amount
                    $outstandingAmount = $loan->final_amount - $loan->paid_amount;
                    $clientOutstandingAmount += $outstandingAmount;
                    $totalOutstandingAmount += $outstandingAmount;
                }
            }

            // Determine payment status
            $paymentStatus = $this->determinePaymentStatus($clientTotalPaid, $clientExpectedPayment);

            $clientDetails[] = [
                'name' => $client->name,
                'loan_amount' => $clientLoanAmount,
                'expected_payment' => $clientExpectedPayment,
                'amount_collected' => $clientTotalPaid,
                'payment_status' => $paymentStatus,
                'outstanding_amount' => $clientOutstandingAmount,
            ];

            // Update client payment status counts
            switch ($paymentStatus) {
                case 'Paid':
                    $totalClientsPaid++;
                    break;
                case 'Unpaid':
                    $totalClientsUnpaid++;
                    break;
                case 'Advance Paid':
                    $totalClientsAdvancePaid++;
                    break;
                case 'Partial Payment':
                    $totalClientsPartialPaid++;
                    break;
            }
        }

        // Calculate revenue from loan processing fees and interest collected within the date range
        $totalLoanProcessingFees = $loansDisbursed->sum('processing_fee');
        $totalInterestIncome = $payments->sum('interest');

        $totalRevenue = $totalLoanProcessingFees + $totalInterestIncome;

        // **Calculate Cash Outs (Loans Disbursed) within the date range**
        $totalCashOut = $loansDisbursed->sum('amount'); // Assuming 'amount' is the principal loan amount

        // **Calculate Return on Investment (ROI)**
        // ROI = (Total Revenue - Total Expenses) / Total Cash Out * 100%
        // Ensure that Total Cash Out is not zero to avoid division by zero
        if ($totalCashOut > 0) {
            $roiPercentage = (($totalRevenue - $totalAgentExpenses) / $totalCashOut) * 100;
            $arrears = $totalExpectedPayments - $totalCollected;
            $interestLostOnArrears = $arrears * 0.2; // Assuming 20% interest rate
        } else {
            $roiPercentage = null; // Or set to zero or N/A as appropriate
            $arrears = null;
        }

        // Net Profit/Loss calculation
        $netProfitLoss = $totalRevenue - $totalAgentExpenses;

        // Collection efficiency calculation
        $collectionEfficiency = $totalExpectedPayments > 0 ? ($totalCollected / $totalExpectedPayments) * 100 : 0;

        // Prepare report date range string
        if ($startDate->isSameDay($endDate)) {
            $reportDateRange = $startDate->format('d M Y');
        } else {
            $reportDateRange = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
        }

        // Return all data as an array
        return [
            'agent' => $agent,
            'totalClients' => $totalClients,
            'totalLoanAmount' => $totalLoanAmount,
            'totalCollected' => $totalCollected,
            'totalOutstandingAmount' => $totalOutstandingAmount,
            'totalExpectedPayments' => $totalExpectedPayments,
            'totalClientsPaid' => $totalClientsPaid,
            'totalClientsUnpaid' => $totalClientsUnpaid,
            'totalClientsAdvancePaid' => $totalClientsAdvancePaid,
            'totalClientsPartialPaid' => $totalClientsPartialPaid,
            'totalRevenue' => $totalRevenue,
            'netProfitLoss' => $netProfitLoss,
            'collectionEfficiency' => $collectionEfficiency,
            'roiPercentage' => $roiPercentage,
            'clientDetails' => $clientDetails,
            'agentExpenses' => $agentExpenses,
            'totalAgentExpenses' => $totalAgentExpenses,
            'totalLoanProcessingFees' => $totalLoanProcessingFees,
            'totalInterestIncome' => $totalInterestIncome,
            'totalCashOut' => $totalCashOut,
            'reportDateRange' => $reportDateRange,
            'reportPeriod' => $period,
            'startDateInput' => $startDateInput,
            'endDateInput' => $endDateInput,
            'arrears' => $arrears, 
            'interestLostOnArrears' => $interestLostOnArrears,
        ];
    }

    /**
     * Determine the client's payment status based on the collected amount.
     *
     * @param float $paid
     * @param float $expected
     * @return string
     */
    private function determinePaymentStatus($paid, $expected)
    {
        if ($expected > 0) {
            if ($paid > $expected) {
                return 'Advance Paid';
            } elseif ($paid == $expected) {
                return 'Paid';
            } elseif ($paid > 0 && $paid < $expected) {
                return 'Partial Payment';
            } else {
                return 'Unpaid';
            }
        } else {
            // When expected payment is zero
            if ($paid > 0) {
                return 'Advance Paid';
            } else {
                return 'Unpaid';
            }
        }
    }

    /**
     * Helper function to get date range for daily, weekly, and monthly reports.
     *
     * @param string $period
     * @return array
     */
    private function getDateRange($period)
    {
        switch ($period) {
            case 'weekly':
                return [
                    Carbon::now()->startOfWeek()->startOfDay(),
                    Carbon::now()->endOfWeek()->endOfDay()
                ];
            case 'monthly':
                return [
                    Carbon::now()->startOfMonth()->startOfDay(),
                    Carbon::now()->endOfMonth()->endOfDay()
                ];
            case 'daily':
            default:
                $now = Carbon::now();
                return [
                    $now->copy()->startOfDay(),
                    $now->copy()->endOfDay()
                ];
        }
    }
}
