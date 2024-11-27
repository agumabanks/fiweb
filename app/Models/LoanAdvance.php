<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'client_id',
        'total_advance_amount',
        'remaining_advance_amount',
        'total_installments',
        'remaining_installments',
    ];

    /**
     * Get the loan that owns the advance.
     */
    public function loan()
    {
        return $this->belongsTo(UserLoan::class, 'loan_id');
    }

    /**
     * Get the client that owns the advance.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
