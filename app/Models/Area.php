<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Area extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = [
        'name'
    ];
    protected $fillable = [
        'name', 'city_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

//    protected function getNameAttribute()
//    {
//        return $this->getTranslations('name')[$this->getLocale()];
//    }
}
