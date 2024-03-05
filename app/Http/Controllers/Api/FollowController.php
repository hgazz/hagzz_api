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

        // Eager load the followable relationship
        $follows = $user->follows()->get();

        // Transform the follows collection to customize the response
        $customFollows = $follows->map(function ($follow) {
            // Directly access the followable entity here
            $followable = $follow->followable_type;
            // Initialize an empty array to avoid undefined variable issues
            $data = [];

            if ($followable == Coach::class) {
                    // Customize the data for a Coach
                    $data = [
                        'id' => $follow->followable_id,
                        'type' => 'Coach',
                        'data' => Coach::find($follow->followable_id),
                    ];
                } elseif ($followable == Academies::class) {
                    // Customize the data for an Academy
                    $data = [
                        'id' => $follow->followable_id,
                        'type' => 'Academy',
                        'data' => Academies::find($follow->followable_id),
                    ];
            }

            return $data;
        });

        // Filter out any empty arrays that may have been added for followables that don't exist or don't match the conditions
        $filteredCustomFollows = $customFollows->filter(function ($value) {
            return !empty($value);
        });

        return $this->apiResponse(200, trans('api.home.following_list'), null, $filteredCustomFollows);
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
