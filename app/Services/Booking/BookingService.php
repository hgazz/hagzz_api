<?php

namespace App\Services\Booking;

use App\Models\CanceledBooking;
use App\Models\Join;
use App\Models\User;
use App\Notifications\CancelBookingNotifications;
use App\Services\Firebase\NotificationService;
use App\Services\SMSMISR\SmsService;
use Illuminate\Support\Facades\DB;

class BookingService
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function cancelBooking($request)
    {
        DB::beginTransaction();

        $join = Join::findOrFail($request->id);
        $join->invoice()->update(['is_canceled' => true]);

        CanceledBooking::create([
            'invoice_id' => $join->invoice_id,
            'user_id' => auth()->id(),
            'reason' => $request->reason
        ]);

        $join->delete();
        $details = [
            'training_id' => $join->training_id,
            'longitude' => $join->training->address->longitude,
            'latitude' => $join->training->address->latitude,
            'academy_name' => $join->training->academy->commercial_name,
        ];
        $title = "Booking Cancelled";
        $body = "Your booking with {$join->training->academy->commercial_name} is Cancelled. Please explore other trainings.";
        $this->smsService->sendMessage($join->user->phone, "{$title} - {$body}");
        $data = [
            'title' => $title,
            'body' => $body,
            'image' => $join->training->academy->image,
            'details' => $details
        ];
        NotificationService::firebaseNotification($data, $join->user->fcm_token);
        NotificationService::dbNotification($join->user_id, User::class, 0, $title, $body, $join->training->academy->image, $details);
//        $join->training->academy->notify(new CancelBookingNotifications($join->training, $join->user));

        DB::commit();
    }
}
