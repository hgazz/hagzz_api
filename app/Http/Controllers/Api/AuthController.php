<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\apiResponse;
use App\Http\Traits\FileUploader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use apiResponse, FileUploader;

    // register account
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'name' => 'required',
            'phone' => 'required|unique:users,phone',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $imageName = $request->hasFile('image') ? $this->upload($request->file('image'), User::PATH) : null;
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'image' => $imageName,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'area_id' => $request->area_id,
        ]);

        auth()->loginUsingId($user->id);

       // $token = JWTAuth::fromUser($user);
        $token = '1234';
        return $this->apiResponse(200, trans('api.auth.success_register'), null, new UserResource($user, $token));
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate($request->input('token'));
            return $this->apiResponse(200, trans('api.auth.logout'));
        } catch (JWTException $e) {
            return $this->apiResponse(400, trans('api.auth.failed_logout'));
        }
    }
}
