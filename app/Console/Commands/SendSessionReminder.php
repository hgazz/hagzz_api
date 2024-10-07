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
        $tomorrowClasses = TClass::with('training.academy')
            ->where('date', now()->addDay()->toDateString())
            ->get();

        foreach ($tomorrowClasses as $class) {
            $joins = Join::with(['user', 'training'])->where('training_id', $class->training_id)->get();

            foreach ($joins as $join) {
                $user = $join->user;
                $title= 'Session Reminder';
                $body = 'Get ready,  Don’t forget to bring the required equipment for your upcoming session.' ;
                $data = [
                    'title' => $title,
                    'body' => $body,
                    'id' => $join->training_id,
                    'page' => 'directions',
                    'longitude' => $join->training->address->longitude,
                    'latitude' => $join->training->address->latitude
                ];
                NotificationService::firebaseNotification($data, $user->fcm_token);
            }

        }
        $this->info('Session reminders sent successfully.');
    }
}
