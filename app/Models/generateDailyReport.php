<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class generateDailyReport extends Model
{
    use HasFactory;
      protected $fillable = [
        'branch_name',
        'report_date',
        'opening_balance',
        'capital',
        'total_cash','total_cash_out',
        'closing_balance'
    ];
    
    
    
}
