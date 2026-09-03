<?php

namespace Marvel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Marvel\Events\OrderCreated;
use Marvel\Notifications\NewOrderReceived;
use Marvel\Notifications\OrderPlacedSuccessfully;
use Marvel\Traits\OrderSmsTrait;

class SendOrderCreationNotification implements ShouldQueue
{
    use OrderSmsTrait;

    /**
     * Handle the event.
     *
     * @param OrderCreated $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        $order    = $event->order;
        $customer = $event->order->customer;

        // Customer email (logged-in users only). Guests get SMS only.
        try {
            if ($customer && $customer->email) {
                $customer->notify(new OrderPlacedSuccessfully($event->invoiceData));
            }
        } catch (\Throwable $e) {
            info('Order creation customer email failed: ' . $e->getMessage());
        }

        // Merchant email to MERCHANT_EMAIL
        try {
            $merchantEmail = config('shop.merchant_email');
            if ($merchantEmail) {
                Notification::route('mail', $merchantEmail)
                    ->notify(new NewOrderReceived($order, 'admin'));
            }
        } catch (\Throwable $e) {
            info('Order creation merchant email failed: ' . $e->getMessage());
        }

        // Customer + merchant SMS (already try/caught inside the trait)
        try {
            $this->sendOrderCreationSmsAlways($order);
        } catch (\Throwable $e) {
            info('Order creation SMS failed: ' . $e->getMessage());
        }
    }
}
