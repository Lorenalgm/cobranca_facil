<?php

namespace App\Http\Controllers;

use App\Domain\Invoices\Services\CreateInvoicesFromFile;
use App\Http\Requests\InvoicesFileRequest;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Resend;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Postgres does not guarantee the order of the records if you don't specify an order by
            $invoices = Invoice::orderBy('id', 'asc')->get();

            return response()->json(['invoices' => $invoices]);
        } catch (Exception $error) {
            return response()->json([
                'error' => 'Error. Try again',
                'message' => $error->getMessage(),
            ], 500);
        }
    }

    public function store(InvoicesFileRequest $request): JsonResponse
    {
        $request->validated();

        /** @var UploadedFile */
        $file = $request->file('csv_file');

        CreateInvoicesFromFile::process($file);

        /*
        * It's a good practice to process as much as possible, instead of throwing and halting
        * the execution of the script. In this case, we could have a log of the errors and give
        * the user a feedback only for the unprocessable invoices.
        */

        return response()->json([
            'message' => 'CSV uploaded sucessfully',
        ], 200);
    }

    public function generateDailyInvoices(): JsonResponse
    {
        try {
            $invoices = Invoice::getInvoicesWithoutBarCode();
            $count_invoices = 0;

            foreach ($invoices as $invoice) {
                $gateway = GatewayController::store($invoice);

                if ($gateway) {
                    $invoice->invoice_barcode = $gateway['barcode'];
                    $invoice->invoice_due_date = $gateway['date'];
                    $invoice->update();

                    /** @var string $apiKeyForResendService */
                    $apiKeyForResendService = env('RESEND_API_KEY');

                    $resend = Resend::client($apiKeyForResendService);

                    $resend->emails->send([
                        'from' => 'onboarding@resend.dev',
                        'to' => $invoice->email,
                        'subject' => 'Lembrete de pagamento!',
                        'html' => "<p>Olá, $invoice->name.
                        Você tem um boleto para pagamento: <strong>$invoice->invoice_barcode</strong>!
                    </p>",
                    ]);

                    $count_invoices++;
                }
            }

            return response()->json([
                'message' => $count_invoices.' barcodes created!',
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'error' => 'Error. Try again',
                'message' => $error->getMessage(),
            ], 500);
        }
    }
}
