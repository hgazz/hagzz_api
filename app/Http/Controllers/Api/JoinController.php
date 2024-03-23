<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Join;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JoinController extends Controller
{
    use apiResponse;
    public function addJoin(Request $request)
    {
        try {
            DB::beginTransaction();
            $validations = Validator::make($request->all(),[
                'invoice_id'=>'required|exists:invoices,id',
                'training_id'=>'required|exists:trainings,id',
                'price'=>'required|numeric|min:0.01',
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
            $joins = Join::create([
                'user_id'=> auth()->id(),
                'invoice_id' => $request->invoice_id,
                'training_id'=>$request->training_id,
                'price'=>$request->price,
            ]);

            //notification to academy
            Notification::create([
                'id'=>Str::uuid(),
                'type'=>'New Booking',
               'notifiable_type'=> Academies::class,
                'notifiable_id'=> $joins->training->academy_id,
                'data'=> auth()->user()->name.' booked '.$joins->training->academy->commercial_name,
            ]);

            //notifications to user
            Notification::create([
                'id'=>Str::uuid(),
                'type'=>'Booking Confirmed',
                'notifiable_type'=> User::class,
                'notifiable_id'=> auth()->id(),
                'data'=>'  your booking with '.$joins->training->academy->commercial_name.' is confirmed',
            ]);

            DB::commit();
            return $this->apiResponse(200,trans('api.home.joined as training successfully'),null , $joins);
        }catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    public function join(Request $request)
    {
        $pageSize = 10;
        $page = $request->has('page') ? (int) $request->input('page') : 1;
        $today = Carbon::now()->startOfDay();

        // Query for upcoming trainings
        $upcomingQuery = Join::query()->whereHas('training', function ($query) use ($today) {
            $query->where('start_date', '>', $today);
        })->with([
            'training' => function ($query) {
                $query->where('active', true);
                $query->select(['id', 'name', 'image', 'price', 'start_date', 'end_date', 'max_players', 'level', 'gender', 'age_group', 'address_id', 'academy_id', 'active']);
                $query->withCount(['joins', 'classes']);
            },
            'training.academy' => function ($query) {
                $query->select(['id', 'commercial_name']);
                $query->withCount(['follows']);
            },
            'training.address' => function ($query) {
                $query->select(['id', 'address']);
            }
        ])->where('user_id', auth()->id());

        // Query for past trainings
        $pastQuery = Join::query()->whereHas('training', function ($query) use ($today) {
            $query->where('start_date', '<', $today);
        })->with([
            'training' => function ($query) {
                $query->where('active', true);
                $query->select(['id', 'name', 'image', 'price', 'start_date', 'end_date', 'max_players', 'level', 'gender', 'age_group', 'address_id', 'academy_id', 'active']);
                $query->withCount(['joins', 'classes']);
            },
            'training.academy' => function ($query) {
                $query->select(['id', 'commercial_name']);
                $query->withCount(['follows']);
            },
            'training.address' => function ($query) {
                $query->select(['id', 'address']);
            }
        ])->where('user_id', auth()->id());

        // Pagination for upcoming trainings
        $upcomingTotal = $upcomingQuery->count();
        $upcomingJoins = $upcomingQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        // Pagination for past trainings
        $pastTotal = $pastQuery->count();
        $pastJoins = $pastQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        $data = [
            'upcoming_joins' => $upcomingJoins,
            'past_joins' => $pastJoins,
            'total_upcoming' => $upcomingTotal,
            'total_past' => $pastTotal,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages_upcoming' => ceil($upcomingTotal / $pageSize),
            'totalPages_past' => ceil($pastTotal / $pageSize),
        ];

        return $this->apiResponse(200, trans('api.home.join by user'), null, $data);
    }
}
