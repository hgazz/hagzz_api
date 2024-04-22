<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\CoachSport;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use apiResponse;
    public function coachSportByUserFavSports()
    {
        // Get the sports IDs associated with the authenticated user
        $userSportsIds = auth('api')->user()->sports->pluck('id');

        $coaches = CoachSport::whereIn('sport_id', $userSportsIds)
            ->with(['coach' => function ($query) {
                $query->select('id', 'name', 'image')
                      ->with('sports'); // Limit fields to ID and name
            }])
            ->distinct() // Ensure unique coaches
            ->get()
            ->pluck('coach');

        return $this->apiResponse(200, trans('api.coach_sports'), null, $coaches);
    }

    public function userNotifications(Request $request)
    {
        $pageSize = 10;
        $page = $request->has('page') ? (int) $request->input('page') : 1;

        $Notifications = auth('api')->user()->notifications()->skip(($page - 1) * $pageSize)->take($pageSize)->get();
        $NotificationsCount = auth('api')->user()->Notifications()->count();
        return $this->apiResponse(200, trans('api.notifications'), null, [
            'Notifications' => $Notifications,
            'total_read_notifications' => ceil($NotificationsCount / $pageSize),
        ]);
    }
}
