<?php

namespace Modules\Invoices\Domain\Exceptions;

use Illuminate\Http\Response;

class InvoiceDoesNotHaveProductLinesException extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct(
            'Invoice must have product lines',
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
