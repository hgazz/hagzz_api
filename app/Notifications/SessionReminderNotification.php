<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionReminderNotification extends Notification
{
    use Queueable;

    public $academyName;
    public $time;

    /**
     * Create a new notification instance.
     */
    public function __construct($academyName, $time)
    {
        $this->academyName = $academyName;
        $this->time = $time;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Session Reminder',
            'body' => 'Get Ready, your session with ' . $this->academyName . ' will start at ' . $this->time,
        ];
    }
}
