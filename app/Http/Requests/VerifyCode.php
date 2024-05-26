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
            'otp'=>'required|numeric|min:5|exists:users,otp',
            'phone_number'=>'required|exists:users,phone',
        ];
    }

    public function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException($this->apiResponse(400, trans('api.validation_errors'), $validator->errors()));
    }

    public function messages()
    {
        return [
            'otp.required' => 'The otp field is required.',
            'otp.numeric' => 'The otp must be a number.',
            'otp.min' => 'The otp must be at least 5 characters.',
            'otp.exists' => 'The otp is not correct.',
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.exists' => 'The phone number is not correct.',
        ];
    }
}
