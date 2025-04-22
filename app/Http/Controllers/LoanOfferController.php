<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanOffer;
use App\Models\LoanPlan;
use App\Models\UserLoan;
use App\Models\ClientFine;

use App\Models\LoanPayment;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;

use App\Models\ClientCollateral;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;

use App\Models\User;
use App\Models\Client;
use App\Models\AgentLoan;
use App\Models\Guarantor;

use App\Models\PartialLoanDisbursement;

use App\Models\PaymentTransaction;

use App\CentralLogics\Helpers;
use App\Models\LoanPaymentInstallment;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Http\Controllers\Auth;

class LoanOfferController extends Controller
{
    
 

    public function payPartialLoanPage(Request $request, $loanId)
    {
        $loan = UserLoan::findOrFail($loanId);
        $client = Client::findOrFail($loan->client_id);
        $clientGuarantors = Guarantor::where('client_id', $loan->client_id)->get();


        // Return the Blade view, passing agents for the dropdown.
        return view('admin-views.Loans.partialLoanApprov', compact('loan','client','clientGuarantors'));
    }

 


public function partialA(Request $request)
    {
        $loan = UserLoan::findOrFail($request->id);
        $client = Client::findOrFail($loan->client_id);
        $clientGuarantors = Guarantor::where('client_id', $loan->client_id)->get();

        // Loan must be status=0 (pending) or 4 (partially disbursed)
        if ($loan->status != 0 && $loan->status != 4) {
            Toastr::error('Loan cannot be disbursed in its current state.');
            return back();
        }

        // If client must have guarantors
        // if ($clientGuarantors->isEmpty()) {
        //     Toastr::error('Client has no guarantors.');
        //     return back();
        // }

        $partialDisbursementAmount = $request->input('partial_disbursement_amount');
        if ($partialDisbursementAmount <= 0) {
            Log::error('Invalid Partial Disbursement Amount: ' . $partialDisbursementAmount);
            Toastr::error('Invalid partial disbursement amount.');
            return back();
        }

        $newTotalDisbursed = $loan->partial_disbursement_amount + $partialDisbursementAmount;
        if ($newTotalDisbursed > $loan->amount) {
            Toastr::error('Total disbursed amount cannot exceed loan amount.');
            return back();
        }

        DB::beginTransaction();
        try {
            // Record partial disbursement in partial_loan_disbursements if you want
            PartialLoanDisbursement::create([
                'user_loan_id' => $loan->id,
                'amount'       => $partialDisbursementAmount,
                'disbursed_at' => now(),
            ]);

            $loan->partial_disbursement_amount = $newTotalDisbursed;
            if ($newTotalDisbursed == $loan->amount) {
                // Fully disbursed
                $loan->status = 1;
                $loan->disbursed_at = Carbon::now();
                $loan->loan_taken_date = Carbon::now();
                $loanTakenDate = Carbon::now();

                $loan->due_date = $loanTakenDate->copy()->addDays($loan->installment_interval * $loan->total_installment);
                $loan->next_installment_date = $loanTakenDate->copy()->addDays($loan->installment_interval);
                $loan->save();

                // Update client balance
                $client->credit_balance = $loan->final_amount;
                $client->save();

                $this->createPaymentInstallments($loan);
                $message = 'Loan fully disbursed and is now running.';

            } else {
                // Partially disbursed
                $loan->is_partial_disbursement = 1;
                $loan->save();

                Log::info('Client Credit Balance Before Update: ' . $client->credit_balance);
                $client->credit_balance += $partialDisbursementAmount;
                $client->save();
                Log::info('Client Credit Balance After Update: ' . $client->credit_balance);

                $message = 'Partial disbursement processed successfully.';
            }

            DB::commit();
            Toastr::success($message);
            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Partial disbursement error: ' . $e->getMessage());
            Toastr::error('An error occurred during partial disbursement.');
            return back();
        }
    }



    public function removeOrphanedLoans()
        {
            // 1) Find all loans that do NOT have a matching client
            $orphanedLoans = UserLoan::whereDoesntHave('client')->get();

            // 2) Delete them
            foreach ($orphanedLoans as $loan) {
                // Optionally delete installments, payments, etc. first
                // e.g. $loan->installments()->delete();

                // Then delete the loan
                $loan->delete();
                $loan->loanPaymentInstallments()->delete();
                // $loan->loanPayments()->delete();

            }

            return back()->with('success', 'Successfully removed orphaned loans with missing clients.');
        }



        public function loanArrearsDataWWW(Request $request)
        {
            $currentHour = Carbon::now()->hour;
            $today = Carbon::today();
        
            // Base query for running loans with due_date after today
            $query = DB::table('user_loans')
                ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
                ->leftJoin('loan_payment_installments', function ($join) use ($today) {
                    $join->on('loan_payment_installments.loan_id', '=', 'user_loans.id')
                         ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                         ->where('loan_payment_installments.date', '<', $today);
                } )
                ->where('user_loans.due_date', '>=', $today) // Due date after today
                ->where('user_loans.status', 1) // Running loans only
               
                ->select(
                    'clients.id as client_id',
                    'clients.name as client_name',
                    'clients.phone as client_phone',
                    'clients.credit_balance as client_balance',
                    'user_loans.id as loan_id',
                    'user_loans.loan_taken_date',
                    'user_loans.due_date',
                    DB::raw('COALESCE(COUNT(loan_payment_installments.id), 0) as total_overdue_installments'),
                    DB::raw('COALESCE(SUM(loan_payment_installments.install_amount), 0) as total_overdue_amount')
                )
                ->groupBy('user_loans.id', 'clients.id', 'clients.name', 'clients.phone', 'clients.credit_balance', 'user_loans.loan_taken_date', 'user_loans.due_date');
        
            // Time-based logic
            if ($currentHour >= 8) {
                $query->having('total_overdue_installments', '>', 0);
            }
        
            // Filters
            if ($request->filled('agent_id') && $request->agent_id !== 'all') {
                $query->where('user_loans.user_id', $request->agent_id);
            }
        
            if ($searchValue = $request->input('search.value')) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('clients.name', 'like', "%{$searchValue}%")
                      ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                      ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
                });
            }
        
            if ($request->filled('overdue_months')) {
                $months = (int) $request->overdue_months;
                $cutoffDate = Carbon::now()->subMonths($months);
                $query->havingRaw('MIN(loan_payment_installments.date) <= ?', [$cutoffDate]);
            }
        
            // Pagination and ordering
            $totalRecords = $query->get()->count();
            $orderColumn = $request->input('columns.' . $request->input('order.0.column', 0) . '.data', 'client_name');
            $orderDir = $request->input('order.0.dir', 'asc');
            $data = $query->skip($request->input('start', 0))
                          ->take($request->input('length', 20))
                          ->orderBy($orderColumn, $orderDir)
                          ->get();
        
            // Summary
            $allResults = $query->get();
            $summary = [
                'client_count' => $allResults->unique('client_id')->count(),
                'total_overdue_amount' => $allResults->sum('total_overdue_amount'),
            ];
        
            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
                'summary' => $summary,
            ]);
        }
        
        public function getMissedInstallmentsWWW($loanId)
        {
            $installments = DB::table('loan_payment_installments')
                ->where('loan_id', $loanId)
                ->whereIn('status', ['pending', 'withbalance'])
                ->where('date', '<', Carbon::today())
                ->select('date', 'install_amount', 'status')
                ->get();
        
            return response()->json(['installments' => $installments]);
        }



/**
 * Get data for loan arrears DataTable
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
/**
 * Get data for loan arrears DataTable
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function loanArrearsDataBx(Request $request)
{
    try {
        $today = \Carbon\Carbon::today();
        
        // First, build our base query for running loans
        $baseQuery = \DB::table('user_loans')
            ->join('clients', 'clients.id', '=', 'user_loans.client_id')
            ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
            ->where('user_loans.status', 1); // Only running loans
        
        // Get the total count of all running loans
        $runningLoansCount = (clone $baseQuery)->count();
        
        // Next, find loans with overdue installments
        $query = $baseQuery
            ->select([
                'user_loans.id',
                'user_loans.trx',
                'user_loans.amount',
                'user_loans.final_amount',
                'user_loans.paid_amount',
                'user_loans.per_installment',
                'user_loans.loan_taken_date',
                'user_loans.due_date',
                'user_loans.next_installment_date',
                'clients.id as client_id',
                'clients.name as client_name',
                'clients.phone as client_phone',
                'clients.credit_balance as client_balance',
                'agents.id as agent_id',
                \DB::raw('CONCAT(agents.f_name, " ", agents.l_name) as agent_name')
            ]);
        
        // Apply search filter if provided
        if ($searchValue = $request->input('search.value')) {
            $query->where(function($q) use ($searchValue) {
                $q->where('clients.name', 'like', "%{$searchValue}%")
                  ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                  ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
            });
        }
        
        // Apply agent filter if selected
        if ($request->filled('agent_id') && $request->agent_id !== 'all') {
            $query->where('user_loans.user_id', $request->agent_id);
        }
        
        // Get IDs of loans that match our filters
        $filteredLoanIds = $query->pluck('user_loans.id');
        $recordsFiltered = count($filteredLoanIds);
        
        if ($filteredLoanIds->isNotEmpty()) {
            $loansWithInstallments = \DB::table('user_loans')
                ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
                ->leftJoin('loan_payment_installments', function($join) use ($today) {
                    $join->on('loan_payment_installments.loan_id', '=', 'user_loans.id')
                         ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                         ->where('loan_payment_installments.date', '<', $today);
                })
                ->whereIn('user_loans.id', $filteredLoanIds)
                ->select([
                    'user_loans.id as loan_id',
                    'user_loans.trx as loan_trx',
                    'user_loans.amount as loan_amount',
                    'user_loans.final_amount as loan_final_amount',
                    'user_loans.paid_amount as loan_paid_amount',
                    'user_loans.per_installment',
                    'user_loans.loan_taken_date',
                    'user_loans.due_date',
                    'user_loans.next_installment_date',
                    'clients.id as client_id',
                    'clients.name as client_name',
                    'clients.phone as client_phone',
                    'clients.credit_balance as client_balance',
                    'agents.id as agent_id',
                    \DB::raw('CONCAT(agents.f_name, " ", agents.l_name) as agent_name'),
                    \DB::raw('COUNT(loan_payment_installments.id) as total_overdue_installments'),
                    \DB::raw('SUM(loan_payment_installments.install_amount) as total_overdue_amount'),
                    \DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
                ])
                ->groupBy(
                    'user_loans.id', 'user_loans.trx', 'user_loans.amount', 
                    'user_loans.final_amount', 'user_loans.paid_amount', 
                    'user_loans.per_installment', 'user_loans.loan_taken_date', 
                    'user_loans.due_date', 'user_loans.next_installment_date', 
                    'clients.id', 'clients.name', 'clients.phone', 
                    'clients.credit_balance', 'agents.id',
                    'agents.f_name', 'agents.l_name'
                )
                ->having('total_overdue_installments', '>', 0);
            
            // Apply overdue period filter - show loans with arrears within the specified period
            if ($request->filled('overdue_days') && (int)$request->overdue_days > 0) {
                $days = (int)$request->overdue_days;
                $startDate = $today->copy()->subDays($days);
                
                // Find loans with installments that became overdue within this period
                $loansWithInstallments->where(function($q) use ($today, $startDate) {
                    $q->whereExists(function($subQ) use ($today, $startDate) {
                        $subQ->select(\DB::raw(1))
                            ->from('loan_payment_installments')
                            ->whereColumn('loan_payment_installments.loan_id', 'user_loans.id')
                            ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                            ->where('loan_payment_installments.date', '<', $today)
                            ->where('loan_payment_installments.date', '>=', $startDate);
                    });
                });
            }
            
            // Apply sorting
            $sortField = $request->input('sort_field', 'total_overdue_amount');
            $sortDirection = $request->input('sort_direction', 'desc');
            
            switch($sortField) {
                case 'progress':
                    $loansWithInstallments->orderByRaw('(loan_paid_amount/loan_final_amount) ' . $sortDirection);
                    break;
                case 'days_overdue':
                    $loansWithInstallments->orderByRaw('DATEDIFF(?, earliest_overdue_date) ' . $sortDirection, [$today]);
                    break;
                case 'missed_installments':
                    $loansWithInstallments->orderBy('total_overdue_installments', $sortDirection);
                    break;
                default:
                    $loansWithInstallments->orderBy('total_overdue_amount', $sortDirection);
            }
            
            // Apply pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $loans = $loansWithInstallments->skip($start)->take($length)->get();
            
            // Calculate additional fields for each loan
            foreach ($loans as $loan) {
                // Calculate days overdue
                if ($loan->earliest_overdue_date) {
                    $earliestDate = \Carbon\Carbon::parse($loan->earliest_overdue_date);
                    $loan->days_overdue = $earliestDate->diffInDays($today);
                } else {
                    $loan->days_overdue = 0;
                }
                
                // Calculate progress percentage
                $loan->progress_percentage = $loan->loan_final_amount > 0 
                    ? round(($loan->loan_paid_amount / $loan->loan_final_amount) * 100, 1) 
                    : 0;
                
                // Calculate days remaining on loan
                $dueDate = \Carbon\Carbon::parse($loan->due_date);
                $daysToDue = max(0, $today->diffInDays($dueDate, false)); // Negative if past due
                $totalLoanDays = \Carbon\Carbon::parse($loan->loan_taken_date)->diffInDays($dueDate);
                $timeRemainingPercent = $totalLoanDays > 0 ? ($daysToDue / $totalLoanDays) * 100 : 0;
                
                // Determine priority level based on the amount paid and time remaining
                // High priority: Less than 30% time remaining but less than 70% paid
                // Medium priority: Less than 50% time remaining but less than 80% paid
                // Low priority: Other cases
                if ($timeRemainingPercent < 30 && $loan->progress_percentage < 70) {
                    $loan->priority = 'high';
                } else if ($timeRemainingPercent < 50 && $loan->progress_percentage < 80) {
                    $loan->priority = 'medium';
                } else {
                    $loan->priority = 'low';
                }
                
                // Also consider overdue amount in priority calculation
                if ($loan->total_overdue_amount > 500000 || $loan->days_overdue > 30) {
                    $loan->priority = 'high';
                } else if ($loan->total_overdue_amount > 200000 || $loan->days_overdue > 14) {
                    $loan->priority = 'medium';
                }
            }
            
            // Calculate summary statistics
            $summary = [
                'client_count' => $loans->unique('client_id')->count(),
                'total_overdue_amount' => $loans->sum('total_overdue_amount'),
                'average_days_overdue' => $loans->avg('days_overdue') ? round($loans->avg('days_overdue'), 1) : 0,
                'high_priority_count' => $loans->where('priority', 'high')->count(),
                'medium_priority_count' => $loans->where('priority', 'medium')->count(),
                'low_priority_count' => $loans->where('priority', 'low')->count(),
            ];
        } else {
            $loans = collect();
            $summary = [
                'client_count' => 0,
                'total_overdue_amount' => 0,
                'average_days_overdue' => 0,
                'high_priority_count' => 0,
                'medium_priority_count' => 0,
                'low_priority_count' => 0,
            ];
        }
        
        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => $runningLoansCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $loans,
            'summary' => $summary
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Loan Arrears Data Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'An error occurred while loading data: ' . $e->getMessage(),
            'summary' => [
                'client_count' => 0,
                'total_overdue_amount' => 0,
                'average_days_overdue' => 0,
                'high_priority_count' => 0,
                'medium_priority_count' => 0,
                'low_priority_count' => 0,
            ]
        ]);
    }
}
/**
 * Display the loan arrears index page
 * 
 * @return \Illuminate\View\View
 */
public function loanArrearsIndex()
{
    // Fetch all active agents for the filter dropdown
    $agents = \App\Models\User::where('is_active', 1)->get();
    
    // Return the Blade view, passing agents for the dropdown
    return view('admin-views.Loans.arrears.index', compact('agents'));
}





/**
 * Get data for loan arrears DataTable with enhanced filtering and priority calculations
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function loanArrearsData2w(Request $request)
 {
    try {
        $today = Carbon::today();
        
        // First, build our base query for running loans
        $baseQuery = DB::table('user_loans')
            ->join('clients', 'clients.id', '=', 'user_loans.client_id')
            ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
            ->where('user_loans.status', 1)
            ->where('user_loans.due_date', '>', Carbon::today()); // Due date is in the future
             // Only running loans
        
        // Get the total count of all running loans
        $runningLoansCount = (clone $baseQuery)->count();
        
        // Next, find loans with overdue installments
        $query = $baseQuery
            ->select([
                'user_loans.id',
                'user_loans.trx',
                'user_loans.amount',
                'user_loans.final_amount',
                'user_loans.paid_amount',
                'user_loans.per_installment',
                'user_loans.loan_taken_date',
                'user_loans.due_date',
                'user_loans.next_installment_date',
                'user_loans.installment_interval', 
                'user_loans.total_installment',
                'clients.id as client_id',
                'clients.name as client_name',
                'clients.phone as client_phone',
                'clients.credit_balance as client_balance',
                'agents.id as agent_id',
                DB::raw('CONCAT(agents.f_name, " ", agents.l_name) as agent_name')
            ]);
        
        // Apply search filter if provided
        if ($searchValue = $request->input('search.value')) {
            $query->where(function($q) use ($searchValue) {
                $q->where('clients.name', 'like', "%{$searchValue}%")
                  ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                  ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
            });
        }
        
        // Apply agent filter if selected
        if ($request->filled('agent_id') && $request->agent_id !== 'all') {
            $query->where('user_loans.user_id', $request->agent_id);
        }
        
        // Get IDs of loans that match our filters
        $filteredLoanIds = $query->pluck('user_loans.id');
        $recordsFiltered = count($filteredLoanIds);
        
        if ($filteredLoanIds->isNotEmpty()) {
            $loansWithInstallments = DB::table('user_loans')
                ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
                ->leftJoin('loan_payment_installments', function($join) use ($today) {
                    $join->on('loan_payment_installments.loan_id', '=', 'user_loans.id')
                         ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                         ->where('loan_payment_installments.date', '<', $today);
                })
                ->whereIn('user_loans.id', $filteredLoanIds)
                ->select([
                    'user_loans.id as loan_id',
                    'user_loans.trx as loan_trx',
                    'user_loans.amount as loan_amount',
                    'user_loans.final_amount as loan_final_amount',
                    'user_loans.paid_amount as loan_paid_amount',
                    'user_loans.per_installment',
                    'user_loans.loan_taken_date',
                    'user_loans.due_date',
                    'user_loans.next_installment_date',
                    'user_loans.installment_interval',
                    'user_loans.total_installment',
                    'clients.id as client_id',
                    'clients.name as client_name',
                    'clients.phone as client_phone',
                    'clients.credit_balance as client_balance',
                    'agents.id as agent_id',
                    DB::raw('CONCAT(agents.f_name, " ", agents.l_name) as agent_name'),
                    DB::raw('COUNT(loan_payment_installments.id) as total_overdue_installments'),
                    DB::raw('SUM(loan_payment_installments.install_amount) as total_overdue_amount'),
                    DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
                ])
                ->groupBy(
                    'user_loans.id', 'user_loans.trx', 'user_loans.amount', 
                    'user_loans.final_amount', 'user_loans.paid_amount', 
                    'user_loans.per_installment', 'user_loans.loan_taken_date', 
                    'user_loans.due_date', 'user_loans.next_installment_date', 
                    'user_loans.installment_interval', 'user_loans.total_installment',
                    'clients.id', 'clients.name', 'clients.phone', 
                    'clients.credit_balance', 'agents.id',
                    'agents.f_name', 'agents.l_name'
                )
                ->having('total_overdue_installments', '>', 0);
            
            // Apply overdue period filter - show loans with arrears within the specified period
            if ($request->filled('overdue_days') && (int)$request->overdue_days > 0) {
                $days = (int)$request->overdue_days;
                $cutoffDate = $today->copy()->subDays($days);
                
                // Find loans with installments that became overdue within this period
                $loansWithInstallments->where(function($q) use ($today, $cutoffDate) {
                    $q->whereExists(function($subQ) use ($today, $cutoffDate) {
                        $subQ->select(DB::raw(1))
                            ->from('loan_payment_installments')
                            ->whereColumn('loan_payment_installments.loan_id', 'user_loans.id')
                            ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                            ->where('loan_payment_installments.date', '<', $today)
                            ->where('loan_payment_installments.date', '>=', $cutoffDate);
                    });
                });
            }
            
            // Apply sorting
            $sortField = $request->input('sort_field', 'total_overdue_amount');
            $sortDirection = $request->input('sort_direction', 'desc');
            
            switch($sortField) {
                case 'progress':
                    $loansWithInstallments->orderByRaw('(loan_paid_amount/loan_final_amount) ' . $sortDirection);
                    break;
                case 'days_overdue':
                    $loansWithInstallments->orderByRaw('DATEDIFF(?, earliest_overdue_date) ' . $sortDirection, [$today]);
                    break;
                case 'missed_installments':
                    $loansWithInstallments->orderBy('total_overdue_installments', $sortDirection);
                    break;
                default:
                    $loansWithInstallments->orderBy('total_overdue_amount', $sortDirection);
            }
            
            // Apply pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $loans = $loansWithInstallments->skip($start)->take($length)->get();
            
            // Calculate additional fields for each loan
            foreach ($loans as $loan) {
                // Calculate days overdue
                if ($loan->earliest_overdue_date) {
                    $earliestDate = Carbon::parse($loan->earliest_overdue_date);
                    $loan->days_overdue = $earliestDate->diffInDays($today);
                } else {
                    $loan->days_overdue = 0;
                }
                
                // Calculate progress percentage
                $loan->progress_percentage = $loan->loan_final_amount > 0 
                    ? round(($loan->loan_paid_amount / $loan->loan_final_amount) * 100, 1) 
                    : 0;
                
                // Calculate days remaining on loan
                $dueDate = Carbon::parse($loan->due_date);
                $loanTakenDate = Carbon::parse($loan->loan_taken_date);
                $daysToDue = max(0, $today->diffInDays($dueDate, false)); // Negative if past due
                $totalLoanDays = $loanTakenDate->diffInDays($dueDate);
                $timeRemainingPercent = $totalLoanDays > 0 ? ($daysToDue / $totalLoanDays) * 100 : 0;
                
                // Enhanced Priority Calculation:
                // 1. Calculate expected progress based on time elapsed
                $timeElapsedPercent = 100 - $timeRemainingPercent;
                $expectedProgress = $timeElapsedPercent; // Simple linear expectation
                
                // 2. Calculate payment deficit (how far behind expected payment)
                $paymentDeficit = $expectedProgress - $loan->progress_percentage;
                
                // 3. Consider overdue amount relative to loan amount
                $overduePercentage = ($loan->total_overdue_amount / $loan->loan_final_amount) * 100;
                
                // 4. Determine priority level using multiple factors
                if (
                    ($timeRemainingPercent < 20 && $loan->progress_percentage < 70) || // Less than 20% time left but less than 70% paid
                    ($paymentDeficit > 30) || // More than 30% behind expected payment
                    ($overduePercentage > 20) || // More than 20% of loan is overdue
                    ($loan->days_overdue > 30) // Overdue for more than a month
                ) {
                    $loan->priority = 'high';
                } 
                else if (
                    ($timeRemainingPercent < 40 && $loan->progress_percentage < 75) || // Less than 40% time left but less than 75% paid
                    ($paymentDeficit > 15) || // More than 15% behind expected payment
                    ($overduePercentage > 10) || // More than 10% of loan is overdue
                    ($loan->days_overdue > 14) // Overdue for more than two weeks
                ) {
                    $loan->priority = 'medium';
                } 
                else {
                    $loan->priority = 'low';
                }
                
                // Store additional computed values for UI display
                $loan->time_remaining_percent = round($timeRemainingPercent, 1);
                $loan->expected_progress = round($expectedProgress, 1);
                $loan->payment_deficit = round($paymentDeficit, 1);
                $loan->overdue_percentage = round($overduePercentage, 1);
            }
            
            // Calculate summary statistics
            $summary = [
                'client_count' => $loans->unique('client_id')->count(),
                'total_overdue_amount' => $loans->sum('total_overdue_amount'),
                'average_days_overdue' => $loans->avg('days_overdue') ? round($loans->avg('days_overdue'), 1) : 0,
                'high_priority_count' => $loans->where('priority', 'high')->count(),
                'medium_priority_count' => $loans->where('priority', 'medium')->count(),
                'low_priority_count' => $loans->where('priority', 'low')->count(),
                'total_loan_value' => $loans->sum('loan_final_amount'),
                'total_paid_value' => $loans->sum('loan_paid_amount'),
            ];
        } else {
            $loans = collect();
            $summary = [
                'client_count' => 0,
                'total_overdue_amount' => 0,
                'average_days_overdue' => 0,
                'high_priority_count' => 0,
                'medium_priority_count' => 0,
                'low_priority_count' => 0,
                'total_loan_value' => 0,
                'total_paid_value' => 0,
            ];
        }
        
        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => $runningLoansCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $loans,
            'summary' => $summary
        ]);
        
    } catch (\Exception $e) {
        Log::error('Loan Arrears Data Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'An error occurred while loading data: ' . $e->getMessage(),
            'summary' => [
                'client_count' => 0,
                'total_overdue_amount' => 0,
                'average_days_overdue' => 0,
                'high_priority_count' => 0,
                'medium_priority_count' => 0,
                'low_priority_count' => 0,
                'total_loan_value' => 0,
                'total_paid_value' => 0,
            ]
        ]);
    }
}




/**
 * Get data for loan arrears DataTable with enhanced filtering and performance
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
/**
 * Get data for loan arrears DataTable with proper column referencing
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
/**
 * Get data for loan arrears DataTable with proper filtering and payment sync
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function loanArrearsData(Request $request)
{
    try {
        $today = Carbon::today();
        
        // STEP 1: Get all running loans with future due dates
        $runningLoansQuery = DB::table('user_loans')
            ->where('status', 1) // Only running loans
            ->where('due_date', '>', $today); // Due date is in the future
            
        // Store the count for pagination info
        $runningLoansCount = $runningLoansQuery->count();
        
        // Get all running loans with their loan_taken_date
        $runningLoans = $runningLoansQuery
            ->select('user_loans.id', 'user_loans.loan_taken_date')
            ->get();
        
        // If there are no running loans, return early with empty data
        if ($runningLoans->isEmpty()) {
            return response()->json([
                'draw' => (int)$request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'summary' => [
                    'client_count' => 0,
                    'total_overdue_amount' => 0,
                    'average_days_overdue' => 0,
                    'high_priority_count' => 0,
                    'medium_priority_count' => 0,
                    'low_priority_count' => 0,
                ]
            ]);
        }
        
        // Create a mapping of loan ID to loan taken date
        $loanTakenDates = [];
        $runningLoanIds = [];
        foreach ($runningLoans as $loan) {
            $loanTakenDates[$loan->id] = $loan->loan_taken_date;
            $runningLoanIds[] = $loan->id;
        }
        
        // STEP 2: Get recent payments for all running loans
        $recentPayments = DB::table('loan_payments')
            ->whereIn('loan_id', $runningLoanIds)
            ->select('loan_id', 'payment_date', 'amount')
            ->get();
        
        // Create a lookup of payments by loan and date
        $paymentsByLoan = [];
        foreach ($recentPayments as $payment) {
            if (!isset($paymentsByLoan[$payment->loan_id])) {
                $paymentsByLoan[$payment->loan_id] = [];
            }
            
            $paymentDate = Carbon::parse($payment->payment_date)->format('Y-m-d');
            if (!isset($paymentsByLoan[$payment->loan_id][$paymentDate])) {
                $paymentsByLoan[$payment->loan_id][$paymentDate] = 0;
            }
            
            $paymentsByLoan[$payment->loan_id][$paymentDate] += $payment->amount;
        }
        
        // STEP 3: Get all installments for running loans
        $allInstallments = DB::table('loan_payment_installments')
            ->whereIn('loan_id', $runningLoanIds)
            ->whereIn('status', ['pending', 'withbalance'])
            ->where('date', '<', $today)
            ->select('loan_id', 'date', 'install_amount', 'installment_balance', 'status')
            ->get();
        
        // Filter installments to only those after loan taken date and not paid
        $overdueLoanIds = [];
        $overdueStats = [];
        
        foreach ($allInstallments as $installment) {
            // Skip if before loan taken date
            $loanTakenDate = Carbon::parse($loanTakenDates[$installment->loan_id]);
            $installmentDate = Carbon::parse($installment->date);
            
            if ($installmentDate < $loanTakenDate) {
                continue;
            }
            
            // Skip if the installment has been paid
            $isPaid = false;
            $formatDate = $installmentDate->format('Y-m-d');
            if (isset($paymentsByLoan[$installment->loan_id][$formatDate])) {
                $paymentAmount = $paymentsByLoan[$installment->loan_id][$formatDate];
                if ($paymentAmount >= $installment->install_amount) {
                    $paymentsByLoan[$installment->loan_id][$formatDate] -= $installment->install_amount;
                    $isPaid = true;
                }
            }
            
            // Skip if the installment is marked as withbalance but has zero balance
            if ($installment->status === 'withbalance' && ($installment->installment_balance ?? 0) <= 0) {
                continue;
            }
            
            // Only include unpaid installments
            if (!$isPaid) {
                // Initialize loan data if not exists
                if (!isset($overdueStats[$installment->loan_id])) {
                    $overdueStats[$installment->loan_id] = [
                        'count' => 0,
                        'amount' => 0,
                        'earliest_date' => null
                    ];
                    $overdueLoanIds[] = $installment->loan_id;
                }
                
                // Increment count and add to amount
                $overdueStats[$installment->loan_id]['count']++;
                
                // Calculate amount based on status
                $amount = $installment->status === 'withbalance' 
                    ? ($installment->installment_balance ?? $installment->install_amount) 
                    : $installment->install_amount;
                
                $overdueStats[$installment->loan_id]['amount'] += $amount;
                
                // Update earliest date if needed
                if (
                    $overdueStats[$installment->loan_id]['earliest_date'] === null || 
                    $installmentDate < Carbon::parse($overdueStats[$installment->loan_id]['earliest_date'])
                ) {
                    $overdueStats[$installment->loan_id]['earliest_date'] = $installment->date;
                }
            }
        }
        
        // If no loans have overdue installments, return early
        if (empty($overdueLoanIds)) {
            return response()->json([
                'draw' => (int)$request->input('draw', 1),
                'recordsTotal' => $runningLoansCount,
                'recordsFiltered' => 0,
                'data' => [],
                'summary' => [
                    'client_count' => 0,
                    'total_overdue_amount' => 0,
                    'average_days_overdue' => 0,
                    'high_priority_count' => 0,
                    'medium_priority_count' => 0,
                    'low_priority_count' => 0,
                ]
            ]);
        }
        
        // STEP 4: Get loan details for loans with actual overdue installments
        $query = DB::table('user_loans')
            ->join('clients', 'clients.id', '=', 'user_loans.client_id')
            ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
            ->whereIn('user_loans.id', $overdueLoanIds)
            ->select([
                'user_loans.id as loan_id',
                'user_loans.trx as loan_trx',
                'user_loans.amount as loan_amount',
                'user_loans.final_amount as loan_final_amount',
                'user_loans.paid_amount as loan_paid_amount',
                'user_loans.per_installment',
                'user_loans.loan_taken_date',
                'user_loans.due_date',
                'user_loans.next_installment_date',
                'user_loans.installment_interval',
                'user_loans.total_installment',
                'clients.id as client_id',
                'clients.name as client_name',
                'clients.phone as client_phone',
                'clients.credit_balance as client_balance',
                'agents.id as agent_id',
                DB::raw('CONCAT(agents.f_name, " ", agents.l_name) as agent_name')
            ]);
        
        // Apply filters
        if ($searchValue = $request->input('search.value')) {
            $escapedSearch = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $searchValue);
            
            $query->where(function($q) use ($escapedSearch) {
                $q->where('clients.name', 'like', "%{$escapedSearch}%")
                  ->orWhere('clients.phone', 'like', "%{$escapedSearch}%")
                  ->orWhere('user_loans.trx', 'like', "%{$escapedSearch}%");
            });
        }
        
        if ($request->filled('agent_id') && $request->agent_id !== 'all') {
            $query->where('user_loans.user_id', $request->agent_id);
        }
        
        // Apply overdue period filter if provided
        if ($request->filled('overdue_days') && (int)$request->overdue_days > 0) {
            $days = (int)$request->overdue_days;
            $cutoffDate = $today->copy()->subDays($days);
            
            // Filter loans based on earliest overdue date
            $loansInRange = [];
            foreach ($overdueStats as $loanId => $stats) {
                $earliestDate = Carbon::parse($stats['earliest_date']);
                if ($earliestDate >= $cutoffDate && $earliestDate < $today) {
                    $loansInRange[] = $loanId;
                }
            }
            
            if (!empty($loansInRange)) {
                $query->whereIn('user_loans.id', $loansInRange);
            } else {
                // No loans in the date range, return empty result
                return response()->json([
                    'draw' => (int)$request->input('draw', 1),
                    'recordsTotal' => $runningLoansCount,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'summary' => [
                        'client_count' => 0,
                        'total_overdue_amount' => 0,
                        'average_days_overdue' => 0,
                        'high_priority_count' => 0,
                        'medium_priority_count' => 0,
                        'low_priority_count' => 0,
                    ]
                ]);
            }
        }
        
        // STEP 5: Get filtered count and loans
        $recordsFiltered = $query->count();
        $filteredLoans = $query->get();
        $filteredLoanData = [];
        
        // STEP 6: Prepare the data with all calculations
        foreach ($filteredLoans as $loan) {
            // Only process loans that have overdue data
            if (isset($overdueStats[$loan->loan_id])) {
                // Add overdue metrics
                $loan->total_overdue_installments = $overdueStats[$loan->loan_id]['count'];
                $loan->total_overdue_amount = $overdueStats[$loan->loan_id]['amount'];
                $loan->earliest_overdue_date = $overdueStats[$loan->loan_id]['earliest_date'];
                
                // Calculate days overdue
                $earliestDate = Carbon::parse($loan->earliest_overdue_date);
                $loan->days_overdue = $earliestDate->diffInDays($today);
                
                // Calculate progress percentage
                $loan->progress_percentage = $loan->loan_final_amount > 0 
                    ? round(($loan->loan_paid_amount / $loan->loan_final_amount) * 100, 1) 
                    : 0;
                
                // Calculate days remaining on loan
                $dueDate = Carbon::parse($loan->due_date);
                $loanTakenDate = Carbon::parse($loan->loan_taken_date);
                $daysToDue = max(0, $today->diffInDays($dueDate, false));
                $totalLoanDays = $loanTakenDate->diffInDays($dueDate);
                $timeRemainingPercent = $totalLoanDays > 0 ? ($daysToDue / $totalLoanDays) * 100 : 0;
                
                // Calculate expected progress based on time elapsed
                $timeElapsedPercent = 100 - $timeRemainingPercent;
                $expectedProgress = $timeElapsedPercent;
                
                // Calculate payment deficit
                $paymentDeficit = $expectedProgress - $loan->progress_percentage;
                
                // Calculate overdue amount as percentage of loan amount
                $overduePercentage = ($loan->total_overdue_amount / $loan->loan_final_amount) * 100;
                
                // Determine priority level
                if (
                    ($timeRemainingPercent < 20 && $loan->progress_percentage < 70) ||
                    ($paymentDeficit > 30) ||
                    ($overduePercentage > 20) ||
                    ($loan->days_overdue > 30)
                ) {
                    $loan->priority = 'high';
                } 
                else if (
                    ($timeRemainingPercent < 40 && $loan->progress_percentage < 75) ||
                    ($paymentDeficit > 15) ||
                    ($overduePercentage > 10) ||
                    ($loan->days_overdue > 14)
                ) {
                    $loan->priority = 'medium';
                } 
                else {
                    $loan->priority = 'low';
                }
                
                // Store additional computed values
                $loan->time_remaining_percent = round($timeRemainingPercent, 1);
                $loan->expected_progress = round($expectedProgress, 1);
                $loan->payment_deficit = round($paymentDeficit, 1);
                $loan->overdue_percentage = round($overduePercentage, 1);
                
                // Add to our filtered data array
                $filteredLoanData[] = $loan;
            }
        }
        
        // STEP 7: Convert to collection for sorting
        $loansCollection = collect($filteredLoanData);
        
        // Apply sorting in memory
        $sortField = $request->input('sort_field', 'total_overdue_amount');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        if ($sortField === 'progress') {
            $loansCollection = $sortDirection === 'asc' 
                ? $loansCollection->sortBy('progress_percentage') 
                : $loansCollection->sortByDesc('progress_percentage');
        } else if ($sortField === 'days_overdue') {
            $loansCollection = $sortDirection === 'asc' 
                ? $loansCollection->sortBy('days_overdue') 
                : $loansCollection->sortByDesc('days_overdue');
        } else if ($sortField === 'missed_installments') {
            $loansCollection = $sortDirection === 'asc' 
                ? $loansCollection->sortBy('total_overdue_installments') 
                : $loansCollection->sortByDesc('total_overdue_installments');
        } else {
            // Default sort by overdue amount
            $loansCollection = $sortDirection === 'asc' 
                ? $loansCollection->sortBy('total_overdue_amount') 
                : $loansCollection->sortByDesc('total_overdue_amount');
        }
        
        // STEP 8: Apply pagination
        $start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 25);
        $paginatedLoans = $loansCollection->skip($start)->take($length)->values();
        
        // STEP 9: Calculate summary statistics
        $summary = [
            'client_count' => $loansCollection->unique('client_id')->count(),
            'total_overdue_amount' => $loansCollection->sum('total_overdue_amount'),
            'average_days_overdue' => $loansCollection->avg('days_overdue') ? round($loansCollection->avg('days_overdue'), 1) : 0,
            'high_priority_count' => $loansCollection->where('priority', 'high')->count(),
            'medium_priority_count' => $loansCollection->where('priority', 'medium')->count(),
            'low_priority_count' => $loansCollection->where('priority', 'low')->count(),
            'total_loan_value' => $loansCollection->sum('loan_final_amount'),
            'total_paid_value' => $loansCollection->sum('loan_paid_amount'),
        ];
        
        // STEP 10: Return response
        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => $runningLoansCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $paginatedLoans,
            'summary' => $summary
        ]);
        
    } catch (\Exception $e) {
        Log::error('Loan Arrears Data Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'An error occurred while loading data: ' . $e->getMessage(),
            'summary' => [
                'client_count' => 0,
                'total_overdue_amount' => 0,
                'average_days_overdue' => 0,
                'high_priority_count' => 0,
                'medium_priority_count' => 0,
                'low_priority_count' => 0,
                'total_loan_value' => 0,
                'total_paid_value' => 0,
            ]
        ]);
    }
}
/**
 * Get missed installments for a specific loan with payment cross-check
 * 
 * @param int $loanId
 * @return \Illuminate\Http\JsonResponse
 */
public function getMissedInstallments($loanId)
{
    try {
        $today = Carbon::today();
        
        // Verify this is a running loan
        $loan = DB::table('user_loans')
            ->join('clients', 'clients.id', '=', 'user_loans.client_id')
            ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
            ->where('user_loans.id', $loanId)
            ->where('user_loans.status', 1) // Only running loans
            ->select(
                'user_loans.*', 
                'clients.name as client_name', 
                'clients.phone as client_phone',
                'clients.credit_balance as client_balance',
                'agents.f_name as agent_first_name', 
                'agents.l_name as agent_last_name'
            )
            ->first();
        
        if (!$loan) {
            return response()->json(['error' => 'Loan not found or not in running state'], 404);
        }
        
        // Make sure we only get installments AFTER the loan_taken_date
        $loanTakenDate = Carbon::parse($loan->loan_taken_date);
        
        // Get all overdue installments for this loan
        $installments = DB::table('loan_payment_installments')
            ->where('loan_id', $loanId)
            ->where('date', '<', $today)
            ->where('date', '>=', $loanTakenDate)
            ->whereIn('status', ['pending', 'withbalance'])
            ->orderBy('date', 'asc')
            ->get();
        
        // Get recent payment history for this loan
        $recentPayments = DB::table('loan_payments')
            ->where('loan_id', $loanId)
            ->orderBy('payment_date', 'desc')
            ->limit(10) // Increased limit to capture more payments
            ->get();
        
        // Build a lookup of payment dates to quickly check which installments are actually paid
        $paymentDateAmounts = [];
        foreach ($recentPayments as $payment) {
            $paymentDate = Carbon::parse($payment->payment_date)->format('Y-m-d');
            
            // Create or add to the payment amount for this date
            if (!isset($paymentDateAmounts[$paymentDate])) {
                $paymentDateAmounts[$paymentDate] = 0;
            }
            $paymentDateAmounts[$paymentDate] += $payment->amount;
        }
        
        // Filter out installments that have corresponding payments
        $actualMissedInstallments = collect();
        foreach ($installments as $installment) {
            $installmentDate = Carbon::parse($installment->date)->format('Y-m-d');
            
            // Check if there's a payment matching this installment date and amount
            $isPaid = false;
            
            if (isset($paymentDateAmounts[$installmentDate])) {
                // If the payment amount for this date equals or exceeds the installment amount
                if ($paymentDateAmounts[$installmentDate] >= $installment->install_amount) {
                    $isPaid = true;
                    
                    // Reduce the payment amount by what was "used" for this installment
                    $paymentDateAmounts[$installmentDate] -= $installment->install_amount;
                }
            }
            
            // Add to missed installments only if it's not paid
            if (!$isPaid) {
                // Check if the installment balance is zero
                if ($installment->status === 'withbalance' && ($installment->installment_balance ?? 0) <= 0) {
                    // Skip installments with 'withbalance' status but zero balance
                    continue;
                }
                
                $actualMissedInstallments->push($installment);
            }
        }
        
        // If no valid installments after filtering, return appropriate message
        if ($actualMissedInstallments->isEmpty()) {
            return response()->json([
                'loan' => $loan,
                'installments' => [],
                'recent_payments' => $recentPayments,
                'summary' => [
                    'total_missed' => 0,
                    'total_with_balance' => 0,
                    'progress' => round(($loan->paid_amount / max(1, $loan->final_amount)) * 100, 1),
                    'expected_progress' => $this->calculateExpectedProgress($loan, $today),
                    'payment_deficit' => 0,
                    'installments_count' => 0,
                    'days_overdue' => 0,
                    'high_severity_count' => 0,
                    'medium_severity_count' => 0,
                    'low_severity_count' => 0,
                ]
            ]);
        }
        
        // Calculate detailed summary information
        $totalOverdue = $actualMissedInstallments->sum(function($item) {
            return $item->status === 'withbalance' ? 
                ($item->installment_balance ?? $item->install_amount) : 
                $item->install_amount;
        });
        
        $totalOverdueWithBalance = $actualMissedInstallments
            ->where('status', 'withbalance')
            ->sum('installment_balance');
        
        $loanProgress = 0;
        if ($loan->final_amount > 0) {
            $loanProgress = round(($loan->paid_amount / $loan->final_amount) * 100, 1);
        }
        
        // Calculate expected progress based on time elapsed
        $expectedProgress = $this->calculateExpectedProgress($loan, $today);
        
        $paymentDeficit = max(0, $expectedProgress - $loanProgress);
        
        // Calculate days overdue for earliest missed installment
        $daysOverdue = 0;
        if ($actualMissedInstallments->isNotEmpty()) {
            $earliestOverdue = Carbon::parse($actualMissedInstallments->min('date'));
            $daysOverdue = $earliestOverdue->diffInDays($today);
        }
        
        // Enhanced installments data
        $enhancedInstallments = $actualMissedInstallments->map(function($item) use ($today) {
            $dueDate = Carbon::parse($item->date);
            $daysLate = $dueDate->diffInDays($today);
            
            // Calculate the actual balance
            $actualBalance = $item->status === 'withbalance' ? 
                ($item->installment_balance ?? 0) : 
                $item->install_amount;
            
            // Calculate severity level
            $severity = 'low';
            if ($daysLate > 30) {
                $severity = 'high';
            } else if ($daysLate > 14) {
                $severity = 'medium';
            }
            
            return [
                'id' => $item->id,
                'date' => $item->date,
                'formatted_date' => $dueDate->format('M d, Y'),
                'install_amount' => $item->install_amount,
                'installment_balance' => $actualBalance,
                'status' => $item->status,
                'days_late' => $daysLate,
                'severity' => $severity
            ];
        });
        
        return response()->json([
            'loan' => $loan,
            'installments' => $enhancedInstallments,
            'recent_payments' => $recentPayments,
            'summary' => [
                'total_missed' => $totalOverdue,
                'total_with_balance' => $totalOverdueWithBalance,
                'progress' => $loanProgress,
                'expected_progress' => $expectedProgress,
                'payment_deficit' => $paymentDeficit,
                'installments_count' => count($enhancedInstallments),
                'days_overdue' => $daysOverdue,
                'high_severity_count' => $enhancedInstallments->where('severity', 'high')->count(),
                'medium_severity_count' => $enhancedInstallments->where('severity', 'medium')->count(),
                'low_severity_count' => $enhancedInstallments->where('severity', 'low')->count(),
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Failed to get missed installments: ' . $e->getMessage(), [
            'loan_id' => $loanId,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'error' => 'Failed to load installment details: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Calculate expected progress based on time elapsed
 * 
 * @param object $loan
 * @param Carbon $today
 * @return float
 */
private function calculateExpectedProgress($loan, $today)
{
    $dueDate = Carbon::parse($loan->due_date);
    $loanTakenDate = Carbon::parse($loan->loan_taken_date);
    $totalLoanDays = $loanTakenDate->diffInDays($dueDate);
    $daysElapsed = $loanTakenDate->diffInDays($today);
    
    $expectedProgress = 0;
    if ($totalLoanDays > 0) {
        $expectedProgress = min(100, round(($daysElapsed / $totalLoanDays) * 100, 1));
    }
    
    return $expectedProgress;
}



/**
 * Get missed installments for a specific loan with enhanced details
 * 
 * @param int $loanId
 * @return \Illuminate\Http\JsonResponse
 */
public function getMissedInstallmentsM28($loanId)
{
    try {
        $today = Carbon::today();
        
        // Verify this is a running loan
        $loan = DB::table('user_loans')
            ->join('clients', 'clients.id', '=', 'user_loans.client_id')
            ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
            ->where('user_loans.id', $loanId)
            ->where('user_loans.status', 1) // Only running loans
            ->select(
                'user_loans.*', 
                'clients.name as client_name', 
                'clients.phone as client_phone',
                'clients.credit_balance as client_balance',
                'agents.f_name as agent_first_name', 
                'agents.l_name as agent_last_name'
            )
            ->first();
        
        if (!$loan) {
            return response()->json(['error' => 'Loan not found or not in running state'], 404);
        }
        
        // Get all overdue installments for this loan with detailed information
        $installments = DB::table('loan_payment_installments')
            ->where('loan_id', $loanId)
            ->whereIn('status', ['pending', 'withbalance'])
            ->where('date', '<', $today)
            ->orderBy('date', 'asc')
            ->get();
        
        // Get recent payment history for this loan (last 5 payments)
        $recentPayments = DB::table('loan_payments')
            ->where('loan_id', $loanId)
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();
        
        // Calculate detailed summary information
        $totalOverdue = $installments->sum('install_amount');
        $totalOverdueWithBalance = $installments->sum(function($item) {
            return $item->status === 'withbalance' ? $item->installment_balance : 0;
        });
        
        $loanProgress = 0;
        if ($loan->final_amount > 0) {
            $loanProgress = round(($loan->paid_amount / $loan->final_amount) * 100, 1);
        }
        
        // Calculate expected progress based on time elapsed
        $dueDate = Carbon::parse($loan->due_date);
        $loanTakenDate = Carbon::parse($loan->loan_taken_date);
        $totalLoanDays = $loanTakenDate->diffInDays($dueDate);
        $daysElapsed = $loanTakenDate->diffInDays($today);
        
        $expectedProgress = 0;
        if ($totalLoanDays > 0) {
            $expectedProgress = min(100, round(($daysElapsed / $totalLoanDays) * 100, 1));
        }
        
        $paymentDeficit = max(0, $expectedProgress - $loanProgress);
        
        // Calculate days overdue for earliest missed installment
        $daysOverdue = 0;
        if ($installments->isNotEmpty()) {
            $earliestOverdue = Carbon::parse($installments->min('date'));
            $daysOverdue = $earliestOverdue->diffInDays($today);
        }
        
        // Enhanced installments data
        $enhancedInstallments = $installments->map(function($item) use ($today) {
            $dueDate = Carbon::parse($item->date);
            $daysLate = $dueDate->diffInDays($today);
            
            // Calculate a severity level for this installment
            $severity = 'low';
            if ($daysLate > 30) {
                $severity = 'high';
            } else if ($daysLate > 14) {
                $severity = 'medium';
            }
            
            return [
                'id' => $item->id,
                'date' => $item->date,
                'formatted_date' => $dueDate->format('M d, Y'),
                'install_amount' => $item->install_amount,
                'installment_balance' => $item->installment_balance ?? 0,
                'status' => $item->status,
                'days_late' => $daysLate,
                'severity' => $severity
            ];
        });
        
        return response()->json([
            'loan' => $loan,
            'installments' => $enhancedInstallments,
            'recent_payments' => $recentPayments,
            'summary' => [
                'total_missed' => $totalOverdue,
                'total_with_balance' => $totalOverdueWithBalance,
                'progress' => $loanProgress,
                'expected_progress' => $expectedProgress,
                'payment_deficit' => $paymentDeficit,
                'installments_count' => count($installments),
                'days_overdue' => $daysOverdue,
                'high_severity_count' => $enhancedInstallments->where('severity', 'high')->count(),
                'medium_severity_count' => $enhancedInstallments->where('severity', 'medium')->count(),
                'low_severity_count' => $enhancedInstallments->where('severity', 'low')->count(),
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Failed to get missed installments: ' . $e->getMessage(), [
            'loan_id' => $loanId,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'error' => 'Failed to load installment details: ' . $e->getMessage()
        ], 500);
    }
}






/**
 * Get missed installments for a specific loan
 * 
 * @param int $loanId
 * @return \Illuminate\Http\JsonResponse
 */
public function getMissedInstallmentsBx($loanId)
{
    try {
        $today = \Carbon\Carbon::today();
        
        // Verify this is a running loan
        $loan = \DB::table('user_loans')
            ->join('clients', 'clients.id', '=', 'user_loans.client_id')
            ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
            ->where('user_loans.id', $loanId)
            ->where('user_loans.status', 1) // Only running loans
            ->select(
                'user_loans.*', 
                'clients.name as client_name', 
                'clients.phone as client_phone',
                'clients.credit_balance as client_balance',
                'agents.f_name as agent_first_name', 
                'agents.l_name as agent_last_name'
            )
            ->first();
        
        if (!$loan) {
            return response()->json(['error' => 'Loan not found or not in running state'], 404);
        }
        
        // Get all overdue installments for this loan
        $installments = \DB::table('loan_payment_installments')
            ->where('loan_id', $loanId)
            ->whereIn('status', ['pending', 'withbalance'])
            ->where('date', '<', $today)
            ->orderBy('date', 'asc')
            ->get();
        
        // Get recent payment history for this loan (last 5 payments)
        $recentPayments = \DB::table('loan_payments')
            ->where('loan_id', $loanId)
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();
        
        // Calculate summary information
        $totalOverdue = $installments->sum('install_amount');
        $loanProgress = 0;
        if ($loan->final_amount > 0) {
            $loanProgress = round(($loan->paid_amount / $loan->final_amount) * 100, 1);
        }
        
        return response()->json([
            'loan' => $loan,
            'installments' => $installments,
            'recent_payments' => $recentPayments,
            'summary' => [
                'total_missed' => $totalOverdue,
                'progress' => $loanProgress,
                'installments_count' => count($installments),
                'days_overdue' => $installments->isEmpty() ? 0 : \Carbon\Carbon::parse($installments->min('date'))->diffInDays($today)
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Failed to get missed installments: ' . $e->getMessage(), [
            'loan_id' => $loanId,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'error' => 'Failed to load installment details: ' . $e->getMessage()
        ], 500);
    }
}




        public function loanArrearsDataXXXX(Request $request)
            {
                $currentHour = Carbon::now()->hour;
                $today = Carbon::today();

                // Base query for running loans, excluding those due today
                $query = DB::table('user_loans')
                    ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                    ->leftJoin('loan_payment_installments', function ($join) use ($today) {
                        $join->on('loan_payment_installments.loan_id', '=', 'user_loans.id')
                            ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                            ->where('loan_payment_installments.date', '<', $today);
                    })
                    ->where('user_loans.status', 1) // Running loans only
                    ->where('user_loans.due_date', '!=', $today) // Exclude loans due today
                    ->select(
                        'clients.id as client_id',
                        'clients.name as client_name',
                        'clients.phone as client_phone',
                        'clients.credit_balance as client_balance',
                        'user_loans.id as loan_id',
                        'user_loans.loan_taken_date',
                        DB::raw('MIN(loan_payment_installments.date) as next_installment_date'),
                        DB::raw('COALESCE(COUNT(loan_payment_installments.id), 0) as total_overdue_installments'),
                        DB::raw('COALESCE(SUM(loan_payment_installments.install_amount), 0) as total_overdue_amount')
                    )
                    ->groupBy('user_loans.id', 'clients.id', 'clients.name', 'clients.phone', 'clients.credit_balance', 'user_loans.loan_taken_date');

                // Time-based logic
                if ($currentHour >= 8) {
                    $query->having('total_overdue_installments', '>', 0);
                }

                // Filters
                if ($request->filled('agent_id') && $request->agent_id !== 'all') {
                    $query->where('user_loans.user_id', $request->agent_id);
                }

                if ($searchValue = $request->input('search.value')) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('clients.name', 'like', "%{$searchValue}%")
                        ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                        ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
                    });
                }

                if ($request->filled('overdue_months')) {
                    $months = (int) $request->overdue_months;
                    $cutoffDate = Carbon::now()->subMonths($months);
                    $query->havingRaw('MIN(loan_payment_installments.date) <= ?', [$cutoffDate]);
                }

                // Pagination and ordering
                $totalRecords = $query->get()->count();
                $data = $query->skip($request->input('start', 0))
                            ->take($request->input('length', 20))
                            ->orderBy($request->input('columns.' . $request->input('order.0.column', 0) . '.data', 'client_name'), $request->input('order.0.dir', 'asc'))
                            ->get();

                // Summary
                $allResults = $query->get();
                $summary = [
                    'client_count' => $allResults->unique('client_id')->count(),
                    'total_overdue_amount' => $allResults->sum('total_overdue_amount'),
                ];

                return response()->json([
                    'draw' => (int) $request->input('draw'),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data,
                    'summary' => $summary,
                ]);
            }
        public function loanArrearsDataXXX(Request $request)
            {
                $currentHour = Carbon::now()->hour;
                $referenceDate = Carbon::now()->startOfDay();

                // Base query for running loans
                $query = DB::table('user_loans')
                    ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                    ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
                    ->leftJoin('loan_payment_installments', function ($join) use ($referenceDate) {
                        $join->on('loan_payment_installments.loan_id', '=', 'user_loans.id')
                            ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                            ->where('loan_payment_installments.date', '<', $referenceDate);
                    })
                    ->where('user_loans.status', 1)
                    ->select(
                        'clients.id as client_id',
                        'clients.name as client_name',
                        'clients.phone as client_phone',
                        'clients.credit_balance as client_balance',
                        'user_loans.id as loan_id',
                        'user_loans.trx as loan_transaction_id',
                        'user_loans.loan_taken_date as loan_taken_date',
                        DB::raw('COALESCE(COUNT(loan_payment_installments.id), 0) as total_overdue_installments'),
                        DB::raw('COALESCE(SUM(loan_payment_installments.install_amount), 0) as total_overdue_amount'),
                        DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
                    )
                    ->groupBy(
                        'user_loans.id',
                        'clients.id',
                        'clients.name',
                        'clients.phone',
                        'clients.credit_balance',
                        'user_loans.trx',
                        'user_loans.loan_taken_date',
                        'agents.f_name',
                        'agents.l_name'
                    );

                // Time-based logic
                if ($currentHour >= 8) {
                    $query->havingRaw('COUNT(loan_payment_installments.id) > 0');
                }

                // Apply filters
                // Agent filter
                if ($request->filled('agent_id') && $request->agent_id !== 'all') {
                    $query->where('user_loans.user_id', $request->agent_id);
                }

                // Global search filter
                $searchValue = $request->input('search.value');
                if ($searchValue) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('clients.name', 'like', "%{$searchValue}%")
                        ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                        ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
                    });
                }

                // Overdue months filter
                if ($request->filled('overdue_months')) {
                    $months = (int) $request->overdue_months;
                    $cutoffDate = Carbon::now()->subMonths($months)->startOfDay();
                    $query->havingRaw("MIN(loan_payment_installments.date) <= ?", [$cutoffDate]);
                }

                // Get results for DataTables
                $allResults = $query->get();
                $recordsTotal = $allResults->count();

                // In-memory ordering
                $order = $request->input('order', []);
                $columns = $request->input('columns', []);
                if (!empty($order)) {
                    foreach ($order as $o) {
                        $columnIndex = $o['column'];
                        $columnName = $columns[$columnIndex]['data'] ?? 'client_name';
                        $dir = $o['dir'] === 'asc' ? 'asc' : 'desc';
                        $allResults = ($dir === 'asc')
                            ? $allResults->sortBy($columnName)
                            : $allResults->sortByDesc($columnName);
                        $allResults = $allResults->values();
                    }
                } else {
                    $allResults = $allResults->sortBy('client_name')->values();
                }

                // Pagination
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 20);
                $pagedResults = $allResults->slice($start, $length)->values();

                // Summary data
                $uniqueClients = $allResults->unique('client_id')->count();
                $totalOverdueAmount = $allResults->sum('total_overdue_amount');

                return response()->json([
                    'draw' => (int) $request->input('draw'),
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsTotal,
                    'data' => $pagedResults,
                    'summary' => [
                        'client_count' => $uniqueClients,
                        'total_overdue_amount' => $totalOverdueAmount,
                    ]
                ]);
            }



        public function loanArrearsData21(Request $request)
            {
                // Use an optional 'reference_date' provided by the user; default to today's start-of-day.
                $referenceDate = $request->filled('reference_date')
                    ? Carbon::parse($request->input('reference_date'))->startOfDay()
                    : Carbon::now()->startOfDay();

                // Build the base query:
                // Only include running loans (status = 1)
                // and only include installments that are still pending (date >= referenceDate).
                $query = DB::table('user_loans')
                    ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                    ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
                    ->join('loan_payment_installments', 'loan_payment_installments.loan_id', '=', 'user_loans.id')
                    ->where('user_loans.status', 1)
                    ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                    ->where('loan_payment_installments.date', '>=', $referenceDate)
                    ->select(
                        'clients.id as client_id',
                        'clients.name as client_name',
                        'clients.phone as client_phone',
                        'clients.credit_balance as client_balance',
                        'user_loans.id as loan_id',
                        'user_loans.trx as loan_transaction_id',
                        'user_loans.loan_taken_date as loan_taken_date', // NEW: Loan Taken Date
                        // Aggregated fields (aliases match DataTables configuration)
                        DB::raw('COUNT(loan_payment_installments.id) as total_overdue_installments'),
                        DB::raw('SUM(loan_payment_installments.install_amount) as total_overdue_amount'),
                        DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
                    )
                    ->groupBy(
                        'user_loans.id',
                        'clients.id',
                        'clients.name',
                        'clients.phone',
                        'clients.credit_balance',
                        'user_loans.trx',
                        'user_loans.loan_taken_date', // include in grouping
                        'agents.f_name',
                        'agents.l_name'
                    );

                // OPTIONAL FILTERS

                // 1) Agent filter
                if ($request->filled('agent_id') && $request->agent_id !== 'all') {
                    $query->where('user_loans.user_id', $request->agent_id);
                }

                // 2) Global search filter (search by client name, phone, or loan transaction)
                $searchValue = $request->input('search.value');
                if ($searchValue) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('clients.name', 'like', "%{$searchValue}%")
                        ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                        ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
                    });
                }

                // 3) Overdue months filter (applied on the next installment date)
                if ($request->filled('overdue_months')) {
                    $months = (int) $request->overdue_months;
                    $cutoffDate = Carbon::now()->subMonths($months)->startOfDay();
                    // Only include loans whose earliest (next) installment date is before or equal to the cutoff.
                    $query->havingRaw("MIN(loan_payment_installments.date) <= ?", [$cutoffDate]);
                }

                // GET RESULTS FOR DATATABLES

                // Retrieve all results.
                $allResults = $query->get();
                $recordsTotal = $allResults->count();

                // In-memory ordering
                $order   = $request->input('order', []);
                $columns = $request->input('columns', []);
                if (!empty($order)) {
                    foreach ($order as $o) {
                        $columnIndex = $o['column'];
                        // The default column is client_name. Ensure that if you use a new field like loan_taken_date,
                        // DataTables sends a matching column name.
                        $columnName = $columns[$columnIndex]['data'] ?? 'client_name';
                        $dir = $o['dir'] === 'asc' ? 'asc' : 'desc';
                        $allResults = ($dir === 'asc')
                            ? $allResults->sortBy($columnName)
                            : $allResults->sortByDesc($columnName);
                        $allResults = $allResults->values();
                    }
                } else {
                    $allResults = $allResults->sortBy('client_name')->values();
                }

                // Pagination
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 20);
                $pagedResults = $allResults->slice($start, $length)->values();

                // Summary: unique clients count and total pending amount.
                $uniqueClients = $allResults->unique('client_id')->count();
                $totalOverdueAmount = $allResults->sum('total_overdue_amount');

                return response()->json([
                    'draw' => (int) $request->input('draw'),
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsTotal,
                    'data' => $pagedResults,
                    'summary' => [
                        'client_count' => $uniqueClients,
                        'total_overdue_amount' => $totalOverdueAmount,
                    ]
                ]);
}


        public function loanArrearsDataWorkButnot(Request $request)
        {
            // Build the query:
            // We select only running loans (status = 1) and join the installments.
            // We use conditional aggregates to calculate:
            //   - missed_installments: count of installments with date < CURDATE()
            //   - overdue_amount: sum of installment amounts where date < CURDATE()
            //   - next_installment_date: minimum installment date where date >= CURDATE()
            $query = DB::table('user_loans')
                ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
                ->join('loan_payment_installments', 'loan_payment_installments.loan_id', '=', 'user_loans.id')
                ->where('user_loans.status', 1)
                ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
                ->select(
                    'clients.id as client_id',
                    'clients.name as client_name',
                    'clients.phone as client_phone',
                    'clients.credit_balance as client_balance',
                    'user_loans.id as loan_id',
                    'user_loans.trx as loan_transaction_id',
                    'user_loans.loan_taken_date as loan_taken_date',
                    DB::raw("SUM(CASE WHEN loan_payment_installments.date < CURDATE() THEN 1 ELSE 0 END) as missed_installments"),
                    DB::raw("SUM(CASE WHEN loan_payment_installments.date < CURDATE() THEN loan_payment_installments.install_amount ELSE 0 END) as overdue_amount"),
                    DB::raw("MIN(CASE WHEN loan_payment_installments.date >= CURDATE() THEN loan_payment_installments.date ELSE NULL END) as next_installment_date")
                )
                ->groupBy(
                    'user_loans.id',
                    'clients.id',
                    'clients.name',
                    'clients.phone',
                    'clients.credit_balance',
                    'user_loans.trx',
                    'user_loans.loan_taken_date',
                    'agents.f_name',
                    'agents.l_name'
                );

            // Only include loans that have at least one missed installment
            $query->havingRaw("SUM(CASE WHEN loan_payment_installments.date < CURDATE() THEN 1 ELSE 0 END) > 0");

            // OPTIONAL FILTERS

            // 1) Agent filter
            if ($request->filled('agent_id') && $request->agent_id !== 'all') {
                $query->where('user_loans.user_id', $request->agent_id);
            }

            // 2) Global search filter
            $searchValue = $request->input('search.value');
            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('clients.name', 'like', "%{$searchValue}%")
                      ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                      ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
                });
            }

            // 3) Overdue months quick filter: if provided, only show loans whose next installment date
            // is less than or equal to the cutoff date (current date minus the given number of months)
            if ($request->filled('overdue_months')) {
                $months = (int) $request->overdue_months;
                $cutoffDate = Carbon::now()->subMonths($months)->startOfDay();
                $query->havingRaw("MIN(CASE WHEN loan_payment_installments.date >= CURDATE() THEN loan_payment_installments.date ELSE NULL END) <= ?", [$cutoffDate]);
            }

            // GET RESULTS FOR DATATABLES

            $allResults = $query->get();
            $recordsTotal = $allResults->count();

            // In-memory ordering (if needed)
            $order = $request->input('order', []);
            $columns = $request->input('columns', []);
            if (!empty($order)) {
                foreach ($order as $o) {
                    $columnIndex = $o['column'];
                    $columnName  = $columns[$columnIndex]['data'] ?? 'client_name';
                    $dir = $o['dir'] === 'asc' ? 'asc' : 'desc';
                    $allResults = ($dir === 'asc')
                        ? $allResults->sortBy($columnName)
                        : $allResults->sortByDesc($columnName);
                    $allResults = $allResults->values();
                }
            } else {
                $allResults = $allResults->sortBy('client_name')->values();
            }

            // Pagination
            $start = (int)$request->input('start', 0);
            $length = (int)$request->input('length', 20);
            $pagedResults = $allResults->slice($start, $length)->values();

            // SUMMARY: unique client count and total overdue amount
            $uniqueClients = $allResults->unique('client_id')->count();
            $totalOverdueAmount = $allResults->sum('overdue_amount');

            return response()->json([
                'draw' => (int)$request->input('draw'),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data' => $pagedResults,
                'summary' => [
                    'client_count' => $uniqueClients,
                    'total_overdue_amount' => $totalOverdueAmount,
                ]
            ]);
        }

    public function loanArrearsDactXa(Request $request)
     {
            $currentHour = Carbon::now()->hour;

            // Only show running loans (status = 1)
            $query = DB::table('user_loans')
                ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
                ->join('loan_payment_installments', 'loan_payment_installments.loan_id', '=', 'user_loans.id')
                ->where('user_loans.status', 1)
                ->whereIn('loan_payment_installments.status', ['pending','withbalance'])
                ->select(
                    'clients.id as client_id',
                    'clients.name as client_name',
                    'clients.phone as client_phone',
                    'clients.credit_balance as client_balance', // add the client's current balance
                    'user_loans.id as loan_id',
                    'user_loans.trx as loan_transaction_id',
                    // We'll group by the earliest overdue date, missed installments, etc.
                    DB::raw('COUNT(loan_payment_installments.id) as total_overdue_installments'),
                    DB::raw('SUM(loan_payment_installments.install_amount) as total_overdue_amount'),
                    DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
                )
                ->groupBy(
                    'user_loans.id',
                    'clients.id',
                    'clients.name',
                    'clients.phone',
                    'clients.credit_balance',
                    'user_loans.trx',
                    'agents.f_name',
                    'agents.l_name'
                );

            // Overdue logic: after 8 AM, only overdue installments < today
            if ($currentHour >= 8) {
                $query->where('loan_payment_installments.date', '<', Carbon::now()->startOfDay());
            } else {
                $query->where('loan_payment_installments.date', '<', Carbon::now()->startOfDay());
            }

            // ============= FILTERS ==============

            // 1) Agent filter
            if ($request->filled('agent_id') && $request->agent_id !== 'all') {
                $query->where('user_loans.user_id', $request->agent_id);
            }

            // 2) Search filter
            $searchValue = $request->input('search.value');
            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('clients.name', 'like', "%{$searchValue}%")
                    ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                    ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
                });
            }

            // 3) Overdue months
            // If "1 month" -> earliest_overdue_date <= now()->subMonths(1)
            // If "2 months" -> earliest_overdue_date <= now()->subMonths(2)
            // etc.
            if ($request->filled('overdue_months')) {
                $months = (int) $request->overdue_months;
                $cutoffDate = Carbon::now()->subMonths($months)->startOfDay();
                // Because we do grouping, we can use having() or filter after we get results.
                // But let's do a "havingRaw" approach:
                // We want: MIN(installments.date) <= $cutoffDate
                // This is tricky with Query Builder. Let's do a simpler approach:
                $query->havingRaw("MIN(loan_payment_installments.date) <= ?", [$cutoffDate]);
            }

            // ========== GET RESULTS FOR DATATABLES ==========

            // Step 1: get all results from the DB
            $allResults = $query->get();

            // Step 2: DataTables normally does ordering in the DB,
            // but because we have grouping and some custom logic,
            // we can do an in-memory sort. For large data sets, consider advanced solutions.
            $order   = $request->input('order', []);
            $columns = $request->input('columns', []);
            if (!empty($order)) {
                foreach ($order as $o) {
                    $columnIndex = $o['column'];
                    $columnName  = $columns[$columnIndex]['data'] ?? 'client_name';
                    $dir         = $o['dir'] === 'asc' ? 'asc' : 'desc';

                    if ($dir === 'asc') {
                        $allResults = $allResults->sortBy($columnName)->values();
                    } else {
                        $allResults = $allResults->sortByDesc($columnName)->values();
                    }
                }
            } else {
                // Default sort by client name asc
                $allResults = $allResults->sortBy('client_name')->values();
            }

            $recordsTotal = $allResults->count();

            // Step 3: pagination
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 20);
            $pagedResults = $allResults->slice($start, $length)->values();

            // ========== SUMMARY DATA ==========
            // We want to show in the blade:
            //   - number of unique clients
            //   - total overdue amount
            $uniqueClients           = $allResults->unique('client_id')->count();
            $totalOverdueAmount      = $allResults->sum('total_overdue_amount');

            // Return DataTables JSON plus the summary
            return response()->json([
                'draw'            => (int) $request->input('draw'),
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data'            => $pagedResults,
                'summary'         => [
                    'client_count'         => $uniqueClients,
                    'total_overdue_amount' => $totalOverdueAmount
                ]
            ]);
        }

    public function loanArrearsDataX(Request $request)
        {
            $currentHour = Carbon::now()->hour;

            // Base query: running loans only (status = 1)
            $query = DB::table('user_loans')
                ->join('clients', 'clients.id', '=', 'user_loans.client_id')
                ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
                ->where('user_loans.status', 1)
                ->select(
                    'clients.id as client_id',
                    'clients.name as client_name',
                    'clients.phone as client_phone',
                    'user_loans.id as loan_id',
                    'user_loans.trx as loan_transaction_id',
                    'agents.f_name as agent_first_name',
                    'agents.l_name as agent_last_name'
                )
                // Join with installments
                ->join('loan_payment_installments', 'loan_payment_installments.loan_id', '=', 'user_loans.id')
                ->whereIn('loan_payment_installments.status', ['pending', 'withbalance']);

            // Overdue logic
            if ($currentHour >= 8) {
                // After 8 AM: only those with overdue installments
                $query->where('loan_payment_installments.date', '<', Carbon::now()->startOfDay());
            } else {
                // Before 8 AM: treat all running loans as arrears, but usually you'd still filter by < today
                $query->where('loan_payment_installments.date', '<', Carbon::now()->startOfDay());
            }

            // Aggregate fields
            $query->addSelect(
                DB::raw('COUNT(loan_payment_installments.id) as total_overdue_installments'),
                DB::raw('SUM(loan_payment_installments.install_amount) as total_overdue_amount'),
                DB::raw('MIN(loan_payment_installments.date) as earliest_overdue_date')
            )
            ->groupBy(
                'user_loans.id',
                'clients.id',
                'clients.name',
                'clients.phone',
                'user_loans.trx',
                'agents.f_name',
                'agents.l_name'
            );

            // 1) Filter by agent_id if provided
            if ($request->filled('agent_id') && $request->agent_id !== 'all') {
                $query->where('user_loans.user_id', $request->agent_id);
            }

            // 2) Global search
            $searchValue = $request->input('search.value');
            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('clients.name', 'like', "%{$searchValue}%")
                    ->orWhere('clients.phone', 'like', "%{$searchValue}%")
                    ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
                });
            }

            // 3) Additional filters: start_date / end_date (example: filter by loan_taken_date)
            if ($request->filled('start_date')) {
                $query->whereDate('user_loans.loan_taken_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('user_loans.loan_taken_date', '<=', $request->end_date);
            }

            // Example: min_overdue
            // if ($request->filled('min_overdue')) {
            //     $minOverdue = floatval($request->min_overdue);
            //     // Having Clause: since we are grouping, use havingRaw or a subquery
            //     $query->havingRaw("SUM(loan_payment_installments.install_amount) >= ?", [$minOverdue]);
            // }

            // 4) Fetch all results first (since grouping can complicate DataTables' counting)
            $allResults = $query->get();
            $recordsTotal = $allResults->count();

            // DataTables ordering
            $order   = $request->input('order', []);
            $columns = $request->input('columns', []);
            if (!empty($order)) {
                foreach ($order as $o) {
                    $columnIndex = $o['column'];
                    $columnName  = $columns[$columnIndex]['data'] ?? 'client_name';
                    $dir         = $o['dir'] === 'asc' ? 'asc' : 'desc';
                    // We can attempt to sort by the aggregated fields or fallback to client_name
                    $allResults = ($dir === 'asc')
                        ? $allResults->sortBy($columnName)
                        : $allResults->sortByDesc($columnName);
                }
                // Re-key the collection after sorting
                $allResults = $allResults->values();
            } else {
                // Default sort
                $allResults = $allResults->sortBy('client_name')->values();
            }

            // 5) Pagination
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 20);
            $pagedResults = $allResults->slice($start, $length)->values();

            // 6) Return in DataTables format
            return response()->json([
                'draw'            => (int) $request->input('draw'),
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data'            => $pagedResults,
            ]);
    }





    public function deleteLoanNow(Request $request, $id)
    {
        // Optionally require a reason from the request, e.g. a hidden input or modal
        $reason = $request->input('reason');  // If you want the user to provide a reason

        DB::beginTransaction();
        try {
            // 1) Find the loan by ID
            $loan = UserLoan::findOrFail($id);

            // 2) (Optional) Check if the loan has any recorded payments
            // $hasPayments = LoanPayment::where('loan_id', $loan->id)->exists();
            // if ($hasPayments) {
            //     // If your business rules forbid deleting a loan that has payments,
            //     // you can either abort or proceed with additional logic (like reversing them).
            //     return back()->withErrors([
            //         'error' => 'This loan has existing payments. Deletion is not allowed.'
            //     ]);
            // }

            // 3) Delete all loan installments associated with this loan
            LoanPaymentInstallment::where('loan_id', $loan->id)->delete();


             // 3) Delete or handle loan payments
            LoanPayment::where('loan_id', $loan->id)->delete();

            // 4) Delete or handle payment transactions
            PaymentTransaction::where('loan_id', $loan->id)->delete();

            // 4) (Optional) Archive or log the loan before deletion
            //    For instance, you could store it in a separate 'archived_loans' table
            //    or log the details. Here's a simple log entry:
            Log::info("Loan ID {$loan->id} is being deleted by User ID ".auth()->id().". Reason: {$reason}");

            // 5) Delete the loan itself
            $loan->delete();  // Hard delete

            // 6) Commit the transaction
            DB::commit();

            // 7) Redirect back with success message
            return back()->with('success', 'Loan and its installments deleted successfully.');
        } catch (\Exception $e) {
            // Roll back the transaction on error
            DB::rollBack();

            // Log the error for debugging
            Log::error("Failed to delete Loan ID {$id}: ".$e->getMessage());

            // Return error response
            return back()->withErrors(['error' => 'Failed to delete the loan. Please try again.']);
        }
    }




public function forceClearLoan($id)
    {
        DB::beginTransaction();

        try {
            // 1) Find the loan
            $loan = UserLoan::findOrFail($id);

            // 2) Mark it as fully paid
            $loan->status              = 2; // Typically 2 => 'Fully Paid'
            $loan->paid_amount         = $loan->final_amount;
            $loan->next_installment_date = null;  // no more installments needed
            $loan->save();

            // 3) Clear all related installments
            LoanPaymentInstallment::where('loan_id', $loan->id)->delete();

            // 4) (Optional) Clear any loan payments:
            //    LoanPayment::where('loan_id', $loan->id)->delete();

            // 5) (Optional) Clear any payment transactions:
            //    PaymentTransaction::where('loan_id', $loan->id)->delete();

            DB::commit();

            // 6) Return success
            return back()->with('success', 'Loan forcibly cleared and marked as paid successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            Log::error("Failed to forcibly clear loan #{$id}. Error: {$e->getMessage()}");

            return back()->withErrors(['error' => 'Failed to clear the loan. Please try again.']);
        }
    }



    public function reversePayment($id)
    {
        // Find the payment by its ID
        $payment = LoanPayment::findOrFail($id);

        // Check if the payment is already reversed
        if ($payment->is_reversed) {
            return redirect()->back()->withErrors(['error' => 'This payment has already been reversed.']);
        }

        // Find the client and update their credit balance
        $client = $payment->client;
        if ($client) {
            // Decrease the credit balance by the payment amount
            $client->credit_balance += $payment->amount;
            $client->save();
        }

        // Mark the payment as reversed
        $payment->is_reversed = true;
        $payment->save();

        // Update the installment and loan statuses accordingly
        $loan = $payment->loan;
        $loanInstallment = $loan->loanPaymentInstallments()
                                ->where('status', 'paid')
                                ->orderBy('date', 'desc')
                                ->first();

        if ($loanInstallment) {
            $loanInstallment->status = 'pending';
            $loanInstallment->save();
        }

        // Adjust the loan's paid amount
        $loan->paid_amount -= $payment->amount;
        $loan->save();

        return redirect()->back()->with('success', 'Payment has been reversed successfully.');
    }


    // client loan payment history api
    public function clientLoanspayHistory(Request $request): JsonResponse
    {
        try {
            $clientloanspays = LoanPayment::where('client_id', $request->id)->get();
            return response()->json(response_formatter(DEFAULT_200, $clientloanspays, null), 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve loan payment history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // clients with running loans api
    public function getAgentsClientsWithRunningLoans($agentId)
        {
            // Fetch all clients assigned to this agent with running loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 1) // Adjust 'running' based on your actual status
                ->get();

            // Count the total number of clients with running loans
            $totalClients = $clients->count();

            // Check if clients with active loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No clients with running loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $clients,
                'total_clients' => $totalClients
            ]);
        }



    // clients with pending loans api
    public function getAgentsClientsWithPendingLoans($agentId)
        {
            // Fetch all clients assigned to this agent with running loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 0) // Adjust 'running' based on your actual status
                ->get();

            // Count the total number of clients with running loans
            $totalClients = $clients->count();

            // Check if clients with active loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No clients with pending loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $clients,
                'total_clients' => $totalClients
            ]);
        }



           // clients with paid loans api
    public function getAgentsClientsWithPaidLoans($agentId)
        {
            // Fetch all clients assigned to this agent with running loans
            $clients = DB::table('clients')
                ->join('user_loans', 'clients.id', '=', 'user_loans.client_id')
                ->select('clients.*', 'user_loans.status')
                ->where('clients.added_by', $agentId)
                ->where('user_loans.status', 2) // Adjust 'running' based on your actual status
                ->get();

            // Count the total number of clients with running loans
            $totalClients = $clients->count();

            // Check if clients with active loans were found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No clients with running loans found for this agent.',
                    'total_clients' => 0
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $clients,
                'total_clients' => $totalClients
            ]);
        }


        public function deleteLoan($id)
            {
                // Find the loan by ID
                $loan = UserLoan::findOrFail($id);

                // Retrieve the client associated with the loan
                $client = Client::find($loan->client_id);

                // Sum up the total amount of non-reversed payments for this loan
                $totalPaymentToReverse = LoanPayment::where('loan_id', $loan->id)
                    ->where('is_reversed', false)
                    ->sum('amount');
                    $client->credit_balance = 0;
                    $client->save();
                // If there is any payment to reverse, update the client's credit balance
                // if ($totalPaymentToReverse > 0 && $client) {
                //     // Add the total non-reversed payment amount back to the client's credit balance
                //     $client->credit_balance += $totalPaymentToReverse;
                //     $client->save();

                //     // Mark all non-reversed payments for this loan as reversed
                //     LoanPayment::where('loan_id', $loan->id)
                //         ->where('is_reversed', false)
                //         ->update(['is_reversed' => true]);
                // }

                // Delete all loan installments associated with this loan
                LoanPaymentInstallment::where('loan_id', $loan->id)->delete();

                // Delete the loan itself
                $loan->delete();

                // Redirect back with a success message  route('admin.clients.profile', $data['client']->id)
                // return back()->with('success', 'Loan and its installments deleted successfully. Client credit has been cleared.');
                return redirect()->route('admin.clients.profile', $client->id)->with('success', 'Loan and its installments deleted successfully. Client credit has been cleared.');

            }

    // delete loan
    public function deleteLoanXXXX($id)
        {
            // Find the loan by ID
            $loan = UserLoan::findOrFail($id);

            // Delete all loan installments associated with this loan
            LoanPaymentInstallment::where('loan_id', $loan->id)->delete();



            // Delete the loan itself
            $loan->delete();

            // Redirect back with success message
            return redirect()->route('admin.clients.profile', $data['client']->id)->with('success', 'Payment processed successfully!');

            return back()->with('success', 'Loan and its installments deleted successfully.');
        }

        public function addClientLoan($id)
        {
            // Fetch the necessary data
            $client = Client::with('guarantors')->find($id); // Load client with related guarantors
            $loanPlans = LoanPlan::all();

            // Check if client or loanPlans is null and handle appropriately
            if (!$client || !$loanPlans) {
                return redirect()->back()->withErrors(['message' => 'Invalid Client or Loan Plans']);
            }

            // Fetch agents who manage clients
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

            // Return the view with the data
            return view('admin-views.Loans.addClientLoan', compact('client', 'loanPlans', 'agents'));
        }




// pay admin loan
    public function adminPayingLoan($id)
    {
        // Fetch client details
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['errors' => 'Client not found'], 404);
        }

        // Find the running loan associated with this client
        $loan = UserLoan::where('client_id', $id)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->first();

        if (!$loan) {
            return response()->json(['errors' => 'No running loans found for the client'], 404);
        }

        // Fetch the agent associated with the loan
        $agent = User::find($loan->user_id);

        // Fetch all payment slots (installments) associated with this loan
        $loanInstallments = LoanPaymentInstallment::where('loan_id', $loan->id)->get();

        // Return the view with the fetched data admin-views.Loans.pay-loan?
        return view('admin-views.Loans.pay-loan', compact('client', 'loanInstallments', 'loan', 'agent'));
    }




public function payLoan(Request $request): JsonResponse
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount'    => 'required|numeric|min:1',
        'note'      => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $clientId      = $request->input('client_id');
    $paymentAmount = $request->input('amount');

    // Fetch the client and check their credit balance
    $client = Client::findOrFail($clientId);

    if ($paymentAmount > $client->credit_balance) {
        return response()->json([
            'errors' => 'Payment amount exceeds client\'s credit balance.',
        ], 400);
    }

    // Start a database transaction for atomicity
    DB::beginTransaction();

    try {
        // Fetch all active loans for the client
        $loans = UserLoan::where('client_id', $clientId)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->orderBy('id', 'asc') // Process loans in the order they were taken
            ->get();

        if ($loans->isEmpty()) {
            return response()->json(['errors' => 'No active loans found for this client.'], 404);
        }

        $remainingPayment = $paymentAmount;
        $excessPayment    = 0;

        // Calculate total amount owed across all loans
        $totalOwed = $loans->sum(function ($loan) {
            return max($loan->final_amount - $loan->paid_amount, 0);
        });

        // If payment amount exceeds total owed, calculate excess
        if ($paymentAmount > $totalOwed) {
            $excessPayment    = $paymentAmount - $totalOwed;
            $remainingPayment = $totalOwed;
        }

        // Process payment towards loans
        foreach ($loans as $loan) {
            $loanBalance = $loan->final_amount - $loan->paid_amount;

            if ($loanBalance <= 0) {
                continue; // Skip if loan is already fully paid
            }

            $paymentForLoan = min($remainingPayment, $loanBalance);

            // Update loan's paid amount
            $loan->paid_amount += $paymentForLoan;

            // Update loan status if fully paid, with rounding to handle floating-point precision
            if (round($loan->paid_amount, 2) >= round($loan->final_amount, 2)) {
                $loan->status = 2; // Fully Paid
            }

            $loan->save();

            // Apply payment to installments
            $this->applyPaymentToInstallments($loan->id, $paymentForLoan);

            // Check if loan is fully paid after payment
            if ($loan->status == 2) {
                // Mark all remaining installments as 'paid'
                LoanPaymentInstallment::where('loan_id', $loan->id)
                    ->whereIn('status', ['pending', 'withbalance'])
                    ->update([
                        'status' => 'paid',
                        'installment_balance' => 0,
                    ]);
            }

            // Record the payment for this loan
            LoanPayment::create([
                'loan_id'        => $loan->id,
                'client_id'      => $clientId,
                'agent_id'       => $loan->user_id,
                'amount'         => $paymentForLoan,
                'credit_balance' => $client->credit_balance,
                'payment_date'   => now(),
                'note'           => $request->input('note') ?? null,
            ]);

            $remainingPayment -= $paymentForLoan;

            if ($remainingPayment <= 0) {
                break;
            }
        }

        // Update client's credit balance
        $client->credit_balance -= ($paymentAmount - $excessPayment);

        // If there's excess payment, add it to the client's savings balance
        if ($excessPayment > 0) {
            $client->savings_balance += $excessPayment;
        }

        $client->save();

        // Commit the transaction
        DB::commit();

        return response()->json([
            'response_code'     => 'default_200',
            'message'           => 'Payment processed successfully.',
            'remaining_balance' => $client->credit_balance,
            'savings_balance'   => $client->savings_balance,
        ], 200);

    } catch (\Exception $e) {
        // Rollback the transaction on error
        DB::rollBack();
        return response()->json(['errors' => 'Error processing payment: ' . $e->getMessage()], 500);
    }
}


public function topup(Request $request, $clientId)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'amount' => 'required|numeric|min:1',
        'note'   => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Fetch the client
    $client = Client::findOrFail($clientId);

    $paymentAmount = $request->input('amount');

    // Check if the amount being paid is equal to the client's current credit balance
    if (round($paymentAmount, 2) != round($client->credit_balance, 2)) {
        return response()->json([
            'errors' => ['amount' => ['Payment amount must be equal to the client\'s credit balance.']],
        ], 422);
    }

    // Begin transaction
    DB::beginTransaction();

    try {
        // Fetch all active loans for the client
        $loans = UserLoan::where('client_id', $clientId)
            ->where('status', '<>', 2) // Exclude fully paid loans
            ->orderBy('id', 'asc')
            ->get();

        if ($loans->isEmpty()) {
            return response()->json(['errors' => ['No active loans found for this client.']], 404);
        }

        // Process each loan
        foreach ($loans as $loan) {
            $loanBalance = $loan->final_amount - $loan->paid_amount;

            if ($loanBalance <= 0) {
                continue; // Skip if loan is already fully paid
            }

            // Update loan's paid amount to final amount (fully paid)
            $loan->paid_amount = $loan->final_amount;
            $loan->status = 2; // Fully Paid
            $loan->save();

            // Mark all remaining installments as 'paid'
            LoanPaymentInstallment::where('loan_id', $loan->id)
                ->whereIn('status', ['pending', 'withbalance'])
                ->update([
                    'status' => 'paid',
                    'installment_balance' => 0,
                ]);

            // Record the payment for this loan
            LoanPayment::create([
                'loan_id'        => $loan->id,
                'client_id'      => $clientId,
                'agent_id'       => $loan->user_id,
                'amount'         => $loanBalance,
                'credit_balance' => 0, // Will be updated after
                'payment_date'   => now(),
                'note'           => $request->input('note') ?? null,
            ]);
        }

        // Clear the client's credit balance
        $client->credit_balance = 0;
        $client->save();

        // Commit transaction
        DB::commit();

        return response()->json([
            'message'           => 'Top-up payment successful.',
            'remaining_balance' => $client->credit_balance,
            // Optionally, include updated loan history or other data
        ], 200);

    } catch (\Exception $e) {
        // Rollback transaction
        DB::rollBack();
        return response()->json(['errors' => ['An error occurred while processing the top-up.']], 500);
    }
}

public function storeClientFine(Request $request, $clientId)
{
    // Validate the incoming request data
    $validatedData = $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'reason' => 'required|string|max:255',
    ]);


    // Fetch the client
    $client = Client::findOrFail($clientId);

    // Begin a database transaction
    DB::beginTransaction();

    try {
        // Create the fine record
        $fine = ClientFine::create([
            'client_id' => $client->id,
            'added_by' => 1,
            'amount' => $request->amount,
            'reason' => $request->reason,
        ]);

        // Adjust the client's credit balance
        $client->credit_balance += $validatedData['amount']; // Change to -= if fines deduct balance
        $client->save();

        DB::commit();

        // Log the fine addition
        Log::info("Fine added to client ID {$client->id} by user ID " , ['fine_id' => $fine->id]);

        // Notify the client (optional)
        // $client->notify(new FineAddedNotification($fine));

        // Determine if the request expects JSON (AJAX)
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Fine added successfully.',
                'fine' => $fine,
                'new_credit_balance' => $client->credit_balance,
            ], 200);
        }

        // Redirect back with success message for non-AJAX requests
        return redirect()->back()->with('success', 'Fine added successfully.');
    } catch (\Exception $e) {
        DB::rollBack();

        // Log the error for debugging
        Log::error("Failed to add fine to client ID {$client->id}: " . $e->getMessage());

        // Determine if the request expects JSON (AJAX)
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Failed to add the fine. Please try again.',
            ], 500);
        }

        return redirect()->back()->withErrors(['error' => 'Failed to add the fine. Please try again.']);
    }
}



public function storeClientCollateral(Request $request, $clientId)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string',
        'file'        => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // Adjust file types and size as needed
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        // Begin transaction
        DB::beginTransaction();

        // Fetch the client
        $client = Client::findOrFail($clientId);

        // Handle the file upload
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            // Generate a unique file name
            $filename = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();

            // Store the file securely
            $filePath = $uploadedFile->storeAs('collaterals/' . $client->id, $filename, 'public');

            // Create the collateral record
            $collateral = ClientCollateral::create([
                'client_id'         => $client->id,
                'title'             => $request->input('title'),
                'description'       => $request->input('description'),
                'file_path'         => $filePath,
                'file_type'         => $uploadedFile->getClientMimeType(),
                'mime_type'         => $uploadedFile->getClientMimeType(),
                'original_filename' => $uploadedFile->getClientOriginalName(),
            ]);

            // Commit transaction
            DB::commit();

            return response()->json([
                'message'    => 'Collateral added successfully.',
                'collateral' => $collateral,
            ], 200);
        } else {
            return response()->json(['errors' => ['file' => ['No file uploaded.']]], 422);
        }
    } catch (\Exception $e) {
        // Rollback transaction
        DB::rollBack();

        // Log the error
        Log::error("Failed to add collateral for client ID {$clientId}: " . $e->getMessage(), [
            'client_id'     => $clientId,
            'user_id'       => Auth::id(),
            'error_message' => $e->getMessage(),
            'stack_trace'   => $e->getTraceAsString(),
        ]);

        return response()->json(['errors' => ['An error occurred while adding the collateral.']], 500);
    }
}


public function tellerIndex()
{
    // Example: if you want to load Agents for an agent filter
    $agents = User::where('is_active', 1)->get();

    return view('admin-views.Loans.teller.index', compact('agents'));
}

/**
 * Provide DataTables JSON for installments that are "due" (or "pending"/"withbalance")
 * within a given date range (e.g., today's date by default).
 */
public function tellerData2(Request $request)
{
    // 1) Build the query on "loan_payment_installments"
    $query = DB::table('loan_payment_installments')
        ->join('user_loans', 'user_loans.id', '=', 'loan_payment_installments.loan_id')
        ->join('clients', 'clients.id', '=', 'loan_payment_installments.client_id')
        ->leftJoin('users as agents', 'agents.id', '=', 'loan_payment_installments.agent_id')
        ->whereIn('loan_payment_installments.status', ['pending', 'withbalance'])
        ->select(
            'loan_payment_installments.id as installment_id',
            'loan_payment_installments.loan_id',
            'loan_payment_installments.install_amount',
            'loan_payment_installments.date as installment_date',
            'loan_payment_installments.status as installment_status',
            'clients.name as client_name',
            'clients.phone as client_phone',
            'clients.credit_balance as client_balance',
            'user_loans.trx as loan_trx',
            'user_loans.status as loan_status',
            DB::raw("CONCAT(agents.f_name, ' ', agents.l_name) as agent_full_name")
        );

    // 2) Filter by date range (or default to "today").
    //    Here we show an example "from - to" approach.
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate   = Carbon::parse($request->to_date)->endOfDay();
        $query->whereBetween('loan_payment_installments.date', [$fromDate, $toDate]);
    } else {
        // Example: Only today's installments
        $todayStart = Carbon::now()->startOfDay();
        $todayEnd   = Carbon::now()->endOfDay();
        $query->whereBetween('loan_payment_installments.date', [$todayStart, $todayEnd]);
    }

    // 3) Optional agent filter
    if ($request->filled('agent_id') && $request->agent_id != 'all') {
        $query->where('loan_payment_installments.agent_id', $request->agent_id);
    }

    // 4) Optional search filter (DataTables global search)
    $searchValue = $request->input('search.value');
    if ($searchValue) {
        $query->where(function($subQ) use ($searchValue) {
            $subQ->where('clients.name', 'LIKE', "%{$searchValue}%")
                 ->orWhere('clients.phone', 'LIKE', "%{$searchValue}%")
                 ->orWhere('user_loans.trx', 'LIKE', "%{$searchValue}%");
        });
    }

    // 5) Get the results before sorting/pagination
    $allResults = $query->get();

    // 6) Sorting
    $order   = $request->input('order', []);
    $columns = $request->input('columns', []);
    if (!empty($order)) {
        foreach ($order as $o) {
            $columnIndex = $o['column'];
            // The columns array might have data fields; adapt to your actual column names
            $columnName  = $columns[$columnIndex]['data'] ?? 'client_name';
            $dir         = $o['dir'] === 'asc' ? 'asc' : 'desc';

            $allResults = ($dir === 'asc')
                ? $allResults->sortBy($columnName)
                : $allResults->sortByDesc($columnName);
            $allResults = $allResults->values(); // reindex
        }
    } else {
        // Default sort by date ascending
        $allResults = $allResults->sortBy('installment_date')->values();
    }

    // 7) Pagination
    $recordsTotal = $allResults->count();
    $start  = (int)$request->input('start', 0);
    $length = (int)$request->input('length', 20);
    $pagedResults = $allResults->slice($start, $length)->values();

    // 8) Return JSON
    return response()->json([
        'draw'            => (int)$request->input('draw'),
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data'            => $pagedResults,
    ]);
}


public function getStats(Request $request)
{
    // Get filter parameters
    $agentId = $request->agent_id != 'all' ? $request->agent_id : null;
    
    // Today's payments
    $todayPaymentsQuery = LoanPayment::whereDate('payment_date', today());
        
    if ($agentId) {
        $todayPaymentsQuery->where('agent_id', $agentId);
    }
    
    $todayPaymentsCount = $todayPaymentsQuery->count();
    $todayPaymentsAmount = $todayPaymentsQuery->sum('amount');
    
    // Pending installments (due within next 7 days)
    $pendingQuery = LoanPaymentInstallment::whereBetween('date', [now(), now()->addDays(7)])
        ->where('status', 'pending');
        
    if ($agentId) {
        $pendingQuery->where('agent_id', $agentId);
    }
    
    $pendingCount = $pendingQuery->count();
    
    // Overdue installments
    $overdueQuery = LoanPaymentInstallment::where('date', '<', now())
        ->where('status', 'pending');
        
    if ($agentId) {
        $overdueQuery->where('agent_id', $agentId);
    }
    
    $overdueCount = $overdueQuery->count();
    
    return response()->json([
        'todayPayments' => $todayPaymentsCount,
        'todayPaymentsAmount' => $todayPaymentsAmount,
        'pendingCount' => $pendingCount,
        'overdueCount' => $overdueCount
    ]);
}
 

public function tellerData(Request $request)
{
    // 1) Build the base query: select installments with a status of 'pending' or 'withbalance'
    $query = DB::table('loan_payment_installments')
        ->join('user_loans', 'user_loans.id', '=', 'loan_payment_installments.loan_id')
        ->leftJoin('clients', 'clients.id', '=', 'loan_payment_installments.client_id')
        ->leftJoin('users as agents', 'agents.id', '=', 'loan_payment_installments.agent_id')
        ->select(
            'loan_payment_installments.id as installment_id',
            'loan_payment_installments.loan_id',
            'loan_payment_installments.install_amount',
            'loan_payment_installments.date as installment_date',
            'loan_payment_installments.status as installment_status',
            'loan_payment_installments.client_id',
            'clients.name as client_name',
            'clients.phone as client_phone',
            'clients.credit_balance as client_balance',
            'user_loans.trx as loan_trx',
            'user_loans.status as loan_status',
            DB::raw("CONCAT(agents.f_name, ' ', agents.l_name) as agent_full_name")
        )
        ->whereIn('loan_payment_installments.status', ['pending', 'withbalance']);

    // 2) Filter by date range if provided; otherwise default to today's date.
    if ($request->has('from_date') && $request->filled('from_date') &&
        $request->has('to_date') && $request->filled('to_date')) {
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate   = Carbon::parse($request->to_date)->endOfDay();
    } else {
        $fromDate = Carbon::today()->startOfDay();
        $toDate   = Carbon::today()->endOfDay();
    }
    $query->whereBetween('loan_payment_installments.date', [$fromDate, $toDate]);

    // 3) Agent filter: if an agent is selected, filter by that agent_id.
    if ($request->filled('agent_id') && $request->agent_id != 'all') {
        $query->where('loan_payment_installments.agent_id', $request->agent_id);
    }

    // 4) Global search filter: search against client name, phone, or loan transaction number.
    $searchValue = $request->input('search.value');
    if ($searchValue) {
        $query->where(function ($sub) use ($searchValue) {
            $sub->where('clients.name', 'LIKE', "%{$searchValue}%")
                ->orWhere('clients.phone', 'LIKE', "%{$searchValue}%")
                ->orWhere('user_loans.trx', 'LIKE', "%{$searchValue}%");
        });
    }

    // 5) Get all results before applying pagination.
    $allResults = $query->get();

    // 6) Sorting: if DataTables sends ordering parameters, sort the collection accordingly.
    $order   = $request->input('order', []);
    $columns = $request->input('columns', []);
    if (!empty($order)) {
        foreach ($order as $o) {
            $colIndex   = $o['column'];
            $columnName = $columns[$colIndex]['data'] ?? 'installment_date';
            $dir        = $o['dir'] === 'asc' ? 'asc' : 'desc';
            $allResults = ($dir === 'asc')
                ? $allResults->sortBy($columnName)
                : $allResults->sortByDesc($columnName);
            $allResults = $allResults->values();
        }
    } else {
        // Default sort: ascending by installment_date
        $allResults = $allResults->sortBy('installment_date')->values();
    }

    // 7) Pagination: DataTables sends the start index and length
    $recordsTotal = $allResults->count();
    $start  = (int) $request->input('start', 0);
    $length = (int) $request->input('length', 20);
    $pagedResults = $allResults->slice($start, $length)->values();

    // 8) Return JSON in the required DataTables format.
    return response()->json([
        'draw'            => (int) $request->input('draw'),
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data'            => $pagedResults,
    ]);
}



public function tellerPayLoan2(Request $request)
{
    $request->validate([
        'client_id' => 'required|exists:clients,id',
        'amount'    => 'required|numeric|min:1',
        'installment_id' => 'required|exists:loan_payment_installments,id',
        'note'      => 'nullable|string|max:255',
    ]);

    $client   = Client::findOrFail($request->client_id);
    $amount   = $request->input('amount');
    $installmentId = $request->input('installment_id');

    // 1) Check client credit balance if needed
    if ($amount > $client->credit_balance) {
        return response()->json(['error' => 'Payment exceeds client credit balance'], 400);
    }

    DB::beginTransaction();
    try {
        // 2) Find the installment
        $installment = LoanPaymentInstallment::with('loan')->find($installmentId);
        if (!$installment || !in_array($installment->status, ['pending','withbalance'])) {
            return response()->json(['error' => 'Installment is not pending or withbalance'], 400);
        }

        // 3) Update loan paid_amount
        $loan = $installment->loan;
        $loanBalance = $loan->final_amount - $loan->paid_amount;
        $amountToApply = min($amount, $loanBalance);
        $loan->paid_amount += $amountToApply;

        // Mark loan fully paid if everything is covered
        if ($loan->paid_amount >= $loan->final_amount) {
            $loan->status = 2; // fully paid
        }
        $loan->save();

        // 4) Update the installment
        $installmentDue = $installment->install_amount + $installment->installment_balance;
        if ($amountToApply >= $installmentDue) {
            // fully pay this installment
            $installment->status = 'paid';
            $installment->installment_balance = 0;
        } else {
            // partial
            $installment->status = 'withbalance';
            $installment->installment_balance = $installmentDue - $amountToApply;
        }
        $installment->save();

        // 5) Create LoanPayment record
        $loanPayment = LoanPayment::create([
            'loan_id'        => $loan->id,
            'client_id'      => $client->id,
            'agent_id'       => $loan->user_id,  // or teller user_id if relevant
            'amount'         => $amountToApply,
            'credit_balance' => $client->credit_balance,
            'payment_date'   => now(),
            'note'           => $request->input('note'),
        ]);

        // 6) Deduct from client’s credit balance
        $client->credit_balance -= $amountToApply;
        $client->save();

        DB::commit();

        // Return success
        return response()->json([
            'message' => 'Installment paid successfully',
            'loan_status' => $loan->status,
            'remaining_balance' => $client->credit_balance,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Teller Pay Loan Error: '.$e->getMessage());
        return response()->json(['error' => 'Something went wrong.'], 500);
    }
}


public function tellerPayLoan(Request $request)
{
    $request->validate([
        'client_id'      => 'required|exists:clients,id',
        'installment_id' => 'required|exists:loan_payment_installments,id',
        'amount'         => 'required|numeric|min:1',
        'note'           => 'nullable|string|max:255',
    ]);

    $client       = Client::findOrFail($request->client_id);
    $installment  = LoanPaymentInstallment::findOrFail($request->installment_id);
    $amountToPay  = $request->amount;

    if ($amountToPay > $client->credit_balance) {
        return response()->json(['error' => 'Amount exceeds client credit balance'], 400);
    }

    DB::beginTransaction();
    try {
        // Update loan
        $installment = LoanPaymentInstallment::findOrFail($request->installment_id);
        $loan = UserLoan::find($installment->loan_id);
        if (!$loan) {
            throw new \Exception('Loan not found for this installment.');
        }

        // $loan = $installment->loan;
        $loanBalance = $loan->final_amount - $loan->paid_amount;
        $actualPay = min($loanBalance, $amountToPay);
        $loan->paid_amount += $actualPay;

        if ($loan->paid_amount >= $loan->final_amount) {
            $loan->status = 2; // fully paid
        }
        $loan->save();

        // Update installment
        $dueForThisInstallment = $installment->install_amount + $installment->installment_balance;
        if ($actualPay >= $dueForThisInstallment) {
            $installment->status = 'paid';
            $installment->installment_balance = 0;
        } else {
            $installment->status = 'withbalance';
            $installment->installment_balance = $dueForThisInstallment - $actualPay;
        }
        $installment->save();

        // Create a LoanPayment record
        LoanPayment::create([
            'loan_id'        => $loan->id,
            'client_id'      => $client->id,
            'agent_id'       => $loan->user_id, // or teller user id
            'amount'         => $actualPay,
            'credit_balance' => $client->credit_balance,
            'payment_date'   => now(),
            'note'           => $request->note,
        ]);

        // Deduct from client's credit_balance
        $client->credit_balance -= $actualPay;
        $client->save();

        DB::commit();
        return response()->json(['message' => 'Payment processed successfully'], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Teller Pay Error: ".$e->getMessage());
        return response()->json(['error' => 'An error occurred while processing payment'], 500);
    }
}



public function collateralsList($clientId)
{
    try {
        // Fetch the client
        $client = Client::findOrFail($clientId);

        // Load collaterals
        $collaterals = $client->collaterals()->orderBy('created_at', 'desc')->get();

        // Render the collaterals list partial view
        $collateralsHtml = view('admin-views.clients.partials.collaterals-list', compact('collaterals'))->render();

        return response()->json(['html' => $collateralsHtml], 200);
    } catch (\Exception $e) {
        // Log the error
        Log::error("Failed to fetch collaterals for client ID {$clientId}: " . $e->getMessage(), [
            'client_id'     => $clientId,
            'user_id'       => 1,
            'error_message' => $e->getMessage(),
            'stack_trace'   => $e->getTraceAsString(),
        ]);

        return response()->json(['errors' => ['An error occurred while fetching the collaterals.']], 500);
    }
}

public function finesList(Client $client)
{
    // Load fines with the user who added them
    $client->load('fines.addedBy');

    // Render the fines list partial view
    $finesHtml = view('admin-views.clients.partials.fines-list', compact('client'))->render();

    return response()->json(['html' => $finesHtml], 200);
}



// banks the great
public function renewLoan(Request $request, $loanId)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $clientId = $validatedData['client_id'];

        // Fetch the client
        $client = Client::findOrFail($clientId);

        // Fetch the loan to renew
        $loan = UserLoan::where('client_id', $clientId)
            ->where('id', $loanId)
            ->where('status', 1) // Assuming status 1 is Running
            ->first();

        if (!$loan) {
            return response()->json(['error' => 'Loan not found or not eligible for renewal.'], 404);
        }

        // Check if loan is already renewed
        if ($loan->is_renewed) {
            return response()->json(['error' => 'Loan has already been renewed.'], 400);
        }

        // Get client's current credit balance
        $currentCreditBalance = $client->credit_balance;

        if ($currentCreditBalance <= 0) {
            return response()->json(['error' => 'Client has insufficient credit balance for renewal.'], 400);
        }

        // Begin a transaction
        DB::beginTransaction();

        try {
            // Update the loan's renewal fields
            $loan->is_renewed = true;
            $loan->renewed_amount = $currentCreditBalance;
            $loan->renewed_date = now()->toDateString();

            // Calculate the new final amount based on renewal logic (e.g., 20% interest)
            $interestRate = 1.2; // 20% interest
            $loan->final_renewed_to_amount = $currentCreditBalance * $interestRate;
            $loan->final_amount = $currentCreditBalance * $interestRate;
            // Optionally, update other fields as necessary
            // For example, resetting paid_amount or updating due_date
            $loan->paid_amount = 0; // Reset paid amount for the renewed loan
            $loan->status = 1; // Keep the status as Running
            $loan->disbursed_at = now();
            $loan->due_date = now()->addDays($loan->installment_interval ?? 30); // Default to 30 days if interval is null

            $loan->save();

            // Create a PaymentTransaction record for the renewal
            PaymentTransaction::create([
                'client_id' => $clientId,
                'loan_id' => $loan->id,
                'agent_id' => $loan->user_id,
                'transaction_id' => $this->generateTransactionId(),
                'payment_type' => 'renewal',
                'amount' => $currentCreditBalance,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // Deduct the renewal amount from the client's credit balance
            $client->credit_balance = $currentCreditBalance * $interestRate;
            $client->save();

            // Optionally, create new installments based on the renewed amount
            // This depends on your application logic

            DB::commit();

            return redirect()->back();

            // response()->json([
            //     'message' => 'Loan renewed successfully.',
            //     'loan' => $loan,
            //     'client' => $client,
            // ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            \Log::error('Loan Renewal Failed: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to renew the loan. Please try again later.'], 500);
        }
    }


/**
 * Apply payment to loan installments.
 *
 * @param int $loanId
 * @param float $paymentAmount
 * @return void
 */



private function applyPaymentToInstallments($loanId, $paymentAmount)
{
    $remainingPayment = $paymentAmount;

    // Fetch pending or withbalance installments for the loan, ordered by date
    $installments = LoanPaymentInstallment::where('loan_id', $loanId)
        ->whereIn('status', ['pending', 'withbalance'])
        ->orderBy('date', 'asc')
        ->get();

    foreach ($installments as $installment) {
        // Calculate the total amount due for the installment
        $installmentDue = $installment->install_amount + $installment->installment_balance;

        // Determine how much to pay towards this installment
        $amountToPay = min($remainingPayment, $installmentDue);

        // Update installment status and balance
        if ($amountToPay >= $installmentDue) {
            // Fully paid installment
            $installment->status = 'paid';
            $installment->installment_balance = 0;
        } else {
            // Partially paid installment
            $installment->status = 'withbalance';
            $installment->installment_balance = $installmentDue - $amountToPay;
        }

        $installment->save();

        // Deduct the amount paid towards this installment from the remaining payment
        $remainingPayment -= $amountToPay;

        if ($remainingPayment <= 0) {
            break;
        }
    }
}



public function updateLoanPayment(Request $request, $loanId)
{
    $validator = Validator::make($request->all(), [
        'payment_amount' => 'required|numeric|min:1',
        'note'           => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            return redirect()->back()->withErrors($validator->errors());
        }
    }

    DB::beginTransaction();
    try {
        $loan = UserLoan::findOrFail($loanId);
        $client = Client::findOrFail($loan->client_id);

        $paymentAmount = $request->input('payment_amount');
        $note = $request->input('note');

        // Check if Payment Amount Exceeds Client's Credit Balance
        if ($paymentAmount > $client->credit_balance) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['payment_amount' => 'Payment amount exceeds client\'s credit balance.']], 400);
            } else {
                return redirect()->back()->withErrors(['payment_amount' => 'Payment amount exceeds client\'s credit balance.']);
            }
        }

        // Calculate Total Owed for this loan
        $loanBalance = max($loan->final_amount - $loan->paid_amount, 0);

        $amountAppliedToLoan = min($paymentAmount, $loanBalance);
        $excessAmount = $paymentAmount - $amountAppliedToLoan;

        // Update Loan's Paid Amount
        $loan->paid_amount += $amountAppliedToLoan;

        // If fully paid
        if (round($loan->paid_amount, 2) >= round($loan->final_amount, 2)) {
            $loan->status = 2; // Fully Paid
            // Mark all remaining installments as 'paid'
            LoanPaymentInstallment::where('loan_id', $loan->id)
                ->whereIn('status', ['pending', 'withbalance'])
                ->update([
                    'status' => 'paid',
                    'installment_balance' => 0,
                ]);
        }

        $loan->save();

        // Apply Payment to Installments
        $this->applyPaymentToInstallments($loan->id, $amountAppliedToLoan);

        // If there's excess payment, convert it to advance installments
        if ($excessAmount > 0 && $loan->status != 2) {
            $installmentAmount = $loan->per_installment;
            $installmentsCovered = floor($excessAmount / $installmentAmount);
            $advanceAmount = $installmentsCovered * $installmentAmount;

            if ($installmentsCovered > 0) {
                // Check if there's an existing advance record for this loan
                $advance = LoanAdvance::where('loan_id', $loan->id)->first();

                if (!$advance) {
                    $advance = new LoanAdvance();
                    $advance->loan_id = $loan->id;
                    $advance->client_id = $client->id;
                    $advance->total_advance_amount = $advanceAmount;
                    $advance->remaining_advance_amount = $advanceAmount;
                    $advance->total_installments = $installmentsCovered;
                    $advance->remaining_installments = $installmentsCovered;
                } else {
                    // Update existing advance
                    $advance->total_advance_amount += $advanceAmount;
                    $advance->remaining_advance_amount += $advanceAmount;
                    $advance->total_installments += $installmentsCovered;
                    $advance->remaining_installments += $installmentsCovered;
                }

                $advance->save();

                // If there's any leftover after forming full installments, add to savings
                $remainingExcess = $excessAmount - $advanceAmount;
                if ($remainingExcess > 0) {
                    $client->savings_balance += $remainingExcess;
                }
            } else {
                // If no full installment could be formed, add the entire excess to savings
                $client->savings_balance += $excessAmount;
            }

        }

        // Deduct full payment amount from client's credit balance
        $client->credit_balance -= $paymentAmount;
        $client->save();

        // Record the Payment
        // LoanPayment::create([
        //     'loan_id'        => $loan->id,
        //     'client_id'      => $loan->client_id,
        //     'agent_id'       => $loan->user_id,
        //     'credit_balance' => $client->credit_balance,
        //     'amount'         => $paymentAmount,
        //     'payment_date'   => now(),
        //     'note'           => $note,
        // ]);


        // Record the Payment
        $loanPayment = LoanPayment::create([
            'loan_id'        => $loan->id,
            'client_id'      => $loan->client_id,
            'agent_id'       => $loan->user_id,
            'credit_balance' => $client->credit_balance,
            'amount'         => $paymentAmount,
            'payment_date'   => now(),
            'note'           => $note,
        ]);

        DB::commit();

        if ($request->ajax()) {
            // return response()->json(['message' => 'Payment processed successfully.'], 200);
            return response()->json([
                'message' => 'Payment processed successfully.',
                'transaction_id' => $loanPayment->id, // Include Transaction ID
            ], 200);
        } else {
            Toastr::success('Loan payment updated successfully');
            return redirect()->route('admin.loans.show', $loanId)->with('success', 'Payment processed successfully!');
        }

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error updating loan payment: ' . $e->getMessage(), [
            'loan_id' => $loanId,
            'amount'  => $request->input('payment_amount'),
        ]);

        if ($request->ajax()) {
            return response()->json(['errors' => ['server' => 'An error occurred while processing the payment.']], 500);
        } else {
            return redirect()->back()->withErrors(['server' => 'An error occurred while processing the payment.']);
        }
    }
}



public function updateLoanPayment0612(Request $request, $loanId)
{
    // **Validation**
    $validator = Validator::make($request->all(), [
        'payment_amount' => 'required|numeric|min:1',
        'payment_dates'  => 'nullable|string', // Validate as a string
        'note'           => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            return redirect()->back()->withErrors($validator->errors());
        }
    }

    // **Process Payment Dates**
    $paymentDates = [];
    if ($request->has('payment_dates') && !empty($request->input('payment_dates'))) {
        $dates = explode(',', $request->input('payment_dates'));
        foreach ($dates as $date) {
            try {
                $parsedDate = \Carbon\Carbon::createFromFormat('Y-m-d', trim($date));
                $paymentDates[] = $parsedDate->format('Y-m-d');
            } catch (\Exception $e) {
                if ($request->ajax()) {
                    return response()->json(['errors' => ['payment_dates' => 'Invalid date format.']], 422);
                } else {
                    return redirect()->back()->withErrors(['payment_dates' => 'Invalid date format.']);
                }
            }
        }
    }

    // **Start Database Transaction**
    DB::beginTransaction();

    try {
        // **Retrieve Loan and Client**
        $loan = UserLoan::findOrFail($loanId);
        $client = Client::findOrFail($loan->client_id);

        $paymentAmount = $request->input('payment_amount');

        // **Check if Payment Amount Exceeds Client's Credit Balance**
        if ($paymentAmount > $client->credit_balance) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['payment_amount' => 'Payment amount exceeds client\'s credit balance.']], 400);
            } else {
                return redirect()->back()->withErrors(['payment_amount' => 'Payment amount exceeds client\'s credit balance.']);
            }
        }

        // **Calculate Remaining Loan Balance**
        $loanBalance = $loan->final_amount - $loan->paid_amount;

        if ($loanBalance <= 0) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['loan' => 'This loan is already fully paid.']], 400);
            } else {
                return redirect()->back()->withErrors(['loan' => 'This loan is already fully paid.']);
            }
        }

        // **Determine Amount Applied to Loan and Excess**
        $amountAppliedToLoan = min($paymentAmount, $loanBalance);
        $excessAmount = $paymentAmount - $amountAppliedToLoan;

        // **Update Loan's Paid Amount**
        $loan->paid_amount += $amountAppliedToLoan;

        // **Update Loan Status if Fully Paid**
        if (round($loan->paid_amount, 2) >= round($loan->final_amount, 2)) {
            $loan->status = 2; // Fully Paid

            // **Update Installments to 'paid'**
            LoanPaymentInstallment::where('loan_id', $loan->id)
                ->whereIn('status', ['pending', 'withbalance'])
                ->update([
                    'status' => 'paid',
                    'installment_balance' => 0,
                ]);
        }

        $loan->save();

        // **Apply Payment to Installments**
        $this->applyPaymentToInstallments($loan->id, $amountAppliedToLoan);

        // **Update Client's Credit Balance**
        $client->credit_balance -= $paymentAmount; // Deduct the entire payment amount

        // **Handle Excess Amount by Adding to Advances**
        if ($excessAmount > 0) {
            $installmentAmount = $loan->per_installment;
            $installmentsCovered = floor($excessAmount / $installmentAmount);
            $advanceAmount = $installmentsCovered * $installmentAmount;

            if ($installmentsCovered > 0) {
                // **Create Loan Advance Record**
                LoanAdvance::create([
                    'loan_id'                   => $loan->id,
                    'client_id'                 => $client->id,
                    'total_advance_amount'      => $advanceAmount,
                    'remaining_advance_amount'  => $advanceAmount,
                    'total_installments'        => $installmentsCovered,
                    'remaining_installments'    => $installmentsCovered,
                ]);
            }

            // **Handle Any Remaining Excess Amount**
            $remainingExcess = $excessAmount - $advanceAmount;

            if ($remainingExcess > 0) {
                // Optionally, add to savings or handle accordingly
                $client->savings_balance += $remainingExcess;
            }
        }

        $client->save();

        // **Log the Payment Dates**
        if (!empty($paymentDates)) {
            \Log::info('Payment Dates for Loan ID ' . $loanId . ':', $paymentDates);
        } else {
            \Log::info('No payment dates provided for Loan ID ' . $loanId . '.');
        }

        // **Record the Payment**
        LoanPayment::create([
            'loan_id'        => $loan->id,
            'client_id'      => $loan->client_id,
            'agent_id'       => $loan->user_id,
            'credit_balance' => $client->credit_balance,
            'amount'         => $paymentAmount, // Original payment amount
            'payment_date'   => now(),
            'note'           => $request->input('note') ?? null,
        ]);

        // **Commit the Transaction**
        DB::commit();

        // **Return Success Response**
        if ($request->ajax()) {
            return response()->json(['message' => 'Payment processed successfully.'], 200);
        } else {
            Toastr::success('Loan payment updated successfully');
            return redirect()->route('admin.loans.show', $loanId)->with('success', 'Payment processed successfully!');
        }

    } catch (\Exception $e) {
        // **Rollback the Transaction on Error**
        DB::rollBack();

        // **Log the Error**
        \Log::error('Error updating loan payment: ' . $e->getMessage(), [
            'loan_id'   => $loanId,
            'client_id' => $loan->client_id,
            'amount'    => $request->input('payment_amount'),
        ]);

        if ($request->ajax()) {
            return response()->json(['errors' => ['server' => 'An error occurred while processing the payment. Please try again later.']], 500);
        } else {
            return redirect()->back()->withErrors(['server' => 'An error occurred while processing the payment. Please try again later.']);
        }
    }
}




public function loanAdvancesIndex()
{
    // Return the blade view that lists the loan advances.
    // Make sure 'admin-views.Loans.advances.index' is the correct path to your view.
    return view('admin-views.Loans.advances.index');
}

public function listLoanAdvances(Request $request)
{
    $query = DB::table('loan_advances')
        ->join('user_loans', 'user_loans.id', '=', 'loan_advances.loan_id')
        ->join('clients', 'clients.id', '=', 'loan_advances.client_id')
        ->leftJoin('users as agents', 'agents.id', '=', 'user_loans.user_id')
        ->where('user_loans.status', 1) // Running loans
        ->select(
            'clients.id as client_id',
            'clients.name as client_name',
            'clients.phone as client_phone',
            'user_loans.id as loan_id',
            'user_loans.trx as loan_transaction_id',
            'agents.f_name as agent_first_name',
            'agents.l_name as agent_last_name',
            'loan_advances.total_advance_amount',
            'loan_advances.remaining_advance_amount',
            'loan_advances.total_installments',
            'loan_advances.remaining_installments'
        );

    // Searching
    if ($searchValue = $request->input('search.value')) {
        $query->where(function($q) use ($searchValue) {
            $q->where('clients.name', 'like', "%{$searchValue}%")
              ->orWhere('clients.phone', 'like', "%{$searchValue}%")
              ->orWhere('user_loans.trx', 'like', "%{$searchValue}%");
        });
    }

    $recordsTotal = $query->count();

    // Ordering
    $order = $request->input('order', []);
    $columns = $request->input('columns', []);
    if (!empty($order)) {
        foreach ($order as $o) {
            $columnIndex = $o['column'];
            $columnName = $columns[$columnIndex]['data'] ?? 'client_name';
            $dir = $o['dir'] == 'asc' ? 'asc' : 'desc';
            $query->orderBy($columnName, $dir);
        }
    } else {
        $query->orderBy('client_name', 'asc');
    }

    // Pagination: 20 per page
    $start = $request->input('start', 0);
    $length = $request->input('length', 20);
    if ($length != -1) {
        $query->skip($start)->take($length);
    }

    $advances = $query->get();

    return response()->json([
        'draw' => $request->input('draw'),
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $advances
    ]);
}


public function storeClientLoan(Request $request)
{
    // Check if guarantors are required based on the business setting
    $isGuarantorRequired = \App\CentralLogics\Helpers::get_business_settings('is_loan_guarantor_a_must');

    // Define validation rules
    $validationRules = [
        'client_id' => 'required|exists:clients,id',
        'agent_id' => 'required|exists:users,id',
        'plan_id' => 'required|exists:loan_plans,id',
        'amount' => 'required|numeric|min:0',
        'installment_interval' => 'required|numeric|min:1',
        'paid_amount' => 'nullable|numeric|min:0',
        'next_installment_date' => 'nullable|date',
        'note' => 'nullable|string|max:255',
        'taken_date' => 'nullable|date',
    ];

    // Add guarantor validation rules only if required
    if ($isGuarantorRequired) {
        $validationRules['guarantors'] = 'required|array|min:1';
        $validationRules['guarantors.*'] = 'exists:guarantors,id';
    }

    // Validate the request data
    $validatedData = $request->validate($validationRules);

    // Fetch the loan plan based on the plan_id
    $loanPlan = LoanPlan::findOrFail($validatedData['plan_id']);

    // Dynamic calculation based on the loan plan details
    $per_installment = ($validatedData['amount'] * (1 + ($loanPlan->installment_value / 100))) / $validatedData['installment_interval'];
    $final_amount = $validatedData['amount'] * (1 + ($loanPlan->installment_value / 100));

    // Create a new UserLoan instance
    $loan = new UserLoan();
    $loan->user_id = $validatedData['agent_id'];
    $loan->plan_id = $loanPlan->id;
    $loan->trx = $this->generateUniqueTrx();
    $loan->amount = $validatedData['amount'];
    $loan->per_installment = $per_installment;
    $loan->installment_interval = $validatedData['installment_interval'];
    $loan->total_installment = $validatedData['installment_interval'];
    $loan->processing_fee = $validatedData['paid_amount'] ?? 0.00;
    $loan->final_amount = $final_amount;
    $loan->loan_taken_date = $validatedData['taken_date'];
    $loan->client_id = $validatedData['client_id'];
    $loan->status = 0; // Default status
    $loan->next_installment_date = $validatedData['next_installment_date'] ?? null;
    $loan->save();

    // Attach the selected guarantors to the loan if guarantors are required
    if ($isGuarantorRequired && isset($validatedData['guarantors'])) {
        $loan->guarantors()->attach($validatedData['guarantors']);
    }

    // If there is a paid amount, create a LoanPayment record
    if (!empty($validatedData['paid_amount']) && $validatedData['paid_amount'] > 0) {
        LoanPayment::create([
            'loan_id' => $loan->id,
            'client_id' => $validatedData['client_id'],
            'agent_id' => $validatedData['agent_id'],
            'amount' => $validatedData['paid_amount'],
            'payment_date' => now(),
            'note' => $validatedData['note'] ?? null,
        ]);
    }

    // Redirect back with success message
    return redirect()->route('admin.loan-pendingLoans')->with('success', 'Loan added successfully' . ($isGuarantorRequired ? ' along with guarantors.' : '.'));
}


    public function storeClientLoan2DEC(Request $request)
{
    // Validate the request data, including guarantors
    $validatedData = $request->validate([
        'client_id' => 'required|exists:clients,id',
        'agent_id' => 'required|exists:users,id',
        'plan_id' => 'required|exists:loan_plans,id',
        'amount' => 'required|numeric|min:0',
        'installment_interval' => 'required|numeric|min:1',
        'paid_amount' => 'nullable|numeric|min:0',
        'next_installment_date' => 'nullable|date',
        'note' => 'nullable|string|max:255',
        'taken_date' => 'nullable|date',
        'guarantors' => 'required|array|min:1',
        'guarantors.*' => 'exists:guarantors,id',
    ]);

    // Fetch the loan plan based on the plan_id
    $loanPlan = LoanPlan::findOrFail($validatedData['plan_id']);

    // Dynamic calculation based on the loan plan details
    $per_installment = ($validatedData['amount'] * (1 + ($loanPlan->installment_value / 100))) / $validatedData['installment_interval'];
    $final_amount = $validatedData['amount'] * (1 + ($loanPlan->installment_value / 100));

    // Create a new UserLoan instance
    $loan = new UserLoan();
    $loan->user_id = $validatedData['agent_id'];
    $loan->plan_id = $loanPlan->id;
    $loan->trx = $this->generateUniqueTrx();
    $loan->amount = $validatedData['amount'];
    $loan->per_installment = $per_installment;
    $loan->installment_interval = $validatedData['installment_interval'];
    $loan->total_installment = $validatedData['installment_interval'];
    $loan->processing_fee = $validatedData['paid_amount'] ?? 0.00;
    $loan->final_amount = $final_amount;
    $loan->loan_taken_date = $validatedData['taken_date'];
    $loan->client_id = $validatedData['client_id'];
    $loan->status = 0; // Default status
    $loan->next_installment_date = $validatedData['next_installment_date'] ?? null;
    $loan->save();

    // Attach the selected guarantors to the loan
    $loan->guarantors()->attach($validatedData['guarantors']);

    // If there is a paid amount, create a LoanPayment record
    if (!empty($validatedData['paid_amount']) && $validatedData['paid_amount'] > 0) {
        LoanPayment::create([
            'loan_id' => $loan->id,
            'client_id' => $validatedData['client_id'],
            'agent_id' => $validatedData['agent_id'],
            'amount' => $validatedData['paid_amount'],
            'payment_date' => now(),
            'note' => $validatedData['note'] ?? null,
        ]);
    }

    // Redirect back with success message
    return redirect()->route('admin.loan-pendingLoans')->with('success', 'Loan added successfully along with guarantors.');
}



    public function createLoan(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'trx' => 'nullable|string|max:40',
            'amount' => 'required|numeric|min:0',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }

        // Fetch the client and agent
        $client = Client::find($request->client_id);
        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        // $agent = User::findOrFail($request->user_id)->where('is_active', 1)->get();
        $agent = User::where('id', $request->user_id)->where('is_active', 1)->firstOrFail();


        // Calculate per installment and final amount
        $per_installment = ($request->amount * 1.2) / $request->installment_interval;
        $final_amount = $request->amount * 1.2;

        // Calculate remaining amount
        $remaining_amount = $final_amount - ($request->paid_amount ?? 0);

        // Create a new UserLoan instance
        $loan = new UserLoan();
        $loan->user_id = $request->user_id;
        $loan->plan_id = 8;  // Assuming plan_id is static or predefined
        $loan->trx = $request->trx; // Generate unique transaction ID
        $loan->amount = $request->amount;
        $loan->per_installment = $per_installment;
        $loan->installment_interval = $request->installment_interval;
        $loan->total_installment = $request->installment_interval;
        $loan->paid_amount = $request->paid_amount ?? 0.00;
        $loan->final_amount = $final_amount;
        $loan->user_details = $request->user_details ?? null;
        $loan->admin_feedback = null;  // Assuming this field is optional
        $loan->status = 0;  // Assuming default status is 0
        $loan->next_installment_date = $request->next_installment_date ?? null;
        $loan->client_id = $request->client_id; // Assign the client ID
        $loan->save();  // Save the loan to the database

        // Create a new agent loan record
        $agentLoan = new AgentLoan();
        $agentLoan->user_id = $request->user_id;
        $agentLoan->client_id = $request->client_id;
        $agentLoan->loan_amount = $request->amount;
        $agentLoan->final_loan_amount = $final_amount;
        $agentLoan->save();


         // Check if paid amount is greater than 0 to create a LoanPayment record
        if ($request->paid_amount > 0) {
            // Create a record for the payment made
            LoanPayment::create([
                'loan_id' => $loan->id,
                'client_id' => $request->client_id,
                'agent_id' => $request->agent_id,
                'amount' => $request->paid_amount, // Use the correct paid amount
                'payment_date' => now(), // Current date/time as the payment record date
                // 'note' => $validatedData['note'] ?? null, // Include optional note if provided
            ]);
        }

        return response()->json(response_formatter(DEFAULT_200, $loan, null), 200);
    }



    function generateUniqueTrx()
    {
            do {
                // Generate a random transaction ID
                $trx = 'TRX' . Str::random(8);

                // Check if the trx already exists in the UserLoan table
                $exists = UserLoan::where('trx', $trx)->exists();
            } while ($exists);

            return $trx;
    }



    public function editLoan($id) {
            // Find the loan by ID
            $loan = UserLoan::findOrFail($id);

            // Retrieve the related client and loan plan
            $client = Client::find($loan->client_id);
            $loanPlan = LoanPlan::find($loan->plan_id);

            // Pass the loan, client, and loan plan data to the view
            return view('admin-views.Loans.edit', compact('loan', 'client', 'loanPlan'));
    }




    public function saveLoanEdit(Request $request)
    {

                $loan = UserLoan::findOrFail($request->id);

                // Check if the loan is in a pending or running state (status 0 or 1)
                if ($loan->status != 0 && $loan->status != 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Loan cannot be edited in its current state.',
                    ]);
                }

                // Update the loan details
                $loan->amount = $request->amount;
                $loan->per_installment = $request->per_installment;
                $loan->installment_interval = $request->installment_interval;
                $loan->total_installment = $request->installment_interval;
                $loan->final_amount = $loan->per_installment * $loan->total_installment;
                $loan->processing_fee = $request->processing_fee;


                // Save the changes
                $loan->save();

                // Redirect to the loan details view after saving the changes
                return $this->showLoan($loan->id);
    }


     public function showLoan($id)
    {
        // Fetch the loan by ID
        $loan = UserLoan::findOrFail($id);

        // Retrieve client details using the client_id
        $client = Client::find($loan->client_id);

        $agent = User::find($loan->user_id);

        // Retrieve loan plan details using the plan_id
        $loanPlan = LoanPlan::find($loan->plan_id);

        // CLIENT guarantors
        $clientGuarantors =   Guarantor::where('client_id', $loan->client_id)->get();
        $loanSlots =  LoanPaymentInstallment::where('client_id', $loan->client_id)->get();



        // Pass the loan, client, and loan plan data to the view
        return view('admin-views.Loans.view', [
            'loan' => $loan,
            'client' => $client,
            'loanPlan' => $loanPlan,
            'agent' => $agent,
            'clientGuarantors' => $clientGuarantors,
            'loanSlots' => $loanSlots
        ]);
    }


    // on loan approval, create payment slots/plan
    public function approveLoan(Request $request)
    {

            $loan = UserLoan::findOrFail($request->id);
            $client = Client::find($loan->client_id);
            $clientGuarantors =   Guarantor::where('client_id', $loan->client_id)->get();

            // Check if the loan is in a pending state (status 0)
            if ($loan->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan is not in a pending state.',
                ]);
            }


             // Check if the client has no guarantors
            // if ($clientGuarantors->isEmpty()) {
            //         //   return response()->warning('Client has no guarantors.'); // Assuming you have a 'warning' response helper
            //         Toastr::error(translate('Client has no guarantors.'));
            //       return back();
            // }
             // Check if guarantors are required based on the business setting
            $isGuarantorRequired = \App\CentralLogics\Helpers::get_business_settings('is_loan_guarantor_a_must');

            if ($isGuarantorRequired && $clientGuarantors->isEmpty()) {
                Toastr::error(translate('Client has no guarantors.'));
                return back();
            }

            // Check if client credit balance is greater than 0
            if ($client->credit_balance != 0) { // Assuming you have a 'credit_balance' column on your Client model
                Toastr::error(translate('Client has a positive credit balance.'));
                return back();
            }

            if ($loan->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan is not in a pending state.',
                ]);
            }

          // Loan taken at
            $loanTakenDate = Carbon::parse($loan->loan_taken_date); //$loan->loan_taken_date;

            // Update the loan status to 'Running' (status 1)
            $loan->status = 1;
            $loan->disbursed_at = $loanTakenDate;
            $loan->due_date = $loanTakenDate->copy()->addDays($loan->installment_interval);

            // set the client credit balance to the add the loan

            // Set the next installment date (assuming today is the approval date)
            $loan->next_installment_date = $loanTakenDate->copy()->addDays($loan->installment_interval);

            // Save the changes
            $loan->save();


           // Set the next installment date (assuming today is the approval date)
            $loan->next_installment_date = $loanTakenDate->copy()->addDays($loan->installment_interval);
            $loan->save();

            // Update the client's credit balance by adding the loan amount
            $client->credit_balance = isset($client->credit_balance) ? $client->credit_balance + $loan->final_amount : $loan->final_amount;
            $client->save();


            // Generate payment installments
            $this->createPaymentInstallments($loan);

            return back();
    }




    public function partialLoanApprovalX(Request $request)
    {
        // Retrieve the loan and associated client
        $loan = UserLoan::findOrFail($request->id);
        $client = Client::findOrFail($loan->client_id);
        $clientGuarantors = Guarantor::where('client_id', $loan->client_id)->get();

        // Check if the loan is in a pending state (status 0) or partially disbursed (status 4)
        if ($loan->status != 0 && $loan->status != 4) {
            Toastr::error('Loan cannot be disbursed in its current state.');
            return back();
        }

        // Check if the client has guarantors
        if ($clientGuarantors->isEmpty()) {
            Toastr::error('Client has no guarantors.');
            return back();
        }

        // Validate the partial disbursement amount
        $partialDisbursementAmount = $request->input('partial_disbursement_amount');
        if ($partialDisbursementAmount <= 0) {
            Log::error('Invalid Partial Disbursement Amount: ' . $partialDisbursementAmount);
            Toastr::error('Invalid partial disbursement amount.');
            return back();
        }

        // Calculate the new total disbursed amount
        $newTotalDisbursed = $loan->partial_disbursement_amount + $partialDisbursementAmount;




        // Check if the new total disbursed amount exceeds the loan amount
        if ($newTotalDisbursed > $loan->amount) {
            Toastr::error('Total disbursed amount cannot exceed loan amount.');
            return back();
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Update loan with new total disbursed amount
            $loan->partial_disbursement_amount = $newTotalDisbursed;

            if ($newTotalDisbursed == $loan->amount) {
                // Fully disbursed
                $loan->status = 1; // Running
                $loan->disbursed_at = Carbon::now();
                $loanTakenDate = Carbon::now();

                $loan->due_date = $loanTakenDate->copy()->addDays($loan->installment_interval * $loan->total_installment);
                $loan->next_installment_date = $loanTakenDate->copy()->addDays($loan->installment_interval);

                $loan->save();
                $client->credit_balance  =  $loan->final_amount;
                $client->save();

                $this->createPaymentInstallments($loan);

                $message = 'Loan fully disbursed and is now running.';
            } else {
                // Partially disbursed
                $loan->status = 4; // Partially Disbursed
                $loan->is_partial_disbursement = 1;
                $loan->save();

                Log::info('Client Credit Balance Before Update: ' . $client->credit_balance);
                $client->credit_balance += $partialDisbursementAmount;
                $client->save();
                Log::info('Client Credit Balance After Update: ' . $client->credit_balance);


                $message = 'Partial disbursement processed successfully.';
            }

            // Update the client's credit balance

            // Commit transaction
            DB::commit();
            Toastr::success($message);
            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Partial disbursement error: ' . $e->getMessage());
            Toastr::error('An error occurred during partial disbursement.');
            return back();
        }
    }





    // payment slots created after the loan is approved
    protected function createPaymentInsta(UserLoan $loan)
        {
            $installmentAmount = $loan->per_installment;
            $totalInstallments = $loan->total_installment;

            for ($i = 0; $i < $totalInstallments; $i++) {
                $installmentDate = now()->addDays($i); // Add days incrementally for daily installments

                LoanPaymentInstallment::create([
                    'loan_id' => $loan->id,
                    'agent_id' => $loan->user_id,
                    'client_id' => $loan->client_id,
                    'install_amount' => $installmentAmount,
                    'date' => $installmentDate,
                    'status' => 'pending', // Initially set status as 'pending'
                ]);
            }
        }


protected function createPaymentInstallments(UserLoan $loan)
{
    $installmentAmount = $loan->per_installment;
    $installmentInterval = $loan->installment_interval;
    $totalInstallments = $loan->total_installment;
    $loanTakenDate = Carbon::parse($loan->loan_taken_date); //$loan->loan_taken_date;


    for ($i = 1; $i <= $totalInstallments; $i++) {
        // Calculate the base installment date
        $installmentDate = $loanTakenDate->copy()->addDays($i);

        // Adjust the time to 11 AM to ensure it falls within the business day
        $installmentDate->setTime(11, 0);

        // Save the installment
        LoanPaymentInstallment::create([
            'loan_id' => $loan->id,
            'agent_id' => $loan->user_id,
            'client_id' => $loan->client_id,
            'install_amount' => $installmentAmount,
            'date' => $installmentDate,
            'status' => 'pending', // Initially set status as 'pending'
        ]);
    }
}












   public function getClientQr(Request $request): JsonResponse
{
    // Retrieve a single client record
    $customer = Client::where('id', $request->client_id)->first();

    // Check if the customer exists
    if ($customer) {
        $data = [];
        $data['name'] = $customer->name;
        $data['phone'] = $customer->phone;
        $data['clientid'] = $customer->id;
        $data['image'] = $customer->image;

        $qr = Helpers::get_qrcode_client($data);



        // Return the response with customer data
        return response()->json([
            'qr_code' => strval($qr),
        ], 200);
    } else {
        // Handle case where the customer was not found
        return response()->json([
            'message' => 'Client not found',
        ], 404);
    }
}






    protected function generateTransactionId()
        {
            do {
                $transactionId = 'abROi' . mt_rand(1000000000, 9999999999);
            } while (PaymentTransaction::where('transaction_id', $transactionId)->exists());

            return $transactionId;
        }


// pay loan tests





public function payLoanNew(Request $request): JsonResponse
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    // Get input values
    $clientId = $request->input('client_id');
    $amountPaid = $request->input('amount');
    $agentId = $request->input('agent_id'); // Optional
    $transactionId = $this->generateTransactionId();
    $paymentType = 'loan'; // Default to loan
    $today = now()->toDateString();

    // Fetch client details
    $client = Client::find($clientId);
    if (!$client) {
        return response()->json(['errors' => 'Client not found'], 404);
    }

    // Find the running loan for the client
    $loan = UserLoan::where('client_id', $clientId)
        ->where('status', '<>', 2) // Exclude fully paid loans
        ->first();

    if (!$loan) {
        return response()->json(['errors' => 'No running loans found for the client'], 404);
    }

    // Fetch the agent associated with the loan
    $agent = User::find($loan->user_id);

    // Fetch all installments for the current loan
    $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
        ->where('status', 'pending') // Only consider pending installments
        ->get();

    // If no installments are found, return an error
    if ($installments->isEmpty()) {
        return response()->json(['errors' => 'No pending installments found for the loan'], 404);
    }

    // Process payment for available installments
    $totalPaid = $amountPaid;

    foreach ($installments as $installment) {
        if ($totalPaid <= 0) {
            break;
        }

        // Mark the installment as paid and deduct from the total paid amount
        $installmentAmount = min($installment->install_amount, $totalPaid);
        $installment->status = 'paid';
        $installment->save();

        // Update the loan's paid amount
        $loan->paid_amount += $installmentAmount;

        // Create payment transaction record
        PaymentTransaction::create([
            'client_id' => $clientId,
            'loan_id' => $loan->id,
            'agent_id' => $agentId, // Optional
            'transaction_id' => $transactionId,
            'payment_type' => $paymentType,
            'amount' => $installmentAmount,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Deduct from the total paid amount
        $totalPaid -= $installmentAmount;

        // If the loan is fully paid, update the status
        if ($loan->paid_amount >= $loan->final_amount) {
            $loan->status = 2; // Fully paid loan
            $loan->save();
            break;
        }
    }

    // Return success response
    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Loan installment(s) paid successfully',
    ], 200);
}






public function payLoan111e(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:0',
        'agent_id' => 'nullable|exists:agents,id', // Optional, only if there's an agent involved
        'transaction_id' => 'required|unique:payment_transactions,transaction_id',
        'payment_type' => 'required|string', // e.g., cash, card, mobile, etc.
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 403);
    }

    $clientId = $request->input('client_id');
    $amountPaid = $request->input('amount');
    $agentId = $request->input('agent_id');
    $transactionId = $request->input('transaction_id');
    $paymentType = $request->input('payment_type');
    $today = now()->toDateString();

    // Record the payment transaction
    $paymentTransaction = PaymentTransaction::create([
        'client_id' => $clientId,
        'amount' => $amountPaid,
        'agent_id' => $agentId,
        'transaction_id' => $transactionId,
        'payment_type' => $paymentType,
        'status' => 'completed',
        'paid_at' => now(),
    ]);

    // The rest of the payLoan logic remains the same
    // Get running loans for the client
    $loans = UserLoan::where('client_id', $clientId)
        ->where('status', '<>', 2) // Exclude fully paid loans
        ->get();

    if ($loans->isEmpty()) {
        return response()->json(['errors' => 'No running loans found for the client'], 404);
    }

    $totalPaid = $amountPaid;

    foreach ($loans as $loan) {
        // Get today's installments for the current loan
        $installments = LoanPaymentInstallment::where('loan_id', $loan->id)
            ->where('date', $today)
            ->where('status', 'pending')
            ->get();

        if ($installments->isEmpty()) {
            continue;
        }

        foreach ($installments as $installment) {
            // Update the installment status to 'paid' and the amount paid
            $installment->status = 'paid';
            $installment->save();

            // Update the loan's paid amount
            $loan->paid_amount += $installment->install_amount;

            // Associate the payment transaction with the loan
            $paymentTransaction->loan_id = $loan->id;
            $paymentTransaction->save();

            // If the total paid amount is equal to or exceeds the final amount, update the loan status
            if ($loan->paid_amount >= $loan->final_amount) {
                $loan->status = 2; // Status 2 indicates a fully paid loan
            }

            $loan->save();

            // Deduct the installment amount from the total amount paid
            $totalPaid -= $installment->install_amount;

            // If the total amount paid covers the current installment, continue to the next
            if ($totalPaid <= 0) {
                break;
            }
        }

        // If the total amount paid covers all installments, break the loop
        if ($totalPaid <= 0) {
            break;
        }
    }

    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Loan installment(s) paid successfully'], 200);
}




   public function todaysLoanInstallments(): JsonResponse
    {
        // Get today's date in Y-m-d format
        $today = now()->toDateString();

        // Query LoanPaymentInstallment for today's date and pending status todaysLoanInstallments
    $installments = LoanPaymentInstallment::where('date', $today)
        ->where('status', 'pending')
        ->get();

        // Check if there are any installments for today
        if ($installments->isEmpty()) {
            return response()->json(['message' => 'No installments due today'], 404);
        }

        // Return the installments
        return response()->json(response_formatter(DEFAULT_200, $installments, null), 200);



    }


    // today schedule
    public function todaysSchedule2(Request $request): JsonResponse
    {
        // Get today's date in Y-m-d format
        $today = now()->toDateString();

        $agentId = $request->input('agent_id');

        // Query LoanPaymentInstallment for today's date and pending status todaysLoanInstallments
        $installments = LoanPaymentInstallment::where('agent_id', $agentId)
            ->where('date', $today)
            ->get();




            // Prepare the response data with client details
    $responseData = [];

    foreach ($installments as $installment) {
        // Get client details for each installment
        $client = Client::find($installment->client_id);
        $clientBalance =  $client->credit_balance;

        $responseData[] = [
            'id' => $installment->id,
            'loan_id' => $installment->loan_id,
            'agent_id' => $installment->agent_id,
            'client_id' => $installment->client_id,
            'client_name' => $client ? $client->name : null, // Assuming 'name' is a field in the clients table
            'client_phone' => $client ? $client->phone : null, // Assuming 'phone' is a field in the clients table
            'install_amount' => $installment->install_amount,
            'date' => $installment->date,
            'balance' => $clientBalance,
            'status' => $installment->status,
            'created_at' => $installment->created_at,
            'updated_at' => $installment->updated_at,
        ];
    }

     return response()->json([
        'response_code' => 'default_200',
        'message' => 'Successfully fetched data',
        'DataContent' => $responseData
    ], 200);

     // Return the installments with client details
    // return response()->json(response_formatter(DEFAULT_200, $responseData, null), 200);
    }

    public function todaysSchedule(Request $request): JsonResponse
        {
            // Get the start and end date-time for today using getDateRange2
            [$startDate, $endDate] = $this->getDateRange2('daily');

            $agentId = $request->input('agent_id');

            // Query LoanPaymentInstallment for today's date range
            $installments = LoanPaymentInstallment::where('agent_id', $agentId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            // Prepare the response data with client details
            $responseData = [];

            foreach ($installments as $installment) {
                // Get client details for each installment
                $client = Client::find($installment->client_id);
                $clientBalance = $client ? $client->credit_balance : null;

                $responseData[] = [
                    'id' => $installment->id,
                    'loan_id' => $installment->loan_id,
                    'agent_id' => $installment->agent_id,
                    'client_id' => $installment->client_id,
                    'client_name' => $client ? $client->name : null,
                    'client_phone' => $client ? $client->phone : null,
                    'install_amount' => $installment->install_amount,
                    'date' => $installment->date,
                    'balance' => $clientBalance,
                    'status' => $installment->status,
                    'created_at' => $installment->created_at,
                    'updated_at' => $installment->updated_at,
                ];
            }

            return response()->json([
                'response_code' => 'default_200',
                'message' => 'Successfully fetched data',
                'DataContent' => $responseData
            ], 200);
        }

        /**
         * Get the start and end date-time based on the period.
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
                        $now->copy()->startOfWeek()->setTime(8, 0),
                        $now->copy()->endOfWeek()->addDay()->setTime(7, 59, 59)
                    ];
                case 'monthly':
                    return [
                        $now->copy()->startOfMonth()->setTime(8, 0),
                        $now->copy()->endOfMonth()->addDay()->setTime(7, 59, 59)
                    ];
                case 'daily':
                default:
                    if ($now->hour < 8) {
                        // If current time is before 8 AM, set start to yesterday 8 AM
                        return [
                            $now->copy()->subDay()->setTime(8, 0),
                            $now->copy()->setTime(7, 59, 59)
                        ];
                    } else {
                        // If current time is after 8 AM, set start to today 8 AM
                        return [
                            $now->copy()->setTime(8, 0),
                            $now->copy()->addDay()->setTime(7, 59, 59)
                        ];
                    }
            }
        }


    // private function getDateRange2($period)
    //     {
    //         $now = Carbon::now();

    //         switch ($period) {
    //             case 'weekly':
    //                 return [
    //                     $now->copy()->startOfWeek()->setTime(8, 0),
    //                     $now->copy()->endOfWeek()->addDay()->setTime(7, 59, 59)
    //                 ];
    //             case 'monthly':
    //                 return [
    //                     $now->copy()->startOfMonth()->setTime(8, 0),
    //                     $now->copy()->endOfMonth()->addDay()->setTime(7, 59, 59)
    //                 ];
    //             case 'daily':
    //             default:
    //                 if ($now->hour < 8) {
    //                     // If current time is before 8 AM, set start to yesterday 8 AM
    //                     return [
    //                         $now->copy()->subDay()->setTime(8, 0),
    //                         $now->copy()->setTime(7, 59, 59)
    //                     ];
    //                 } else {
    //                     // If current time is after 8 AM, set start to today 8 AM
    //                     return [
    //                         $now->copy()->setTime(8, 0),
    //                         $now->copy()->addDay()->setTime(7, 59, 59)
    //                     ];
    //                 }
    //         }
    //     }



    // collected


    // daily total for agent for today
  public function totalAmountForAgentOnDate(Request $request): JsonResponse
{
    // Get today's date and time at 11 am
    $startOfBusinessDay = now()->setTime(11, 0, 0);

    // Get the agent_id from the request
    $agentId = $request->input('agent_id');

    // Query the installments for the given agent from 11 am today
    $totalAmount = LoanPaymentInstallment::where('agent_id', $agentId)
                    ->whereDate('date', now()->toDateString()) // Ensure only the date part is considered
                    ->sum('install_amount');

    $totalAmountCollected2 = LoanPaymentInstallment::where('agent_id', $agentId)
                    ->whereDate('date', now()->toDateString()) // Ensure only the date part is considered
                    ->where('status', 'paid')
                    ->sum('install_amount');




    // Set the custom time window (4:00 PM to 3:59 PM the next day)
        $startDate = now()->subDay()->setTime(16, 0, 0); // Yesterday at 4:00 PM
        $endDate = now()->setTime(15, 59, 59); // Today at 3:59 PM


        $totalAmountCollected = LoanPayment::where('agent_id', $agentId)
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->where('is_reversed', false) // Only non-reversed payments
                                ->sum('amount');





    // Return the total amount the agent needs to collect
    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Successfully fetched data',
        'total_amount' => $totalAmount,
        'collected' => $totalAmountCollected
    ], 200);
}



    public function totalAmountForAgentOnDate1000(Request $request): JsonResponse
    {
        // Get today's date in Y-m-d format
        $today = now()->toDateString();



    // Get the agent_id and date from the request
    $agentId = $request->input('agent_id');
    // $date = $request->input('date');

    // Query the installments for the given agent and date
    $totalAmount = LoanPaymentInstallment::where('agent_id', $agentId)
                    ->where('date', $today)
                    ->sum('install_amount');

    $totalAmountCollected = LoanPaymentInstallment::where('agent_id', $agentId)
                    ->where('date', $today)
                    ->where('status', 'paid')
                    ->sum('install_amount');

    // Return the total amount the agent needs to collect
    return response()->json([
        'response_code' => 'default_200',
        'message' => 'Successfully fetched data',
        'total_amount' => $totalAmount,
        'collected' => $totalAmountCollected
    ], 200);
}



     // loan Plans
     public function allplans(){
        //  get all the available plans
        $loanPlans = LoanPlan::all();

         return view('admin-views.Loans.plan.index', compact('loanPlans'));
     }
    // add
    public function addplan(){
         return view('admin-views.Loans.plan.create');
    }

     // create
    public function createplan(Request $request){
        $request->validate([
            'plan_name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'installment_value' => 'required|numeric|min:0',
            'installment_interval' => 'required|numeric|min:0',
            'total_installments' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
        ]);

        LoanPlan::create($request->all());

        return view('admin-views.Loans.plan.create');
    }


    // edit
       public function editplan($id){
         $loanPlan = LoanPlan::findOrFail($id);
         return view('admin-views.Loans.plan.edit',compact('loanPlan'));
    }


    //  delet paln
    public function destroyNow($id)
    {

        $loanPlan = LoanPlan::findOrFail($id);
        $loanPlan->delete();

        return redirect()->route('admin.loan-plans')->with('success', 'Loan Plan deleted successfully');
    }




     public function updateNow(Request $request, $id)
    {
        $request->validate([
            'plan_name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'installment_value' => 'required|numeric|min:0',
            'installment_interval' => 'required|numeric|min:0',
            'total_installments' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
        ]);

        $loanPlan = LoanPlan::findOrFail($id);
        $loanPlan->update($request->all());

        return redirect()->route('admin.loan-plans')->with('success', 'Loan Plan updated successfully');
    }


     // all loans
     public function all_loans(){
        //  get all the available plans
        // $loanPlans = LoanPlan::all();
        $pageTitle      = 'All Loans';

        if(request()->search){
            $query          = UserLoan::where('trx', request()->search);
            $emptyMessage   = 'No Data Found';
        }else{
            $query          = UserLoan::latest();
            $emptyMessage   = 'No Loan Yet';
        }
 $totalLoans = $query->count();
        $loans = $query->paginate(20);

         return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
     }




    // paid Loans

    public function paidLoans()
     {
                $pageTitle      = 'Paid Loans';


                if(request()->search){
                    $query          = UserLoan::paid()->where('trx', request()->search);
                    $emptyMessage   = 'No Data Found';
                }else{
                    $query          = UserLoan::paid()->latest();
                    $emptyMessage   = 'No Paid Loan Yet';
                }
         $totalLoans = $query->count();
                $loans = $query->paginate(20);

                return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
            }

public function dueLoans()
{
    $pageTitle = 'Due Loans';
    $emptyMessage = 'No Due Loans Found';

    // Initialize query for due loans
    $query = UserLoan::due()->latest();

    // Search functionality
    if ($search = request('search')) {
        $query->where('trx', 'LIKE', '%' . $search . '%');
        $emptyMessage = 'No loans found matching your search criteria.';
    }

    // Get total number of due loans (before pagination)
    $totalLoans = $query->count();

    // Paginate the result
    $loans = $query->paginate(20);

    // Pass the totalDueLoans count to the view along with the loans data
    return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
}





    // pending loans
    public function pendingLoans()
    {
                $pageTitle      = 'Pending Loans';

                if(request()->search){
                    $query          = UserLoan::pending()->where('trx', request()->search);
                    $emptyMessage   = 'No Data Found';
                }else{
                    $query          = UserLoan::pending()->latest();
                    $emptyMessage   = 'No Pending Loan Yet';
                }
                $totalLoans = $query->count();
                $loans = $query->paginate(20);


        return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
    }


    public function pendingLoans10()
{
    $pageTitle = 'Pending Loans';

    if (request()->search) {
        $query = UserLoan::pending()
            ->where('trx', request()->search)
            ->with('client') // Load client data based on client_id
            ->latest();
        $emptyMessage = 'No Data Found';
    } else {
        $query = UserLoan::pending()
            ->with('client') // Load client data based on client_id
            ->latest();
        $emptyMessage = 'No Pending Loan Yet';
    }

    $loans = $query->paginate(20);

    return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans'));
}




    // rejected loans
    public function rejectedLoans()
            {
                $pageTitle      = 'Rejected Loans';

                if(request()->search){
                    $query          = UserLoan::rejected()->where('trx', request()->search);
                    $emptyMessage   = 'No Data Found';
                }else{
                    $query          = UserLoan::rejected()->latest();
                    $emptyMessage   = 'No Rejected Loan Yet';
                }
        $totalLoans = $query->count();
                $loans = $query->paginate(20);

                return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
            }




    // running loans
    public function runningLoans()
    {
                $pageTitle      = 'Running Loans';

                if(request()->search){
                    $query          = UserLoan::running()->where('trx', request()->search);
                    $emptyMessage   = 'No Data Found';
                }else{
                    $query          = UserLoan::running()->latest();
                    $emptyMessage   = 'No Running Loan Yet';
                }
        $totalLoans = $query->count();
                $loans = $query->paginate(20);

                return view('admin-views.Loans.index', compact('pageTitle', 'emptyMessage', 'loans', 'totalLoans'));
            }




    public function loanplansindex()
    {

        $loanPlans = LoanPlan::all();
        return response()->json($loanPlans);
    }








// create loan.
    public function createLoan10(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',

            'trx' => 'nullable|string|max:40',
            'amount' => 'required|numeric|min:0',
            'per_installment' => 'required|numeric|min:0',
            'installment_interval' => 'required|integer|min:1',
            'total_installment' => 'required|integer|min:1',
            'given_installment' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'user_details' => 'nullable|string',
            'next_installment_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }

        $user = User::find($request->user_id);
        $plan = LoanPlan::find($request->plan_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }



        $loan = new UserLoan();
        $loan->user_id = $request->user_id;
        $loan->plan_id = $request->plan_id;
        $loan->trx = $request->trx;
        $loan->amount = $request->amount;
        $loan->per_installment = $request->per_installment;
        $loan->installment_interval = $request->installment_interval;
        $loan->total_installment = $request->total_installment;
        $loan->given_installment = $request->given_installment ?? 0;
        $loan->paid_amount = $request->paid_amount ?? 0.00;
        $loan->final_amount = $request->final_amount;
        $loan->user_details = $request->user_details ?? null;
        $loan->admin_feedback = null;
        $loan->status = 0;
        $loan->next_installment_date = $request->next_installment_date ?? null;
        $loan->client_id = $request->client_id;
        $loan->save();


        // Create a new agent loan record
                $agentLoan = new AgentLoan();
                $agentLoan->user_id = $request->user_id;
                $agentLoan->client_id = $request->client_id;
                $agentLoan->loan_amount = $request->amount;
                $agentLoan->final_loan_amount = $request->final_amount;
                $agentLoan->save();

        // $client = Client::where('client_id', $request->client_id)






        return response()->json(response_formatter(DEFAULT_200, $loan, null), 200);
    }








    // client loans
    public function clientLoans(Request $request): JsonResponse
    {       
        $loans = UserLoan::where('client_id', $request -> id)->get();
        return response()->json(response_formatter(DEFAULT_200, $loans, null), 200);
    }

// user loans
   public function userLoansList(Request $request): JsonResponse
    {
        $loans = UserLoan::where('user_id', $request -> id)->get();
        return response()->json(response_formatter(DEFAULT_200, $loans, null), 200);

    }



 public function withdrawalMethods(Request $request): JsonResponse
    {
        $withdrawalMethods = $this->withdrawalMethod->latest()->get();
        return response()->json(response_formatter(DEFAULT_200, $withdrawalMethods, null), 200);
    }



    public function show($id)
    {
        $loanOffer = LoanOffer::findOrFail($id);
        return response()->json($loanOffer);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'interest_rate' => 'sometimes|required|numeric',
            'min_amount' => 'sometimes|required|integer|min:0',
            'max_amount' => 'sometimes|required|integer|min:0',
            'min_term' => 'sometimes|required|integer|min:0',
            'max_term' => 'sometimes|required|integer|min:0',
        ]);

        $loanOffer = LoanOffer::findOrFail($id);
        $loanOffer->update($request->all());
        return response()->json($loanOffer);
    }

    public function destroy2($id)
    {
        $loanOffer = LoanOffer::findOrFail($id);
        $loanOffer->delete();
        return response()->json(['message' => 'Loan offer deleted successfully']);
    }


    //
}
