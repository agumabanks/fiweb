<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\EMoney;
use App\Models\LoanPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLoan;
use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * DashboardController constructor.
     *
     * @param User        $user
     * @param EMoney      $eMoney
     * @param Transaction $transaction
     */
    public function __construct(
        private User $user,
        private EMoney $eMoney,
        private Transaction $transaction
    ) {
        // Initialization if needed
    }

    /**
     * Display the settings page.
     *
     * @return View|Factory|Application
     */
    public function settings(): View|Factory|Application
    {
        return view('admin-views.settings');
    }

    /**
     * Update admin settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'phone'  => 'required|string|max:20',
            'email'  => 'nullable|email|max:255',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $admin = $this->user->find(Auth::id());
        if (!$admin) {
            Toastr::error('Admin not found!');
            return back();
        }

        $admin->f_name = $request->f_name;
        $admin->l_name = $request->l_name;
        $admin->email = $request->email ?? $admin->email;
        $admin->phone = $request->phone;

        if ($request->hasFile('image')) {
            $admin->image = Helpers::update('admin/', $admin->image, 'png', $request->file('image'));
        }

        $admin->save();

        Toastr::success('Admin updated successfully!');
        return back();
    }

    /**
     * Display the dashboard with key metrics.
     *
     * @return View
     */
    public function dashboard(): View
    {
        // Key Metrics
        $totalLoansDisbursed = UserLoan::sum('amount'); // Total loans disbursed
        $totalRepayments = LoanPayment::sum('amount'); // Total repayments

        $totalOverdueLoans = UserLoan::where('status', '<>', 2) // Assuming status 2 = paid
            ->where('due_date', '<', now())
            ->sum('final_amount'); // Total overdue loan amount

        // New clients registered today
        $newClientsToday = Client::whereDate('created_at', now())->count();

        // Balance calculations (Example calculations)
        $usedBalance = UserLoan::sum('amount'); // Total amount of loans disbursed
        $unusedBalance = LoanPayment::sum('amount'); // Total repayments received
        $totalEarned = $usedBalance * 0.05; // Assuming 5% interest earned on used balance

        // Balance Data
        $balance = [
            'total_balance'   => $usedBalance + $unusedBalance,
            'used_balance'    => $usedBalance,
            'unused_balance'  => $unusedBalance,
            'total_earned'    => $totalEarned,
        ];

        // Performance Data
        $monthlyLoanData = $this->getMonthlyLoanData();
        $topAgents = $this->getTopPerformingAgents();
        $topCustomers = $this->getTopCustomers();
        $topTransactions = $this->getTopTransactions();

        // Agent Performance & Loan Collection Analytics
        $agentLoanCollections = $this->getAgentLoanCollections();

        // Delinquency Analysis (Risk and Loan Aging)
        $loanAging = $this->getLoanAging();

        // Pass all data to the view
        return view('admin-views.dashboard', compact(
            'balance',
            'totalLoansDisbursed',
            'totalRepayments',
            'totalOverdueLoans',
            'newClientsToday',
            'monthlyLoanData',
            'topAgents',
            'topCustomers',
            'topTransactions',
            'agentLoanCollections',
            'loanAging'
        ));
    }

    /**
     * Get monthly loan disbursement and repayment data.
     *
     * @return array
     */
    private function getMonthlyLoanData(): array
    {
        $loanData = [
            'disbursed' => [],
            'repaid'    => [],
        ];

        $currentYear = now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $loanData['disbursed'][] = UserLoan::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->sum('amount');

            $loanData['repaid'][] = LoanPayment::whereYear('payment_date', $currentYear)
                ->whereMonth('payment_date', $month)
                ->sum('amount');
        }

        return $loanData;
    }

    /**
     * Get top performing agents based on total loan disbursed.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getTopPerformingAgents()
    {
        return User::select('users.id', 'users.f_name', 'users.l_name', DB::raw('SUM(user_loans.amount) as total_disbursed'))
            ->join('user_loans', 'users.id', '=', 'user_loans.user_id')
            ->groupBy('users.id', 'users.f_name', 'users.l_name')
            ->orderByDesc('total_disbursed')
            ->take(5)
            ->get();
    }

    /**
     * Get top customers based on total repayments.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getTopCustomers()
    {
        return Client::select('clients.id', 'clients.name', DB::raw('SUM(loan_payments.amount) as total_repaid'))
            ->join('loan_payments', 'clients.id', '=', 'loan_payments.client_id')
            ->groupBy('clients.id', 'clients.name')
            ->orderByDesc('total_repaid')
            ->take(5)
            ->get();
    }

    /**
     * Get top loan payments based on amount paid.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTopTransactions()
    {
        return LoanPayment::with('client')
        ->orderByDesc('payment_date')  // Order by payment date, newest first
        ->take(10)  // Increased from 5 to 10 to show more recent transactions
        ->get();
    }

    /**
     * Get agent loan collection performance.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getAgentLoanCollections()
    {
        return User::select(
                'users.id',
                'users.f_name',
                'users.l_name',
                DB::raw('SUM(user_loans.amount) as total_loan_amount'),
                DB::raw('SUM(user_loans.per_installment) as expected_daily_collection'),
                DB::raw('SUM(user_loans.paid_amount) as total_collected')
            )
            ->join('user_loans', 'users.id', '=', 'user_loans.user_id')
            ->groupBy('users.id', 'users.f_name', 'users.l_name')
            ->get();
    }

    /**
     * Get loan aging data for delinquency analysis.
     *
     * @return array
     */
    private function getLoanAging(): array
    {
        // Count loans overdue by 30 days (between 30-59 days)
        $thirtyDays = UserLoan::where('status', 'overdue')
            ->where('due_date', '<', now()->subDays(30))
            ->where('due_date', '>=', now()->subDays(60))
            ->count();
            
        // Count loans overdue by 60 days (between 60-89 days)
        $sixtyDays = UserLoan::where('status', 'overdue')
            ->where('due_date', '<', now()->subDays(60))
            ->where('due_date', '>=', now()->subDays(90))
            ->count();
            
        // Count loans overdue by 90+ days
        $ninetyDays = UserLoan::where('status', 'overdue')
            ->where('due_date', '<', now()->subDays(90))
            ->count();
            
        // Calculate total overdue percentage
        $totalLoans = UserLoan::count();
        $totalOverdue = $thirtyDays + $sixtyDays + $ninetyDays;
        $overduePercentage = $totalLoans > 0 ? round(($totalOverdue / $totalLoans) * 100, 1) : 0;
            
        return [
            '30_days' => $thirtyDays,
            '60_days' => $sixtyDays,
            '90_days' => $ninetyDays,
            'total_overdue' => $totalOverdue,
            'total_loans' => $totalLoans,
            'overdue_percentage' => $overduePercentage
        ];
    }
}
