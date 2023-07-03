<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GatewayTest extends TestCase
{

    public function testCreateInvoices(){
        $data = [
            "debtId" => "12",
            "paidAt"=> "2022-06-09 10:00:00",
            "paidAmount" => 100000.00,
            "paidBy" => "John Doe"
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer xxxxxx',
        ])->post('webhook', $data);

        $response->assertStatus(204);
    }
}
