<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;
    const PATH = 'images/trainings';
    protected $fillable = [
        'name',
        'image',
        'start_date',
        'end_date',
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
}
