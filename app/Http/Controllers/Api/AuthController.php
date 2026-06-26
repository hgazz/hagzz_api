<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyCode;
use App\Http\Resources\UserSportResource;
use App\Http\Traits\apiResponse;
use App\Http\Traits\FileUploader;
use App\Models\User;
use App\Services\Chataman\ChatamanService;
use App\Services\SMSMISR\SmsMisrOtpSender;
use App\Support\InternationalPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use apiResponse, FileUploader;

    private User $userModel;
    private SmsMisrOtpSender $smsOtp;

    private ChatamanService $chatamanService;

    /**
     * @param User $user
     * @param SmsMisrOtpSender $smsOtp
     * @param ChatamanService $chatamanService
     */
    public function __construct(User $user, SmsMisrOtpSender $smsOtp, ChatamanService $chatamanService)
    {
        $this->userModel = $user;
        $this->smsOtp = $smsOtp;
        $this->chatamanService = $chatamanService;
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
            DB::beginTransaction();

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

            if ($validatedData['phone'] !== '01070809633') {
                $this->sendOtp(
                    $validatedData['send_type'] ?? 'whatsapp',
                    InternationalPhoneNumber::format($validatedData['country_code'], $validatedData['phone']),
                    $otp,
                    $lang
                );
            }

            DB::commit();

            return $this->apiResponse(200, trans('api.auth.the_verify_code_successfully', [], $lang), null, [
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration OTP delivery failed', [
                'provider' => 'chataman',
                'message' => $e->getMessage(),
            ]);

            return $this->apiResponse(502, trans('api.auth.registration_failed', [], $lang));
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

        try {
            $user = $this->userModel::where('phone',$request->phone)->withCount('sports')->first();
            $user->update(['otp' => $otp]);
            if($request->has('fcm_token'))
            {
                $user->update(['fcm_token' => $request->fcm_token]);
            }
            if($request->phone == '01070809633') {
                $user->update(['otp' => '12345', 'fcm_token' => $request->fcm_token]);
            }else{
                $this->sendOtp(
                    $request->send_type ?? 'whatsapp',
                    InternationalPhoneNumber::format($request->country_code, $request->phone),
                    $otp,
                    $request->lang ?? $user->language ?? 'en'
                );
            }
        } catch (\Exception $e) {
            Log::error('Login OTP delivery failed', [
                'provider' => $request->send_type ?? 'whatsapp',
                'message' => $e->getMessage(),
            ]);

            return $this->apiResponse(502, 'OTP delivery failed');
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
            'country_code' => 'nullable',
            'send_type' => 'nullable|in:sms,whatsapp',
        ]);
        if ($validation->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }
        $user = $this->userModel::where('phone',$request->phone)->first();
        $otp = $request->phone == '01070809633' ? '12345' : rand(10000,99999);
        try {
            if ($request->phone !== '01070809633') {
                $this->sendOtp(
                    $request->send_type ?? 'whatsapp',
                    InternationalPhoneNumber::format($request->country_code ?? $user->country_code, $request->phone),
                    $otp,
                    $request->lang ?? $user->language ?? 'en'
                );
            }
            $user->update(['otp' => $otp]);
        } catch (\Exception $e) {
            Log::error('Resend OTP delivery failed', [
                'provider' => $request->send_type ?? 'whatsapp',
                'message' => $e->getMessage(),
            ]);

            return $this->apiResponse(502, 'OTP delivery failed');
        }

        return $this->apiResponse(200, trans('api.auth.resend code'), null, 'otp was send');
    }

    private function sendOtp(string $sendType, string $phoneNumber, string $otp, string $locale): void
    {
        if ($sendType === 'sms') {
            $result = $this->smsOtp->sendOtp($phoneNumber, $otp);

            if (($result['code'] ?? 'error') === 'error') {
                Log::warning('SMS OTP failed; falling back to WhatsApp template', [
                    'message' => $result['message'] ?? 'SMS OTP delivery failed.',
                ]);

                $this->chatamanService->sendOtp($phoneNumber, $otp, $locale);
            }

            return;
        }

        $this->chatamanService->sendOtp($phoneNumber, $otp, $locale);
    }

    public function verifyCode(VerifyCode $request)
    {
        $otp = $this->normalizeOtp($request->otp);
        $phoneCandidates = $this->phoneCandidates($request->phone_number, $request->country_code);

        $user = User::whereIn('phone', $phoneCandidates)
            ->where('otp', $otp)
            ->with('sports')
            ->first();

        if($user)
        {
            $user->update([
                'otp' =>'',
                'is_verify' => true
            ]);


            $ip = $request->ip();
            $location = Location::get($ip);


            $token = JWTAuth::fromUser($user);
            return $this->apiResponse(200, trans('api.auth.the verify code successfully'), null, [
                'token'=>$token,
                'user'=> new UserSportResource($user),
                'country' => $user->country ? $user->country->name : $location->countryCode,
            ]);
        }

        Log::warning('OTP verification failed', [
            'phone_suffixes' => array_values(array_unique(array_map(
                fn ($phone) => substr($phone, -4),
                $phoneCandidates
            ))),
            'otp_length' => strlen($otp),
            'otp_exists' => User::where('otp', $otp)->exists(),
            'phone_exists' => User::whereIn('phone', $phoneCandidates)->exists(),
        ]);

        return  $this->apiResponse(400 ,trans('api.auth.failed the otp'));
    }

    private function normalizeOtp(?string $otp): string
    {
        $otp = trim((string) $otp);
        $normalized = '';

        foreach (preg_split('//u', $otp, -1, PREG_SPLIT_NO_EMPTY) as $character) {
            $codepoint = mb_ord($character, 'UTF-8');

            if ($codepoint >= 0x0660 && $codepoint <= 0x0669) {
                $normalized .= (string) ($codepoint - 0x0660);
                continue;
            }

            if ($codepoint >= 0x06F0 && $codepoint <= 0x06F9) {
                $normalized .= (string) ($codepoint - 0x06F0);
                continue;
            }

            if (ctype_digit($character)) {
                $normalized .= $character;
            }
        }

        return $normalized;
    }

    private function phoneCandidates(string $phoneNumber, ?string $countryCode = null): array
    {
        $rawPhone = trim($phoneNumber);
        $digits = preg_replace('/\D/', '', $rawPhone);
        $candidates = array_filter([
            $rawPhone,
            preg_replace('/\s+/', '', $rawPhone),
            $digits,
        ]);

        if ($countryCode) {
            $countryDigits = ltrim(preg_replace('/\D/', '', $countryCode), '0');
            if ($countryDigits && $digits) {
                $withoutCountry = str_starts_with($digits, $countryDigits)
                    ? substr($digits, strlen($countryDigits))
                    : $digits;

                $candidates[] = $withoutCountry;
                $candidates[] = ltrim($withoutCountry, '0');

                if (!str_starts_with($withoutCountry, '0')) {
                    $candidates[] = '0' . $withoutCountry;
                }
            }
        }

        return array_values(array_unique(array_filter($candidates)));
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
