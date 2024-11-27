<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanOffer extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'interest_rate',
        'min_amount',
        'max_amount',
        'min_term',
        'max_term',
    ];
}
