<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LinkedWebsite extends Model
{
    use HasFactory;

    protected $casts = [
        'name' => 'string',
        'image' => 'string',
        'url' => 'string',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['image_fullpath'];

    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img2.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('website/' . $image)) {
            $path = asset('storage/app/public/website/' . $image);
        }
        return $path;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query): mixed
    {
        return $query->where('status', 1);
    }
}
