<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repositories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Ramsey\Uuid\UuidInterface;

interface InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void;

    public function findById(UuidInterface $id): ?Invoice;

    public function addProductLine(Invoice $invoice, InvoiceProductLine $productLine): void;
}
