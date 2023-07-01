<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health_check', function(){
    return response()->json([ 'sucess' => true]);
});


Route::middleware('guest')->prefix('v1')->group(function() {
    Route::get('/daily', 'InvoiceController@generateDailyInvoices');
    Route::get('/invoices', 'InvoiceController@index');
    Route::post('/invoices','InvoiceController@store');
});