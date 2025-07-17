<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LikeNotification extends Notification
{
    public $liker;
    public $postId;

    public function __construct($liker, $postId)
    {
        $this->liker = $liker;
        $this->postId = $postId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'like',
            'post_id' => $this->postId,
            'liker_id' => $this->liker->id,
            'liker_username' => $this->liker->username,
            'message' => "{$this->liker->username} liked your post",
        ];
    }
}
