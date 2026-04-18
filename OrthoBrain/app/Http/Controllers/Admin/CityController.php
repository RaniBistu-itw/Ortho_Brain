<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CityRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        // If only the state is picked, infer its parent country so the filter
        // UI stays consistent ("child always knows its parent").
        if ($request->filled('state_id') && ! $request->filled('country_id')) {
            $countryId = State::whereKey($request->integer('state_id'))
                ->value('country_id');
            if ($countryId) {
                $request->merge(['country_id' => $countryId]);
            }
        }

        $cities = City::with('state.country')->withCount('zipcodes')
            ->when($request->filled('country_id'),
                fn ($q) => $q->whereHas('state', fn ($s) => $s->where('country_id', $request->integer('country_id'))))
            ->when($request->filled('state_id'),
                fn ($q) => $q->where('state_id', $request->integer('state_id')))
            ->when($request->filled('search'),
                fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'),
                fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.cities.index', [
            'cities'    => $cities,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
            'states'    => State::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'country_id']),
        ]);
    }

    public function create()
    {
        return view('admin.cities.create', [
            'city'      => new City(['status' => 'ACTIVE']),
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function store(CityRequest $request)
    {
        City::create($request->safe()->only(['state_id', 'name', 'status']));
        return redirect()->route('admin.cities.index')->with('success', 'City created successfully.');
    }

    public function edit(City $city)
    {
        $city->loadMissing('state.country');
        return view('admin.cities.edit', [
            'city'      => $city,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function update(CityRequest $request, City $city)
    {
        $city->update($request->safe()->only(['state_id', 'name', 'status']));
        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        if ($city->zipcodes()->exists()) {
            return back()->with('error', 'Cannot delete — this city has zip codes linked to it.');
        }
        $city->delete();
        return redirect()->route('admin.cities.index')->with('success', 'City deleted.');
    }
}