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

        $classes = TClass::with('training.academy')
            ->where('date', $now->toDateString())
            ->where('start_time', '>=', $now)
            ->where('start_time', '<=', $sixHoursLater)
            ->orderBy('start_time')
            ->get();

        foreach ($classes as $class) {
            $joins = Join::where('training_id', $class->training_id)->get();
            $detail = [
                'training_id' => $class->training_id,
                'longitude' => $class->training->address->longitude,
                'latitude' => $class->training->address->latitude,
                'academy_name' => $class->training->academy->getTranslations('commercial_name', 'en'),
            ];

            foreach ($joins as $join) {
                $user = $join->user;
                $data = [
                    'title' => 'Session Reminder',
                    'body' => 'Your upcoming session with ' . $class->training->academy->getTranslations('commercial_name', 'en') .  ' starting soon. Session starts at  ' . $class->start_time,
                    'details' => $detail
                ];
                NotificationService::firebaseNotification($data, $user->fcm_token);
            }
        }
        $this->info('Session reminders sent successfully.');
    }
}
