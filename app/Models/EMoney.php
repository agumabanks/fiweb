<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EMoney extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected $casts = [
        'user_id' => 'integer',
        'current_balance' => 'float:4',
        'charge_earned' => 'float:4',
        'pending_balance' => 'float:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
