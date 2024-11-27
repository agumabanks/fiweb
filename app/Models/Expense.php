<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    // Adding the newly added fields for managing reversals to the fillable array
    protected $fillable = [
        'transaction_id',
        'description',
        'amount',
        'time',
        'category',
        'payment_method',
        'notes',
        'added_by',
        'is_reversed',
        'reversed_at',
        'reversed_by',
        'original_transaction_id',
        'reversal_reason',
        'status',
        'approval_status',
        'reference_number','user_id'
    ];

    // Relationship to the User who added the expense
    public function addedByUser()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // Relationship to the User who reversed the expense
    public function reversedByUser()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    // Check if the expense has been reversed
    public function isReversed()
    {
        return $this->is_reversed;
    }

    // Get the status of the expense
    public function getStatus()
    {
        return $this->status;
    }
    
     public function agent()
    {
        return $this->belongsTo(User::class, 'user_id'); // Assuming `agent_id` is the foreign key
    }

    // Accessor to format the amount to include commas and decimals
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }
}
