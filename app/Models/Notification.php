<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'image' => 'string',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    protected $appends = ['image_fullpath'];

    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img2.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('notification/' . $image)) {
            $path = asset('storage/app/public/notification/' . $image);
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
