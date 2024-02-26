<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Academies extends Model
{
    use HasFactory;

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
        'contract_number',
        'account_manager',
        'is_registered'
    ];

    public function sports()
    {
        return $this->hasMany(Sport::class,'academy_id');
    }
}
