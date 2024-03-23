<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CancelBookingNotifications extends Notification
{
    use Queueable;

    public $user;
    public $training;
    /**
     * Create a new notification instance.
     */
    public function __construct($user, $training)
    {
        $this->user = $user;
        $this->training = $training;
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
            'title' => 'Booking Cancelled',
            'body' => $this->user->name . ' left' . $this->training->name
        ];
    }
}
