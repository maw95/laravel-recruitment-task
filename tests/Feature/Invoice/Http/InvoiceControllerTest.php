<?php

declare(strict_types=1);

namespace Tests\Feature\Invoice\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        $this->setUpFaker();

        parent::setUp();
    }

    public function testTest(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('viewInvoiceProvider')]
    public function testView(callable $invoiceFactory): void
    {
        $invoice = $invoiceFactory();
        $uri = route('invoice.view', ['invoice' => $invoice]);

        $response = $this->getJson($uri);
        $response->assertOk();
        $response->assertJson([
            'id' => $invoice->id->toString(),
            'status' => $invoice->status->value,
            'customerName' => $invoice->customer_name,
            'customerEmail' => $invoice->customer_email,
            'invoiceProductLines' => $invoice->invoiceProductLines->map(fn (InvoiceProductLine $line) => [
                'productName' => $line->name,
                'quantity' => $line->quantity,
                'unitPrice' => $line->price,
                'totalUnitPrice' => $line->getTotalUnitPrice(),
            ])->toArray(),
            'totalPrice' => $invoice->getTotalPrice(),
        ]);
    }

    public static function viewInvoiceProvider(): array
    {
        return [
            [fn () => Invoice::factory()->create(['status' => StatusEnum::Draft])],
            [fn () => Invoice::factory()->has(InvoiceProductLine::factory(1))->create(['status' => StatusEnum::Sending])],
            [fn () => Invoice::factory()->has(InvoiceProductLine::factory(2))->create(['status' => StatusEnum::SentToClient])],
        ];
    }

    public function testViewNonExistingInvoice(): void
    {
        $nonExistingInvoiceId = 'non-existing-id';

        $uri = route('invoice.view', ['invoice' => $nonExistingInvoiceId]);

        $response = $this->getJson($uri);

        $response->assertNotFound();
    }

    public function testCreateInvoice(): void
    {
        $validData = [
            'customerName' => $this->faker->name(),
            'customerEmail' => $this->faker->email(),
            'invoiceProductLines' => [
                [
                    'name' => $this->faker->word(),
                    'quantity' => $this->faker->numberBetween(1, 10),
                    'price' => $this->faker->numberBetween(1, 100),
                ],
                [
                    'name' => $this->faker->word(),
                    'quantity' => $this->faker->numberBetween(1, 10),
                    'price' => $this->faker->numberBetween(1, 100),
                ],
            ],
        ];

        $uri = route('invoice.create');

        $response = $this->postJson($uri, $validData);

        $response->assertCreated(); // Asserts a 201 status code
        $response->assertJsonStructure([
            'id',
            'customerName',
            'customerEmail',
            'invoiceProductLines' => [
                '*' => [
                    'productName',
                    'quantity',
                    'unitPrice',
                    'totalUnitPrice',
                ],
            ],
            'totalPrice',
        ]);

        $this->assertDatabaseHas((new Invoice)->getTable(), [
            'id' => $response->json('id'),
            'status' => StatusEnum::Draft,
            'customer_name' => $validData['customerName'],
            'customer_email' => $validData['customerEmail'],
        ]);

        foreach ($validData['invoiceProductLines'] as $line) {
            $this->assertDatabaseHas((new InvoiceProductLine)->getTable(), [
                'name' => $line['name'],
                'quantity' => $line['quantity'],
                'price' => $line['price'],
                'invoice_id' => $response->json('id'),
            ]);
        }
    }

    public function testCreateInvoiceValidation(): void
    {
        $invalidData = [
            'customerName' => '',
            'customerEmail' => 'invalid-email',
            'invoiceProductLines' => [
                [
                    'name' => '',
                    'quantity' => 0,
                    'price' => -10,
                ],
            ],
        ];

        $uri = route('invoice.create');

        $response = $this->postJson($uri, $invalidData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'customerName',
            'customerEmail',
            'invoiceProductLines.0.name',
            'invoiceProductLines.0.quantity',
            'invoiceProductLines.0.price',
        ]);
    }

    public function testSendInvoice(): void
    {
        $invoice = Invoice::factory()
            ->has(InvoiceProductLine::factory(2))
            ->create(['status' => StatusEnum::Draft]);

        $uri = route('invoice.send', ['invoice' => $invoice]);

        $response = $this->postJson($uri);

        $response->assertOk();
        $this->assertDatabaseHas((new Invoice)->getTable(), [
            'id' => $invoice->id,
            'status' => StatusEnum::Sending,
        ]);
    }

    public function testSendInvoiceWithIncorrectStatus(): void
    {
        $invoice = Invoice::factory()
            ->has(InvoiceProductLine::factory(2))
            ->create();

        $invoice->status = StatusEnum::Sending;
        $invoice->save();

        $uri = route('invoice.send', ['invoice' => $invoice]);

        $response = $this->postJson($uri);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'Invoice not in draft status',
        ]);
    }

    public function testSendInvoiceWithoutProductLines(): void
    {
        $invoice = Invoice::factory()->create(['status' => StatusEnum::Draft]);

        $uri = route('invoice.send', ['invoice' => $invoice]);

        $response = $this->postJson($uri);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'Invoice must have product lines',
        ]);
    }
}
