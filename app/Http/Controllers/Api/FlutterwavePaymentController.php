<?php

namespace App\Http\Controllers\Api;

use App\Traits\BaseResponse;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\ValidateOtpRequest;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;

class FlutterwavePaymentController extends Controller
{
    use BaseResponse;

    public $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\HttpResponseException
     * @return array
     */
    public function pay(PaymentRequest $request)
    {
        $data = $this->paymentService->pay($request->all());

        return response()->json($data, 200);
    }

     /**
     * @param Request $request
     * @return array
     */
    public function authorizePayment(Request $request)
    {
        $data = $this->paymentService->authorizePayment($request->all());

        return response()->json($data, 200);
    }

     /**
     * @param Request $request
     * @throws \Illuminate\Validation\HttpResponseException
     * @return array
     */
    public function validateOtp(ValidateOtpRequest $request)
    {
        $data = $this->paymentService->validateOtp($request->all());

        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\HttpResponseException
     * @return array
     */
    public function handleCallback(Request $request)
    {
        $data = json_decode($request->response);
        $data = $this->paymentService->handleGatewayCallback($data);

        return response()->json($data, 200);
    }
}
