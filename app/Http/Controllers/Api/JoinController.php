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
    public function joinList()
    {
        $joins = Join::with(['user:id,name,phone','invoice:id,order_number,status,amount','training:id,name,start_date,end_date'])->get();
        return $this->apiResponse(200 ,trans('api.home.join list') , null ,[
            'joins' => $joins
        ]);
    }

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
        $joinsExist = Join::where('user_id',auth()->id())->exists();
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

    public function join($id)
    {
        $join = Join::with(['user:id,name,phone','invoice:id,order_number,status,amount','training:id,name,start_date,end_date'])
            ->find($id);
        if(!$join)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.join not found'));
        }

        return  $this->apiResponse(200 , trans('api.home.join by user'),null , $join);
    }
}
