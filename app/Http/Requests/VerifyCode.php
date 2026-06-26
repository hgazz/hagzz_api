<?php

namespace App\Http\Requests;

use App\Http\Traits\apiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class VerifyCode extends FormRequest
{
    use apiResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'otp'=>'required|string|min:5',
            'phone_number'=>'required|string',
        ];
    }

    public function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException($this->apiResponse(400, trans('api.validation_errors'), $validator->errors()));
    }

    public function messages()
    {
        return [
            'otp.required' => __('validation.otp_required'),
            'otp.numeric' => __('validation.otp_numeric'),
            'otp.min' => __('validation.otp_min'),
            'otp.exists' => __('validation.otp_exists'),
            'phone_number.required' => __('validation.phone_number_required'),
            'phone_number.exists' => __('validation.phone_number_exists'),
        ];
    }
}
