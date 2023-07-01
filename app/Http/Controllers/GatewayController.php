<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;

class GatewayController extends Controller
{
    public static function store($invoice){
        $gateway = [
            'barcode' => Hash::make($invoice->debtId),
            'date' =>  Date('Y-m-d'),
        ];

        return $gateway;
    }
}
