<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoachResource;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\apiResponse;
use App\Models\CoachSport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotificationCollection;

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

        return $this->apiResponse(200, trans('api.coach_sports'), null, CoachResource::collection($coaches));
    }

    public function userNotifications(Request $request)
    {

        $pageSize = 10;
        $page = $request->has('page') ? (int) $request->input('page') : 1;

        $unreadNotifications = auth('api')->user()->notifications()
            ->whereNull('read_at')
            ->skip(($page - 1) * $pageSize)->take($pageSize)->get();
        $readNotifications = auth('api')->user()->notifications()
            ->whereNotNull('read_at')
            ->skip(($page - 1) * $pageSize)->take($pageSize)->get();
        $readNotificationsCount = auth('api')->user()->readNotifications()->count();
        $unReadNotificationsCount = auth('api')->user()->unreadNotifications()->count();
        return $this->apiResponse(200, trans('api.notifications'), null, [
            'read_notifications' => NotificationResource::collection($readNotifications),
            'unread_notifications' => NotificationResource::collection($unreadNotifications),
            'total_read_notifications' => ceil($readNotificationsCount / $pageSize),
            'total_unread_notifications' => ceil($unReadNotificationsCount / $pageSize),
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth('api')->user()->notifications()->find($id);
        $notification->markAsRead();
        return $this->apiResponse(200, trans('api.notifications.mark_as_read'), null, $notification);
    }
}
