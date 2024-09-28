<?php

namespace App\Console\Commands;

use App\Models\Join;
use App\Models\TClass;
use App\Models\User;
use App\Notifications\SessionReminderNotification;
use App\Services\Firebase\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendSessionReminderBefore6Hours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-session-reminder-6-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a reminder to users about today\'s sessions before start 6 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $sixHoursLater = $now->copy()->addHours(6);

        $classes = $this->getUpcomingClasses($now, $sixHoursLater);

        foreach ($classes as $class) {
            $this->sendRemindersForClass($class);
        }

        $this->info('Session reminders sent successfully.');
    }

    private function getUpcomingClasses($now, $sixHoursLater)
    {
        return TClass::with(['training.academy', 'training.address'])
            ->where('date', $now->toDateString())
//            ->whereBetween('start_time', [$now, $sixHoursLater])
            ->orderBy('start_time')
            ->get();
    }

    private function sendRemindersForClass($class)
    {
        $joins = Join::where('training_id', $class->training_id)->with('user')->get();
        $notificationData = $this->prepareNotificationData($class);

        foreach ($joins as $join) {
            if ($join->user && $join->user->fcm_token) {
                NotificationService::firebaseNotification($notificationData, $join->user->fcm_token);
            }
        }
    }

    private function prepareNotificationData($class)
    {
        $detail = [
            'training_id' => $class->training_id,
            'longitude' => $class->training->address->longitude,
            'latitude' => $class->training->address->latitude,
            'academy_name' => $class->training->academy->getTranslation('commercial_name', 'en'),
        ];

        return [
            'title' => 'Session Reminder',
            'body' => sprintf(
                'Your upcoming session with %s starting soon. Session starts at %s',
                $class->training->academy->getTranslation('commercial_name', 'en'),
                $class->start_time
            ),
            'details' => $detail
        ];
    }

}
