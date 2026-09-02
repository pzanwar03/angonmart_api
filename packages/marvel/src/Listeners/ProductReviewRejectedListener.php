<?php

namespace Marvel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Events\ProductReviewRejected;
use Marvel\Notifications\ProductRejectedNotification;
use Marvel\Traits\UsersTrait;

class ProductReviewRejectedListener implements ShouldQueue
{
    use UsersTrait;

    /**
     * Handle the event.
     *
     * @param  ProductReview $event
     * @return void
     */
    public function handle(ProductReviewRejected $event)
    {
        foreach ($this->getAdminUsers() as $admin) {
            $admin->notify(new ProductRejectedNotification($event->product));
        }
    }
}
