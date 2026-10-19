<?php

namespace App\Models;

use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    const PATH = 'images/banners/';
    protected $fillable = ['logo', 'status', 'country'];

    public function scopeVisibleIn(Builder $query, string $country): Builder
    {
        return $query->where('status', 'active')->where('country', strtolower($country));
    }

    protected $hidden = ['created_at', 'updated_at'];
    public function getLogoAttribute($value)
    {
        return StorageUrl::asset($value, self::PATH, 'fallbacks/academy.svg');
    }
}
