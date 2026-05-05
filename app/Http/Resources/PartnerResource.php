<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesTranslations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
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
            'commercial_name' => $this->translated('app_name'),
            'logo' => $this->logo,
            'sports' => SportResource::collection($this->sports),
            'follows_count' => $this->follows()->count(),
        ];
    }
}
