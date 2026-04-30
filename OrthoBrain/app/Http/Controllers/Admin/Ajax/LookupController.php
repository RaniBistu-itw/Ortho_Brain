<?php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Doctor;
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

    public function doctorSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = Doctor::query()
            ->select(['id', 'first_name', 'last_name', 'practice_id'])
            ->with('practice:id,name')
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('first_name', 'like', $q . '%')
                  ->orWhere('last_name',  'like', $q . '%');
            });
        }

        return response()->json(
            $query->limit(30)->get()->map(fn (Doctor $d) => [
                'id'   => $d->id,
                'text' => trim($d->first_name . ' ' . $d->last_name)
                          . ($d->practice ? ' — ' . $d->practice->name : ''),
            ])->values()
        );
    }
}
