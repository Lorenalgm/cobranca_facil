<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fails_when_file_is_missing(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer xxxxxx'])->postJson('api/v1/invoices', []);

        $response
            ->assertJsonValidationErrors(['csv_file' => 'File is required.'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_fails_when_file_type_is_invalid(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer xxxxxx'])
            ->postJson('api/v1/invoices', [
                'csv_file' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        $response
            ->assertJsonValidationErrors(['csv_file' => 'Invalid csv file.'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_returns_all_invoices(): void
    {
        [$firstInvoice, $secondInvoice] = Invoice::factory()->count(2)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer xxxxxx'])->get('/api/v1/invoices');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('invoices', 2)
                ->has('invoices.0', fn (AssertableJson $json) => $json
                    ->where('id', $firstInvoice->id)
                    ->where('name', $firstInvoice->name)
                    ->where('government_id', $firstInvoice->government_id)
                    ->where('email', $firstInvoice->email)
                    ->where('debt_amount', number_format($firstInvoice->debt_amount, 2))
                    ->where('debt_due_date', $firstInvoice->debt_due_date)
                    ->where('debt_id', $firstInvoice->debt_id)
                    ->etc()
                )
                ->has('invoices.1', fn (AssertableJson $json) => $json
                    ->where('id', $secondInvoice->id)
                    ->where('name', $secondInvoice->name)
                    ->where('government_id', $secondInvoice->government_id)
                    ->where('email', $secondInvoice->email)
                    ->where('debt_amount', number_format($secondInvoice->debt_amount, 2))
                    ->where('debt_due_date', $secondInvoice->debt_due_date)
                    ->where('debt_id', $secondInvoice->debt_id)
                    ->etc()
                )
            );
    }

    public function test_it_create_invoices_from_file(): void
    {
        $givenContent = <<<'CSV'
        name,governmentId,email,debtAmount,debtDueDate,debtId
        Pedro,3458077293,lgoesmontes@gmail.com,10000,2022-01-12,123
        Maria,1258077293,lorena@teste.com,4000,2023-01-12,124
        CSV;

        $givenFile = File::fake()
            ->createWithContent('upload-teste.csv', $givenContent)
            ->mimeType('csv');

        $response = $this->withHeaders(['Authorization' => 'Bearer xxxxxx'])->post('api/v1/invoices', [
            'csv_file' => $givenFile,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount(Invoice::class, 2);

        $this->assertDatabaseHas(Invoice::class, [
            'name' => 'Pedro',
            'government_id' => '3458077293',
            'email' => 'lgoesmontes@gmail.com',
            'debt_amount' => 10000,
            'debt_due_date' => '2022-01-12',
            'debt_id' => '123',
        ]);

        $this->assertDatabaseHas(Invoice::class, [
            'name' => 'Maria',
            'government_id' => '1258077293',
            'email' => 'lorena@teste.com',
            'debt_amount' => 4000,
            'debt_due_date' => '2023-01-12',
            'debt_id' => '124',
        ]);
    }

    #[DataProvider('provideInvalidInvoiceData')]
    public function test_it_fails_when_invoice_data_is_invalid(string $givenContent): void
    {
        $givenFile = File::fake()
            ->createWithContent('upload-teste.csv', $givenContent)
            ->mimeType('csv');

        $response = $this->withHeaders(['Authorization' => 'Bearer xxxxxx'])->post('api/v1/invoices', [
            'csv_file' => $givenFile,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return Iterator<array<int, string>>
     */
    public function provideInvalidInvoiceData(): Iterator
    {
        yield 'all attributes are missing' => [
            <<<'CSV'
            name,governmentId,email,debtAmount,debtDueDate,debtId
            ,,,,,
            CSV
        ];

        yield 'name is missing' => [
            <<<'CSV'
            name,governmentId,email,debtAmount,debtDueDate,debtId
            ,1,test@test.com,123,2023-01-01,1
            CSV
        ];

        yield 'governmentId is missing' => [
            <<<'CSV'
            name,governmentId,email,debtAmount,debtDueDate,debtId
            some name,,test@test.com,123,2023-01-01,1
            CSV
        ];

        yield 'email is missing' => [
            <<<'CSV'
            name,governmentId,email,debtAmount,debtDueDate,debtId
            some name,1,,123,2023-01-01,1
            CSV
        ];

        yield 'debtAmount is missing' => [
            <<<'CSV'
            name,governmentId,email,debtAmount,debtDueDate,debtId
            some name,1,test@test.com,,2023-01-01,1
            CSV
        ];

        yield 'debtDueDate is missing' => [
            <<<'CSV'
            name,governmentId,email,debtAmount,debtDueDate,debtId
            some name,1,test@test.com,123,,1
            CSV
        ];

        yield 'debtId is missing' => [
            <<<'CSV'
            name,governmentId,email,debtAmount,debtDueDate,debtId
            some name,1,test@test.com,123,2023-01-01,
            CSV
        ];
    }
}
