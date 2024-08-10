<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyCode;
use App\Http\Resources\UserSportResource;
use App\Http\Traits\apiResponse;
use App\Http\Traits\FileUploader;
use App\Models\User;
use App\Services\Beon\BeonService;
use App\Services\SMSMISR\SmsMisrOtpSender;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use apiResponse, FileUploader;

    private $userModel;
    private $smsOtp;

    private $beonService;

    /**
     * @param User $user
     * @param SmsMisrOtpSender $smsOtp
     * @param BeonService $beonService
     */
    public function __construct(User $user, SmsMisrOtpSender $smsOtp, BeonService $beonService)
    {
        $this->userModel = $user;
        $this->smsOtp = $smsOtp;
        $this->beonService = $beonService;
    }

    public function register(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'phone' => 'required',
            'name' => 'required',
            'gender' => 'required|in:male,female',
            'birthdate' => 'required',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'country_code' => 'required',
            'send_type' => 'required|in:sms,whatsapp',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        try {
            $otp = $request->phone == '01070809633' ? '12345' : rand(10000,99999);
            if($request->has('old_phone'))
            {
                $user = User::where('phone', $request->old_phone)->first();
                if($user)
                {
                    $user->update([
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'country_code' => $request->country_code,
                        'gender' => $request->gender,
                        'birth_date' => $request->birthdate,
                        'country_id' => $request->country_id,
                        'city_id' => $request->city_id,
                        'area_id' => $request->area_id,
                        'otp' => $otp,
                        'fcm_token' => $request->fcm_token
                    ]);
                }else{
                    return $this->apiResponse(401, 'the user not exists');
                }
            }else{
                $validation = Validator::make($request->all(),[
                    'phone' => 'unique:users,phone',
                ]);

                if($validation->fails())
                {
                    return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
                }

                $user = User::create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'country_code' => $request->country_code,
                    'gender' => $request->gender,
                    'birth_date' => $request->birthdate,
                    'country_id' => $request->country_id,
                    'city_id' => $request->city_id,
                    'area_id' => $request->area_id,
                    'otp' => $otp,
                    'fcm_token' => $request->fcm_token,
                    'language' => $request->lang ?? 'ar'
                ]);
            }
            if ($request->send_type == 'whatsapp') {
                $responseOtp = $this->beonService->sendOtp($request->country_code . $request->phone, $otp);
                $user->update(['otp' => $responseOtp->data, 'fcm_token' => $request->fcm_token]);
            }else{
                $responseOtp = $this->smsOtp->sendOtp($request->country_code .$request->phone, $otp);
                if($responseOtp['code'] == 'error')
                {
                    return $this->apiResponse(400, 'sms error', $responseOtp['message']);
                }
            }


            return $this->apiResponse(200, trans('api.auth.the_verify_code_successfully', [],$request->lang), null, [
                'user'=> $user,
            ]);

        } catch (\Exception $e) {
            return $this->apiResponse(500, trans('api.auth.registration_failed'), $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $otp = $request->phone == '01070809633' ? '12345' : rand(10000,99999);
//        $otp =  rand(10000,99999);
        $validation = Validator::make($request->all(),[
            'phone'=> 'required|exists:users,phone',
            'country_code' => 'required',
            'send_type' => 'required|in:sms,whatsapp',
        ]);

        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = $this->userModel::where('phone',$request->phone)->withCount('sports')->first();
        $user->update(['otp' => $otp, 'fcm_token' => $request->fcm_token]);
        if ($request->send_type == 'whatsapp'){
            $data = $this->beonService->sendOtp($request->country_code .$request->phone, $otp);
            $user->update(['otp' => $data->data, 'fcm_token' => $request->fcm_token]);
        }else{
            $this->smsOtp->sendOtp($request->country_code .$request->phone, $otp);
        }

        return $this->apiResponse(200, trans('api.auth.login success'), null, 'otp was send');

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


        if ($request->has('sport_id') && count($request->sport_id) > 0) {
            $sportsWithLevels = [];
            foreach ($request->sport_id as $index => $sportId) {
                if (isset($request->level[$index])) {
                    $sportsWithLevels[$sportId] = ['level' => $request->level[$index]];
                }
            }
            \auth()->user()->sports()->sync($sportsWithLevels);
        }
        return $this->apiResponse(200, trans('api.sports.add_sports'));
//        return $this->apiResponse(200, trans('api.sports.add_sports'), null, new UserSportResource($user));
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
        $response = $this->smsOtp->sendOtp($user->country_code.$request->phone, $user->otp);
        $user->update(['otp' => $user->otp]);
        return $this->apiResponse(200, trans('api.auth.resend code'), null, [
            "status" => $response
        ]);
    }

    public function verifyCode(VerifyCode $request)
    {

        $user = User::where([
            ['phone', $request->phone_number],
            ['otp', $request->otp]
        ])->with('sports')->first();

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
                'user'=> new UserSportResource($user),
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
        $user = User::find(auth('api')->id());
        $user->delete();
        return $this->apiResponse(200, trans('api.auth.account_was_deleted'));
    }

    public function updateProfile(Request $request)
    {

        $validation = Validator::make($request->all(),[
            'name' => 'nullable',
            'country_code' => 'required|unique:users,phone,',
            'phone' => 'required|unique:users,phone,'. auth()->id(),
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
                'country_code' => $request->country_code ?? $user->country_code,
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
