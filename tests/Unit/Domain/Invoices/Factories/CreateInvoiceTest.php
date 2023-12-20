<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Invoices\Factories;

use App\Domain\Invoices\Factories\CreateInvoice;
use App\Models\Invoice;
use function fake;
use Tests\TestCase;

final class CreateInvoiceTest extends TestCase
{
    public function test_it_creates_an_invoice(): void
    {
        $invoiceAttributes = [
            'name' => fake()->name,
            'governmentId' => fake()->randomNumber(),
            'email' => fake()->email,
            'debtAmount' => fake()->randomFloat(2, 0, 1000),
            'debtDueDate' => fake()->date,
            'debtId' => fake()->uuid,
        ];

        $invoice = CreateInvoice::for($invoiceAttributes);

        $this->assertDatabaseCount(Invoice::class, 1);
        $this->assertDatabaseHas(Invoice::class, [
            'name' => $invoiceAttributes['name'],
            'government_id' => $invoiceAttributes['governmentId'],
            'email' => $invoiceAttributes['email'],
            'debt_amount' => $invoiceAttributes['debtAmount'],
            'debt_due_date' => $invoiceAttributes['debtDueDate'],
            'debt_id' => $invoiceAttributes['debtId'],
        ]);
    }
}
