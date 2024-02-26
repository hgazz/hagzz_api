<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $countries = Country::get();
        return $this->apiResponse(200, trans('api.countries.countries'), $countries);
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

        $cities = City::where('country_id', $request->country_id)->get(['id', 'name']);
        return $this->apiResponse(200, trans('api.countries.cities'), $cities);
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
        $areas = Area::where('city_id', $request->city_id)->get(['id', 'name']);
        return $this->apiResponse(200, trans('api.countries.areas'), $areas);
    }
}
