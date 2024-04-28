<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
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
            'commercial_name' => $this->getTranslation('commercial_name', app()->getLocale()),
            'logo' => $this->logo,
            'sports' => SportResource::collection($this->sports),
            'follows_count' => $this->follows()->count(),
        ];
    }
}
