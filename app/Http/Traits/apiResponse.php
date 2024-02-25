<?php

namespace App\Http\Traits;

trait apiResponse
{
    public function apiResponse($code = 200, $message = null, $errors = null, $data = null)
    {
        $array = [
            'status' => $code,
            'message' => $message,

        ];

        if (is_null($data) && ! is_null($errors)) {
            $array['errors'] = $errors;
        } elseif (is_null($errors) && ! is_null($data)) {
            $array['data'] = $data;
        }

        return response($array, $code);
    }
}
