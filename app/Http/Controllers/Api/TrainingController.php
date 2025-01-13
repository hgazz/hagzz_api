<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingResource;
use App\Http\Traits\apiResponse;
use App\Models\Join;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    use apiResponse;

    public function getAllTrainings(Request $request)
    {
        $pageSize = 10;
        $page = (request()->has('page')) ? request('page') : 1;
        $query = Training::query()->select(['id','name','price','end_date','max_players','level','gender','age_group','academy_id','address_id','sport_id']);
        $total = $query->count();
        $query = $query->skip($page * $pageSize - $pageSize)->limit($pageSize);
        $trainings = $query->with(['academy'=> function ($query) {
            $query->select(['id', 'app_name', 'logo']);
            $query->withCount('follows');
        },
            'address:id,address,area_id,city_id',
            'sport:id,name,icon'])
            ->whereHas('address', function ($q) {
                $q->where('country_id', auth('api')->user()->country_id);
            })
            ->withCount(['classes', 'joins'])->get();
        $data = [
            'trainings' => TrainingResource::collection($trainings),
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ];

        return $this->apiResponse(200, trans('api.home.Training List'), null, $data);

    }

    public function index(Request $request)
    {

        try {
            $pageSize = 10;
            $page = (request()->has('page')) ? request('page') : 1;
            $query = Training::query()->select(['id','name','start_time','end_time','classes_days','classes_number','price','max_players','level','gender','age_group','academy_id','address_id','sport_id', 'discount_price']);

            $request->whenHas('sports_ids', function($sportsIds) use($query){
                $query->whereIn('sport_id', $sportsIds);
            });

            $request->whenHas('search', function ($search) use($query){
                $lowercaseSearchTerm = '%' . mb_strtolower($search) . '%'; // Always lowercase
                $query->whereRaw('LOWER(JSON_UNQUOTE(name->"$.en")) LIKE ?', [$lowercaseSearchTerm])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(name->"$.ar")) LIKE ?', [$lowercaseSearchTerm]);
            });


            $request->whenHas('age_group', function ($age_group) use($query){
                $query->whereIn('age_group',$age_group);
            });

            $request->whenHas('almost_full', function () use ($query) {
                $query->whereRaw('
                (SELECT COUNT(*) FROM joins WHERE joins.training_id = trainings.id) / trainings.max_players * 100 >= 50
                AND
                (SELECT COUNT(*) FROM joins WHERE joins.training_id = trainings.id) / trainings.max_players * 100 < 100
            ');
            });

            $request->whenHas('gender', function ($gender) use($query){
                $query->where('gender',$gender);
            });

            $request->whenHas('level', function ($level) use($query){
                $query->whereIn('level',$level);
            });

            $request->whenHas('near_me', function () use($query){
                $query->whereHas('address', function($query){
                    return $query->where('country_id', auth('api')->user()->country_id);
                });
            });

            $request->whenHas('areas_ids', function($areaId) use($query){
                $query->whereHas('address', function($query) use($areaId){
                    return $query->whereIn('area_id',$areaId);
                });
            });

            $total = $query->count();
            $query = $query->skip($page * $pageSize - $pageSize)->limit($pageSize);
            // Calculate the total number of pages
            $totalPages = ceil($total / $pageSize);
            $trainings = $this->filterHome($query);
            $data = [
                'trainings' => TrainingResource::collection($trainings),
                'total' => $total,
                'page' => $page,
                'pageSize' => $pageSize,
                'totalPages' => $totalPages
            ];

            return $this->apiResponse(200, trans('api.home.All Training'), null, $data);
        }catch (Exception $exception){
            return $this->apiResponse(400, trans('api.validation_error'), ["code" => $exception->getLine(),"file" => $exception->getFile(),"message" =>$exception->getMessage()]);
        }
    }

    public function trainingDetails($id)
    {
        $training = Training::with([
            'coach:id,name,image,gender',
            'address:id,address,longitude,latitude',
        ])->find($id);


        if(!$training)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.training_not_found'),);
        }

        $joins = User::whereHas('joins', function($query) use($training){
            return $query->where('training_id',$training->id);
        })->select(['id','image'])->get();

        $is_joined = auth('api')->check() ? Join::whereUserId(auth('api')->id())->whereTrainingId($id)->exists() : null;
        $data = [
            'training' => new TrainingResource($training),
            'is_joined' => $is_joined,
            'joins' => $joins
        ];
        return $this->apiResponse(200, trans('api.home.Training Detail'), null, $data);
    }


    private function filterHome($query)
    {
        return auth('api')->check() ? $query->with(['academy'=> function ($query) {
            $query->select(['id', 'commercial_name', 'logo']);
            $query->withCount('follows');
        },
            'address:id,address,area_id,city_id',
            'sport:id,name,icon'])
//            ->whereHas('address.country', function ($query) {
//                return $query->where('id', auth('api')->user()->country_id);
//            })
            ->withCount(['classes', 'joins'])->isActive()
            ->get() :
            $query->with(['academy'=> function ($query) {
                $query->select(['id', 'app_name', 'logo']);
                $query->withCount('follows');
            },
                'address:id,address,area_id,city_id',
                'sport:id,name,icon'])
                ->withCount(['joins'])
                ->isActive()->get();
    }

}
