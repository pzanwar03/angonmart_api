<?php

namespace Marvel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Events\ProductReviewApproved;
use Marvel\Notifications\ProductApprovedNotification;
use Marvel\Traits\UsersTrait;

class ProductReviewApprovedListener implements ShouldQueue
{
    use UsersTrait;

    /**
     * Handle the event.
     *
     * @param  ProductReview $event
     * @return void
     */
    public function handle(ProductReviewApproved $event)
    {
        foreach ($this->getAdminUsers() as $admin) {
            $admin->notify(new ProductApprovedNotification($event->product));
        }
    }
}
