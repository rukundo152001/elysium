<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Province;
use App\Models\District;
use App\Models\Sector;

class LocationController extends Controller
{
    public function countries()
    {
        return Country::orderBy('name')->get();
    }

    public function provinces($country)
    {
        return Province::where('country_id', $country)->orderBy('name')->get();
    }

    public function districts($province)
    {
        return District::where('province_id', $province)->orderBy('name')->get();
    }

    public function sectors($district)
    {
        return Sector::where('district_id', $district)->orderBy('name')->get();
    }

   
}
