<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class Branch extends Model
{
    use HasFactory, UsesUuid;

    protected $primaryKey = 'branch_id';
    protected $fillable = ['branch_name', 'location'];
}
