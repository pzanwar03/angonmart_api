<?php

namespace Marvel\Listeners;

use App\Events\ReviewCreated;
use App\Notifications\NewReviewCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Marvel\Database\Models\NotifyLogs;
use Marvel\Enums\EventType;
use Marvel\Events\OrderCreated;
use Illuminate\Support\Facades\Cache;
use Marvel\Database\Models\User;
use Marvel\Enums\Permission;
use Marvel\Traits\UsersTrait;

class StoredOrderNotifyLogsListener implements ShouldQueue
{

    use UsersTrait;

    /**
     * Handle the event.
     *
     * @param  OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        // save notification for admin
        $admins = $this->getAdminUsers();
        if (isset($admins)) {
            foreach ($admins as $key => $admin) {
                NotifyLogs::create([
                    'receiver' => $admin->id,
                    'sender' => isset($event->user) ? $event->order->customer_id : null,
                    'notify_type' => 'order',
                    'notify_receiver_type' => 'admin',
                    'is_read' => false,
                    'notify_text' => 'One new order created. Order ID : ' . $event->order->tracking_number,
                    'notify_tracker' => $event->order->tracking_number
                ]);
            }
        }
    }
}
