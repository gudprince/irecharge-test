<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Customer;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
        return [
            "transaction_id" => $this->transaction_id,
            "reference" => $this->reference,
            "amount" => $this->amount,
            "product_id" =>$this->product_id,
            "payment_method" => $this->payment_method,
            "currency" => $this->currency,
            "customer_id" => $this->customer_id,
            "customer_email" => $this->customer_email,
            "status" => $this->status,
            "paid_at" => $this->paid_at,
        ];
    }

    protected function customer($id){

        return Customer::find($id);
    }
}
