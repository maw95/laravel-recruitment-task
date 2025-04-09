<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Invoices\Application\Events\InvoiceDeliveredEvent;
use Modules\Invoices\Application\Listeners\UpdateInvoiceStatusListener;
use Modules\Invoices\Domain\Repositories\InvoiceProductLineRepositoryInterface;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Repositories\InvoiceProductLineRepository;
use Modules\Invoices\Infrastructure\Repositories\InvoiceRepository;

final class InvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->scoped(InvoiceProductLineRepositoryInterface::class, InvoiceProductLineRepository::class);
    }

    /** @return array<class-string> */
    public function provides(): array
    {
        return [
            InvoiceRepositoryInterface::class,
            InvoiceProductLineRepositoryInterface::class,
        ];
    }

    public function boot(): void
    {
        Event::listen(
            InvoiceDeliveredEvent::class,
            UpdateInvoiceStatusListener::class,
        );
    }
}
