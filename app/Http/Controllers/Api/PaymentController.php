<?php

namespace App\Http\Controllers\Api;

use App\Traits\BaseResponse;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Http\Controllers\Controller;


class PaymentController extends Controller
{
    use BaseResponse;

    /**
     * @return Customer|Payment
     */
    public function customerPayment($customerId)
    {
        $payments = Payment::where('customer_id', $customerId)->get();

        return $this->sendResponse(PaymentResource::collection($payments), 'Payments Retrieved Successfully.');
    }
}
