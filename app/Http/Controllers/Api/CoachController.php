<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoachResource;
use App\Http\Resources\TrainingResource;
use App\Http\Traits\apiResponse;
use App\Models\Coach;
use App\Models\Follow;
use App\Models\Join;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CoachController extends Controller
{
    use apiResponse;
    public function coachProfile($id)
    {

        $coach = Coach::with([
            'academy'])->find($id);
        if(!$coach)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.coach_not_found'),);
        }
        $isFollow = auth('api')->check() ? Follow::whereBelongsTo(auth('api')->user(), 'user')
            ->where([['followable_id', $coach->id], ['followable_type', Coach::class]])->exists() : null;

        $data =  [
            'is_follow' => $isFollow,
            'coach' => new CoachResource($coach),
        ];
       return $this->apiResponse(200,trans('api.home.coach profile'),null, $data);
    }

    public function getTrainingsByCoach($id, Request $request)
    {

        try {
            $pageSize = 10;
            $page = $request->has('page') ? (int) $request->input('page') : 1;

            $trainings = Training::where('coach_id', $id)
                ->with([
                    'academy',
                    'address',
                    'sport'
                ])
                ->withCount(['joins', 'classes'])
                ->isActive()
                ->get();

            $today = Carbon::now()->startOfDay();

            $upcomingTrainings = $trainings->filter(function ($training) use ($today) {
                return Carbon::parse($training->start_date)->startOfDay()->isAfter($today);
            })->values()->all(); // Reset keys and convert to array

            $pastTrainings = $trainings->filter(function ($training) use ($today) {
                return $training->where([['start_date', '<', now()], ['end_date', '<', now()]]);
            })->values()->all(); // Reset keys and convert to array

            $total = $trainings->count();

            $data = [
                'upcoming_trainings' => TrainingResource::collection($upcomingTrainings), // Ensure sequential indexing
                'past_trainings' => TrainingResource::collection($pastTrainings),
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
        } catch (\Exception $e) {
        Log::error('Error fetching coach trainings: '.$e->getMessage(), ['exception' => $e]);

        return $this->apiResponse(500, 'An error occurred while fetching coach trainings.', $e->getMessage());
        }

    }

}
