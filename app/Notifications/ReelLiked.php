<?php

namespace App\Notifications;

use App\Models\Reel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReelLiked extends Notification
{
    use Queueable;

    protected $reel;
    protected $byUser;

    public function __construct(Reel $reel, $byUser)
    {
        $this->reel = $reel;
        $this->byUser = $byUser;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; // for real-time
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'reel_like',
            'message' => "{$this->byUser->name} liked your reel.",
            'reel_id' => $this->reel->id,
            'user_id' => $this->byUser->id,
        ];
    }
}
