<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JoinResource;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Favorite;
use App\Models\Join;
use App\Models\Training;
use App\Models\User;
use App\Models\Invoice;
use App\Services\Booking\BookingService;
use App\Services\Firebase\NotificationService;
use App\Services\SMSMISR\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JoinController extends Controller
{
    use apiResponse;

    private BookingService $bookingService;
    protected $smsService;

    /**
     * @param BookingService $bookingService
     * @param SmsService $smsService
     */
    public function __construct(BookingService $bookingService , SmsService $smsService)
    {
        $this->smsService = $smsService;
        $this->bookingService = $bookingService;
    }


    public function addJoin(Request $request)
    {
        $validations = Validator::make($request->all(), [
            'training_id' => 'required|exists:trainings,id',
            'price' => 'required|numeric|min:0.01',
            'payment_status' => 'required|in:Pending,Paid',
            'payment_order_id' => 'required'
        ]);

        if ($validations->fails()) {
            return $this->apiResponse(400, trans('api.validation_error'), $validations->errors());
        }

        if (Join::where(['user_id' => auth('api')->id(), 'training_id' => $request->training_id])->exists()) {
            return $this->apiResponse(400, null, trans('api.home.join training already exists'));
        }

        $training = Training::find($request->training_id);
        $count = $training->joins()->count();
        if ($count >= $training->max_players) {
            return $this->apiResponse(400, null, trans('api.home.join training is full'));
        }

        DB::beginTransaction();
        try {
            $netAmount = $request->price - (($training->academy->percentage / 100) * $request->price);

            $invoice = Invoice::create([
                'user_id' => auth('api')->id(),
                'training_id' => $request->training_id,
                'order_number' => $request->invoice_id,
                'status' => 'paid',
                'amount' => $request->price,
                'payment_status' => $request->payment_status,
                'payment_order_id' => $request->payment_order_id,
                'net_amount' => $netAmount,
                'user_type' => 'online'
            ]);

            $join = Join::create([
                'user_id' => auth('api')->id(),
                'invoice_id' => $invoice->id,
                'training_id' => $request->training_id,
                'price' => $request->price,
                'net_amount' => $netAmount
            ]);

            DB::commit();

            // Send notifications (not rolled back)
            $this->sendNotifications($join, $request->header('fcm-token'));
            $this->sendNotificationsForSavedTraining($request, $training);

            return $this->apiResponse(200, trans('api.home.joined as training successfully'), null, $join);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiResponse(400, trans('api.validation_error'), $e->getMessage());
        }
    }

    private function sendNotifications($join, $fcmToken)
    {
        $details = [
            'training_id' => $join->training_id,
            'longitude' => $join->training->address->longitude,
            'latitude' => $join->training->address->latitude,
            'training_name' => $join->training->getTranslation('name', 'en'),
        ];

        // Notification to academy
        $academyTitle = 'New Booking';
        $academyDescription = $join->user->name . ' booked ' . $join->training->getTranslation('name', 'en') . ' with you. Please check your bookings';
        NotificationService::dbNotification($join->training->academy_id, Academies::class, 2, $academyTitle, $academyDescription, $join->training->academy->image, $details);
        $this->smsService->sendMessage($join->training->academy->phone, "{$academyTitle} - {$academyDescription}");

        // Notifications to user
        $title = 'Booking Confirmed';
        $body = 'Your booking with ' . $join->training->academy->getTranslation('commercial_name', 'en') . ' is confirmed Training - ' . $join->training->getTranslation('name', 'en');
        $data = [
            'title' => $title,
            'body' => $body,
            'image' => $join->training->academy->image,
            'details' => $details
        ];
        NotificationService::firebaseNotification($data, $fcmToken);
        NotificationService::dbNotification($join->user_id, User::class, 2, $title, $body, $join->training->academy->image, $details);
        $this->smsService->sendMessage($join->user->phone, "{$title} - {$body}");
    }


    public function join(Request $request)
    {
        $pageSize = 10;
        $page = $request->has('page') ? (int) $request->input('page') : 1;
        $today = Carbon::now()->startOfDay();

        // Query for upcoming trainings
        $upcomingQuery = Join::query()->whereHas('training', function ($query) use ($today) {
            $query->where([['start_date', '>', $today],['end_date', '>=', $today]]);
        })->with([
            'training' => function ($query) {
                $query->where('active', true);
                $query->select(['id', 'name', 'price', 'start_date', 'end_date', 'max_players', 'level', 'gender', 'age_group', 'address_id', 'academy_id', 'active', 'sport_id']);
                $query->withCount(['joins', 'classes']);
            },
            'training.academy' => function ($query) {
                $query->select(['id', 'commercial_name']);
                $query->withCount(['follows']);
            },
            'training.address' => function ($query) {
                $query->select(['id', 'address']);
            },
            'training.sport' => function ($query) {
                $query->select(['id', 'name', 'icon']);
            },
        ])->where('user_id', auth('api')->id());

        // Query for past trainings
        $pastQuery = Join::query()->whereHas('training', function ($query) use ($today) {
            $query->where([['start_date', '<', $today]]);
        })->with([
            'training' => function ($query) {
                $query->where('active', true);
                $query->select(['id', 'name', 'price', 'start_date', 'end_date', 'max_players', 'level', 'gender', 'age_group', 'address_id', 'academy_id', 'active', 'discount_price', 'sport_id']);
                $query->withCount(['joins', 'classes']);
            },
            'training.academy' => function ($query) {
                $query->select(['id', 'commercial_name']);
                $query->withCount(['follows']);
            },
            'training.address' => function ($query) {
                $query->select(['id', 'address']);
            },
            'training.sport' => function ($query) {
                $query->select(['id', 'name', 'icon']);
            },
        ])->where('user_id', auth('api')->id());

        // Pagination for upcoming trainings
        $upcomingTotal = $upcomingQuery->count();
        $upcomingJoins = $upcomingQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        // Pagination for past trainings
        $pastTotal = $pastQuery->count();
        $pastJoins = $pastQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        $data = [
            'upcoming_joins' => JoinResource::collection($upcomingJoins),
            'past_joins' => JoinResource::collection($pastJoins),
            'total_upcoming' => $upcomingTotal,
            'total_past' => $pastTotal,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages_upcoming' => ceil($upcomingTotal / $pageSize),
            'totalPages_past' => ceil($pastTotal / $pageSize),
        ];

        return $this->apiResponse(200, trans('api.home.join by user'), null, $data);
    }

    public function cancelBooking(Request $request)
    {
        $validations = Validator::make($request->all(), [
            'id' => ['required','exists:trainings,id'],
            'reason' => 'required|min:3|max:255',
        ]);

        if ($validations->fails()) {
            return $this->apiResponse(400, trans('api.validation_error'), $validations->errors());
        }

        try {
            $this->bookingService->cancelBooking($request);
            return $this->apiResponse(200, trans('api.home.cancel booking successfully'));
        } catch (\Exception $e) {

            return $this->apiResponse(400, trans('api.error'), $e->getMessage());
        }

    }

    /**
     * @param Request $request
     * @param Training $training
     * @return void
     */
    public function sendNotificationsForSavedTraining(Request $request,Training $training): void
    {
        $favorites = Favorite::where(['training_id' => $request->training_id])->get();
        $joinsCount = Join::where('training_id', $request->training_id)->count();
        $slotsAvailable = $training->max_players - $joinsCount;
        $detail = [
            'training_id' => $training->id,
            'longitude' => $training->address->longitude,
            'latitude' => $training->address->latitude,
            'academy_name' => $training->academy->getTranslations('commercial_name', 'en'),
        ];
        if ($favorites->count() > 0 & $slotsAvailable <= 2) {
            foreach ($favorites as $favorite) {
                $title = "Last Chance!";
                $body = "Only two slots remaining in a training you saved";
                $data = [
                    'title' => $title,
                    'body' => $body,
                    'details' => $detail
                ];
                NotificationService::firebaseNotification($data, $favorite->user->fcm_token);
            }
        }
    }

    protected function checkJoinDate(Request $request)
    {
        $join = Join::find($request->id);
        if ($join->created_at->gte(Carbon::now()->subDays(2))) {
            return true; // The join date is within the last 2 days
        } else {
            return false; // The join date is more than 2 days ago
        }
    }
}
