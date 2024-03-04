<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Coach;
use App\Models\Follow;

class CoachController extends Controller
{
    use apiResponse;
    public function coachProfile($id)
    {
        $coach = Coach::with(['academy:id,phone,commercial_name,logo,address,facebook,instagram'])
            ->find($id);
        if(!$coach)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.coach_not_found'),);
        }
        $isFollow = Follow::whereBelongsTo(auth()->user(), 'user')
            ->where( 'followable_id', $coach->id)->exists();
        $data =  [
            'is_follow' => $isFollow,
            'coach' => $coach,
            'classes_count' => 0, // will be displayed later
            'users_count' => 0, // will be displayed later
        ];
       return $this->apiResponse(200,trans('api.home.coach profile'),null, $data);
    }
}
