<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fails_when_file_is_missing(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer xxxxxx'])->postJson('api/v1/invoices', []);
    
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['csv_file' => 'File is required.']);
    }

    public function test_it_fails_when_file_type_is_invalid(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer xxxxxx'])
            ->postJson('api/v1/invoices', [
                'csv_file' => UploadedFile::fake()->image('avatar.jpg'),
            ]);
    
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['csv_file' => 'Invalid csv file.']);
    }

    public function testGetInvoices()
    {
        Invoice::factory()->count(2)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer xxxxxx',
        ])->get('/api/v1/invoices');

        $response->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) => $json->has(2)
            ->first(fn (AssertableJson $json) => $json->hasAll(['name', 'government_id', 'email'])
                ->etc()
            )
        );

    }

    public function testCreateInvoices()
    {
        $data = [
            'csv_file' => new \Illuminate\Http\UploadedFile(
                resource_path('upload-teste.csv'),
                'test.csv',
                'text/csv',
                null,
                true
            ),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer xxxxxx',
        ])->post('api/v1/invoices', $data);

        $response->assertStatus(200);
    }
}
