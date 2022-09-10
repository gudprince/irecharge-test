<?php

namespace App\Services;

use App\Models\Payment;
use App\Traits\BaseResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    use BaseResponse;

    /**
     * Process the card payment
     * @return array
     */
    public function handlePayment($payload)
    {
        try {

            $result = $this->chargeCard($payload);

            if ($result['status'] == "error") {

                return $result;
            }

            $mode = $result['meta']['authorization']['mode'];

            //create the payment record in database 
            if ($mode == 'otp' || $mode == 'redirect') {
                $params = [
                    'reference' => $payload['tx_ref'],
                    'amount'  => $result['data']['amount'],
                    'transaction_id' => $result['data']['id'],
                    'product_id'  => $payload['meta']['product_id'],
                    'customer_id'  => $payload['meta']['customer_id'],
                    'payment_method' => "CARD",
                    'currency' => $payload['currency'],
                    'customer_email' =>  $payload['email'],
                ];

                Payment::create($params);
            }

            //return authentication mode
            if ($mode == 'pin' || $mode == 'avs_noauth') {

                return $result;
            }

            if ($mode == 'otp') {
                $response = $this->validateOpt($result);

                return $response;
            }

            if ($mode == 'redirect') {

                return  $result;
            }
        } catch (\Exception $e) {
            throw new HttpResponseException(
                $this->sendError('An Error Occured', ['error' => $e->getMessage()], 500)
            );
        }
    }

    /**
     * Obtain Flutterwave information
     * @return array
     */
    public function handleGatewayCallback($response)
    {
        $status = $response->status;
        if ($status === 'successful') {
            $response = $this->verifyPayment($response->id, $status);

            return $response;
        }

        return ['status' => false, 'message' => 'Payment Failed'];
    }

    /**
     * Encrypt the payload
     * @return string
     */
    protected function encrypt(string $encryptionKey, array $payload)
    {
        $encrypted = openssl_encrypt(json_encode($payload), 'DES-EDE3', $encryptionKey, OPENSSL_RAW_DATA);

        return base64_encode($encrypted);
    }

    /**
     * Initiate the payment
     * @return array
     */

    protected function chargeCard(array $params)
    {
        $encrytionKey = config('service.flutterwave.encryption_key');
        $secretKey = config('service.flutterwave.secret');

        $encryptedData = $this->encrypt($encrytionKey, $params);
        $data = ["client" => $encryptedData];
        $chargeUrl = "https://api.flutterwave.com/v3/charges?type=card";

        $response = Http::withToken($secretKey)->post($chargeUrl, $data);
        $response = $response->json();
        return $response;
    }

    /**
     *  Validate the charge
     * @return array
     */

    protected function validateOpt(array $result)
    {
        $transactionId = $result['data']['id'];
        $flwRef = $result['data']['flw_ref'];
        $otp = '12345';
        $secretKey = config('service.flutterwave.secret');
        $data = ['otp' => $otp, 'flw_ref' => $flwRef];
        $validateUrl = 'https://api.flutterwave.com/v3/validate-charge';
        $response = Http::withToken($secretKey)->post($validateUrl, $data);
        $response = $response->json();

        $status = $response['data']['status'];
        if ($status === 'successful' || $status === 'pending') {

            $status = $response['data']['status'];
            $transactionId = $response['data']['id'];
            $result = $this->verifyPayment($transactionId, $status);

            return $result;
        }


        return $response;
    }

    /**
     * Verify the payment
     * @return array
     */
    protected function verifyPayment(int $transactionId, string $paymentStatus)
    {
        $verifyUrl = 'https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify';
        $secretKey = config('service.flutterwave.secret');

        $response = Http::withToken($secretKey)->get($verifyUrl);
        $response = $response->json();

        $payment = Payment::where('transaction_id', $transactionId)->first();

        $payment->update([
            'status' => $paymentStatus,
            'paid_at' => now()
        ]);

        return $response;
    }
}
