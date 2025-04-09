<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Services;

use Modules\Invoices\Application\Services\InvoiceNotificationServiceInterface;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;

final readonly class InvoiceNotificationService implements InvoiceNotificationServiceInterface
{
    public function __construct(
        private NotificationFacadeInterface $notificationFacade
    ) {}

    public function notifyInvoiceSending(Invoice $invoice): void
    {
        $notifyData = new NotifyData(
            $invoice->id,
            $invoice->customer_email,
            'Invoice Notification',
            'Your invoice is being processed.',
        );

        $this->notificationFacade->notify($notifyData);
    }
}
