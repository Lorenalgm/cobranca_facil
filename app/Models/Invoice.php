<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'government_id',
        'email',
        'debt_amount',
        'debt_due_date',
        'debt_id',
        'invoice_barcode',
        'invoice_due_date',
        'payment_date'
    ];

    protected $table = 'invoices';

    public static function getInvoicesWithoutBarCode(){
        return Invoice::whereNull('invoice_barcode')->get();
    }
}
