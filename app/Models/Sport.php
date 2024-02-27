<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Sport extends Model
{
    use HasFactory, HasTranslations;

    const PATH = 'images/sports';
    public $translatable = ['name'];
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'name',
        'icon',
        'status',
        'level',
        'academy_id',
    ];

    public function getIconAttribute($value)
    {
        return config('services.s3.url') . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $value;
    }

    // sports
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sport', 'user_id', 'sport_id');
    }

    // scope return status active sport
    public function scopeActive($query)
    {
        return $query->whereStatus('active');
    }
    public function academy()
    {
        return $this->belongsTo(Academies::class,'academy_id');
    }
}
