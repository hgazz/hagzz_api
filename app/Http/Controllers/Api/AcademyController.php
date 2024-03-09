<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Follow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AcademyController extends Controller
{
    use  apiResponse;
    public function academyDetails($id)
    {

        $academy = Academies::with(['trainings' => [
            'academy:id,logo,commercial_name',
            'academy.follows',
            'address:id,address,longitude,latitude',
        ],'galleries', 'sports'])
            ->select(['id', 'phone', 'commercial_name', 'logo', 'address', 'facebook', 'instagram'])
            ->withCount(['follows','coaches', 'trainings', 'addresses', 'trainings.classes'])
            ->find($id);

        if(!$academy)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.academy_not_found'));
        }
        $isFollowing = $this->checkAcademyFollow($academy);
        $data = [
            'academy' => $academy,
            'isFollowing' => $isFollowing,
        ];
        return $this->apiResponse(200,trans('api.home.Academy Details'),null, $data);
    }

    /**
     * @param Model|Collection|Builder|array $academy
     * @return mixed
     */
    public function checkAcademyFollow(Model|Collection|Builder|array $academy)
    {
        return Follow::whereBelongsTo(auth()->user(), 'user')
            ->where('followable_id', $academy->id)->exists();
    }
}
