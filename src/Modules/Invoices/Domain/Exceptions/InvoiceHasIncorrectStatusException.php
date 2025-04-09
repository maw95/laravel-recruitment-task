<?php

namespace Modules\Invoices\Domain\Exceptions;

use Illuminate\Http\Response;
use Modules\Invoices\Domain\Enums\StatusEnum;

class InvoiceHasIncorrectStatusException extends \InvalidArgumentException
{
    public function __construct(StatusEnum $expectedStatus)
    {
        parent::__construct(
            sprintf('Invoice not in %s status', $expectedStatus->value),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
