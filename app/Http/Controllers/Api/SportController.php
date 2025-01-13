<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportResource;
use App\Http\Traits\apiResponse;
use App\Models\Sport;
use App\Models\UserSport;
use Illuminate\Http\Request;

class SportController extends Controller
{
    use apiResponse;

    public function getSports()
    {
        $sports = SportResource::collection(Sport::active()->get());
        return $this->apiResponse(200, trans('api.sports.sports'), $sports);
    }

    public function getSportsNotSelected()
    {
        $userSports = UserSport::where('user_id',auth('api')->id())->pluck('id')->toArray();

        $sports = SportResource::collection(Sport::active()->whereKeyNot($userSports)->get());

        return $this->apiResponse(200, trans('api.sports.sports'), $sports);
    }
}
