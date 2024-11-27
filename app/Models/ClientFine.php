<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'added_by',
        'amount',
        'reason',
        'note',
    ];

    /**
     * Get the client that owns the fine.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who added the fine.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
