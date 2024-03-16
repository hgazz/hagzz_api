<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Join;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JoinController extends Controller
{
    use apiResponse;
    public function addJoin(Request $request)
    {
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
        return $this->apiResponse(200,trans('api.home.joined as training successfully'),null , $joins);
    }

    public function join(Request $request)
    {
        $pageSize = 10;
        $page = $request->has('page') ? (int) $request->input('page') : 1;

        $query = Join::query()->with([
            'training' => function ($query) {
                $query->where('active', true);
                $query->select(['id', 'name', 'image', 'price', 'start_date', 'end_date', 'max_players', 'level', 'gender', 'age_group', 'address_id', 'academy_id', 'active']);
                $query->withCount(['joins', 'classes']);
            },
            'training.academy' => function ($query) {
                $query->select(['id', 'commercial_name']);
            },
            'training.address' => function ($query) {
                $query->select(['id', 'address']);
            },
            'training.academy.follows'
        ])->where('user_id', auth()->id());

        $total = $query->count();
        $joins = $query->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        $data = [
            'joins' => $joins,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ];

        return $this->apiResponse(200, trans('api.home.join by user'), null, $data);
    }
}
