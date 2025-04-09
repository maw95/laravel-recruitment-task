<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Services;

use Illuminate\Foundation\Testing\WithFaker;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Modules\Shared\Application\Services\InvoiceNotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceNotificationServiceTest extends TestCase
{
    use WithFaker;

    private InvoiceNotificationService $invoiceNotificationService;

    private NotificationFacadeInterface&MockObject $notificationFacade;

    protected function setUp(): void
    {
        $this->setUpFaker();

        $this->invoiceNotificationService = new InvoiceNotificationService(
            $this->notificationFacade = $this->createMock(NotificationFacadeInterface::class),
        );
    }

    public function testNotifyInvoiceSending(): void
    {
        $invoice = new Invoice(['id' => Uuid::uuid4(), 'customer_email' => $this->faker->email()]);

        $this->notificationFacade->expects($this->once())->method('notify')
            ->with($this->callback(function (NotifyData $notifyData) use ($invoice) {
                return $notifyData->resourceId === $invoice->id && $notifyData->toEmail === $invoice->customer_email;
            }));

        $this->invoiceNotificationService->notifyInvoiceSending($invoice);
    }
}
