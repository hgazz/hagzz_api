<?php

namespace App\Console\Commands;

use App\Models\Join;
use App\Models\TClass;
use App\Notifications\SessionReminderNotification;
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
        $tomorrowClasses = TClass::with('training.academy', 'training.user')
            ->whereDate('date', now()->addDay()->toDateString())
            ->get();

        foreach ($tomorrowClasses as $class) {
            $joins = Join::where('training_id', $class->training_id)->get();

            foreach ($joins as $join) {
                $user = $join->user;
                $user->notify(new SessionReminderNotification($join->training->academy->commercial_name, $class->start_time));
            }
        }
        $this->info('Session reminders sent successfully.');
    }
}
