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

        $today = Carbon::now()->startOfDay();

        $upcomingTrainings = $trainings->filter(function ($training) use ($today) {
            return Carbon::parse($training->start_date)->startOfDay()->isAfter($today);
        })->values()->all(); // Reset keys and convert to array

        $pastTrainings = $trainings->filter(function ($training) use ($today) {
            return Carbon::parse($training->start_date)->startOfDay()->isBefore($today);
        })->values()->all(); // Reset keys and convert to array

        // Since manual pagination logic is used, apply it here if necessary
        // Note: Manual pagination logic has been omitted for brevity

        $total = $trainings->count();

        $data = [
            'upcoming_trainings' => array_values($upcomingTrainings), // Ensure sequential indexing
            'past_trainings' => array_values($pastTrainings),
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
            'total_upcoming' => count($upcomingTrainings),
            'total_past' => count($pastTrainings),
            'totalPages_upcoming' => ceil(count($upcomingTrainings) / $pageSize),
            'totalPages_past' => ceil(count($pastTrainings) / $pageSize),
        ];

        return $this->apiResponse(200, 'Coach Trainings', null, $data);
    }

}
