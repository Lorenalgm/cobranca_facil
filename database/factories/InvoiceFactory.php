<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'government_id' => Str::random(10),
            'email' => fake()->unique()->safeEmail(),
            'debt_id' => Str::random(5),
            'debt_amount' => 400.00,
            'debt_due_date' => '2023-01-12',
            'invoice_barcode' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'invoice_due_date' => '2023-01-12',
        ];
    }
}
