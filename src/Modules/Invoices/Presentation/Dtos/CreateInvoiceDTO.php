<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Dtos;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class CreateInvoiceDTO
{
    public function __construct(
        public string $customerName,
        public string $customerEmail,
        /** @var ProductLineDTO[] */
        public array $productLines = [],
    ) {}

    public static function fromRequest(): self
    {
        $data = request()->all();

        $validator = Validator::make($data, [
            'customerName' => 'required|string',
            'customerEmail' => 'required|email',
            'invoiceProductLines' => 'nullable|array',
            'invoiceProductLines.*.name' => 'required|string',
            'invoiceProductLines.*.quantity' => 'required|integer|min:1',
            'invoiceProductLines.*.price' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $productLines = array_map(
            fn (array $line) => ProductLineDTO::fromArray($line),
            $data['invoiceProductLines']
        );

        return new self(
            $data['customerName'],
            $data['customerEmail'],
            $productLines
        );
    }
}
