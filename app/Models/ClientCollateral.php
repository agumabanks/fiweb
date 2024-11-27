<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCollateral extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'mime_type',
        'original_filename',
    ];

    /**
     * Get the client that owns the collateral.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
