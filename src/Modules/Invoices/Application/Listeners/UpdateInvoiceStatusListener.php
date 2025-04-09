<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Listeners;

use Modules\Invoices\Application\Events\InvoiceDeliveredEvent;
use Modules\Invoices\Application\Services\InvoiceService;

final readonly class UpdateInvoiceStatusListener
{
    public function __construct(
        private InvoiceService $invoiceService,
    ) {}

    public function handle(InvoiceDeliveredEvent $event): void
    {
        $this->invoiceService->markAsSent($event->invoiceId);
    }
}
