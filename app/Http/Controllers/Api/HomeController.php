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
    public function home()
    {
        $data = [];
        $banners = Banner::limit(6)->inRandomOrder()->get();
        $sports = UserSport::with('sport')->where('user_id',auth()->id())->get();
        $academies = Academies::with('sports')->get();
        $trainings = Training::get();
        foreach ($trainings as $training){
            $data[] =  Sport::where('academy_id',$training->academy_id)->get();
        }
        return $this->apiResponse(200,trans('api.home.All Data in Home Screen'),null,[
            'banners'=> $banners,
            'sports related user authenticated'=> $sports,
            'academies and related sports'=> $academies,
            'training' => $training,
            'sports related Training'=> $data,
        ]);
    }

}
