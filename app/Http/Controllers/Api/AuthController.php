<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\apiResponse;
use App\Http\Traits\FileUploader;
use App\Models\User;
use App\Services\SMSMISR\SmsMisrOtpSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use apiResponse, FileUploader;

    private $smsOtp;

    /**
     * @param SmsMisrOtpSender $smsOtp
     */
    public function __construct(SmsMisrOtpSender $smsOtp)
    {
        $this->smsOtp = $smsOtp;
    }

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
            'sport_id' => 'required|array',
            'sport_id.*' => 'exists:sports,id',
            'level' => 'required|array',
            'level.*' => 'in:beginner,intermediate,advanced',
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

            $sportsWithLevels = [];
            foreach ($request->sport_id as $index => $sportId) {
                if (isset($request->level[$index])) {
                    $sportsWithLevels[$sportId] = ['level' => $request->level[$index]];
                }
            }
            $user->sports()->attach($sportsWithLevels);

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
        $otp = rand(10000,99999);
        $validation = Validator::make($request->all(),[
            'phone'=> 'required|exists:users,phone',
        ]);
        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = User::where('phone',$request->phone)->first();
        $user->update(['otp' => $otp]);
        $this->smsOtp->sendOtp($request->phone, $otp);
        auth()->loginUsingId($user->id);

        $token = JWTAuth::fromUser($user);
        return $this->apiResponse(200, trans('api.auth.login success'), null, [
            'token'=>$token,
            'user'=> $user
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
    public function logout()
    {
            Auth::logout();
            return $this->apiResponse(200, trans('api.auth.logout'));
    }

    public function deleteAccount()
    {
        $user = User::where('id', auth()->id())->first();
        $user->delete();
        return $this->apiResponse(200, trans('api.auth.account_was_deleted'));
    }

    public function updateProfile(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'name' => 'nullable',
            'phone' => 'nullable|unique:users,phone,'. auth()->id(),
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = User::findOrFail(auth()->id());
        $imageName = $this->getImageName($request, $user);
        $user->update([
            'name' => $request->name ?? $user->name,
            'phone' => $request->phone ?? $user->phone,
            'gender' => $request->gender ?? $user->gender,
            'birth_date' => $request->birth_date ?? $user->birth_date,
            'image' => $imageName,
            'country_id' => $request->country_id ?? $user->country_id,
            'city_id' => $request->city_id ?? $user->city_id,
            'area_id' => $request->area_id ?? $user->area_id,
        ]);

        return $this->apiResponse(200, trans('api.auth.profile_updated'), null, $user);
    }

    protected function getImageName(Request $request, User $user)
    {
        if ($request->hasFile('image')){
          return !is_null($user->getRawOriginal('image')) ? $this->upload($request->image, User::PATH, User::PATH . DIRECTORY_SEPARATOR . $user->getRawOriginal('image')) : $this->upload($request->image, User::PATH);
        }
    }
}
