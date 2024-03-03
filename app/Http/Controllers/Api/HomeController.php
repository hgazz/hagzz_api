<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Banner;
use App\Models\Sport;
use App\Models\Training;
use App\Models\User;
use App\Models\UserSport;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use apiResponse;
    public function home()
    {
        $banners = Banner::limit(6)->inRandomOrder()->get();
        $sports = UserSport::with('sport:id,name,icon')->where('user_id',auth()->id())->get();
        $academies = Academies::with('sports')->get(['id','first_name','last_name','logo']);
        $trainings = $this->getUserTraining();
        return $this->apiResponse(200,trans('api.home.All Data in Home Screen'),null,[
            'banners'=> $banners,
            'sports related user authenticated'=> $sports,
            'academies and related sports'=> $academies,
            'training' => $trainings,
        ]);
    }

    protected function getUserTraining()
    {
        // Retrieve user's sports IDs
        $userSportsIds = auth()->user()->sports()->pluck('sport_id')->toArray();

        // Retrieve trainings related to those sports
        return Training::with(['academy:id,commercial_name,logo,address'])->whereHas('classes', function ($query) use ($userSportsIds) {
            $query->whereHas('sport', function ($query) use ($userSportsIds) {
                $query->whereIn('id', $userSportsIds);
            });
        })->withCount('classes')->get();
    }

}
