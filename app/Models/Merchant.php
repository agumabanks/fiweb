<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Merchant extends Model
{
    use HasFactory;

    protected $table = 'merchants';

    protected $casts = [
        'user_id' => 'integer',
        'store_name' => 'string',
        'callback' => 'string',
        'logo' => 'string',
        'address' => 'string',
        'bin' => 'string',
        'public_key' => 'string',
        'secret_key' => 'string',
        'merchant_number' => 'string',
    ];

    protected $appends = ['logo_fullpath'];

    public function getLogoFullPathAttribute(): string
    {
        $logo = $this->logo ?? null;
        $path = asset('public/assets/admin/img/400x400/img2.jpg');

        if (!is_null($logo) && Storage::disk('public')->exists('merchant/' . $logo)) {
            $path = asset('storage/app/public/merchant/' . $logo);
        }
        return $path;
    }

    /**
     * @return BelongsTo
     */
    public function merchant_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
