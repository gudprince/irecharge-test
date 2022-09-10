<?php

namespace App\Http\Controllers\Api;

use App\Traits\BaseResponse;
use App\Http\Requests\StoreCustomerRequest;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    use BaseResponse;

    public $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * @return Customer|mixed
     */

    public function index()
    {

        $customers = $this->customerService->all();
        if (!count($customers) > 0) {

            return $this->sendResponse($customers, 'Record is Empty.');
        }
        return $this->sendResponse($customers, 'Record retrieved successfully.');
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\HttpResponseException
     * @return Customer|mixed
    */
    public function save(StoreCustomerRequest $request)
    {
        $customer = $this->customerService->store($request->all());

        return $this->sendResponse($customer, 'Customer Created Successfully.', 201);
    }

    /**
     * @param $id
     * @return array
     */
    public function find($id)
    {
        $customer = $this->customerService->find($id);
        if (!$customer) {

            return $this->sendError('Customer not Found.', [], 404);
        }

        return $this->sendResponse($customer, 'Customer Retrieved Successfully.');
    }

    /**
     * @param $id
     * @return array
     */
    public function payment($customerId)
    {
        $customer = $this->customerService->payment($customerId);
        if (!$customer) {

            return $this->sendError('Customer not Found.', [], 404);
        }

        return $this->sendResponse($customer, 'Customer Retrieved Successfully.');
    }
}
