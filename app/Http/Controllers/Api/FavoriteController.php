<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\select;

class FavoriteController extends Controller
{
   use  apiResponse;

    public function favoriteList()
    {

        $favorites = Favorite::withwith(['academy:id,commercial_name,logo,address',
            'address:id,address,longitude,latitude', 'academy.follows'])
            ->where('user_id', auth()->id())
            ->get();
        return $this->apiResponse(200 ,'Favorite list',null , $favorites);
   }
    public function addFavorite(Request $request)
    {
        $validation = Validator::make($request->all() , [
            'training_id' => 'required|exists:trainings,id',
        ]);

        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $favExist = Favorite::where(['user_id' =>auth()->id(), 'training_id' => $request->training_id])->exists();
        if ($favExist){
            return  $this->apiResponse(400 , null,trans('api.home.favorite already exists'));
        }
        $fav = Favorite::create([
            'user_id'=>auth()->id(),
            'training_id'=>$request->training_id
        ]);
        return $this->apiResponse(200 ,trans('api.home.add favorite successfully'),null, $fav);
    }

    public function deleteFavorite($id)
    {
        $favorite = Favorite::where([['user_id',auth()->id()], ['training_id', $id]])->first();
       if (!$favorite){
           return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.Favorite not found'));
       }
       $favorite->delete();
       return $this->apiResponse(200 ,trans('api.home.delete favorite successfully'));
    }
}
