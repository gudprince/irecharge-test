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
            "card_number" => "5531886652142950",
            "cvv" => "564",
            "expiry_month" => "09",
            "expiry_year" => "32",
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
            "authorization" => [
                "mode" => "pin",
                "pin" => "3310"
            ]
        ];
        $response = $this->json('POST', url('api/v1/charge-card'), $data);
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals($response['status'],"success");

    }
}
