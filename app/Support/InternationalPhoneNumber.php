<?php

namespace App\Support;

use InvalidArgumentException;

class InternationalPhoneNumber
{
    public static function format(string $countryCode, string $phoneNumber): string
    {
        $countryCode = ltrim(preg_replace('/\D/', '', $countryCode), '0');
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        if ($countryCode === '' || $phoneNumber === '') {
            throw new InvalidArgumentException('Country code and phone number are required.');
        }

        if (str_starts_with($phoneNumber, '00')) {
            return '+' . substr($phoneNumber, 2);
        }

        if (str_starts_with($phoneNumber, $countryCode)) {
            return '+' . $phoneNumber;
        }

        // The released mobile app sends Egypt as "+2" with a leading-zero number.
        if ($countryCode === '2' && str_starts_with($phoneNumber, '0')) {
            return '+' . $countryCode . $phoneNumber;
        }

        return '+' . $countryCode . ltrim($phoneNumber, '0');
    }
}
