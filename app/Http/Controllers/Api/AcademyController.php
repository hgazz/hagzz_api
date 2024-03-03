<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use Illuminate\Http\Request;

class AcademyController extends Controller
{
    use  apiResponse;
    public function academyDetails($id)
    {
        $academy = Academies::findOrFail($id);
        return $this->apiResponse(200,trans('api.home.Academy Details'),null,['Academy' => $academy]);
    }
}
