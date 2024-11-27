<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanaaCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id', 'pan', 'cvv', 'card_status', 'is_printed', 'card_type',
        'issue_date', 'expiry_date', 'balance', 'currency', 'pin_code', 
        'emv_chip', 'magnetic_stripe_data', 'hologram', 'signature_panel', 
        'iin', 'nfc_enabled',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
