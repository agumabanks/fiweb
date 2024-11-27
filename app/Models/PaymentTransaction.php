<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'loan_id',
        'agent_id',
        'transaction_id',
        'payment_type',
        'amount',
        'status',
        'paid_at',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function loan()
    {
        return $this->belongsTo(UserLoan::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
