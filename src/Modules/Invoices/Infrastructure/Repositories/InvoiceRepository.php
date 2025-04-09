<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Repositories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Ramsey\Uuid\UuidInterface;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void
    {
        $invoice->save();
    }

    public function findById(UuidInterface $id): ?Invoice
    {
        return Invoice::find($id);
    }

    public function addProductLine(Invoice $invoice, InvoiceProductLine $productLine): void
    {
        $invoice->invoiceProductLines()->save($productLine);
    }
}
