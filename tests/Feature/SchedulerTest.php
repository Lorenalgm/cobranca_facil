<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Console\Scheduling\Schedule;

class SchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function testDailyInvoiceGenerationTask(){
        $this->artisan('schedule:run')
        ->assertExitCode(0);
    }
}
