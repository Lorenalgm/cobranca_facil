<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;

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
    
            $validator = Validator::make(iterator_to_array($csv->getRecords()), $rules);

            if($validator->fails()){
                return response()->json([
                    'error' => $validator->errors()
                ], 400);
            }
            
            foreach($csv as $invoice){
                $invoiceData = [
                    'name' => $invoice['customer_name'],
                    'governmentId' => $invoice['amount'],
                    'email' => $invoice['description'],
                    'debtAmount' => $invoice['description'],
                    'debtDueDate' => $invoice['description'],
                    'debtId' => $invoice['description'],
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
}
