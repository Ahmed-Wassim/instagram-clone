<?php

namespace App\Notifications;

use App\Models\Reel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReelCommented extends Notification
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
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'reel_comment',
            'message' => "{$this->byUser->name} commented on your reel.",
            'reel_id' => $this->reel->id,
            'user_id' => $this->byUser->id,
        ];
    }
}
