<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Invoice;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function testGetInvoices(){
        Invoice::factory()->count(2)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer xxxxxx',
        ])->get('/api/v1/invoices');

        $response->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) =>
        $json->has(2)
            ->first(fn (AssertableJson $json) =>
                $json->hasAll(['name', 'government_id', 'email'])
                    ->etc()
            )
        );
        
    }

    public function testCreateInvoices(){
        $data = [
            'csv_file' => new \Illuminate\Http\UploadedFile(
                resource_path('upload-teste.csv'),
                'test.csv',
                'text/csv',
                null,
                true
            )
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer xxxxxx',
        ])->post('api/v1/invoices', $data);

        $response->assertStatus(200);
    }
}
