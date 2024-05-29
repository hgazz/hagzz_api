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
        $page = $request->input('page', 1);

        $academiesQuery = Academies::select(['id', 'commercial_name', 'logo'])
            ->with('sports')
            ->whereHas('addresses.country', function (Builder $query) {
                $query->where('id', auth('api')->user()->country_id);
            });

        $total = $academiesQuery->count();
        $academies = $academiesQuery->paginate($pageSize, ['*'], 'page', $page);

        $data = [
            'academies' => PartnersResource::collection($academies->items()),
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => $academies->lastPage()
        ];

        return $this->apiResponse(200, trans('api.home.Academy List'), null, $data);
    }


    public function academyDetails($id)
    {
        try {
            $academy = $this->getAcademyById($id);

            if (!$academy) {
                return $this->apiResponse(400, trans('api.validation_error'), trans('api.home.academy_not_found'));
            }

            $isFollowing = $this->checkAcademyFollow($academy);

            $data = [
                'academy' => new PartnersResource($academy),
                'isFollowing' => $isFollowing,
            ];

            return $this->apiResponse(200, trans('api.home.Academy Details'), null, $data);

        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            \Log::error('Error fetching academy details: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return $this->apiResponse(500, trans('api.something_went_wrong'), ['error' => $e->getMessage()]);
        }
    }

    private function getAcademyById($id)
    {
        return Academies::with(['addresses', 'galleries', 'sports'])
            ->select(['id', 'phone', 'commercial_name', 'logo', 'address', 'facebook', 'instagram'])
            ->withCount(['follows', 'coaches', 'trainings', 'addresses'])
            ->find($id);
    }


    public function getTrainingsByAcademy($id, Request $request)
    {
        try {
            $pageSize = 10;
            $page = $request->input('page', 1);

            $query = $this->buildTrainingsQuery($id);

            // Get paginated results
            $trainings = $query->paginate($pageSize, ['*'], 'page', $page);

            $data = [
                'trainings' => TrainingResource::collection($trainings->items()),
                'total' => $trainings->total(),
                'page' => $trainings->currentPage(),
                'pageSize' => $trainings->perPage(),
                'totalPages' => $trainings->lastPage(),
            ];

            return $this->apiResponse(200, trans('api.home.All Training'), null, $data);
        } catch (\Exception $e) {
            \Log::error('Error fetching trainings: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->apiResponse(500, trans('api.something_went_wrong'), ['error' => $e->getMessage()]);
        }
    }

    private function buildTrainingsQuery($academyId)
    {
        return Training::where('academy_id', $academyId)
            ->with(['academy', 'address', 'sport'])
            ->withCount(['joins', 'classes'])
            ->isActive();
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
