<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use apiResponse;
    public function getProfile($id)
    {
        $user = User::with(['country', 'city', 'area'])->find($id)->makeHidden(['country_id', 'city_id', 'area_id']);
        return $this->apiResponse(200 , trans('api.auth.User Profile'),null,[
            'profile'=>$user,
            'sports'=>$user->sports
        ]);
    }
}
