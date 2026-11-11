<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UserActivityNotification extends Notification
{
    public function __construct(
        public readonly string $event,
        public readonly string $title,
        public readonly string $message,
    ) {}

    /**
     * @return array{event: string, title: string, message: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'title' => $this->title,
            'message' => $this->message,
        ];
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }
}
