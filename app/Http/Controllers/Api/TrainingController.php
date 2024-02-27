<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    use apiResponse;

    // api get all training depend on user sports and in same city or area
    // training has relationship with classes and classes belongsTo academy and academy hasMany address
    // and address belongsTo city and belongsTo area
    public function index()
    {
        $user = auth()->user();
        $userSports = $user->sports()->pluck('sport_id')->toArray(); // Get user's interested sports IDs

        $cityId = $user->city_id;
        $areaId = $user->area_id;

        $trainings = Training::whereHas('classes.academy.addresses', function ($query) use ($cityId, $areaId) {
            // Filter academies that are in the specified city or area
            $query->where('city_id', $cityId)
                ->orWhere('area_id', $areaId);
        })->whereHas('classes.academy.sports', function ($query) use ($userSports) {
            // Filter academies that offer sports the user is interested in
            $query->whereIn('sports.id', $userSports);
        })->get();

        return $this->apiResponse(200, trans('api.home.All Training'), null, $trainings);
    }
}
