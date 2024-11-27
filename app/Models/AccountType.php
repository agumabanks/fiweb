<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'interest_rate',
        'compounding_frequency',
    ];

    /**
     * Get the savings accounts for the account type.
     */
    public function savingsAccounts()
    {
        return $this->hasMany(SavingsAccount::class);
    }
}
