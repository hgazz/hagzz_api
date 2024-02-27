<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image', 'active', 'academy_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function academy()
    {
        return $this->belongsTo(Academies::class, 'academy_id');
    }
}
