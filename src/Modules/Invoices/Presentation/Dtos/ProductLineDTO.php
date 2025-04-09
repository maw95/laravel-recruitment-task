<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Dtos;

final readonly class ProductLineDTO
{
    public function __construct(
        public string $productName,
        public int $quantity,
        public int $unitPrice,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['quantity'],
            $data['price'],
        );
    }
}
