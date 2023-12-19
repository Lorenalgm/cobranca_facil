<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoicesFileRequest;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Resend;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Must explicitly order
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
        $validated = $request->validated();

        try {
            /** @var UploadedFile */
            $file = $request->file('csv_file');

            $filePath = $file->getRealPath();
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            $rules = [
                'name' => 'required|string',
                'governmentId' => 'required|numeric',
                'email' => 'required|email',
                'debtAmount' => 'required|numeric',
                'debtDueDate' => 'required|date',
                'debtId' => 'required|string',
            ];

            foreach ($csv as $invoice) {
                $validator = Validator::make($invoice, $rules);

                if ($validator->fails()) {
                    return response()->json([
                        'error' => $validator->errors(),
                    ], 400);
                }

                $invoiceData = [
                    'name' => $invoice['name'],
                    'government_id' => $invoice['governmentId'],
                    'email' => $invoice['email'],
                    'debt_amount' => $invoice['debtAmount'],
                    'debt_due_date' => $invoice['debtDueDate'],
                    'debt_id' => $invoice['debtId'],
                ];

                Invoice::create($invoiceData);
            }

            File::delete($filePath);

            return response()->json([
                'message' => 'CSV uploaded sucessfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error. Try again',
                'message' => $e->getMessage(),
            ], 500);
        }
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
