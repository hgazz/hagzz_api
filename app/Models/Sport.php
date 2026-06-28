<?php

namespace App\Models;

use App\Support\StorageUrl;
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
    ];

    public function getIconAttribute($value)
    {
        return StorageUrl::asset($value, self::PATH, 'fallbacks/sport.svg');
    }

    // sports
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sport', 'user_id', 'sport_id');
    }

    public function userSport()
    {
        return $this->hasOne(UserSport::class,'sport_id','id');
    }

    // scope return status active sport
    public function scopeActive($query)
    {
        return $query->whereStatus('active');
    }

//    public function getNameAttribute()
//    {
//        return $this->getTranslations('name')[$this->getLocale()];
//    }
}
