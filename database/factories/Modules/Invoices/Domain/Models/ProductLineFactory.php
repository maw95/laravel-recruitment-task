<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Invoices\Domain\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<InvoiceProductLine>
 */
final class ProductLineFactory extends Factory
{
    protected $model = InvoiceProductLine::class;

    public function definition(): array
    {
        return [
            'id' => Uuid::uuid4(),
            'name' => fake()->word,
            'quantity' => fake()->numberBetween(1, 100),
            'price' => fake()->numberBetween(1, 1000),
            'invoice_id' => Invoice::factory(),
        ];
    }
}
