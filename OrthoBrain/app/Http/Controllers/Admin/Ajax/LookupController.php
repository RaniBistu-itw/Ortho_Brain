<?php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\ProductSubcategory;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function statesByCountry(Request $request): JsonResponse
    {
        $request->validate(['country_id' => ['required', 'integer', 'exists:countries,id']]);

        return response()->json(
            State::where('country_id', $request->integer('country_id'))
                ->where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name'])
        );
    }

    public function citiesByState(Request $request): JsonResponse
    {
        $request->validate(['state_id' => ['required', 'integer', 'exists:states,id']]);

        return response()->json(
            City::where('state_id', $request->integer('state_id'))
                ->where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name'])
        );
    }

    public function subcategoriesByCategory(Request $request): JsonResponse
    {
        $request->validate(['category_id' => ['required', 'integer', 'exists:products_category,id']]);

        return response()->json(
            ProductSubcategory::where('category_id', $request->integer('category_id'))
                ->where('status', true)->orderBy('name')->get(['id', 'name'])
        );
    }
}
