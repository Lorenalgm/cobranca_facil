<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Services;

use App\Domain\Invoices\Factories\CreateInvoice;
use App\Domain\Invoices\Validators\InvoiceValidator;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;

final class CreateInvoicesFromFile
{
    /*
     * A better, more scalable and extendable approach for the long run would be to
     * rely on domain events. That way, we could add functionality with ease.
     * For example, one line of the file fails, we could send an event that could be 
     * handled by any number of listeners (one for logging, one for notifiying someone, etc.)
     */
    public static function process(UploadedFile $file): void
    {
        $fileReader = Reader::createFromPath($file->getRealPath(), 'r');
        $fileReader->setHeaderOffset(0);
        
        /**
         * @var array{
         *  name: string,
         *  governmentId: int,
         *  email: string,
         *  debtAmount: float,
         *  debtDueDate: string,
         *  debtId: string
         * } $invoiceAttributes
         */
        foreach ($fileReader as $invoiceAttributes) {
            $invoiceValidator = InvoiceValidator::make($invoiceAttributes);

            if ($invoiceValidator->fails()) {
                continue;
            }

            CreateInvoice::for($invoiceAttributes);
        }
    }
}