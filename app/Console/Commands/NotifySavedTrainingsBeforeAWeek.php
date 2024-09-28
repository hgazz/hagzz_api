<?php

namespace App\Console\Commands;

use App\Models\Favorite;
use App\Models\Join;
use App\Models\Training;
use App\Services\Firebase\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NotifySavedTrainingsBeforeAWeek extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-saved-trainings-before-a-week';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users about saved trainings happening in one week with available slots';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneWeekFromNow = Carbon::now()->addWeek();

        $favorites = Favorite::with(['training', 'user'])->where('created_at', $oneWeekFromNow->toDateString())->get();
dd($favorites, $oneWeekFromNow);
        foreach ($favorites as $favorite) {
            $joinsCount = Join::where('training_id', $favorite->training_id)->count();
            $slotsAvailable = $favorite->training->max_players - $joinsCount;

            if ($slotsAvailable > 0) {
               $title = 'Training Reminder';
               $body = 'A training you saved is starting in just one week. Secure Your Spot now!.';
               $data = [
                   'title' => $title,
                   'body' => $body,
                   'details' => [
                       'training_id' => $favorite->training_id,
                       'longitude' => $favorite->training->address->longitude,
                       'latitude' => $favorite->training->address->latitude,
                       'academy_name' => $favorite->training->academy->getTranslations('commercial_name', 'en'),
                   ]
               ];
               NotificationService::firebaseNotification($data, $favorite->user->fcm_token);
            }
        }

        $this->info('Notifications sent successfully.');
    }
}
