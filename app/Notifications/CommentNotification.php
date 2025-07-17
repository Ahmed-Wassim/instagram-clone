<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentNotification extends Notification
{
    public $commenter;
    public $postId;

    public function __construct($commenter, $postId)
    {
        $this->commenter = $commenter;
        $this->postId = $postId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'comment',
            'post_id' => $this->postId,
            'commenter_id' => $this->commenter->id,
            'commenter_username' => $this->commenter->username,
            'message' => "{$this->commenter->username} commented on your post",
        ];
    }
}
