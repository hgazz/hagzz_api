<?php

namespace App\Services\Payment;

class PaymentService
{

    public static function payment($amount)
    {
        function guidv4($data = null)
        {
            $data = $data ?? random_bytes(16);
            assert(strlen($data) == 16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        $sanboxURL = "https://skipcashtest.azurewebsites.net/api/v1/payments";
        $productionURL = "https://api.skipcash.app/api/v1/payments";

        $myuuid = guidv4();
        $secretkey = "LILYNDkSTjYOlaDLT1qNw2kGoHiwwhxZ1UMnCDQsO/T4AhWN0RlVdatfenQdZ+RdaV+oZ5xWAf8kItGgFTjMYTkRhXX+9gboF0oGwzK/Dr6taRHriQxh1COOIAG/SjtE3FIlJVKyUzPmDnHQIov5ek6v+U6NpfdFvu3MMVJzLh7drxv+6uvWIb6A+LtQPo/MFo6X3oHOIjdAsWTzyj06jjb45Yewmnep1UPXSgX2CmqlaPqXD/D6tQY1JO3gNTXTzpbxvenkpDqg/1J4BZ8kJCdU7VQ0gs6oAWIAluveROfAH5Du2/l6LHp7tI93LS4L8TB2M4jRwRB1nzX8VhnTLHqmeAye9tb90DpD1te7yBfst1CIdRu+RRD31IWD51TDZKlFaFd4eVsbdIkQgUiI9pUXQGD4SUXwQinh+vG7hTob95nvhEhd2sfdz72bVnIObK7VAztsP7jhLgAhPXWcANTmLAeEomAO+tBaBSMqj8FTsS5egskxrm1A+q2eJaiVt9TnBc9KgLUd6f1FOAhorA==";
        $data = [
            'Uid' => $myuuid,
            'KeyId' => "04d4005f-9475-4d8f-845a-c581f242343d",
            'Amount' => strval($amount),
            'FirstName' => auth()->user()->first_name,
            'LastName' => auth()->user()->last_name,
            'Phone' => '+976' . auth()->user()->phone,
            'Email' => "achouak@skipcash.com",
            'TransactionId' => "005"
        ];

        $data_string = json_encode($data);
        $resultheader = "Uid=" . $data['Uid'] . ',KeyId=' . $data['KeyId'] . ',Amount=' . $data['Amount'] . ',FirstName=' . $data['FirstName'] . ',LastName=' . $data['LastName'] . ',Phone=' . $data['Phone'] . ',Email=' . $data['Email'] . ',TransactionId=' . $data['TransactionId'];
        $s = hash_hmac('sha256', $resultheader, $secretkey, true);
        $authorisationheader = base64_encode($s);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $sanboxURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_HTTPHEADER => [
                'Content-Type:application/json', 'Authorization:' . $authorisationheader
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $responseData = json_decode($response, true);

        $formattedResponse = [
            'resultObj' => $responseData['resultObj'],
            'returnCode' => $responseData['returnCode'],
            'errorCode' => $responseData['errorCode'],
            'errorMessage' => $responseData['errorMessage'],
            'error' => $responseData['error'],
            'validationErrors' => $responseData['validationErrors'],
            'hasError' => $responseData['hasError'],
            'hasValidationError' => $responseData['hasValidationError']
        ];

        return $formattedResponse;
    }
}
