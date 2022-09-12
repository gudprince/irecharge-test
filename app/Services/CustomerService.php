<?php

namespace App\Services;

use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use App\Traits\BaseResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    use BaseResponse;

    /**
     * @return array
     */
    public function all()
    {
        $customers = Customer::orderBy('name', 'ASC')->get();
        return $customers->toArray();
    }

    /**
     * @param array $data
     * @return Customer|mixed
     */

    public function store($data)
    {
        try {

            $params = $data;
            $params['password'] = Hash::make($params['password']);
            $customer = Customer::create($params);

            return new CustomerResource($customer);
        } catch (\Exception $e) {

            throw new HttpResponseException(
                $this->sendError('An Error Occured', ['error' => $e->getMessage()], 500)
            );
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function find($id)
    {
        $customer = Customer::find($id);
        if (is_null($customer)) {
            return false;
        }

        return new CustomerResource($customer);
    }

    /**
     * @param int $customId
     * @return mixed
     */
    public function payment($customerId)
    {
        $customer = Customer::find($customerId);

        if (is_null($customer)) {
            return false;
        }

        return new CustomerResource($customer);
    }
}
