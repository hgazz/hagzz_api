<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\SportResource;
use App\Http\Resources\TrainingResource;
use App\Http\Traits\apiResponse;
use App\Models\Academies;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\Training;
use App\Models\User;
use App\Models\UserSport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    use apiResponse;
    public function home()
    {
        $banners = Banner::limit(6)->inRandomOrder()->get();
        $sports = auth('api')->check() ? $this->getUserSports() : SportResource::collection(Sport::limit(6)->inRandomOrder()->get(['id','name','icon']));
        $academies = auth()->check() ? PartnerResource::collection(Academies::with('sports')->select(['id','commercial_name','logo'])->whereHas('addresses.country',function($q){$q->where('id',auth('api')->user()->country_id);})->limit(6)->inRandomOrder()->get(['id','commercial_name','logo'])) : PartnerResource::collection(Academies::with('sports')->select(['id','commercial_name','logo'])->get())  ;
        $trainings = auth('api')->check() ? $this->getUserTraining() : $this->getRandomTrainings();
        return $this->apiResponse(200,trans('api.home.All Data in Home Screen'),null,[
            'banners'=> $banners,
            'sports related user authenticated'=> $sports,
            'academies and related sports'=> $academies,
            'training' => [
                'trainings_count' => Training::count(),
                'trainings' => $trainings
            ],
        ]);
    }
    protected function getUserTraining()
    {
        $userSportsIds = auth('api')->user()->sports->pluck('id')->toArray();

        $trainings = Training::with([
            'academy',
            'address:id,address',
            'classes',
            'sport'
        ])->whereIn('sport_id', $userSportsIds)
            ->withCount(['classes', 'joins'])
            ->inRandomOrder()
            ->limit(4)
            ->whereHas('address', function ($query){
                $query->where('country_id', auth('api')->user()->country_id);
            })
            ->get();

        return TrainingResource::collection($trainings);
    }

    protected function getUserSports()
    {
        $userSports = UserSport::with('sport:id,name,icon')
            ->where('user_id', auth('api')->id())
            ->get();

        // Return the collection of sports using SportResource
        return SportResource::collection($userSports->pluck('sport'));
    }

    protected function getRandomTrainings()
    {
        $trainings = Training::with([
            'academy',
            'address',
            'classes',
            'sport'
        ])->inRandomOrder()
            ->limit(4)
            ->get();
        return TrainingResource::collection($trainings);
    }

    public function changeLang(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'lang' => 'required|in:en,ar',
        ]);

        if($validation->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validation->errors());
        }

        $user = User::where('id', auth()->id())->first();
        $user->update([
            'language' => $request->lang
        ]);

        return $this->apiResponse(200, trans('api.lang_changed'));
    }

    public function terms()
    {
        $terms = Setting::where('key','terms')->first();
        return $this->apiResponse(200, trans('api.terms'), null, $terms);
    }

    public function getFaqs()
    {
        $faqs = FaqResource::collection(Faq::get());
        return $this->apiResponse(200, trans('api.faqs'), null, $faqs);
    }

    protected function getAcademies()
    {
        try {
            $query = Academies::with('sports')
                ->select(['id', 'commercial_name', 'logo']);

            if (auth()->check()) {
                $query->whereHas('addresses.country', function ($q) {
                    $q->where('country_id', auth('api')->user()->country_id);
                })->inRandomOrder()->limit(6);
            }

            $academies = PartnerResource::collection($query->get());

            return $this->apiResponse(200, trans('api.home.Academy List'), null, ['academies' => $academies]);
        } catch (\Exception $e) {
            \Log::error('Error fetching academies: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->apiResponse(500, trans('api.something_went_wrong'), ['error' => $e->getMessage()]);
        }
    }


}
