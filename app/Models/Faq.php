<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasFactory,HasTranslations;

    protected  $fillable = ['question','answer'];
    protected array $translatable = ['question','answer'];

    protected function getQuestionAttribute()
    {
        return $this->getTranslations('question')[$this->getLocale()];
    }

    protected function getAnswerAttribute()
    {
        return $this->getTranslations('answer')[$this->getLocale()];
    }

}
