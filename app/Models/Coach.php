<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Coach extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['name', 'description', 'license', 'license_type'];
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


    public function follows()
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class, 'coach_sports', 'coach_id', 'sport_id');
    }
    public function trainings()
    {
        return $this->hasMany(Training::class, 'coach_id')->withCount(['classes', 'joins']);
    }

    // Coach Model
    public function getTotalHours(): int
    {
        // Use Eloquent relationships to sum the duration of classes associated with this coach's trainings
        $totalMinutes = $this->trainings()
            ->whereHas('classes', fn ($query) => $query->whereDate('date', '<', today()))
            ->with(['classes' => fn ($query) => $query->whereDate('date', '<', today())])
            ->get()
            ->flatMap(function ($training) {
                return $training->classes;
            })
            ->sum(fn($class) => \Carbon\Carbon::parse($class->start_time)->diffInMinutes(\Carbon\Carbon::parse($class->end_time)));

        // Convert total minutes to hours, rounding up if necessary
        $totalHours = $totalMinutes / 60;

        return $totalHours > 0 ? ceil($totalHours) : 0;
    }
    public function getTotalUsersJoined(): int
    {
        // Use Eloquent relationships to count users who joined this coach's trainings
        $totalJoins = $this->trainings()
            ->whereHas('joins')
            ->withCount('joins')
            ->get()
            ->sum('joins_count');

        // Return the total number of users joined
        return $totalJoins;
    }

    public function getGenderAttribute($value)
    {
        return $value == 'male' ? trans('api.training.male') : trans('api.training.female');
    }


}
