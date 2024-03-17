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
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    use apiResponse;
    public function home()
    {
        $banners = Banner::limit(6)->inRandomOrder()->get();
        $sports = UserSport::with('sport:id,name,icon')->where('user_id',auth()->id())->get();
        $academies = Academies::with('sports')->get(['id','commercial_name','logo']);
        $trainings = $this->getUserTraining();
        return $this->apiResponse(200,trans('api.home.All Data in Home Screen'),null,[
            'banners'=> $banners,
            'sports related user authenticated'=> $sports,
            'academies and related sports'=> $academies,
            'training' => $trainings,
        ]);
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
    protected function getUserTraining()
    {
        // Retrieve user's sports IDs
        $userSportsIds = auth()->user()->sports()->pluck('sport_id')->toArray();

        // Retrieve trainings related to those sports
        return Training::with([
            'academy' => function ($query) {
                $query->select(['id', 'commercial_name', 'logo']);
                $query->withCount(['follows']);
            },
            'address:id,address', 'classes'
        ])->withCount(['classes', 'joins'])
            ->get();
    }

}
