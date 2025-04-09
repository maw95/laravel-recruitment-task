<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Domain\Models\Invoice;

interface InvoiceNotificationServiceInterface
{
    public function notifyInvoiceSending(Invoice $invoice): void;
}
