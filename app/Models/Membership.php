<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// class Membership extends Model
// {
//     use HasFactory;
// }

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'membership_type', 'is_paid', 'membership_date','client_id','shares','is_shares_paid','share_value'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
     public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
