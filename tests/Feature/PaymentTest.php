<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;

class PaymentTest extends TestCase
{   
    use RefreshDatabase;

    /**
     * Charge a Customer Card.
     *
     * @return void
     */
    public function testChargeCard()
    {   
        $customer = Customer::factory()->create();

        $data = [
            "card_number" => "5061460410120223210",
            "cvv" => "780",
            "expiry_month" => "12",
            "expiry_year" => "31",
            "currency" => "NGN",
            "amount" => "200",
            "email" => $customer->email,
            "fullname" => "John Doe",
            "tx_ref" => 'MC-'.strtoupper(uniqid()),
            "redirect_url" => url('api/v1/handle-callback'),
            "meta" => [
                "customer_id" => $customer->id,
                "product_id" => 3
            ],
        ];
        //check the authorization mode and add the pin
        $response = $this->json('POST', url('api/v1/charge-card'), $data);

        $data['authorization'] = [
            "mode" => "pin",
            "pin" => "3310"
        ];

        $response = $this->json('POST', url('api/v1/authorize-payment'), $data);

        //verify the otp
        $params = [
                "flw_ref"=> $response['data']['flw_ref'],
                 "otp" => "12345"
        ];

         //verify the payment
        $response = $this->json('POST', url('api/v1/validate-otp'), $params);
       
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals($response['status'],"success");

    }
}
