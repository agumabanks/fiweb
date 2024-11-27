<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// class App extends Model
// {
//     use HasFactory;
//      protected $fillable = ['name', 'description', 'version', 'file_path', 'added_by'];


//     /**
//      * Get the user who added the app.
//      */
//     public function addedBy()
//     {
//         return $this->belongsTo(User::class, 'added_by');
//     }
// }

class App extends Model
{
    use HasFactory;

    // Explicitly define the table name
    protected $table = 'apps'; 

    protected $fillable = ['name', 'description', 'version', 'file_path', 'added_by'];

    /**
     * Get the user who added the app.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
