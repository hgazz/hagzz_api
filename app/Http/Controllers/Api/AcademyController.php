<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnersResource;
use App\Http\Resources\TrainingResource;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Follow;
use App\Models\Training;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AcademyController extends Controller
{
    use  apiResponse;

    public function getAllAcademies(Request $request)
    {
        $pageSize = 10;
        $page = $request->has('page') ? (int) $request->input('page') : 1;
        $query = Academies::query();
        $total = $query->count();
        $academies = $query->select(['id', 'commercial_name', 'logo'])
            ->with('sports')
            ->skip($page * $pageSize - $pageSize)->limit($pageSize)
        ->get();
        $data = [
            'academies' => PartnersResource::collection($academies),
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ];
        return $this->apiResponse(200, trans('api.home.Academy List'), null, $data);
    }

    public function academyDetails($id)
    {
        $academy = Academies::with(['addresses','galleries', 'sports'])
            ->select(['id', 'phone', 'commercial_name', 'logo', 'address', 'facebook', 'instagram'])
            ->withCount(['follows','coaches', 'trainings', 'addresses'])
            ->find($id);

        if(!$academy)
        {
            return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.academy_not_found'));
        }
        $isFollowing = $this->checkAcademyFollow($academy);
        $data = [
            'academy' => new PartnersResource($academy),
            'isFollowing' => $isFollowing,
        ];
        return $this->apiResponse(200,trans('api.home.Academy Details'),null, $data);
    }

    public function getTrainingsByAcademy($id, Request $request)
    {
        $pageSize = 10;
        $page = $request->has('page') ? (int) $request->input('page') : 1;

        // Start building the query with necessary relationships
        $query = Training::where('academy_id', $id)
            ->with([
                'academy' ,
                'address',
                'sport'
            ])
            ->withCount(['joins', 'classes'])
            ->isActive();

        // Get the total count of records that match the query criteria before applying pagination
        $total = $query->count();

        // Apply pagination to the query
        $trainings = $query->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        // Prepare and return the API response with the paginated data and other details
        $data = [
            'trainings' => TrainingResource::collection($trainings),
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ];

        return $this->apiResponse(200, trans('api.home.All Training'), null, $data);
    }

    /**
     * @param Model|Collection|Builder|array $academy
     * @return mixed
     */
    public function checkAcademyFollow(Model|Collection|Builder|array $academy)
    {
        return auth('api')->check() ? Follow::where('user_id', auth('api')->id())
            ->where('followable_type', Academies::class)
            ->where('followable_id', $academy->id)->exists() : null;

    }
}
