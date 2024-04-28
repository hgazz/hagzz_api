<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSport extends Model
{
    use HasFactory;
    protected $table = 'user_sport';
    protected $fillable  = ['user_id','sport_id','level'];
    protected $hidden = ['created_at','updated_at'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class ,'user_id');
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class ,'sport_id');
    }
}
