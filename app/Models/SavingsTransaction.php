<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'savings_account_id',
        'type',
        'amount',
        'description',
    ];

    /**
     * Get the savings account that owns the transaction.
     */
    public function savingsAccount()
    {
        return $this->belongsTo(SavingsAccount::class);
    }
}
