<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserLoan;
use Carbon\Carbon;

class UpdateLoanDueDates extends Command
{
    protected $signature = 'update:loan-due-dates';

    protected $description = 'Update due_date for all existing user loans';

    public function handle()
    {
        // Fetch loans that need updating
        $loans = UserLoan::where(function ($query) {
            $query->whereNull('due_date')
                  ->orWhere('updated_at', '>=', Carbon::yesterday());
        })->get();

        foreach ($loans as $loan) {
            if ($loan->loan_taken_date && $loan->installment_interval && $loan->total_installment) {
                // Since loans are collected daily, installment_interval is 1
                $totalDays = $loan->total_installment;
                $loan->due_date = Carbon::parse($loan->loan_taken_date)->addDays($totalDays);
                $loan->save();

                $this->info("Updated loan ID: {$loan->id}");
            } else {
                $this->warn("Skipped loan ID: {$loan->id} due to missing data");
            }
        }

        $this->info('Loan due dates have been updated.');
    }
}
