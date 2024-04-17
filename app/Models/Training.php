<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Training extends Model
{
    use HasFactory, HasTranslations;

    protected array $translatable = ['name', 'description'];
    protected $hidden = ['created_at', 'updated_at','academy_id','address_id','coach_id'];

    protected $appends = ['is_fav'];
    const PATH = 'images/trainings';
    protected $fillable = [
        'name',
        'image',
        'price',
        'start_date',
        'end_date',
        'description',
        'max_players',
        'level',
        'gender',
        'age_group',
        'address_id',
        'coach_id',
        'academy_id',
        'active',
        'sport_id'
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'coach_id');
    }

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academies::class,'academy_id')->with('sports:id,name');
    }
    public function getImageAttribute($value): string
    {
        return config('services.s3.url') . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $value;
    }

    public function getIsFavAttribute()
    {
        return $this->favorites()->where('user_id', auth('api')->id())->exists();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'training_id');
    }


    public function classes(): HasMany
    {
        return $this->hasMany(TClass::class ,'training_id');
    }

    public function joins(): HasMany
    {
        return $this->hasMany(Join::class, 'training_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id')->with('area', 'city');
    }

    protected function getNameAttribute()
    {
        return $this->getTranslations('name')[$this->getLocale()];
    }

    protected function getDescriptionAttribute()
    {
        return $this->getTranslations('description')[$this->getLocale()];
    }

    //scope return only active
    public function scopeIsActive($query)
    {
        return $query->where('active', 1);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }
}
