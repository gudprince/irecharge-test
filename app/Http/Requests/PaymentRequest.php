<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\BaseResponse;

class PaymentRequest extends FormRequest
{
    use BaseResponse;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "card_number" => "required|string",
            "cvv" => "required|string",
            "expiry_month" => "required|string",
            "expiry_year" => "required|string",
            "currency" => "required|string",
            "amount" => "required|string",
            "email" => "required|string",
            "fullname" => "required|string",
            "tx_ref" => "required|string",
            "redirect_url" => "required|string",
            "meta.customer_id" => "required|exists:customers,id",
            "meta.product_id" => "required|numeric",
        ];
        
        
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            $this->sendError('An Error Occured', $errors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
