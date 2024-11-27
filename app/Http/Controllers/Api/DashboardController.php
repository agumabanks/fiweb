<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\UserLoan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    // Define loan statuses as constants to avoid magic numbers
    private const STATUS_PENDING = 0;
    private const STATUS_RUNNING = 1;
    private const STATUS_PAID = 2;
    private const STATUS_REJECTED = 3;

    /**
     * Get loan dashboard statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLoanDashboardStats(Request $request): JsonResponse
    {
        try {
            // Optional: Add authorization logic here if needed.
            // $this->authorize('viewLoanDashboardStats');

            // Define cache key and duration
            $cacheKey = 'loan_dashboard_stats';
            $cacheDuration = now()->addMinutes(5); // Adjust as needed

            // Retrieve statistics from cache or compute if not cached
            $stats = Cache::remember($cacheKey, $cacheDuration, function () {
                // Get total clients
                $totalClients = Client::count();

                // Get loan counts grouped by status
                $loanCounts = UserLoan::select('status', DB::raw('COUNT(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status');

                // Get total amount in loans
                $totalAmountInLoans = UserLoan::sum('amount');

                // Assign counts with default to 0 if status not present
                $totalPendingLoans  = $loanCounts->get(self::STATUS_PENDING, 0);
                $totalRunningLoans  = $loanCounts->get(self::STATUS_RUNNING, 0);
                $totalPaidLoans     = $loanCounts->get(self::STATUS_PAID, 0);
                $totalRejectedLoans = $loanCounts->get(self::STATUS_REJECTED, 0);

                // Define active loans (assuming running loans are active)
                $totalActiveLoans = $totalRunningLoans;

                return [
                    'total_clients'           => $totalClients,
                    'total_active_loans'     => $totalActiveLoans,
                    'total_pending_loans'    => $totalPendingLoans,
                    'total_running_loans'    => $totalRunningLoans,
                    'total_paid_loans'       => $totalPaidLoans,
                    'total_rejected_loans'   => $totalRejectedLoans,
                    'total_amount_in_loans'  => $totalAmountInLoans,
                ];
            });

            // Return the response as JSON
            return response()->json([
                'status' => 'success',
                'data'   => $stats,
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching loan dashboard stats: ' . $e->getMessage());

            // Return an error response
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve loan dashboard statistics.',
            ], 500);
        }
    }
}
