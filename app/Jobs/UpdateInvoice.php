<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;

class UpdateInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = $this->data;

        $invoice = Invoice::where('debt_id', $data['debtId'])->first();

        if($invoice){
            $invoice->paid_at = $data['paidAt'];
            $invoice->paid_amount =  $data['paidAmount'];
            $invoice->paid_by = $data['paidBy'];
            $invoice->update();
        }

    }
}
