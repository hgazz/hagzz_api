<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    use apiResponse;

    // api get all training depend on user sports and in same city or area
    // training has relationship with classes and classes belongsTo academy and academy hasMany address
    // and address belongsTo city and belongsTo area
    public function index(Request $request)
    {
        $user = auth()->user();

        // Consider eager loading related models if they are used in the response
        $query = Training::query()
            ->with('classes.academy.addresses'); // Adjust based on actual usage

        // Filter by "near me"
        $query->when($request->filled('near_me'), function ($q) use ($user) {
            $q->whereHas('classes.academy.addresses', function ($q) use ($user) {
                $q->where('city_id', $user->city_id)
                    ->where('area_id', $user->area_id);
            });
        });

        // Filter by sport
        $query->when($request->filled('sport_id'), function ($q) use ($request) {
            $q->whereHas('classes.sport', function ($q) use ($request) {
                $q->where('id', $request->input('sport_id'));
            });
        });

        // Filter by search term
        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->whereHas('classes.training', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%');
            });
        });

        // Filter by start soon
        $query->when($request->filled('start_soon'), function ($q) {
            $q->whereHas('classes', function ($q) {
                $q->where('start_date', '>=', now()->toDateString());
            });
        });
        $query->when($request->filled('user_date'), function ($q) use ($request) {
            $user_date = $request->input('user_date');

            $user_date = Carbon::createFromFormat('Y-m-d', $user_date);

            $q->whereHas('classes', function ($q) use ($user_date) {
                $q->whereDate('start_date', '<=', $user_date)
                    ->whereDate('end_date', '>=', $user_date);
            });
        });

        $trainings = $query->get();

        return $this->apiResponse(200, trans('api.home.All Training'), null, $trainings);
    }

    public function trainingDetails($id)
    {
        $training = Training::with([
            'coach:id,name,description,image,active',
            'academy:id,logo,commercial_name',
            'classes'
        ])
            ->withCount(['classes', 'coach'])
            ->find($id);
        if(!$training)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.training_not_found'),);
        }
        $data = [
            'training' => $training,
            'academy_follow' => $training->academy->follows->count()
        ];
        return $this->apiResponse(200, trans('api.home.Training Detail'), null, $data);
    }




}
