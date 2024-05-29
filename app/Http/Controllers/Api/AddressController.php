<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use App\Http\Traits\apiResponse;
use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    use apiResponse;

    public function getCountries()
    {
        $countries = CountryResource::collection(Country::get(['id', 'name']));
        return $this->apiResponse(200, trans('api.countries.countries'), null, $countries);
    }


    public function getCitiesByCountry(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id'
        ]);

        if($validator->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validator->errors());
        }

        $cities = CityResource::collection(City::where('country_id', $request->country_id)->get(['id', 'name']));
        return $this->apiResponse(200, trans('api.countries.cities'), null, $cities);
    }

    public function getAreasByCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_id' => 'required|exists:cities,id'
        ]);

        if($validator->fails())
        {
            return $this->apiResponse(400, trans('api.validation_error'), $validator->errors());
        }
        $areas = AreaResource::collection(Area::where('city_id', $request->city_id)->get(['id', 'name']));
        return $this->apiResponse(200, trans('api.countries.areas'), null, $areas);
    }

    public function getAreas(Request $request)
    {
        $areas = auth()->check() ? AreaResource::collection(Area::where('city_id', auth()->user()->city_id)->get(['id', 'name'])) : AreaResource::collection(Area::get(['id', 'name']));
        return $this->apiResponse(200, trans('api.countries.areas'), null, $areas);
    }
}
