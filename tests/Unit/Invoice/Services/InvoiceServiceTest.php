<?php

declare(strict_types=1);

namespace Tests\Unit\Invoice\Services;

use Illuminate\Foundation\Testing\WithFaker;
use Modules\Invoices\Application\Services\InvoiceNotificationServiceInterface;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\InvoiceDoesNotHaveProductLinesException;
use Modules\Invoices\Domain\Exceptions\InvoiceHasIncorrectStatusException;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceProductLineRepositoryInterface;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Presentation\Dtos\CreateInvoiceDTO;
use Modules\Invoices\Presentation\Dtos\ProductLineDTO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceServiceTest extends TestCase
{
    use WithFaker;

    private InvoiceService $invoiceService;

    private InvoiceRepositoryInterface&MockObject $invoiceRepository;

    private InvoiceProductLineRepositoryInterface&MockObject $invoiceProductLineRepository;

    private InvoiceNotificationServiceInterface&MockObject $invoiceNotificationService;

    protected function setUp(): void
    {
        $this->setUpFaker();

        $this->invoiceService = new InvoiceService(
            $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class),
            $this->invoiceProductLineRepository = $this->createMock(InvoiceProductLineRepositoryInterface::class),
            $this->invoiceNotificationService = $this->createMock(InvoiceNotificationServiceInterface::class),
        );
    }

    public function testCreateInvoice(): void
    {
        $dto = new CreateInvoiceDTO(
            customerName: $this->faker->name(),
            customerEmail: $this->faker->email(),
            productLines: [
                new ProductLineDTO(
                    productName: $this->faker->word(),
                    quantity: $this->faker->numberBetween(1, 10),
                    unitPrice: $this->faker->numberBetween(1, 100),
                ),
                new ProductLineDTO(
                    productName: $this->faker->word(),
                    quantity: $this->faker->numberBetween(1, 10),
                    unitPrice: $this->faker->numberBetween(1, 100),
                ),
            ],
        );

        $this->invoiceRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function ($invoice) use ($dto) {
                $invoice->id = $this->faker->uuid();
                $this->assertNotNull($invoice->id);
                $this->assertEquals($dto->customerName, $invoice->customer_name);
                $this->assertEquals($dto->customerEmail, $invoice->customer_email);
            });

        $i = 0;
        $this->invoiceRepository
            ->expects($this->exactly(count($dto->productLines)))
            ->method('addProductLine')
            ->willReturnCallback(function ($invoice, $productLine) use ($dto, &$i) {
                $this->assertEquals($invoice->id, $productLine->invoice_id);
                $this->assertEquals($dto->productLines[$i]->productName, $productLine->name);
                $this->assertEquals($dto->productLines[$i]->quantity, $productLine->quantity);
                $this->assertEquals($dto->productLines[$i]->unitPrice, $productLine->price);
                $i++;
            });

        $j = 0;
        $this->invoiceProductLineRepository->expects($this->exactly(count($dto->productLines)))
            ->method('save')
            ->willReturnCallback(function ($productLine) use ($dto, &$j) {
                $this->assertEquals($dto->productLines[$j]->productName, $productLine->name);
                $this->assertEquals($dto->productLines[$j]->quantity, $productLine->quantity);
                $this->assertEquals($dto->productLines[$j]->unitPrice, $productLine->price);
                $j++;
            });

        $this->invoiceService->createInvoice($dto);
    }

    public function testSendInvoice(): void
    {
        $invoice = new Invoice(['id' => Uuid::uuid4(), 'status' => StatusEnum::Draft]);
        $invoice->invoiceProductLines = collect(new InvoiceProductLine(
            [
                'name' => $this->faker->word(),
                'quantity' => $this->faker->numberBetween(1, 10),
                'price' => $this->faker->numberBetween(1, 100),
            ]
        ));

        $this->invoiceNotificationService
            ->expects($this->once())
            ->method('notifyInvoiceSending')
            ->with($invoice);

        $this->invoiceRepository
            ->expects($this->once())
            ->method('save')
            ->with($invoice);

        $this->invoiceService->sendInvoice($invoice);

        $this->assertEquals(StatusEnum::Sending, $invoice->status);
    }

    public function testSendInvoiceThrowsExceptionForInvalidStatus(): void
    {
        $invoice = new Invoice(['id' => Uuid::uuid4(), 'status' => StatusEnum::Sending]);

        $this->expectException(InvoiceHasIncorrectStatusException::class);
        $this->expectExceptionMessage('Invoice not in draft status');

        $this->invoiceService->sendInvoice($invoice);
    }

    public function testSendInvoiceThrowsExceptionForInvoiceWithoutProductLines(): void
    {
        $invoice = new Invoice(['id' => Uuid::uuid4(), 'status' => StatusEnum::Draft]);
        $invoice->invoiceProductLines = collect();

        $this->expectException(InvoiceDoesNotHaveProductLinesException::class);

        $this->invoiceService->sendInvoice($invoice);
    }

    public function testMarkAsSent(): void
    {
        $invoice = new Invoice(['id' => Uuid::uuid4(), 'status' => StatusEnum::Sending]);

        $this->invoiceRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($invoice);

        $this->invoiceRepository
            ->expects($this->once())
            ->method('save')
            ->with($invoice);

        $this->invoiceService->markAsSent($invoice->id);

        $this->assertEquals(StatusEnum::SentToClient, $invoice->status);
    }

    public function testMarkAsSentThrowsExceptionIfInvoiceNotFound(): void
    {
        $this->invoiceRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(InvoiceNotFoundException::class);

        $this->invoiceService->markAsSent(Uuid::uuid4());
    }

    public function testMarkAsSentThrowsExceptionIfInvoiceHasIncorrectStatus(): void
    {
        $invoice = new Invoice(['id' => Uuid::uuid4(), 'status' => StatusEnum::Draft]);

        $this->invoiceRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($invoice);

        $this->expectException(InvoiceHasIncorrectStatusException::class);
        $this->expectExceptionMessage('Invoice not in sending status');

        $this->invoiceService->markAsSent($invoice->id);
    }
}
