<?php

namespace App\Console\Commands;

use App\Models\Join;
use App\Models\TClass;
use App\Models\User;
use App\Notifications\SessionReminderNotification;
use App\Services\Firebase\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendSessionReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-session-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a reminder to users about today\'s sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrowClasses = TClass::with([
            'training.academy',
            'training.joins.user',
            'training.joins.training.address'
        ])
            ->where('date', now()->addDay()->toDateString())
            ->get();

        foreach ($tomorrowClasses as $class) {
            // Access all joins related to the training of the class
            foreach ($class->training->joins as $join) {
                $user = $join->user;

                // Prepare the notification data
                $data = [
                    'title' => 'Session Reminder',
                    'body' => 'Get ready, Don’t forget to bring the required equipment for your upcoming session.',
                    'id' => $join->training_id,
                    'page' => 'details',
                    'longitude' => $join->training->address->longitude,
                    'latitude' => $join->training->address->latitude,
                    'class_id' => $class->id ?? null,
                ];

                // Send notification to the user
                NotificationService::firebaseNotification($data, $user->fcm_token);
            }
        }
        $this->info('Session reminders sent successfully.');
    }
}
