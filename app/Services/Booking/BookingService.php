<?php

namespace App\Services\Booking;

use App\Models\CanceledBooking;
use App\Models\Join;
use App\Notifications\CancelBookingNotifications;
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

        $title = "Booking Cancelled";
        $body = "Your booking with {$join->training->academy->commercial_name} is Cancelled. Please explore other trainings.";
        $this->smsService->sendMessage($join->user->phone, "{$title} - {$body}");

        $join->training->academy->notify(new CancelBookingNotifications($join->training, $join->user));

        DB::commit();
    }
}
