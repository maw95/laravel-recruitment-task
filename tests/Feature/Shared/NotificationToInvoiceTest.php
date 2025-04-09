<?php

namespace Tests\Feature\Shared;

use Illuminate\Support\Facades\Event;
use Modules\Invoices\Application\Events\InvoiceDeliveredEvent;
use Modules\Invoices\Application\Listeners\UpdateInvoiceStatusListener;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use Modules\Shared\Application\Listeners\ResourceDeliveredEventTranslator;
use Tests\TestCase;

class NotificationToInvoiceTest extends TestCase
{
    public function testResourceDeliveredEventTranslatorIsCalled(): void
    {
        Event::fake();

        Event::assertListening(
            ResourceDeliveredEvent::class,
            ResourceDeliveredEventTranslator::class
        );
    }

    public function testUpdateInvoiceStatusListenerIsCalled(): void
    {
        Event::fake();
        Event::assertListening(
            InvoiceDeliveredEvent::class,
            UpdateInvoiceStatusListener::class
        );
    }

    public function testNotificationHookChangesInvoiceStatusToSentToClient(): void
    {
        /**
         * @var Invoice $invoice
         */
        $invoice = Invoice::factory()->create();
        $invoice->status = StatusEnum::Sending;
        $invoice->save();

        $uri = route('notification.hook', [
            'action' => 'delivered',
            'reference' => $invoice->id,
        ]);

        $this->getJson($uri)->assertOk();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => StatusEnum::SentToClient,
        ]);
    }
}
