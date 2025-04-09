<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Invoices\Domain\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Models\Invoice;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Invoice>
 */
final class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'id' => Uuid::uuid4(),
            'status' => fake()->randomElement(StatusEnum::cases()),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->unique()->safeEmail(),
        ];
    }
}
