<?php

namespace Modules\Shared\Application\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Modules\Invoices\Application\Events\InvoiceDeliveredEvent;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;

final readonly class ResourceDeliveredEventTranslator
{
    public function __construct(
        private Dispatcher $eventDispatcher
    ) {}

    public function handle(ResourceDeliveredEvent $event): void
    {
        $this->eventDispatcher->dispatch(
            new InvoiceDeliveredEvent($event->resourceId)
        );
    }
}
