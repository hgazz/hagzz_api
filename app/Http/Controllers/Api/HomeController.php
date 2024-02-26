<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Banner;
use App\Models\Sport;
use App\Models\Training;
use App\Models\UserSport;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use apiResponse;
    public function banners()
    {
        $banners = Banner::limit(6)->inRandomOrder()->get();
        return $this->apiResponse(200,trans('api.home.Banners In Home Page'),null,array('banners' => $banners));
    }

    public function sports()
    {
        $sports = UserSport::with('sport')->where('user_id',auth()->id())->get();
        return $this->apiResponse(200 ,trans('api.home.sport with user authenticated'),null,array('sports' => $sports));
    }

    public function academy()
    {
        $academies = Academies::with('sports')->get();
        return $this->apiResponse(200 ,trans('api.home.All Academy and Sports'),null,[
            'academies' => $academies,
        ]);
    }

    public function training()
    {
        $data = [];
        $trainings = Training::where('academy_id',auth()->id())->get();
        foreach ($trainings as $training){
          $data[] =  Sport::where('academy_id',$training->id)->get();
        }
        return $this->apiResponse(200 ,trans('api.home.All Training and Sports'),null,[
            'trainings'=> $trainings,
            'sports' => $data,
        ]);
    }
}
