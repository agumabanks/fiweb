<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentLoan extends Model
{
    use HasFactory;
    protected $fillable = [
         'user_id',
        'client_id',
        'loan_amount',
        'final_loan_amount',
        'interest_rate',
        'loan_term',
        'status',
        'comments',
        'created_by',
        ];
}
