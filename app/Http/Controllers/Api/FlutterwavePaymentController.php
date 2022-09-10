<?php

namespace App\Http\Controllers\Api;

use App\Traits\BaseResponse;
use App\Http\Requests\PaymentRequest;
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
        $data = $this->paymentService->handlePayment($request->all());

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
