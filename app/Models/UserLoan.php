<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Relations\HasMany;


class UserLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'client_id',
        'trx',
        'amount',
        'per_installment',
        'installment_interval',
        'total_installment',
        'given_installment',
        'paid_amount',
        'final_amount',
        'user_details',
        'processing_fee',
        'admin_feedback',
        'status',
        'next_installment_date',

        'is_renewed',                 // New Column
        'renewed_amount',             // New Column
        'renewed_date',               // New Column
        'final_renewed_to_amount',    // New Column
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'next_installment_date' => 'datetime',
        'renewed_amount' => 'decimal:8',             // New Column
        'final_renewed_to_amount' => 'decimal:8',    // New Column
    ];

    // Define relationship to Loan Payment Installments
    public function loanPaymentInstallments()
    {
        return $this->hasMany(LoanPaymentInstallment::class, 'loan_id');
    }
 
    public function loanAdvances()
    {
        return $this->hasMany(LoanAdvance::class, 'loan_id');
    }

    /**
     * Get the total advance amount applied to the loan.
     *
     * @return float
     */
    public function totalAdvanceAmount(): float
    {
        return $this->loanAdvances()->sum('total_advance_amount');
    }

    /**
     * Get the total installments covered by advances.
     *
     * @return int
     */
    public function totalInstallmentsCovered(): int
    {
        return $this->loanAdvances()->sum('total_installments');
    }

    /**
     * Get the total remaining advance amount.
     *
     * @return float
     */
    public function totalRemainingAdvanceAmount(): float
    {
        return $this->loanAdvances()->sum('remaining_advance_amount');
    }

    /**
     * Get the total remaining installments.
     *
     * @return int
     */
    public function totalRemainingInstallments(): int
    {
        return $this->loanAdvances()->sum('remaining_installments');
    }

    
    // public function agent()
    // {
    //     return $this->belongsTo(User::class, 'user_id'); // Assuming 'user_id' refers to the agent
    // }
    // Define relationship to Client
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Define relationship to the agent/user responsible for the loan
    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');  // Assuming 'user_id' refers to the agent who approved/managed the loan
    }

    // Define relationship to Loan Plan
    public function plan()
    {
        return $this->belongsTo(LoanPlan::class, 'plan_id', 'id');
    }

    // Scopes for filtering loans by status
    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 1);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 2);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 3);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', '!=', 3);
    }


    // app/Models/Loan.php
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }



    public function scopeDue($query)
    {
        return $query->where('status', 1) // Only running loans
                     ->where('due_date', '<', Carbon::today());
    }

    public function payments(): HasMany
        {
            return $this->hasMany(LoanPayment::class, 'loan_id'); // Assuming 'loan_id' is the foreign key in the payments table
        }

}
