<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Dtos;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;

final readonly class InvoiceResponseDTO
{
    /**
     * @param  array{productName: string, quantity: int, unitPrice: int, totalUnitPrice: int}[]  $invoiceProductLines
     */
    public function __construct(
        public string $id,
        public string $status,
        public string $customerName,
        public string $customerEmail,
        public array $invoiceProductLines,
        public int $totalPrice,
    ) {}

    public static function fromInvoice(Invoice $invoice): self
    {
        return new self(
            id: $invoice->id->toString(),
            status: $invoice->status->value,
            customerName: $invoice->customer_name,
            customerEmail: $invoice->customer_email,
            invoiceProductLines: $invoice->invoiceProductLines->map(fn (InvoiceProductLine $line) => [
                'productName' => $line->name,
                'quantity' => $line->quantity,
                'unitPrice' => $line->price,
                'totalUnitPrice' => $line->getTotalUnitPrice(),
            ])->toArray(),
            totalPrice: $invoice->getTotalPrice(),
        );
    }
}
