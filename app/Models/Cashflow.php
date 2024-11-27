<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    use HasFactory;

    // Define the table name if it's not plural of the model name
    protected $table = 'cashflow';

    // Specify the fillable attributes for mass assignment
    protected $fillable = [
        'balance_bf',
        'capital_added','unknown_funds',
        'cash_banked',
        'created_at'
    ];

    // Add any necessary relationships here, if needed
}
