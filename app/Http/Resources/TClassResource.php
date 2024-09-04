<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TClassResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', app()->getLocale()) ?? $this->getTranslation('title', 'en'),
            'subtitle' => $this->getTranslation('subtitle', app()->getLocale()) ?? $this->getTranslation('subtitle', 'en'),
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'out_comes' => $this->out_comes,
            'bring_with_me' => $this->bring_with_me,
        ];
    }
}
