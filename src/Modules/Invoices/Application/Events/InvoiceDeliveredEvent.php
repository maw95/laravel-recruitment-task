<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Events;

use Ramsey\Uuid\UuidInterface;

final readonly class InvoiceDeliveredEvent
{
    public function __construct(
        public UuidInterface $invoiceId
    ) {}
}
