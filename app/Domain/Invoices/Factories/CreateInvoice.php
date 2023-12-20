<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Factories;

use App\Models\Invoice;

final class CreateInvoice
{
    /**
     * @param array{
     *  name: string,
     *  governmentId: int,
     *  email: string,
     *  debtAmount: float,
     *  debtDueDate: string,
     *  debtId: string
     * } $invoiceAttributes
     */
    public static function for(array $invoiceAttributes): Invoice
    {
        return Invoice::create([
            'name' => $invoiceAttributes['name'],
            'government_id' => $invoiceAttributes['governmentId'],
            'email' => $invoiceAttributes['email'],
            'debt_amount' => $invoiceAttributes['debtAmount'],
            'debt_due_date' => $invoiceAttributes['debtDueDate'],
            'debt_id' => $invoiceAttributes['debtId'],
        ]);
    }
}
