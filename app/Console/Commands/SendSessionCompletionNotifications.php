<?php

namespace App\Console\Commands;

use App\Models\Join;
use App\Models\TClass;
use App\Notifications\SessionCompletedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendSessionCompletionNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-session-completion-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $now = Carbon::now();

        $currentTime = $now->format('H:i:s');

        // Filter classes that ended today before the current time
        $completedClasses = TClass::whereDate('date', '=', $now->toDateString())
            ->whereTime('end_time', '<', $currentTime)
            ->get();

        foreach ($completedClasses as $class) {
            $joins = Join::where('training_id', $class->training_id)->get();
            foreach ($joins as $join) {
                $user = $join->user;
                $user->notify(new SessionCompletedNotification());
            }
        }

        $this->info('Session completion notifications sent successfully!');
    }
}
