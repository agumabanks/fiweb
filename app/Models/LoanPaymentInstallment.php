<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPaymentInstallment extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_id',
        'agent_id',
        'client_id',
        'install_amount',
        'date',
        'status',
    ];

    // public function loan()
    // {
    //     return $this->belongsTo(Loan::class);
    // }

    // public function agent()
    // {
    //     return $this->belongsTo(Agent::class);
    // }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function userLoan()
    {
        return $this->belongsTo(UserLoan::class, 'loan_id');
    }
    
     // Define relationship with the User (Agent)
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // Define relationship with the Client
    // public function client()
    // {
    //     return $this->belongsTo(Client::class, 'client_id');
    // }
}
