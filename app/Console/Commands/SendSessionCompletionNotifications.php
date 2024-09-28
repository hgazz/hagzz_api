<?php

namespace App\Console\Commands;

use App\Models\Join;
use App\Models\TClass;
use App\Models\User;
use App\Notifications\SessionCompletedNotification;
use App\Services\Firebase\NotificationService;
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
        $bodyMessages = [
            "You completed today's session successfully. Keep up the great work!",
            "Well done! You're making progress every step of the way. Keep up the great work!",
            "Session completed, another step closer to your goals. You're unstoppable!",
            "Amazing job on completing today's session! Your commitment will pay off!",
            "Pat yourself on the back for completing today's session. You're doing fantastic!",
            "Great job finishing today's session! Your hard work is bringing you closer to success!",
            "You're crushing it! Keep up the momentum as you progress on your journey!",
            "Congratulations on completing the session! Your consistency is key to your success!",
            "Well done on finishing today's session! Your determination is truly inspiring!"
        ];
        $now = Carbon::now();

        $currentTime = $now->format('H:i:s');

        // Filter classes that ended today before the current time
        $completedClasses = TClass::whereDate('date', '=', $now->toDateString())
            ->whereTime('end_time', '<', $currentTime)
            ->get();

        foreach ($completedClasses as $class) {
            $joins = Join::where('training_id', $class->training_id)->get();
            $detail = [
                'training_id' => $class->training_id,
                'longitude' => $class->training->address->longitude,
                'latitude' => $class->training->address->latitude,
                'academy_name' => $class->training->academy->getTranslation('commercial_name', 'en')
            ];
            foreach ($joins as $join) {
                $user = $join->user;
                $randomBodyMessage = $bodyMessages[array_rand($bodyMessages)];
                NotificationService::dbNotification($user->id, User::class, 3, 'Session Completed', $randomBodyMessage, $join->training->academy->image, $detail);
            }
        }

        $this->info('Session completion notifications sent successfully!');
    }
}
