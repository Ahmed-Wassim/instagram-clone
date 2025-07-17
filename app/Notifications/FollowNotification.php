<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class FollowNotification extends Notification
{
    public $follower;

    public function __construct($follower)
    {
        $this->follower = $follower;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'follow',
            'follower_id' => $this->follower->id,
            'follower_username' => $this->follower->username,
            'message' => "{$this->follower->username} followed you",
        ];
    }
}
