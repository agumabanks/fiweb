<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'agent_id',
        'account_number',
        'balance',
        'interest_rate','account_type_id'
    ];

    // /**
    //  * Get the client that owns the savings account.
    //  */
    // public function client()
    // {
    //     return $this->belongsTo(Client::class);
    // }

    // /**
    //  * Get the agent responsible for the savings account.
    //  */
    // public function agent()
    // {
    //     return $this->belongsTo(User::class, 'agent_id');
    // }

    // /**
    //  * Get the transactions for the savings account.
    //  */
    // public function transactions()
    // {
    //     return $this->hasMany(SavingsTransaction::class);
    // }
    /**
     * Get the client that owns the savings account.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the agent responsible for the savings account.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the account type of the savings account.
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get the transactions for the savings account.
     */
    public function transactions()
    {
        return $this->hasMany(SavingsTransaction::class);
    }
}
