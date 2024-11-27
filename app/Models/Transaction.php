<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer',
        'transaction_id' => 'string',
        'ref_trans_id' => 'string',
        'transaction_type' => 'string',
        'debit' => 'float:4',
        'credit' => 'float:4',
        'balance' => 'float:4',
        'from_user_id' => 'integer',
        'to_user_id' => 'integer',
        'bonus_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        'amount' => 'float:4',
    ];

    protected $fillable = [
        'user_id',
        'ref_trans_id',
        'transaction_type',
        'debit',
        'credit',
        'balance',
        'from_user_id',
        'to_user_id',
        'bonus_id',
        'note',
        'transaction_id',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeNotAdmin($query): mixed
    {
        return $query->whereHas('user', function ($q) {
            $q->where('type', '!=', 0);
        });
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeAgent($query): mixed
    {
        return $query->whereHas('user', function ($q) {
            $q->where('type', 1);
        });
    }


// app/Models/Transaction.php
public function branch()
{
    return $this->belongsTo(Branch::class, 'branch_id');
}
    /**
     * @param $query
     * @return mixed
     */
    public function scopeCustomer($query): mixed
    {
        return $query->whereHas('user', function ($q) {
            $q->where('type', 2);
        });
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeMerchant($query): mixed
    {
        return $query->whereHas('user', function ($q) {
            $q->where('type', 3);
        });
    }


}
