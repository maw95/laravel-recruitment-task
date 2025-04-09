<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Infrastructure\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Invoices\Application\Services\InvoiceNotificationServiceInterface;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use Modules\Shared\Application\Listeners\ResourceDeliveredEventTranslator;
use Modules\Shared\Application\Services\InvoiceNotificationService;

final class ServiceProvider extends EventServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(InvoiceNotificationServiceInterface::class, InvoiceNotificationService::class);
    }

    /** @return array<class-string> */
    public function provides(): array
    {
        return [
            InvoiceNotificationServiceInterface::class,
        ];
    }

    public function boot(): void
    {
        Event::listen(
            ResourceDeliveredEvent::class,
            ResourceDeliveredEventTranslator::class,
        );
    }
}
