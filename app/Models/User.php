<?php
 

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'last_active_at',
    ];

    protected $casts = [
        'f_name' => 'string',
        'l_name' => 'string',
        'dial_country_code' => 'string',
        'phone' => 'string',
        'email' => 'string',
        'image' => 'string',
        'type' => 'integer',
        'role' => 'integer',
        'password' => 'string',
        'is_phone_verified' => 'integer',
        'is_email_verified' => 'integer',
        'last_active_at' => 'datetime',
        'unique_id' => 'string',
        'referral_id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_fullpath', 'identification_image_fullpath', 'merchant_identification_image_fullpath'];

    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;

        $path = asset('public/assets/admin/img/160x160/img1.jpg');

        $folderNames = [0 => 'admin', 1 => 'agent', 2 => 'customer'];
        $folder = $folderNames[$this->type] ?? 'merchant';
        if (!is_null($image) && Storage::disk('public')->exists($folder . '/' . $image)) {
            $path = asset('storage/app/public/' . $folder . '/' . $image);
        }

        return $path;
    }
    
     public function guarantors()
    {
        return $this->hasMany(Guarantor::class, 'added_by');
    }
    
     public function expenses()
    {
        return $this->hasMany(Expense::class, 'user_id');
    }

    public function getIdentificationImageFullPathAttribute()
    {
        $value = $this->identification_image ?? [];
        $folder = $this->type  == 3 ? 'merchant' : 'identity';
        $imageUrlArray = is_array($value) ? $value : json_decode($value, true);
        if (is_array($imageUrlArray)) {
            foreach ($imageUrlArray as $key => $item) {
                if (Storage::disk('public')->exists('user/identity/' . $item)) {
                    $imageUrlArray[$key] = asset('storage/app/public/user/identity/'. $item) ;
                } else {
                    $imageUrlArray[$key] = asset('public/assets/admin/img/900x400/img1.jpg');
                }
            }
        }
        return $imageUrlArray;
    }

    public function getMerchantIdentificationImageFullPathAttribute()
    {
        $value = $this->identification_image ?? [];
        $imageUrlArray = is_array($value) ? $value : json_decode($value, true);
        if (is_array($imageUrlArray)) {
            foreach ($imageUrlArray as $key => $item) {
                if (Storage::disk('public')->exists('merchant/' . $item)) {
                    $imageUrlArray[$key] = asset('storage/app/public/merchant/'. $item) ;
                } else {
                    $imageUrlArray[$key] = asset('public/assets/admin/img/160x160/img1.jpg');
                }
            }
        }
        return $imageUrlArray;
    }

    /**
     * @return HasMany
     */
    public function AauthAcessToken(): HasMany
    {
        return $this->hasMany(OauthAccessToken::class);
    }
    
    
    
    
    public function loans(): HasMany
    {
        return $this->hasMany(UserLoan::class, 'user_id', 'id'); // Adjust 'Loan::class' and 'user_id' if needed
    }


    public function clients()
    {
        return $this->hasMany(Client::class, 'added_by', 'id');
    }
    
    

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            LoanPayment::class,  // The related model (payments)
            UserLoan::class,     // The intermediary model (loans)
            'user_id',           // Foreign key on UserLoan table
            'loan_id',           // Foreign key on LoanPayment table
            'id',                // Local key on User table
            'id'                 // Local key on UserLoan table
        );
    }


    /**
     * @param $query
     * @return mixed
     */
    public function scopeAgent($query): mixed
    {
        return $query->where('type', '=', 1);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCustomer($query): mixed
    {
        return $query->where('type', '=', 2);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeMerchantUser($query): mixed
    {
        return $query->where('type', '=', 3);
    }

    /**
     * @param $query
     * @param $user_type
     * @return mixed
     */
    public function scopeOfType($query, $user_type): mixed
    {
        return $query->where('type', '=', $user_type);
    }

    /**
     * @return HasOne
     */
    public function emoney(): HasOne
    {
        return $this->hasOne(EMoney::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function user_log_histories(): HasMany
    {
        return $this->hasMany(UserLogHistory::class, 'user_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class, 'user_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        self::updated(function ($model) {
            if ($model->isDirty('is_active')) {
                if ($model->is_active == 0){
                    $model->tokens->each(function ($token, $key) {
                        $token->revoke();
                    });
                }
            }
        });
    }

}
