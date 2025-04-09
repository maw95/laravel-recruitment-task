<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Repositories;

use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceProductLineRepositoryInterface;

final readonly class InvoiceProductLineRepository implements InvoiceProductLineRepositoryInterface
{
    public function save(InvoiceProductLine $productLine): void
    {
        $productLine->save();
    }
}
