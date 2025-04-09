<?php

namespace Modules\Invoices\Domain\Exceptions;

use Illuminate\Http\Response;

class InvoiceNotFoundException extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct(
            'Invoice not found',
            Response::HTTP_NOT_FOUND
        );
    }
}
