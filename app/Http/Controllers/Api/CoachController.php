<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Coach;
use App\Models\Follow;
use App\Models\Join;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    use apiResponse;
    public function coachProfile($id)
    {

        $coach = Coach::with([
            'academy' => function ($query) {
                $query->select('id', 'phone', 'commercial_name', 'logo', 'address', 'facebook', 'instagram')
                    ->withCount('follows');
            },
        ])->find($id);
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

    public function getTrainingsByCoach($id, Request $request)
    {
        $pageSize = 10;
        $page = $request->has('page') ? (int) $request->input('page') : 1;

        // Start building the query with necessary relationships
        $query = Training::where('coach_id', $id)
            ->with([
                'academy:id,logo,commercial_name',
                'address:id,address,city_id,area_id,longitude,latitude',
            ])
            ->withCount(['joins', 'classes'])
            ->isActive();

        // Get the total count of records that match the query criteria before applying pagination
        $total = $query->count();

        // Apply pagination to the query
        $trainings = $query->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        // Prepare and return the API response with the paginated data and other details
        $data = [
            'trainings' => $trainings,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ];
        return $this->apiResponse(200,trans('api.home.coach_trainings'),null, $data);
    }
}
