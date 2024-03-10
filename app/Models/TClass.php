<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TClass extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['title', 'subtitle'];
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at','academy_id','sport_id','training_id'];

    protected $casts = [
        'out_comes' => 'array',
        'bring_with_me' => 'array',
    ];
    public function academy()
    {
        return $this->belongsTo(Academies::class, 'academy_id', 'id');
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class, 'sport_id', 'id');
    }

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }


    protected function getTitleAttribute()
    {
        return $this->getTranslations('title')[$this->getLocale()];
    }

    protected function getSubTitleAttribute()
    {
        return $this->getTranslations('subtitle')[$this->getLocale()];
    }

    public function getOutComesAttribute($value)
    {
        return json_decode($value, true);

    }

//    public function getBringWithMeAttribute($value)
//    {
//        return json_decode($value, true);
//    }

}
