<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repositories;

use Modules\Invoices\Domain\Models\InvoiceProductLine;

interface InvoiceProductLineRepositoryInterface
{
    public function save(InvoiceProductLine $productLine): void;
}
