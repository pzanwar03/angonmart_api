<?php

namespace Marvel\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Enums\EventType;
use Marvel\Events\PaymentFailed;
use Marvel\Notifications\PaymentFailedNotification;
use Marvel\Traits\OrderSmsTrait;
use Marvel\Traits\SmsTrait;

class SendPaymentFailedNotification implements ShouldQueue
{
    use SmsTrait, OrderSmsTrait;

    /**
     * Handle the event.
     *
     * @param PaymentFailed $event
     * @return void
     */
    public function handle(PaymentFailed $event)
    {
        $emailReceiver = $this->getWhichUserWillGetEmail(EventType::ORDER_PAYMENT_FAILED, $event->order->language ?? DEFAULT_LANGUAGE);

        if ($emailReceiver['customer']) {
            $customer = $event->order->customer;
            $customer?->notify(new PaymentFailedNotification($event->order));
        }
        $this->sendPaymentFailedSms($event->order);
    }
}
