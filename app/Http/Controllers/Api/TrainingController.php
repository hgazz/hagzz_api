<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Join;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    use apiResponse;

    public function index(Request $request)
    {
        $query = Training::query()
            ->select('id','name','price','start_date','end_date','max_players','level','gender','age_group','academy_id','address_id','sport_id');

        $request->whenHas('sports_ids', function($sportsIds) use($query){
            $query->whereIn('sport_id', $sportsIds);
        });

        $request->whenHas('search', function ($search) use($query){
            $lowercaseSearchTerm = '%' . mb_strtolower($search) . '%'; // Always lowercase
            $query->whereRaw('LOWER(JSON_UNQUOTE(name->"$.en")) LIKE ?', [$lowercaseSearchTerm])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(name->"$.ar")) LIKE ?', [$lowercaseSearchTerm]);
        });

        $request->whenHas('start_soon', function () use($query){
            $query->whereDate('start_date','<=', Carbon::now()->addDay(10)->toDateString());
        });

        $request->whenHas('age_group', function ($age_group) use($query){
            $query->whereIn('age_group',$age_group);
        });

        $request->whenHas('gender', function ($gender) use($query){
            $query->where('gender',$gender);
        });

        $request->whenHas('area_id', function($areaId) use($query){
            $query->whereHas('address', function($query) use($areaId){
                return $query->whereIn('area_id',$areaId);
            });
        });

        $query->when($request->start_date && $request->end_date, function ($q) use ($request) {
            $q->whereBetween('start_date', [$request->start_date, $request->end_date]);
        });

        $trainings = $query->with(['academy:id,commercial_name',
            'address:id,address'])->withCount(['classes', 'joins'])->get();


        return $this->apiResponse(200, trans('api.home.All Training'), null, $trainings);
    }

    public function trainingDetails($id)
    {
        $training = Training::with([
            'coach:id,name,image',
            'academy:id,commercial_name',
            'address:id,address,longitude,latitude',
            'classes',
            'joins.user:id,image',
        ])
            ->withCount(['classes', 'coach', 'joins'])
            ->select('id','name','price','start_date','end_date','max_players','level','gender','age_group','academy_id','address_id','sport_id')
            ->find($id);
        if(!$training)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.training_not_found'),);
        }
        $is_joined = Join::whereBelongsTo(auth()->user(), 'user')->whereBelongsTo($training, 'training')->exists();
        $data = [
            'training' => $training,
            //   'academy_follow' => $training->academy->follows->count(),
            'is_joined' => $is_joined
        ];
        return $this->apiResponse(200, trans('api.home.Training Detail'), null, $data);
    }




}
