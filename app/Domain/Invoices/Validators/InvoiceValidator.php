<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Validators;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

final class InvoiceValidator
{
    /** @var array<string, string> */
    protected static array $rules = [
        'name' => 'required|string',
        'governmentId' => 'required|numeric',
        'email' => 'required|email',
        'debtAmount' => 'required|numeric',
        'debtDueDate' => 'required|date',
        'debtId' => 'required|string',
    ];

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
    public static function make(array $invoiceAttributes): Validator
    {
        return ValidatorFacade::make($invoiceAttributes, self::$rules);
    }
}
