<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\apiResponse;
use App\Http\Traits\FileUploader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'sport_id' => 'required|exists:sports,id',
            'sport_id.*' => 'required|array',
            'level' => 'required|in:beginner,intermediate,advanced',
            'level.*' => 'required|array|in:beginner,intermediate,advanced',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        try {
            DB::beginTransaction();

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

            $user->sports()->attach($request->sport_id, ['level' => $request->level]);

            auth()->loginUsingId($user->id);

            $token = JWTAuth::fromUser($user);

            DB::commit();

            return $this->apiResponse(200, trans('api.auth.success_register'), null, new UserResource($user, $token));
        } catch (\Exception $e) {
            // An error occurred, rollback the transaction
            DB::rollback();

            // Handle the error, log it, or return an error response
            return $this->apiResponse(500, trans('api.auth.registration_failed'), $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $otp = 1234;
        $validation = Validator::make($request->all(),[
            'phone'=> 'required|exists:users,phone',
        ]);
        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user =User::where('phone',$request->phone)->first();

        auth()->loginUsingId($user->id);

        $token = JWTAuth::fromUser($user);
        return $this->apiResponse(200, trans('api.auth.login success'), null, [
            'otp'=>$otp,
            'token'=>$token
        ]);

    }

    public function resendCode(Request $request)
    {
        $otp = 1234;
        $validation = Validator::make($request->all(),[
            'phone'=> 'required|exists:users,phone',
        ]);
        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        return $this->apiResponse(200, trans('api.auth.resend code'), null, [
            "the otp"=>$otp
        ]);
    }

    public function verifyCode(Request $request)
    {
        $otp = 1234;
        $validation = Validator::make($request->all(),[
            'otp'=>'required|numeric|min:4',
        ]);
        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }
        if ($request->otp != $otp){
            return  $this->apiResponse(400 , trans('api.auth.failed the otp'));
        }
        return  $this->apiResponse(200 ,trans('api.auth.the verify code successfully'));
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

    public function deleteAccount()
    {
        $user = User::where('id', auth()->id())->first();
        $user->delete();
        return $this->apiResponse(200, trans('api.auth.account_was_deleted'));
    }
}
