<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoachResource;
use App\Http\Resources\PartnerResource;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Coach;
use App\Models\Follow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    use apiResponse;
    private $followModel;

    public function __construct(Follow $follow)
    {
        $this->followModel = $follow;
    }

    public function followList()
    {
        $user = auth()->user();

        $pageSize = 10;
        $page = (request()->has('page')) ? request('page') : 1;

        // Start by getting the total count for proper pagination
        $totalFollows = $user->follows()->count();

        // Eager load the followable relationship with proper pagination
        $follows = $user->follows()->with('followable')->skip(($page - 1) * $pageSize)->limit($pageSize)->get();

        // Transform the follows collection to customize the response
        $customFollows = $follows->map(function ($follow) {
            $followable = $follow->followable;

//            if ($followable instanceof Coach) {
//                // Customize the data for a Coach
//                return [
//                    'id' => $followable->id,
//                    'type' => 'Coach',
//                    'data' => $followable->load('sports:id,name,icon'), // Assuming $followable is already the Coach model instance
//                ];
//            } elseif ($followable instanceof Academies) {
//                // Customize the data for an Academy
//                return [
//                    'id' => $followable->id,
//                    'type' => 'Academy',
//                    'data' => $followable->load('sports:id,name,icon'), // Assuming $followable is already the Academies model instance
//                ];
//            }
            if ($followable instanceof Coach) {
                return new CoachResource($followable);
            } elseif ($followable instanceof Academies) {
                return new PartnerResource($followable);
            }

            return null;
        })->filter();

        $data = [
            'follows' => $customFollows,
            'total' => $totalFollows,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($totalFollows / $pageSize),
        ];

        return $this->apiResponse(200, trans('api.home.following_list'), null, $data);
    }



    public function addFollow(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'type' => 'required|in:coach,academy',
            'id' => $this->checkType($request)
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }
        $userId = auth()->id();

        $followType = ($request->type == 'coach') ? Coach::class : Academies::class;

        $existingFollow = $this->followModel::with('followable')->where([
            ['user_id', $userId],
            ['followable_id', $request->id],
            ['followable_type', $followType],
        ])->first();

        if ($existingFollow) {
            return $this->apiResponse(400, "This {$request->type} already in your favourite list", null, $existingFollow);
        }

        $follow = $this->followModel::create([
            'user_id' => $userId,
            'followable_id' => $request->id,
            'followable_type' => $followType,
        ]);
        return $this->apiResponse(200, trans('api.home.following_success'), null, $follow);
    }

    public function deleteFollow(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'type' => 'required|in:coach,academy',
            'id' => $this->checkType($request)
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $followType = ($request->type == 'coach') ? Coach::class : Academies::class;

        $follow = $this->followModel::where([
            ['user_id', auth()->id()],
            ['followable_id', $request->id],
            ['followable_type', $followType],
        ])->first();

        if (!$follow) {
            return $this->apiResponse(400, trans('api.home.there_is_no_follow'));
        }
        $follow->delete();
        return $this->apiResponse(200, trans('api.home.unfollow'));
    }

    protected function checkType($request)
    {
        return $request->type == 'coach' ? 'required|exists:coaches,id' : 'required|exists:academies,id';
    }
}
