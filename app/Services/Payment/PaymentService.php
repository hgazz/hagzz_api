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
        $secretkey = "rMCFUAmM7IsnbHPQh0NoLtBT2Pr/lZ0FYT2tVaOCBTihdfegIpKwCEEJTkR8tP0uc3Vpp+8xOEW1BS+qy6cnwq+OELyOyUvdpsKmvqTQhrM/PIK0VzhMFBEtAlTWwPS9D0ciHjNZoyAxABI9Hes/AZr0RPoFW/LTiYJ2nr1QRNxqh2pQG08XWv8TV5GJpEFtme2q/fXBRRx+PRpREdYg4hWL7WCXktWzq7JU/3f+czbGZpmxyFjsxdKKwbHoORtyU9MxZPeJf9ghfJahsfGcLjwyfZ36LYBPmjMzS641u36h48DKYzZftwLUWE1jRjUBvalqhzi1dDXzss3p/xqvQ2xAWBGsER4vUNin91GARHEkVM0GxJQTDw+D3Davolpm/MDaRJ4UTbjiOryRwcDwiCJN3uud6QEskln+ZsTMe6q7353PIUCPABfz23u5GCj88I9lIkFJlPLyyDrlQoNI1Ci064Sncw950FM50QDTMGJVe2c6SS+mahlQcGyFNH9tLJ68iVJ8Ia8pjIp7cmn0sg==";
        $data = [
            'Uid' => $myuuid,
            'KeyId' => "6681779a-b2b5-475c-8fc3-b3806a62a365",
            'Amount' => strval($amount),
            'FirstName' => auth()->user()->name,
            'LastName' => auth()->user()->name,
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
            CURLOPT_URL => $productionURL,
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
