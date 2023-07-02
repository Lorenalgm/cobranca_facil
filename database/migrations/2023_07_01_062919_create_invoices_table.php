<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('government_id');
            $table->string('email');
            $table->decimal('debt_amount', 8, 2);
            $table->date('debt_due_date');
            $table->string('debt_id');
            $table->string('invoice_barcode')->nullable();
            $table->string('invoice_due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('paid_amount', 8, 2)->nullable();
            $table->string('paid_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
