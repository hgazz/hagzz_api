<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','training_id'];
    protected $hidden = ['created_at', 'updated_at', 'training_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class)->withCount('classes');
    }
}
