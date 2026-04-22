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

        $stats = [
            'total'    => Country::count(),
            'active'   => Country::where('status', 'ACTIVE')->count(),
            'inactive' => Country::where('status', 'INACTIVE')->count(),
        ];

        return view('admin.countries.index', compact('countries', 'stats'));
    }

    public function show(Country $country)
    {
        return view('admin.countries.show', compact('country'));
    }

    public function destroy(Country $country)
    {
        if ($country->states()->exists()) {
            return back()->with('error', 'Cannot delete — this country has states linked to it.');
        }
        $country->delete();
        return redirect()->route('admin.countries.index')->with('success', 'Country deleted.');
    }

    // ─── AJAX endpoints for drawer create / edit ────────────────────────

    public function ajaxStore(CountryRequest $request)
    {
        $country = Country::create($request->validated());
        $country->loadCount('states');

        return response()->json([
            'ok'      => true,
            'country' => $this->presentRow($country),
            'message' => 'Country created.',
        ]);
    }

    public function ajaxUpdate(CountryRequest $request, Country $country)
    {
        $country->update($request->validated());
        $country->loadCount('states');

        return response()->json([
            'ok'      => true,
            'country' => $this->presentRow($country),
            'message' => 'Country updated.',
        ]);
    }

    private function presentRow(Country $c): array
    {
        return [
            'id'           => $c->id,
            'name'         => $c->name,
            'country_code' => $c->country_code,
            'phone_code'   => $c->phone_code,
            'status'       => $c->status,
            'states_count' => (int) ($c->states_count ?? 0),
            'update_url'   => route('admin.countries.ajax.update', $c),
            'destroy_url'  => route('admin.countries.destroy', $c),
            'show_url'     => route('admin.countries.show', $c),
        ];
    }
}
