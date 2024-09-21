<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JoinResource;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
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
        $validations = Validator::make($request->all(),[
            'training_id'=>'required|exists:trainings,id',
            'price'=>'required|numeric|min:0.01',
            'payment_status' => 'required|in:Pending,Paid',
            'payment_order_id' => 'required'
        ]);

        if ($validations->fails()){
            return $this->apiResponse(400, trans('api.validation_error'), $validations->errors());
        }

        $joinsExist = Join::where([
            ['user_id',auth()->id()],
            ['training_id',$request->training_id],
        ])->exists();

        if ($joinsExist){
            return  $this->apiResponse(400 , null,trans('api.home.join training already exists'));
        }
        try {
            DB::beginTransaction();
            $training = Training::find($request->training_id);
            $netAmount = $request->price - (($training->academy->percentage / 100) * $request->price);
            $invoice = Invoice::create([
                'user_id'=>auth()->id(),
                'training_id'=>$request->training_id,
                'order_number' => $request->invoice_id,
                'status'=>'paid',
                'amount'=>$request->price,
                'payment_status' => $request->payment_status,
                'payment_order_id' => $request->payment_order_id,
                'net_amount' => $netAmount,
                'user_type' => 'online'
            ]);
            $join = Join::create([
                'user_id'=> auth()->id(),
                'invoice_id' => $invoice->id,
                'training_id'=> $request->training_id,
                'price'=> $request->price,
                'net_amount' => $netAmount
            ]);
            $details = [
                'training_id' => $join->training_id,
                'longitude' => $join->training->address->longitude,
                'latitude' => $join->training->address->latitude,
                'training_name' => $join->training->name,
            ];
            //notification to academy
            $academyTitle = 'New Booking';
            $academyDescription = $join->user->name.' booked '.$join->training->name.' with you. Please check your bookings';
            NotificationService::dbNotification($join->training->academy_id,Academies::class, 2, $academyTitle, $academyDescription, $join->training->academy->image, $details);
            $this->smsService->sendMessage($join->training->academy->phone, "{$academyTitle} - {$academyDescription}");

            //notifications to user
            $title = 'Booking Confirmed';
            $body = 'your booking with '.$join->training->academy->commercial_name.' is confirmed Training - '. $join->training->name;
            $data = [
                'title' => $title,
                'body' => $body,
                'image' => $join->training->academy->image,
                'details' => $details
            ];
           $x = NotificationService::firebaseNotification($data, auth('api')->user()->fcm_token);
           dd($x);
            NotificationService::dbNotification($join->user_id,User::class, 2, $title, $body, $join->training->academy->image, $details);
            $this->smsService->sendMessage($join->user->phone, "{$title} - {$body}");
            DB::commit();
            return $this->apiResponse(200,trans('api.home.joined as training successfully'),null , $join);
        }catch (\Exception $e){
            DB::rollBack();
            return $this->apiResponse(400, trans('api.validation_error'), $e->getMessage());
        }

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
        ])->where('user_id', auth()->id());

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
        ])->where('user_id', auth()->id());

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
