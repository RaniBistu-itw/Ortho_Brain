<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ZipcodeRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\Zipcode;
use Illuminate\Http\Request;

class ZipcodeController extends Controller
{
    public function index(Request $request)
    {
        // Walk up the chain when only a leaf is picked, so filter UI + query stay in sync.
        if ($request->filled('city_id') && ! $request->filled('state_id')) {
            if ($stateId = City::whereKey($request->integer('city_id'))->value('state_id')) {
                $request->merge(['state_id' => $stateId]);
            }
        }
        if ($request->filled('state_id') && ! $request->filled('country_id')) {
            if ($countryId = State::whereKey($request->integer('state_id'))->value('country_id')) {
                $request->merge(['country_id' => $countryId]);
            }
        }

        $zipcodes = Zipcode::with('city.state.country')
            ->when($request->filled('country_id'),
                fn ($q) => $q->whereHas('city.state', fn ($s) => $s->where('country_id', $request->integer('country_id'))))
            ->when($request->filled('state_id'),
                fn ($q) => $q->whereHas('city', fn ($c) => $c->where('state_id', $request->integer('state_id'))))
            ->when($request->filled('city_id'), fn ($q) => $q->where('city_id', $request->integer('city_id')))
            ->when($request->filled('search'), fn ($q) => $q->where('code', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('admin.zipcodes.index', [
            'zipcodes'  => $zipcodes,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
            'states'    => State::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'country_id']),
            'cities'    => City::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'state_id']),
        ]);
    }

    public function create()
    {
        return view('admin.zipcodes.create', [
            'zipcode'   => new Zipcode(['status' => 'ACTIVE']),
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function store(ZipcodeRequest $request)
    {
        Zipcode::create($request->safe()->only(['city_id', 'code', 'details', 'status']));
        return redirect()->route('admin.zipcodes.index')->with('success', 'Zip code created successfully.');
    }

    public function edit(Zipcode $zipcode)
    {
        $zipcode->loadMissing('city.state.country');
        return view('admin.zipcodes.edit', [
            'zipcode'   => $zipcode,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function update(ZipcodeRequest $request, Zipcode $zipcode)
    {
        $zipcode->update($request->safe()->only(['city_id', 'code', 'details', 'status']));
        return redirect()->route('admin.zipcodes.index')->with('success', 'Zip code updated successfully.');
    }

    public function destroy(Zipcode $zipcode)
    {
        $zipcode->delete();
        return redirect()->route('admin.zipcodes.index')->with('success', 'Zip code deleted.');
    }
}
