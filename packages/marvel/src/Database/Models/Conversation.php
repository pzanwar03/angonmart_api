<?php

namespace Marvel\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Marvel\Enums\Permission;

class Conversation extends Model
{

    public $guarded = [];

    protected $appends = [
        'latest_message',
        'unseen',
    ];

    /**
     * @return belongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return hasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /**
     * Returns the latest message from a conversation.
     *
     * @return Message
     */
    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get all of the conversations participants.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class, 'conversation_id');
    }

    public function getUnseenAttribute()
    {
        if (Auth::check()) {
            $instance = $this->participants()->whereNull('last_read')->where('user_id', auth()->user()->id)->where('type', 'user')->count();

            if (0 == $instance && (auth()->user()->hasPermissionTo(Permission::SUPER_ADMIN) || auth()->user()->hasPermissionTo(Permission::STAFF))) {
                $instance = $this->participants()->whereNull('last_read')->where('type', 'shop')->count();
            }

            return $instance;
        } else {
            return '0';
        }
    }
}
