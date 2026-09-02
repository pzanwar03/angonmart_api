<?php

namespace Marvel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Events\OrderCreated;
use Marvel\Notifications\NewOrderReceived;
use Marvel\Notifications\OrderPlacedSuccessfully;
use Marvel\Traits\OrderSmsTrait;
use Marvel\Traits\SmsTrait;

class SendOrderCreationNotification implements ShouldQueue
{
    use SmsTrait, OrderSmsTrait;

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

        // Always send customer email (order confirmation + invoice PDF)
        if ($customer) {
            $customer->notify(new OrderPlacedSuccessfully($event->invoiceData));
        }

        // Always send merchant (admin) email
        $admins = $this->adminList();
        foreach ($admins as $admin) {
            $admin->notify(new NewOrderReceived($order, 'admin'));
        }

        // Always send customer + merchant SMS via smsgateway.com.bd, bypassing DB toggles
        $this->sendOrderCreationSmsAlways($order);
    }
}
