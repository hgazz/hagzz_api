<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Address extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'academy_id',
        'city_id',
        'area_id',
        'longitude',
        'latitude',
        'address',
        'active',
        'country_id',
    ];

    public $translatable = ['address'];

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academies::class, 'academy_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    protected function getAddressAttribute()
    {
        return $this->getTranslations('address')[$this->getLocale()];
    }

}
