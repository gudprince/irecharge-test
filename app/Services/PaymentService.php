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
    public function pay($payload)
    {
        try {

            $params = [
                'reference' => $payload['tx_ref'],
                'amount'  => $payload['amount'],
                'product_id'  => $payload['meta']['product_id'],
                'customer_id'  => $payload['meta']['customer_id'],
                'payment_method' => "CARD",
                'currency' => $payload['currency'],
                'customer_email' =>  $payload['email'],
            ];

            $response = $this->chargeCard($payload);

            switch ($response['meta']['authorization']['mode'] ?? null) {
                case 'pin':
                case 'avs_noauth':

                    // Store the payment details
                    Payment::create($params);

                    return $response;

                case 'redirect':

                    // Store the payment details

                    Payment::create($payload);

                    return $response;
                default:

                    // No authorization needed; just verify the payment
                    $transactionId = $response['data']['id'];
                    $paymentStatus = $response['data']['status'];
                    $transaction = $this->verifyPayment($transactionId, $paymentStatus);

                    return $transaction;
            }
        } catch (\Exception $e) {
            throw new HttpResponseException(
                $this->sendError('An Error Occured', ['error' => $e->getMessage()], 500)
            );
        }
    }

    /**
     * Authorize the payment
     * @return array
     */

    public function authorizePayment($payload)
    {

        $response = $this->chargeCard($payload);

        if ($response['status'] == 'error') {
            return $response;
        }

        switch ($response['meta']['authorization']['mode'] ?? null) {
            case 'otp':
                return $response;
            case 'redirect':
                return $response;
            default:
                // No validation needed; just verify the payment
                $transactionId = $response['data']['id'];
                $paymentStatus = $response['data']['status'];
                $transaction = $this->verifyPayment($transactionId, $paymentStatus);
                return $transaction;
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

    protected function chargeCard($params)
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

    public function validateOtp($payload)
    {

        $flwRef = $payload['flw_ref'];
        $otp = $payload['otp'];

        $secretKey = config('service.flutterwave.secret');
        $data = ['otp' => $otp, 'flw_ref' => $flwRef];
        $validateUrl = 'https://api.flutterwave.com/v3/validate-charge';
        $response = Http::withToken($secretKey)->post($validateUrl, $data);
        $response = $response->json();

        if ($response['status'] == 'error') {
            return $response;
        }

        if ($response['data']['status'] === 'successful' || $response['data']['status'] === 'pending') {

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
    protected function verifyPayment($transactionId, $paymentStatus)
    {

        $verifyUrl = 'https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify';
        $secretKey = config('service.flutterwave.secret');

        $response = Http::withToken($secretKey)->get($verifyUrl);

        $response = $response->json();

        $payment = Payment::where('reference', $response['data']['tx_ref'])->first();
        if ($payment) {

            $payment->update([
                'status' => $paymentStatus,
                'paid_at' => now()
            ]);
        }

        return $response;
    }
}
