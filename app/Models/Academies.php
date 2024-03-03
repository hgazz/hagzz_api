<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Academies extends Model
{
    use HasFactory;

    const PATH ='images/academies';
    protected $fillable = [
        'first_name',
        'last_name',
        'full_name_arabic',
        'email',
        'phone',
        'password',
        'status',
        'role',
        'commercial_name',
        'trade_license_number',
        'trade_license_expire_date',
        'tax_number',
        'percentage',
        'national_id_number',
        'address',
        'logo',
        'contract_number',
        'account_manager',
        'is_registered'
    ];

    protected $hidden = ['created_at', 'updated_at'];
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getImageAttribute($value)
    {
        return config('services.s3.url') . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $value;
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class,'academy_sport','academy_id','sport_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'academy_id');
    }
}
