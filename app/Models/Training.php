<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Training extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['name', 'description'];
    protected $hidden = ['created_at', 'updated_at'];
    const PATH = 'images/trainings';
    protected $fillable = [
        'name',
        'image',
        'price',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'description',
        'coach_id',
    ];

    public function coach()
    {
        return $this->belongsTo(Coach::class, 'coach_id');
    }

    public function academy()
    {
        return $this->belongsTo(Academies::class,'academy_id');
    }
    public function getImageAttribute($value)
    {
        return config('services.s3.url') . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $value;
    }

    public function classes()
    {
        return $this->belongsToMany(TClass::class ,'training_classes','training_id','class_id');
    }
}
