<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    const PATH = 'images/banners/';
    protected $fillable = [
        'logo',
    ];

    public function getLogoAttribute($value)
    {
        return  config('services.s3.url'). DIRECTORY_SEPARATOR . self::PATH . $value;
    }
}
