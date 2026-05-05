<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesTranslations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TClassResource extends JsonResource
{
    use ResolvesTranslations;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->translated('title'),
            'subtitle' => $this->translated('subtitle'),
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'out_comes' => app()->getLocale() == 'en'? $this->out_comes?->en : $this->out_comes?->ar ?? null,
            'bring_with_me' => app()->getLocale() == 'en' ? $this->bring_with_me?->en : $this->bring_with_me?->ar ?? null,
        ];
    }
}
