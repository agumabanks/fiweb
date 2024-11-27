<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purpose extends Model
{
    use HasFactory;

    protected $appends = ['logo_fullpath'];

    public function getLogoFullPathAttribute(): string
    {
        $logo = $this->logo ?? null;
        $path = asset('public/assets/admin/img/160x160/img1.jpg');

        if (!is_null($logo) && Storage::disk('public')->exists('purpose/' . $logo)) {
            $path = asset('storage/app/public/purpose/' . $logo);
        }
        return $path;
    }
}
