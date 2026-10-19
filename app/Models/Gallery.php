<?php

namespace App\Models;

use App\Support\StorageUrl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    const  PATH ='images/gallery';
    protected $fillable = [
        'image',
        'academy_id',
        'active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getImageAttribute($value)
    {
        return StorageUrl::asset($value, self::PATH, 'fallbacks/academy.svg');
    }
    public function academy()
    {
        return $this->belongsTo(Academies::class, 'academy_id');
    }
}
