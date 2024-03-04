<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $follows = $user->follows;
        return $this->apiResponse(200, trans('api.home.following_list'), null, $follows);
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
