<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Coach extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['name', 'description', 'license'];
    const PATH = 'images/coaches';
    public function getImageAttribute($value)
    {
        return is_null($value) ? null : config('services.s3.url') . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $value;
    }
    protected $fillable = ['name', 'description', 'image', 'active', 'academy_id', 'gender', 'license', 'license_type', 'birth_date'];
    protected $hidden = ['created_at', 'updated_at'];

    public function academy()
    {
        return $this->belongsTo(Academies::class, 'academy_id');
    }

    public function trainings()
    {
        return $this->hasMany(Training::class, 'coach_id')->withCount(['classes', 'joins']);
    }

    public function follows()
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class, 'coach_sports', 'coach_id', 'sport_id');
    }

    public function getTotalHours(): float|int
    {
       $class = TClass::whereHas('training', function($query)  {
            $query->where('coach_id', $this->id);
        })->first();
        return $this->trainings()->count() > 0 !== null ? ceil($class?->duration_in_hours) : 0;
    }

    public function getTotalUsersJoined()
    {
       return $this->trainings() > 0 ? Join::whereHas('training', function ($query) {
            $query->where('coach_id', $this->id);
        })->count() : 0;
    }

}
