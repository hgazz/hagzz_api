<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\CoachSport;

class UserController extends Controller
{
    use apiResponse;
    public function coachSportByUserFavSports()
    {
        $userSportsIds = auth('api')->user()->sports->pluck('id')->toArray();
        $coaches = CoachSport::whereIn('sport_id', $userSportsIds)
            ->whereHas('coach', function ($query)  {
                // Filter coaches by the academy of the authenticated user
                $query->select('id', 'name')
                    ->where('academy_id', auth('academy')->id());
            })
            ->with(['coach' => function ($query) {
                $query->select('id', 'name'); // Limit fields to avoid unnecessary data
            }])
            ->get()
            ->pluck('coach')
            ->unique();
        return $this->apiResponse(200, trans('api.coach_sports'), null, $coaches);
    }
}
