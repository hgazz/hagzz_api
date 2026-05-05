<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesTranslations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
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
            'question' => $this->translated('question'),
            'answer' => $this->translated('answer'),
        ];
    }
}
