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
use Illuminate\Support\Carbon;

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

        $trainings = Training::where('coach_id', $id)
            ->with([
                'academy:id,logo,commercial_name',
                'address:id,address,city_id,area_id,longitude,latitude',
            ])
            ->withCount(['joins', 'classes'])
            ->isActive()
            ->get();

        // Split into upcoming and past trainings based on the start_date
        $today = Carbon::now()->startOfDay();

        $upcomingTrainings = $trainings->filter(function ($training) use ($today) {
            return Carbon::parse($training->start_date)->startOfDay()->isAfter($today);
        });

        $pastTrainings = $trainings->filter(function ($training) use ($today) {
            return Carbon::parse($training->start_date)->startOfDay()->isBefore($today);
        });
        $upcomingPaginated = $upcomingTrainings->skip(($page - 1) * $pageSize)->take($pageSize);
        $pastPaginated = $pastTrainings->skip(($page - 1) * $pageSize)->take($pageSize);
        $total = $trainings->count();

        $data = [
            'upcoming_trainings' => $upcomingPaginated,
            'past_trainings' => $pastPaginated,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
            'total_upcoming' => $upcomingTrainings->count(),
            'total_past' => $pastTrainings->count(),
            'totalPages_upcoming' => ceil($upcomingTrainings->count() / $pageSize),
            'totalPages_past' => ceil($pastTrainings->count() / $pageSize),
        ];

        return $this->apiResponse(200,trans('api.home.coach_trainings'),null, $data);
    }
}
