<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    const  PATH ='images/gallery';
    protected $fillable = [
        'image',
        'academy_id',
    ];

    public function getImageAttribute($value)
    {
        return config('services.s3.url') . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $value;
    }
    public function academy()
    {
        return $this->belongsTo(Academies::class, 'academy_id');
    }
}
