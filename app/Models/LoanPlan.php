<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'plan_name',
        'min_amount',
        'max_amount',
        'installment_value',
        'installment_interval',
        'total_installments',
        'instructions',
    ];
}
