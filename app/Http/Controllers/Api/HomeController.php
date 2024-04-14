<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Banner;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\Training;
use App\Models\User;
use App\Models\UserSport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    use apiResponse;
    public function home()
    {
        $banners = Banner::limit(6)->inRandomOrder()->get();
        $sports = auth('api')->check() ? $this->getUserSports() : Sport::limit(6)->inRandomOrder()->get(['id','name','icon']);
        $academies = Academies::with('sports')->get(['id','commercial_name','logo']);
        $trainings = auth('api')->check() ? $this->getUserTraining() : $this->getRandomTrainings();
        return $this->apiResponse(200,trans('api.home.All Data in Home Screen'),null,[
            'banners'=> $banners,
            'sports related user authenticated'=> $sports,
            'academies and related sports'=> $academies,
            'training' => [
                'trainings_count' => Training::count(),
                'trainings' => $trainings
            ],
        ]);
    }
    protected function getUserTraining()
    {
        // Assuming `auth()->user()->sports` returns Sport models
        $userSportsIds = auth('api')->user()->sports->pluck('id')->toArray();

        // Retrieve trainings related to those sports
        return Training::with([
            'academy' => function ($query) {
                $query->select(['id', 'commercial_name', 'logo']);
                $query->withCount(['follows']);
            },
            'address:id,address',
            'classes',
            'sport:id,name,icon'
        ])->whereIn('sport_id', $userSportsIds) // Filter trainings by user's sports
          ->withCount(['classes', 'joins'])
            ->inRandomOrder()
            ->limit(4)
          ->get();
    }

    protected function getUserSports()
    {
        return UserSport::with('sport:id,name,icon')
            ->where('user_id',auth('api')->id())
            ->get();
    }

    protected function getRandomTrainings()
    {
        return Training::with([
            'academy' => function ($query) {
                $query->select(['id', 'commercial_name', 'logo']);
                $query->withCount(['follows']);
            },
            'address:id,address',
            'classes',
            'sport:id,name,icon'
        ])->withCount(['classes', 'joins'])
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    public function changeLang(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'lang' => 'required|in:en,ar',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = User::where('id', auth()->id())->first();
        $user->update([
            'language' => $request->lang
        ]);

        return $this->apiResponse(200, trans('api.lang_changed'));
    }

    public function terms()
    {
        $terms = Setting::where('key','terms')->first();
        return $this->apiResponse(200, trans('api.terms'), null, $terms);
    }

}
