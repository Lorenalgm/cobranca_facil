<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Laravel\Paddle\Events\WebhookReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\UpdateInvoice;

class GatewayController extends Controller
{
    public static function store($invoice){
        $gateway = [
            'barcode' => Hash::make($invoice->debtId),
            'date' =>  Date('Y-m-d'),
        ];

        return $gateway;
    }

    public function handleWebhook(Request $request){
        $payload = $request->all();

        $rules = [
            'debtId' => 'required|string',
            'paidAt' => 'required|date',
            'paidAmount' => 'required|numeric',
            'paidBy' => 'required|string',
        ];

        $validator = Validator::make($payload, $rules);

        UpdateInvoice::dispatchIf(!$validator->fails(), $payload);

        return response()->noContent();
    }
}
