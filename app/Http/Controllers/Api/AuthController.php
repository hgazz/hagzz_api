<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\apiResponse;
use App\Http\Traits\FileUploader;
use App\Models\User;
use App\Services\SMSMISR\SmsMisrOtpSender;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use apiResponse, FileUploader;

    private $userModel;
    private $smsOtp;

    /**
     * @param User $user
     * @param SmsMisrOtpSender $smsOtp
     */
    public function __construct(User $user, SmsMisrOtpSender $smsOtp)
    {
        $this->userModel = $user;
        $this->smsOtp = $smsOtp;
    }

    public function saveUserPhone(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'phone_number' => 'required',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = User::where([
            'phone' => $request->phone_number
        ])->first();

        $otp = rand(10000,99999);
        $responseOtp = $this->smsOtp->sendOtp('+2'.$request->phone_number, $otp);
        if($responseOtp['code'] == 'error')
        {
            return $this->apiResponse(400, 'sms error', $responseOtp['message']);
        }

        if(!$user)
        {
            User::create([
                'phone' => $request->phone_number,
                'otp' => $otp
            ]);
        }else{
            $user->update([
                'otp' => $otp
            ]);
        }

        return $this->apiResponse(200, 'otp was sended');
    }

    public function updatePersonalData(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'name' => 'required',
            'gender' => 'required|in:male,female',
            'birthdate' => 'required',
            'profile_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }


        try {
            $imageName = $request->hasFile('profile_avatar') ? $this->upload($request->file('profile_avatar'), User::PATH) : null;
            $otp = rand(10000,99999);
            $user = User::where('id', auth()->id())->first();
            $user->update([
                'name' => $request->name,
                'gender' => $request->gender,
                'birth_date' => $request->birthdate,
                'image' => $imageName,
                'country_id' => $request->country_id,
                'city_id' => $request->city_id,
                'area_id' => $request->area_id,
                'otp' => $otp,
//                'fcm_token' => $request->fcm_token
                'fcm_token' => ''
            ]);
            return $this->apiResponse(200, trans('api.auth.profile_updated'), null, $user);
        } catch (\Exception $e) {
            return $this->apiResponse(500, trans('api.auth.registration_failed'), $e->getMessage());
        }
    }

    public function updateSportsData(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'sport_id' => 'required|array',
            'sport_id.*' => 'exists:sports,id',
            'level' => 'required|array',
            'level.*' => 'in:beginner,intermediate,advanced',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }
        $sportsWithLevels = [];
        foreach ($request->sport_id as $index => $sportId) {
            if (isset($request->level[$index])) {
                $sportsWithLevels[$sportId] = ['level' => $request->level[$index]];
            }
        }
        $user = User::where('id', auth()->id())->first();
        $user->sports()->attach($sportsWithLevels);

        return $this->apiResponse(200, trans('api.sports.add_sports'), null, $user);
    }

    public function login(Request $request)
    {
        $otp = rand(10000,99999);
        $validation = Validator::make($request->all(),[
            'phone'=> 'required|exists:users,phone',
            'fcm_token' => 'required|string'
        ]);
        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = $this->userModel::where('phone',$request->phone)->withCount('sports')->first();
        $user->update(['otp' => $otp, 'fcm_token' => $request->fcm_token]);
        $this->smsOtp->sendOtp('+2'.$request->phone, $otp);

        auth()->loginUsingId($user->id);

        $token = JWTAuth::fromUser($user);
        return $this->apiResponse(200, trans('api.auth.login success'), null, [
            'token'=>$token,
            'user'=> $user,
        ]);

    }

    public function resendCode(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'phone'=> 'required|exists:users,phone',
        ]);
        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }
        $user = $this->userModel::where('phone',$request->phone)->first();
        $response = $this->smsOtp->sendOtp('+2'.$request->phone, $user->otp);
        return $this->apiResponse(200, trans('api.auth.resend code'), null, [
            "status" => $response
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'otp'=>'required|numeric|min:5|exists:users,otp',
            'phone_number'=>'required|exists:users,phone',
        ]);

        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = User::where([
            ['phone', $request->phone_number],
            ['otp', $request->otp]
        ])->first();

        $diff =  Carbon::now()->diff($user->updated_at);
        $minutes = $diff->i;

        if($minutes >= 10 )
        {
            return  $this->apiResponse(410 ,trans('api.auth.Otp Expired'));
        }


        if($user)
        {
            $user->update([
                'otp' =>'',
                'is_verify' => true
            ]);
            auth()->loginUsingId($user->id);

            $token = JWTAuth::fromUser($user);
            return $this->apiResponse(200, trans('api.auth.the verify code successfully'), null, [
                'token'=>$token,
                'user'=> $user,
            ]);
        }

        return  $this->apiResponse(400 ,trans('api.auth.failed the otp'));


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
            'sport_id' => 'nullable|array',
            'sport_id.*' => 'exists:sports,id',
            'level' => 'nullable|array',
            'level.*' => 'in:beginner,intermediate,advanced',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        try {
            DB::beginTransaction();
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

            if ($request->has('sport_id') && count($request->sport_id) > 0) {
                $sportsWithLevels = [];
                foreach ($request->sport_id as $index => $sportId) {
                    if (isset($request->level[$index])) {
                        $sportsWithLevels[$sportId] = ['level' => $request->level[$index]];
                    }
                }
                $user->sports()->sync($sportsWithLevels);
            }
            DB::commit();
            return $this->apiResponse(200, trans('api.auth.profile_updated'), null, $user);

        }catch (\Exception $e) {
            DB::rollback();
            return $this->apiResponse(400, trans('api.validation_error'), $e->getMessage());
        }

    }

    protected function getImageName(Request $request, User $user)
    {
        if ($request->hasFile('image')){
          return !is_null($user->getRawOriginal('image')) ? $this->upload($request->image, User::PATH, User::PATH . DIRECTORY_SEPARATOR . $user->getRawOriginal('image')) : $this->upload($request->image, User::PATH);
        }
    }
}
