<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryRequest;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::withCount('states')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search');
                $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('country_code', 'like', "%{$s}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.countries.index', compact('countries'));
    }

    public function create()
    {
        return view('admin.countries.create', ['country' => new Country(['status' => 'ACTIVE'])]);
    }

    public function store(CountryRequest $request)
    {
        Country::create($request->validated());
        return redirect()->route('admin.countries.index')->with('success', 'Country created successfully.');
    }

    public function show(Country $country)
    {
        return view('admin.countries.show', compact('country'));
    }

    public function edit(Country $country)
    {
        return view('admin.countries.edit', compact('country'));
    }

    public function update(CountryRequest $request, Country $country)
    {
        $country->update($request->validated());
        return redirect()->route('admin.countries.index')->with('success', 'Country updated successfully.');
    }

    public function destroy(Country $country)
    {
        if ($country->states()->exists()) {
            return back()->with('error', 'Cannot delete — this country has states linked to it.');
        }
        $country->delete();
        return redirect()->route('admin.countries.index')->with('success', 'Country deleted.');
    }
}
