<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Http\JsonResponse;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Domain\Exceptions\InvoiceDoesNotHaveProductLinesException;
use Modules\Invoices\Domain\Exceptions\InvoiceHasIncorrectStatusException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Presentation\Dtos\CreateInvoiceDTO;
use Modules\Invoices\Presentation\Dtos\InvoiceResponseDTO;
use Symfony\Component\HttpFoundation\Response;

final readonly class InvoiceController
{
    public function __construct(
        private InvoiceService $invoiceService,
    ) {}

    public function view(Invoice $invoice): JsonResponse
    {
        return new JsonResponse(InvoiceResponseDTO::fromInvoice($invoice));
    }

    public function create(): JsonResponse
    {
        $invoice = $this->invoiceService->createInvoice(CreateInvoiceDTO::fromRequest());

        return new JsonResponse(InvoiceResponseDTO::fromInvoice($invoice), Response::HTTP_CREATED);
    }

    public function send(Invoice $invoice): JsonResponse
    {
        try {
            $this->invoiceService->sendInvoice($invoice);
        } catch (InvoiceHasIncorrectStatusException|InvoiceDoesNotHaveProductLinesException $e) {
            return new JsonResponse(
                [
                    'message' => $e->getMessage(),
                ],
                $e->getCode()
            );
        }

        return new JsonResponse;
    }
}
