<?php

namespace Marvel\Listeners;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Enums\EventType;
use Marvel\Events\OrderCancelled;
use Marvel\Notifications\OrderCancelledNotification;
use Marvel\Traits\OrderSmsTrait;
use Marvel\Traits\SmsTrait;


class SendOrderCancelledNotification implements ShouldQueue
{
    use SmsTrait, OrderSmsTrait;

    /**
     * Handle the event.
     *
     * @param OrderCancelled $event
     * @return void
     */
    public function handle(OrderCancelled $event)
    {
        $emailReceiver = $this->getWhichUserWillGetEmail(EventType::ORDER_CANCELLED, $event->order->language);
        if ($emailReceiver['customer'] && $event->order->customer) {
            $event->order->customer->notify(new OrderCancelledNotification($event->order));
        }
        if ($emailReceiver['admin']) {
            $admins = $this->adminList();
            foreach ($admins as $key => $admin) {

                $admin->notify(new OrderCancelledNotification($event->order));
            }
        }
        $this->sendOrderCancelSms($event->order);
    }
}
