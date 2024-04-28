<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Http\Traits\apiResponse;
use App\Models\Favorite;
use App\Models\Join;
use App\Models\Notification;
use App\Models\Training;
use App\Models\User;
use App\Services\Firebase\NotificationService;
use App\Services\SMSMISR\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class FavoriteController extends Controller
{
   use  apiResponse;

   private SmsService $smsService;
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    public function favoriteList()
    {
        $pageSize = 10;
        $page = (request()->has('page')) ? request('page') : 1;
        $query = Favorite::with([
            'training' => function ($query) {
                $query->select('id', 'name', 'price', 'start_date', 'end_date', 'max_players', 'level', 'gender', 'age_group','address_id','academy_id','active');
                $query->withCount(['joins', 'classes']);
            },
            'training.academy'=>function ($query){
                $query->select('id','commercial_name')
                ->withCount(['follows']);
            },
            'training.address'=>function($query){
                $query->select('id','address');
            }, 'training.sport'=>function($query){
                $query->select('id','name', 'icon');
            },
            'training.academy.follows'
        ])->where('user_id', auth('api')->id());
        $totalFavorites = $query->count();
        // Apply pagination to the query
        $favorites = $query->skip($page * $pageSize - $pageSize)->limit($pageSize)->get();

        // Calculate the total number of pages
        $totalPages = ceil($totalFavorites / $pageSize);

        // Prepare the response data including total counts and pagination details
        $data = [
            'favorites' => FavoriteResource::collection($favorites),
            'total' => $totalFavorites,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => $totalPages
        ];
        return $this->apiResponse(200 ,'Favorite list',null , $data);
   }
    public function addFavorite(Request $request)
    {
        try {
            DB::beginTransaction();
            $validation = Validator::make($request->all() , [
                'training_id' => 'required|exists:trainings,id',
            ]);

            if ($validation->fails()){
                return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
            }

            $favExist = Favorite::where(['user_id' =>auth()->id(), 'training_id' => $request->training_id])->exists();
            if ($favExist){
                return  $this->apiResponse(400 , null,trans('api.home.favorite already exists'));
            }
            $fav = Favorite::create([
                'user_id'=>auth()->id(),
                'training_id'=>$request->training_id
            ]);

            $joinsCount = Join::where('training_id', $fav->training_id)->count();

            $training = Training::find($fav->training_id);

            $slotsAvailable = $training->max_players - $joinsCount;

            if ($slotsAvailable <= 2) {
                $detail = [
                    'training_id' => $training->id,
                    'longitude' => $training->address->longitude,
                    'latitude' => $training->address->latitude
                ];
                $title = "Don't miss out";
                $body = "Only two slots are available in a training you saved";
                $data = [
                    'title' => $title,
                    'body' => $body,
                    'image' => $training->academy->image,
                    'details'=>$detail
                ];

                NotificationService::firebaseNotification($data,auth()->user()->fcm_token);
                NotificationService::dbNotification(auth()->id(), User::class, 2, $title, $body, $training->academy->logo, $detail);

              $this->smsService->sendMessage($fav->user->phone, "{$title} - {$body}");
            }
            DB::commit();
            return $this->apiResponse(200 ,trans('api.home.add favorite successfully'),null, $fav);
        }catch (\Exception $e){
            dd($e->getMessage());
        }

    }

    public function deleteFavorite($id)
    {
        $favorite = Favorite::where([['user_id',auth()->id()], ['training_id', $id]])->first();
       if (!$favorite){
           return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.Favorite not found'));
       }
       $favorite->delete();
       return $this->apiResponse(200 ,trans('api.home.delete favorite successfully'));
    }
}
