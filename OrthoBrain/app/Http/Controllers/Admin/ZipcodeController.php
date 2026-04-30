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

        $sortable = [
            'code'       => 'zipcodes.code',
            'city'       => 'cities.name',
            'state'      => 'states.name',
            'country'    => 'countries.name',
            'created_at' => 'zipcodes.created_at',
        ];
        $order   = $request->query('order') === 'oldest' ? 'oldest' : 'newest';
        $sortKey = $request->get('sort');
        if ($sortKey && isset($sortable[$sortKey])) {
            $sortCol = $sortable[$sortKey];
            $dir     = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        } else {
            $sortKey = null;
            $sortCol = 'zipcodes.created_at';
            $dir     = $order === 'oldest' ? 'asc' : 'desc';
        }

        $query = Zipcode::query()
            ->with('city.state.country')
            ->select('zipcodes.*');

        if (in_array($sortKey, ['city', 'state', 'country'], true)) {
            $query->leftJoin('cities', 'cities.id', '=', 'zipcodes.city_id')
                  ->leftJoin('states', 'states.id', '=', 'cities.state_id')
                  ->leftJoin('countries', 'countries.id', '=', 'states.country_id')
                  ->orderBy($sortCol, $dir);
        } else {
            $query->orderBy($sortCol, $dir);
        }

        $zipcodes = $query
            ->when($request->filled('country_id'),
                fn ($q) => $q->whereHas('city.state', fn ($s) => $s->where('country_id', $request->integer('country_id'))))
            ->when($request->filled('state_id'),
                fn ($q) => $q->whereHas('city', fn ($c) => $c->where('state_id', $request->integer('state_id'))))
            ->when($request->filled('city_id'), fn ($q) => $q->where('zipcodes.city_id', $request->integer('city_id')))
            ->when($request->filled('search'), fn ($q) => $q->where('zipcodes.code', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('zipcodes.status', $request->string('status')))
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'    => Zipcode::count(),
            'active'   => Zipcode::where('status', 'ACTIVE')->count(),
            'inactive' => Zipcode::where('status', 'INACTIVE')->count(),
        ];

        return view('admin.zipcodes.index', [
            'zipcodes'  => $zipcodes,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
            'states'    => $request->filled('country_id')
                ? State::where('status', 'ACTIVE')
                      ->where('country_id', $request->integer('country_id'))
                      ->orderBy('name')->get(['id', 'name', 'country_id'])
                : collect(),
            'cities'    => $request->filled('state_id')
                ? City::where('status', 'ACTIVE')
                      ->where('state_id', $request->integer('state_id'))
                      ->orderBy('name')->get(['id', 'name', 'state_id'])
                : collect(),
            'stats'     => $stats,
        ]);
    }

    public function show(Zipcode $zipcode)
    {
        $zipcode->loadMissing('city.state.country');
        return view('admin.zipcodes.show', compact('zipcode'));
    }

    public function destroy(Zipcode $zipcode)
    {
        $zipcode->delete();
        return redirect()->route('admin.zipcodes.index')->with('success', 'Zip code deleted.');
    }

    // ─── AJAX endpoints for drawer create / edit ────────────────────────

    public function ajaxStore(ZipcodeRequest $request)
    {
        $zipcode = Zipcode::create($request->safe()->only(['city_id', 'code', 'details', 'status']));
        $zipcode->loadMissing('city.state.country');

        return response()->json([
            'ok'      => true,
            'zipcode' => $this->presentRow($zipcode),
            'message' => 'Zip code created.',
        ]);
    }

    public function ajaxUpdate(ZipcodeRequest $request, Zipcode $zipcode)
    {
        $zipcode->update($request->safe()->only(['city_id', 'code', 'details', 'status']));
        $zipcode->loadMissing('city.state.country');

        return response()->json([
            'ok'      => true,
            'zipcode' => $this->presentRow($zipcode),
            'message' => 'Zip code updated.',
        ]);
    }

    public function updateStatus(Request $request, Zipcode $zipcode)
    {
        $data = $request->validate([
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $zipcode->update(['status' => $data['status']]);
        return response()->json([
            'ok'      => true,
            'status'  => $zipcode->status,
            'message' => 'Status updated.',
        ]);
    }

    public function ajaxCheckUnique(Request $request)
    {
        $code     = trim((string) $request->input('code', ''));
        $cityId   = $request->integer('city_id') ?: null;
        $ignoreId = $request->integer('ignore_id') ?: null;

        if ($code === '' || ! $cityId) {
            return response()->json(['available' => true]);
        }

        $exists = Zipcode::where('city_id', $cityId)
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message'   => $exists ? 'A zip code with this value already exists for the selected city.' : null,
        ]);
    }

    private function presentRow(Zipcode $z): array
    {
        return [
            'id'           => $z->id,
            'city_id'      => $z->city_id,
            'city_name'    => $z->city?->name ?? '—',
            'state_id'     => $z->city?->state_id,
            'state_name'   => $z->city?->state?->name ?? '—',
            'country_id'   => $z->city?->state?->country_id,
            'country_name' => $z->city?->state?->country?->name ?? '—',
            'code'         => $z->code,
            'details'      => (string) ($z->details ?? ''),
            'status'       => $z->status,
            'update_url'   => route('admin.zipcodes.ajax.update', $z),
            'destroy_url'  => route('admin.zipcodes.destroy', $z),
            'show_url'     => route('admin.zipcodes.show', $z),
            'status_url'   => route('admin.zipcodes.status', $z),
        ];
    }
}
