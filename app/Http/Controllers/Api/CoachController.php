<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Coach;
use App\Models\Follow;
use App\Models\Join;
use App\Models\Training;
use App\Models\User;

class CoachController extends Controller
{
    use apiResponse;
    public function coachProfile($id)
    {

        $coach = Coach::with([
            'academy:id,phone,commercial_name,logo,address,facebook,instagram',
            'trainings' => [
                'academy:id,logo,commercial_name',
                'address:id,address',
                'academy.follows'
            ]
        ])
            ->find($id);
        if(!$coach)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.coach_not_found'),);
        }
        $isFollow = Follow::whereBelongsTo(auth()->user(), 'user')
            ->where( 'followable_id', $coach->id)->exists();

        $numberOfUsers = Join::with(['training.coach', 'user'])
            ->count();
        $data =  [
            'is_follow' => $isFollow,
            'coach' => $coach,
        ];
       return $this->apiResponse(200,trans('api.home.coach profile'),null, $data);
    }
}
