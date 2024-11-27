<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guarantor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'nin', 'photo', 'national_id_photo', 'added_by', 'client_id',
        'phone_number', 'address', 'job', 'client_relationship'
    ];

    /**
     * Get the user that added the guarantor.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
