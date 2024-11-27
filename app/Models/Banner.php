<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
    use HasFactory;

    protected $appends = ['image_fullpath'];

    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/1920x400/img2.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('banner/' . $image)) {
            $path = asset('storage/app/public/banner/' . $image);
        }
        return $path;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query): mixed
    {
        return $query->where('status', '=', 1);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeAgentAndAll($query): mixed
    {
        return $query->where('receiver', 'agents')->orWhere('receiver', 'all');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCustomerAndAll($query): mixed
    {
        return $query->where('receiver', 'customers')->orWhere('receiver', 'all');
    }
}
