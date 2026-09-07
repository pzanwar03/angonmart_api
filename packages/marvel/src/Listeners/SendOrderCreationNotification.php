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
        $customer = $event->user ?? $event->order->customer;

        try {
            if ($customer && $customer->email) {
                $customer->notify(new OrderPlacedSuccessfully($event->invoiceData));
            }
        } catch (\Throwable $e) {
            info('Order creation customer email failed: ' . $e->getMessage());
        }

        try {
            $sentEmails = [];

            foreach ($this->adminList() as $admin) {
                if (!$admin->email) {
                    continue;
                }
                $normalized = strtolower(trim($admin->email));
                if (isset($sentEmails[$normalized])) {
                    continue;
                }
                $admin->notify(new NewOrderReceived($order, 'admin'));
                $sentEmails[$normalized] = true;
            }

            $merchantEmail = config('shop.merchant_email');
            if ($merchantEmail) {
                $normalized = strtolower(trim($merchantEmail));
                if (!isset($sentEmails[$normalized])) {
                    Notification::route('mail', $merchantEmail)
                        ->notify(new NewOrderReceived($order, 'admin'));
                }
            }
        } catch (\Throwable $e) {
            info('Order creation admin email failed: ' . $e->getMessage());
        }

        try {
            $this->sendOrderCreationSmsAlways($order);
        } catch (\Throwable $e) {
            info('Order creation SMS failed: ' . $e->getMessage());
        }
    }
}
