<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\SettingResource;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    use apiResponse;
    public function home()
    {
        $banners = Banner::limit(6)->inRandomOrder()->get();
        $sports = auth('api')->check() ? $this->getUserSports() : SportResource::collection(Sport::limit(6)->inRandomOrder()->get(['id','name','icon']));
        $academies = auth('api')->check() ? PartnerResource::collection($this->getPartnersWithAuth()) : PartnerResource::collection($this->getPartnersGuest())  ;
        $trainings = auth('api')->check() ? $this->getUserTraining() : $this->getRandomTrainings();
        return $this->apiResponse(200,trans('api.home.All Data in Home Screen'),null,[
            'banners'=> $banners,
            'sports related user authenticated'=> $sports,
            'academies and related sports'=> $academies,
            'training' => [
                'trainings_count' => auth('api')->check() ? Training::whereHas('address',function($q){$q->where('country_id',auth('api')->user()->country_id);})->isActive()->count()   : Training::isActive()->count(),
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
        ])->whereHas('address', function ($query){
            $query->where('country_id', auth('api')->user()->country_id);
        })->whereIn('sport_id', $userSportsIds)
            ->withCount(['classes', 'joins'])
            ->inRandomOrder()
            ->isActive()
            ->limit(4)
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
            ->isActive()
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

    public function getSetting()
    {
        try {
            $settingsGrouped = Setting::whereIn('key', ['egypt_address', 'qatar_address', 'email', 'whatsapp'])
                ->get()
                ->groupBy('key');

            // Transform the keys after grouping
            $settingsTransformed = $settingsGrouped->mapWithKeys(function ($item, $key) {
                if ($key === 'egypt_address') {
                    $key = 'Egypt Address';
                }

                if ($key === 'qatar_address') {
                    $key = 'Qatar Address';
                }
                return [$key => $item];
            });

        } catch (\Exception $e) {
            return $this->apiResponse(500, trans('api.something_went_wrong'), ['error' => $e->getMessage()]);
        }

        // Using SettingResource to return the API response
        return $this->apiResponse(200, trans('api.home.setting'), null, $settingsTransformed);
    }

    public function getFaqs()
    {
        $faqs = FaqResource::collection(Faq::get());
        return $this->apiResponse(200, trans('api.faqs'), null, $faqs);
    }

    /**
     * @return Builder[]|Collection
     */
    public function getPartnersGuest(): array|Collection
    {
        return Academies::whereHas('trainings', function ($query) {
                $query->where('active', 1)
                ->whereDate('start_date', '>', Carbon::today()->toDateString());
            })->with('sports')->inRandomOrder()->limit(4)->get(['id', 'commercial_name', 'logo']);
    }

    /**
     * @return Builder[]|Collection
     */
    public function getPartnersWithAuth(): array|Collection
    {
        return Academies::with('sports')->whereHas('addresses', function ($q) {
            $q->where('country_id', auth('api')->user()->country_id);
        })->limit(6)->inRandomOrder()->get(['id', 'commercial_name', 'logo']);
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
