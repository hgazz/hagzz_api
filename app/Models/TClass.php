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


//    protected function getTitleAttribute()
//    {
//        return $this->getTranslations('title')[$this->getLocale()];
//    }
//
//    protected function getSubTitleAttribute()
//    {
//        return $this->getTranslations('subtitle')[$this->getLocale()];
//    }

    public function getOutComesAttribute($value)
    {
        return json_decode($value);

    }

    public function getBringWithMeAttribute($value)
    {
        return json_decode($value);
    }

    // Tclass Model
//    public function getDurationInHoursAttribute()
//    {
//        $startTime = \Carbon\Carbon::parse($this->start_time);
//        $endTime = \Carbon\Carbon::parse($this->end_time);
//
////        // Safeguard against invalid times (e.g., end time before start time)
////        if ($startTime->greaterThan($endTime)) {
////            return 0;
////        }
//
//        return $startTime->diffInMinutes($endTime) / 60; // Convert minutes to hours
//    }

}
