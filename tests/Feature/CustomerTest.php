<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class CustomerTest extends TestCase
{   
    use RefreshDatabase;

    /**
     * Store a new User.
     *
     * @return void
     */
    public function testStoreUser()
    {   
        $customerData = [
            'name' => 'Anochie Prince',
            'phone_number' => '08161155633',
            'email' => 'test1@gmail.com',
            'password' => '12345678',
        ];

        $response = $this->json('POST',url('api/v1/customers'), $customerData);
        $response->assertStatus(201);
        $this->assertArrayHasKey('data',$response);
        $this->assertEquals($response['status'],'success');

       
    }
}
