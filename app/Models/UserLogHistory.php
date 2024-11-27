<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLogHistory extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ip_address',
        'device_id',
        'browser',
        'os',
        'device_model',
        'user_id',
        'is_active',
    ];

}
