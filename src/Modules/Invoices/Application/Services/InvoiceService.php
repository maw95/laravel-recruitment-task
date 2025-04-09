<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\InvoiceDoesNotHaveProductLinesException;
use Modules\Invoices\Domain\Exceptions\InvoiceHasIncorrectStatusException;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceProductLineRepositoryInterface;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Presentation\Dtos\CreateInvoiceDTO;
use Ramsey\Uuid\UuidInterface;

final readonly class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private InvoiceProductLineRepositoryInterface $productLineRepository,
        private InvoiceNotificationServiceInterface $invoiceNotificationService,
    ) {}

    public function createInvoice(CreateInvoiceDTO $dto): Invoice
    {
        $invoice = new Invoice([
            'customer_name' => $dto->customerName,
            'customer_email' => $dto->customerEmail,
        ]);
        $this->invoiceRepository->save($invoice);

        foreach ($dto->productLines as $line) {
            $productLine = new InvoiceProductLine([
                'name' => $line->productName,
                'quantity' => $line->quantity,
                'price' => $line->unitPrice,
                'invoice_id' => $invoice->id,
            ]);
            $this->productLineRepository->save($productLine);
            $this->invoiceRepository->addProductLine($invoice, $productLine);
        }

        $this->invoiceRepository->save($invoice);

        return $invoice;
    }

    public function sendInvoice(Invoice $invoice): void
    {
        if ($invoice->status !== StatusEnum::Draft) {
            throw new InvoiceHasIncorrectStatusException(StatusEnum::Draft);
        } elseif ($invoice->invoiceProductLines->count() === 0) {
            throw new InvoiceDoesNotHaveProductLinesException;
        }

        $this->invoiceNotificationService->notifyInvoiceSending($invoice);

        $invoice->status = StatusEnum::Sending;
        $this->invoiceRepository->save($invoice);
    }

    public function markAsSent(UuidInterface $invoiceId): void
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        if (! $invoice instanceof Invoice) {
            throw new InvoiceNotFoundException;
        } elseif ($invoice->status !== StatusEnum::Sending) {
            throw new InvoiceHasIncorrectStatusException(StatusEnum::Sending);
        }

        $invoice->status = StatusEnum::SentToClient;
        $this->invoiceRepository->save($invoice);
    }
}
