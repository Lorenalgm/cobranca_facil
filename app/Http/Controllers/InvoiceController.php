<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;
use Resend;
use Resend\Client;


class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $invoices = Invoice::all();

            return response()->json($invoices);
        } catch (Exception $error) {
            return response()->json([
                'error' => 'Error. Try again',
                'message' => $error->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            if(!$request->hasFile('csv_file')){
                return response()->json([
                    'error' => 'File is required.'
                ], 400);
            }

            $file = $request->file('csv_file');

            if($file->getClientOriginalExtension() != 'csv'){
                return response()->json([
                    'error' => 'Invalid csv file.'
                ], 400);
            }

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
            
            foreach($csv as $invoice){
                $validator = Validator::make($invoice, $rules);

                if($validator->fails()){
                    return response()->json([
                        'error' => $validator->errors()
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
                'message' => 'CSV uploaded sucessfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error. Try again',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateDailyInvoices(){
        try {
            $invoices = Invoice::all();
            // $invoices = Invoice::getInvoicesWithoutBarCode();
            $count_invoices = 0;

            foreach($invoices as $invoice){
                $gateway = GatewayController::store($invoice);

                if($gateway){
                    $invoice->invoice_barcode = $gateway['barcode'];
                    $invoice->invoice_due_date = $gateway['date'];
                    $invoice->update();

                    $resend = Resend::client(env('RESEND_API_KEY'));

                    $resend->emails->send([
                    'from' => 'onboarding@resend.dev',
                    'to' => $invoice->email,
                    'subject' => 'Lembrete de pagamento!',
                    'html' => "<p>Olá, $invoice->name.
                        Você tem um boleto para pagamento: <strong>$invoice->invoice_barcode</strong>!
                    </p>"
                    ]);

                    $count_invoices++;
                }
            }

            return response()->json([
                'message' => $count_invoices.' barcodes created!'
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'error' => 'Error. Try again',
                'message' => $error->getMessage()
            ], 500);
        }
    }
}
