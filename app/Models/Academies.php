<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Academies extends Model
{
    use HasTranslations;

    public $translatable = ['commercial_name'];
    const PATH ='images/academies';
    protected $fillable = [
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

    protected $withCount = ['follows'];

    public function getLogoAttribute($value)
    {
        return is_null($value) ? null : config('services.s3.url') . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $value;
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class,'academy_sport','academy_id','sport_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'academy_id');
    }

    public function follows()
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function coaches(): HasMany
    {
        return $this->hasMany(Coach::class, 'academy_id');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class, 'academy_id')->withCount(['classes', 'joins']);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'academy_id');
    }

    public function getCommercialNameAttribute($value)
    {

        return  $this->getTranslations('commercial_name',[\App::getLocale()])[\App::getLocale()];
        return $translations[\App::getLocale()] ?? null;
    }
}
