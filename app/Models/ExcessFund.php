<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcessFund extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'excess_funds';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'client_id',
        'amount',
        'date_added',
        'status','user_id'
    ];

    /**
     * Get the client that owns the excess fund.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
