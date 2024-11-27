<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id', 
        'client_id', 
        'agent_id', 
        'amount', 
        'payment_date', 
        'is_reversed', 'credit_balance',       // Add this field
        'reversal_reason',    // Add this field
    ];

    // Define the relationship with the loan model
    public function loan()
    {
        return $this->belongsTo(UserLoan::class);
    }
    
    
// app/Models/Transaction.php
public function branch()
{
    return $this->belongsTo(Branch::class, 'branch_id');
}

    // Define the relationship with the client model
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Define the relationship with the agent model
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // Scope to exclude reversed payments from queries
    public function scopeNotReversed($query)
    {
        return $query->where('is_reversed', false);
    }
    
     
    public function installment()
    {
        return $this->belongsTo(LoanPaymentInstallment::class, 'installment_id');
    }
}
