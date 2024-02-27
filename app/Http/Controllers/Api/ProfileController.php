<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use apiResponse;
    public function getProfile($user)
    {
        $user = User::find($user);
        if (is_null($user)){
            return $this->apiResponse(400,trans('api.auth.User Not Found'));
        }
        return $this->apiResponse(200 , trans('api.auth.User Profile'),null,[
            'profile'=>$user,
            'sports'=>$user->sports
        ]);
    }
}
