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
            ->whereDate('date', now()->addDay()->toDateString())
            ->get();
        dd($tomorrowClasses);

        foreach ($tomorrowClasses as $class) {
            $joins = Join::where('training_id', $class->training_id)->get();
            $detail = [
                'training_id' => $class->training_id,
                'longitude' => $class->training->address->longitude,
                'latitude' => $class->training->address->latitude,
                'academy_name' => $class->training->academy->getTranslation('commercial_name', 'en'),
            ];
            foreach ($joins as $join) {
                $user = $join->user;
                $title= 'Session Reminder';
                $body = 'Get ready,  Don’t forget to bring the required equipment for your upcoming session.' ;
                $data = [
                    'title' => $title,
                    'body' => $body,
                    'details' => $detail
                ];
                NotificationService::firebaseNotification($data, $user->fcm_token);
            }
        }
        $this->info('Session reminders sent successfully.');
    }
}
