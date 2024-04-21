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
        // Get the sports IDs associated with the authenticated user
        $userSportsIds = auth('api')->user()->sports->pluck('id');

// Retrieve unique coaches associated with the user's sports that belong to the same academy
        $coaches = CoachSport::whereIn('sport_id', $userSportsIds)
            ->whereHas('coach', function ($query) {
                $query->where('academy_id', auth('academy')->id()); // Filter by academy ID
            })
            ->with(['coach' => function ($query) {
                $query->select('id', 'name'); // Limit fields to ID and name
            }])
            ->distinct() // Ensure unique coaches
            ->get()
            ->pluck('coach');

        return $this->apiResponse(200, trans('api.coach_sports'), null, $coaches);
    }
}
