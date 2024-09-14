<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $key = $this->key;

        // Check if the key is 'egypt_address' and modify it
        if ($key === 'egypt_address') {
            $key = 'Egypt Address';
        }
        // Check if the key is qatar_address' and modify it
        elseif ($key === 'qatar_address') {
            $key = 'Qatar Address';
        }

        return [
            'key' => $key,
            'value' => $this->value,
        ];
    }
}
