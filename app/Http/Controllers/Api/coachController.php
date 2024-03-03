<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Coach;
use Illuminate\Http\Request;

class coachController extends Controller
{
    use apiResponse;
    public function coachProfile($id)
    {
        $coach = Coach::with('academy')->findOrFail($id);
       return $this->apiResponse(200,trans('api.home.coach profile'),null,['coach' => $coach]);
    }
}
