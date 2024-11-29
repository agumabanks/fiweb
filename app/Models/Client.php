<?php
 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


// ALTER TABLE clients
// ADD COLUMN next_of_kin_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
// ADD COLUMN next_of_kin_phone VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
// ADD COLUMN next_of_kin_relationship VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;


class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
        'kyc_verified_at',
        'dob',
        'business',
        'nin',
        'recommenders',
        'credit_balance',
        'savings_balance',
        'client_photo',
        'next_of_kin',
        'national_id_photo',
        'added_by',
        'branch_id','next_of_kin_name','next_of_kin_phone','next_of_kin_relationship'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'kyc_verified_at' => 'datetime',
        'dob' => 'date',
        'recommenders' => 'array',
        'credit_balance' => 'decimal:2',
        'savings_balance' => 'decimal:2',
    ];


public function savingsAccounts()
    {
        return $this->hasMany(SavingsAccount::class);
    }
    /**
     * Scope a query to only include active clients.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Relationship with LoanPayment model.
     */
    public function loanPayments()
    {
        return $this->hasMany(LoanPayment::class, 'client_id');
    }

    /**
     * Relationship with UserLoan model.
     */
    public function userLoans()
    {
        return $this->hasMany(UserLoan::class, 'client_id');
    }

    /**
     * Get the full address of the client.
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        return $this->address;
    }

    /**
     * Relationship with the User who added the client.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    
     // Other model properties and methods...

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id'); // Assuming 'agent_id' is the foreign key in the clients table
    }

    //     public function agent()
    // {
    //     return $this->belongsTo(User::class, 'agent_id'); // Adjust 'agent_id' based on your database schema
    // }

    public function guarantors()
{
    return $this->hasMany(Guarantor::class, 'client_id');
}

    /**
     * Relationship with the Branch model.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function fines()
    {
        return $this->hasMany(ClientFine::class);
    }

    public function collaterals()
{
    return $this->hasMany(ClientCollateral::class);
}

    /**
     * Get the loan associated with the client (assuming each client has one main loan).
     */
    public function loan()
    {
        return $this->hasOne(UserLoan::class, 'client_id');
    }
    
     public function loans(): HasMany
    {
        return $this->hasMany(UserLoan::class, 'client_id'); // Assuming 'client_id' is the foreign key in the user_loans table
    }

    /**
     * Alias for loanPayments for consistency.
     */
    // public function payments()
    // {
    //     return $this->loanPayments();
    // }
    
     public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class, 'client_id'); // Assuming 'client_id' is the foreign key in the payments table
    }
}


// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Client extends Model
// {
//     use HasFactory;

    
//      protected $fillable = [
//         'name',
//         'email',
//         'phone',
//         'address',
//         'status',
//         'kyc_verified_at',
//         'dob',
//         'business',
//         'nin',
//         'recommenders',
//         'credit_balance',
//         'savings_balance',
//         'client_photo',
//         'next_of_kin',
//         'national_id_photo',
//         'added_by',
//         'branch_id'
//         ];

//     /**
//      * The attributes that should be cast.
//      *
//      * @var array<string, string>
//      */
//     protected $casts = [
//         'kyc_verified_at' => 'datetime',
//         'dob' => 'date',
//         'recommenders' => 'array',
//         'credit_balance' => 'decimal:2',
//         'savings_balance' => 'decimal:2',
//     ];

//     /**
//      * Scope a query to only include active clients.
//      *
//      * @param \Illuminate\Database\Eloquent\Builder $query
//      * @return \Illuminate\Database\Eloquent\Builder
//      */
//     public function scopeActive($query)
//     {
//         return $query->where('status', 'active');
//     }


// // Client.php
// public function loanPayments()
// {
//     return $this->hasMany(LoanPayment::class, 'client_id');
// }

//     /**
//      * Get the full address of the client.
//      *
//      * @return string
//      */
//       public function userLoans()
//     {
//         return $this->hasMany(UserLoan::class, 'client_id');
//     }
    
    
//     public function getFullAddressAttribute()
//     {
//         return "{$this->address}";
//     }
    
    
//     public function addedBy()
//     {
//         return $this->belongsTo(User::class, 'added_by', 'id');
//     }
    
    
//     // app/Models/Client.php
//     public function branch()
//     {
//         return $this->belongsTo(Branch::class, 'branch_id');
//     }
    
    
//      /**
//      * Get the loan associated with the client.
//      */
//     public function loan()
//     {
//         return $this->hasOne(UserLoan::class);
//     }
    
//     // Define the relationship with LoanPayment
//     public function payments()
//     {
//         return $this->hasMany(LoanPayment::class, 'client_id', 'id');
//     }

// }
