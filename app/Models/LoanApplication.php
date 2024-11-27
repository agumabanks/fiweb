<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'loan_offer_id',
        'amount',
        'term',
        'status',
        'interest_rate',
        'monthly_payment',
        'total_payment',
        'client_id' , 
        'agent_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loanOffer()
    {
        return $this->belongsTo(LoanOffer::class);
    }
}
