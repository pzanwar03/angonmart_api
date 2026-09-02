<?php

namespace App\Listeners;

use App\Events\ReviewCreated;
use App\Notifications\NewReviewCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Marvel\Enums\EventType;
use Marvel\Traits\SmsTrait;
use Marvel\Traits\UsersTrait;

class SendReviewNotification implements ShouldQueue
{
    use SmsTrait, UsersTrait;

    /**
     * Handle the event.
     *
     * @param  ReviewCreated  $event
     * @return void
     */
    public function handle(ReviewCreated $event)
    {
        $emailReceiver = $this->getWhichUserWillGetEmail(EventType::REVIEW_CREATED, $event->review->language ?? DEFAULT_LANGUAGE);
        if ($emailReceiver['admin'] ?? false) {
            foreach ($this->getAdminUsers() as $admin) {
                $admin->notify(new NewReviewCreated($event->review));
            }
        }
    }
}
