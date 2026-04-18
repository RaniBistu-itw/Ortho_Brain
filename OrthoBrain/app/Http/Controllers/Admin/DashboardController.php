<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\Scanner;
use App\Models\State;
use App\Models\Zipcode;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products'      => Product::count(),
            'categories'    => ProductCategory::count(),
            'subcategories' => ProductSubcategory::count(),
            'scanners'      => Scanner::count(),
            'countries'     => Country::count(),
            'states'        => State::count(),
            'cities'        => City::count(),
            'zipcodes'      => Zipcode::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
