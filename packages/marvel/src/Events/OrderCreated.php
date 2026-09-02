<?php


namespace Marvel\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Database\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Marvel\Database\Models\NotifyLogs;
use Marvel\Database\Models\Settings;
use Marvel\Database\Models\StoreNotice;
use Marvel\Database\Models\User;
use Marvel\Exceptions\MarvelException;
use Marvel\Enums\Permission;
use Marvel\Traits\UsersTrait;

class OrderCreated implements ShouldQueue, ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, UsersTrait;

    /**
     * @var Order
     */
    public Order $order;

    public array $invoiceData;

    /**
     * user
     *
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order, array $invoiceData, ?User $user)
    {
        $this->order = $order;
        $this->invoiceData = $invoiceData;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $event_channels = [];

        // Notify in admin-end
        $admins = $this->getAdminUsers();
        if (isset($admins)) {
            foreach ($admins as $key => $user) {
                $channel_name = new PrivateChannel('order.created.' . $user->id);
                array_push($event_channels, $channel_name);
            }
        }

        return $event_channels;
    }


    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => 'One new order created.'
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        // event's name will be written here.
        return 'order.create.event';
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        try {
            $settings = Settings::first();
            $enableBroadCast = false;

            if (config('shop.pusher.enabled') === null) {
                return false;
            }

            if (isset($settings->options['pushNotification']['all']['order'])) {
                if ($settings->options['pushNotification']['all']['order'] == true) {
                    $enableBroadCast = true;
                }
            }
            return $enableBroadCast;
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG, $th->getMessage());
        }
    }
}
