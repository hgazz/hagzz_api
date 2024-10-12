<?php

namespace App\Console\Commands;

use App\Models\Join;
use App\Models\Training;
use App\Models\User;
use App\Services\Firebase\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SendNotificationForSavedTraining extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-notification-for-saved-training';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send notification for saved training';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retrieve all joins with necessary relationships to minimize queries in the loop
        $joins = DB::table('joins')
            ->join('users', 'users.id', '=', 'joins.user_id')
            ->join('trainings', 'trainings.id', '=', 'joins.training_id')
            ->select('joins.*', 'trainings.id as training_id', 'trainings.max_players', 'trainings.start_date', 'trainings.name', 'trainings.academy_id')
            ->get();

        // Fetch all necessary training data in one query to avoid multiple finds within the loop
        $trainingIds = $joins->pluck('training_id')->unique();
        $trainings = Training::whereIn('id', $trainingIds)
            ->with(['address', 'academy'])
            ->get()
            ->keyBy('id'); // Store by training_id for easy access

        $oneWeekFromNow = Carbon::now()->addWeek();

        foreach ($joins as $join) {
            $training = $trainings->get($join->training_id);

            if (!$training) continue; // Skip if training data is missing

            $joinsCount = Join::where('training_id', $join->training_id)->count();

            if ($training->max_players > $joinsCount && $training->start_date <= $oneWeekFromNow) {

                $detail = [
                    'training_id' => $training->id,
                    'longitude' => $training->address->longitude,
                    'latitude' => $training->address->latitude,
                    'academy_name' => $training->academy->getTranslation('commercial_name', 'en')
                ];

                $title = "Don't miss out";
                $body = "A training {$training->name} you saved is starting in just one week. Secure your spot now.";

                $data = [
                    'title' => $title,
                    'body' => $body,
                    'image' => $training->academy->image,
                    'details' => $detail,
                    'id' => $training->id,
                    'page' => 'details',
                    'longitude' => $training->address->longitude,
                    'latitude' => $training->address->latitude
                ];

                NotificationService::firebaseNotification($data, $join->user->fcm_token);
//                NotificationService::dbNotification($user->id, User::class, 2, $title, $body, $training->academy->logo, $detail);
            }
        }
    }
}
