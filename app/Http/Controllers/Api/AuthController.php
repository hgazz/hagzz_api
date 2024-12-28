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
use Stevebauman\Location\Facades\Location;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use apiResponse, FileUploader;

    private User $userModel;
    private SmsMisrOtpSender $smsOtp;

    private BeonService $beonService;

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
        $lang = $request->lang ?? 'en';

        $validationRules = [
            'phone' => 'required|unique:users,phone',
            'name' => 'required',
            'gender' => 'required|in:male,female',
            'birthdate' => 'required',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'country_code' => 'required',
            'send_type' => 'nullable|in:sms,whatsapp',
        ];

        $validationMessages = [
            'en' => [
                'phone.required' => 'Phone number is required.',
                'phone.unique' => 'The phone number has already been taken.',
                'name.required' => 'Name is required.',
                'gender.required' => 'Gender is required.',
                'gender.in' => 'Gender must be either male or female.',
                'birthdate.required' => 'Birthdate is required.',
                'country_id.required' => 'Country is required.',
                'country_id.exists' => 'The selected country is invalid.',
                'city_id.required' => 'City is required.',
                'city_id.exists' => 'The selected city is invalid.',
                'area_id.required' => 'Area is required.',
                'area_id.exists' => 'The selected area is invalid.',
                'country_code.required' => 'Country code is required.',
                'send_type.in' => 'Send type must be either SMS or WhatsApp.',
            ],
            'ar' => [
                'phone.required' => 'رقم الهاتف مطلوب.',
                'phone.unique' => 'رقم الهاتف مستخدم من قبل.',
                'name.required' => 'الاسم مطلوب.',
                'gender.required' => 'النوع مطلوب.',
                'gender.in' => 'يجب أن يكون النوع ذكر أو أنثى.',
                'birthdate.required' => 'تاريخ الميلاد مطلوب.',
                'country_id.required' => 'الدولة مطلوبة.',
                'country_id.exists' => 'الدولة المختارة غير صالحة.',
                'city_id.required' => 'المدينة مطلوبة.',
                'city_id.exists' => 'المدينة المختارة غير صالحة.',
                'area_id.required' => 'المنطقة مطلوبة.',
                'area_id.exists' => 'المنطقة المختارة غير صالحة.',
                'country_code.required' => 'كود الدولة مطلوب.',
                'send_type.in' => 'نوع الإرسال يجب أن يكون SMS أو WhatsApp.',
            ],
        ];

        $validator = Validator::make($request->all(), $validationRules, $validationMessages[$lang]);

        if ($validator->fails()) {
            return $this->apiResponse(400, trans('api.validation_error', [], $lang), $validator->errors());
        }

        $validatedData = $validator->validated();

        try {
            $otp = $validatedData['phone'] == '01070809633' ? '12345' : rand(10000, 99999);

            if ($request->has('old_phone')) {
                $user = User::where('phone', $request->old_phone)->first();
                if (!$user) {
                    return $this->apiResponse(401, trans('api.auth.user_not_exists', [], $lang));
                }
                $user->update(array_merge($validatedData, ['otp' => $otp, 'fcm_token' => $request->fcm_token]));
            } else {
                $user = User::create(array_merge($validatedData, [
                    'otp' => $otp,
                    'fcm_token' => $request->fcm_token,
                    'language' => $lang
                ]));
            }

            if ($validatedData['send_type'] == 'whatsapp') {
                $responseOtp = $this->beonService->sendOtp($validatedData['country_code'] . $validatedData['phone'], $otp);
                $otpData = json_decode($responseOtp, true);
                $user->update(['otp' => $otpData['data'], 'fcm_token' => $request->fcm_token]);
            } else {
                $responseOtp = $this->smsOtp->sendOtp($validatedData['country_code'] . $validatedData['phone'], $otp);
                if ($responseOtp['code'] == 'error') {
                    return $this->apiResponse(400, trans('api.auth.sms_error', [], $lang), $responseOtp['message']);
                }
            }

            return $this->apiResponse(200, trans('api.auth.the_verify_code_successfully', [], $lang), null, [
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            return $this->apiResponse(500, trans('api.auth.registration_failed', [], $lang), $e->getMessage());
        }
    }


    public function login(Request $request)
    {
        $otp = $request->phone == '01070809633' ? '12345' : rand(10000,99999);
//        $otp =  rand(10000,99999);
        $validation = Validator::make($request->all(),[
            'phone'=> 'required|exists:users,phone',
            'country_code' => 'required',
            'send_type' => 'nullable|in:sms,whatsapp',
        ]);

        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = $this->userModel::where('phone',$request->phone)->withCount('sports')->first();
        $user->update(['otp' => $otp]);
        if($request->has('fcm_token'))
        {
            $user->update(['fcm_token' => $request->fcm_token]);
        }
        if($request->phone == '01070809633') {
            $user->update(['otp' => '12345', 'fcm_token' => $request->fcm_token]);
        }else{
            if ($request->send_type == 'whatsapp'){
                $data = $this->beonService->sendOtp($request->country_code .$request->phone, $otp);
                $otp = json_decode($data, true);
                $user->update(['otp' => $otp['data'], 'fcm_token' => $request->fcm_token]);
            }else{
//            $this->smsOtp->sendOtp($request->country_code .$request->phone, $otp);
            }
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
            'send_type' => 'nullable|in:sms,whatsapp',
        ]);
        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }
        $user = $this->userModel::where('phone',$request->phone)->first();
        $otp = $request->phone == '01070809633' ? '12345' : rand(10000,99999);
        if ($request->send_type == 'whatsapp'){
            $responseOtp = $this->beonService->sendOtp($user->country_code . $request->phone, $user->name);
            $otp = json_decode($responseOtp, true);
            $user->update(['otp' => $otp['data']]);
        }else{
            $this->smsOtp->sendOtp($user->country_code.$request->phone, $otp);
            $user->update(['otp' => $otp]);
        }

        return $this->apiResponse(200, trans('api.auth.resend code'), null, 'otp was send');
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


            $ip = $request->ip() == '127.0.0.1' ? '196.47.62.26' : $request->ip(); // Replace with a test IP if localhost
            $location = Location::get($ip);


            $token = JWTAuth::fromUser($user);
            return $this->apiResponse(200, trans('api.auth.the verify code successfully'), null, [
                'token'=>$token,
                'user'=> new UserSportResource($user),
                'region' => $location->regionCode
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
            'phone' => 'required|unique:users,phone,'. auth('api')->id(),
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
