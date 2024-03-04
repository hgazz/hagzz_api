<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    use HasFactory;

    const PATH = 'images/coaches';
    public function getImageAttribute($value)
    {
        return is_null($value) ? null : config('services.s3.url') . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $value;
    }
    protected $fillable = ['name', 'description', 'image', 'active', 'academy_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function academy()
    {
        return $this->belongsTo(Academies::class, 'academy_id');
    }

    public function trainings()
    {
        return $this->hasMany(Training::class, 'coach_id');
    }

    public function follows()
    {
        return $this->morphMany(Follow::class, 'followable');
    }

}
