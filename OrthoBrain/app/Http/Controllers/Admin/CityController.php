<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CityRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Support\StatusDependencyChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

        $sortable = [
            'country'        => 'countries.name',
            'state'          => 'states.name',
            'name'           => 'cities.name',
            'zipcodes_count' => 'zipcodes_count',
            'created_at'     => 'cities.created_at',
        ];
        $order   = $request->query('order') === 'oldest' ? 'oldest' : 'newest';
        $sortKey = $request->get('sort');
        if ($sortKey && isset($sortable[$sortKey])) {
            $sortCol = $sortable[$sortKey];
            $dir     = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        } else {
            $sortKey = null;
            $sortCol = 'cities.created_at';
            $dir     = $order === 'oldest' ? 'asc' : 'desc';
        }

        $query = City::query()
            ->select('cities.*')
            ->with('state.country')
            ->withCount('zipcodes');

        if (in_array($sortKey, ['country', 'state'], true)) {
            $query->leftJoin('states', 'states.id', '=', 'cities.state_id')
                  ->leftJoin('countries', 'countries.id', '=', 'states.country_id')
                  ->orderBy($sortCol, $dir);
        } else {
            $query->orderBy($sortCol, $dir);
        }

        $cities = $query
            ->when($request->filled('country_id'),
                fn ($q) => $q->whereHas('state', fn ($s) => $s->where('country_id', $request->integer('country_id'))))
            ->when($request->filled('state_id'),
                fn ($q) => $q->where('cities.state_id', $request->integer('state_id')))
            ->when($request->filled('search'),
                fn ($q) => $q->where('cities.name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'),
                fn ($q) => $q->where('cities.status', $request->string('status')))
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'    => City::count(),
            'active'   => City::where('status', 'ACTIVE')->count(),
            'inactive' => City::where('status', 'INACTIVE')->count(),
        ];

        return view('admin.cities.index', [
            'cities'    => $cities,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
            'states'    => $request->filled('country_id')
                ? State::where('status', 'ACTIVE')
                      ->where('country_id', $request->integer('country_id'))
                      ->orderBy('name')->get(['id', 'name', 'country_id'])
                : collect(),
            'stats'     => $stats,
        ]);
    }

    public function show(City $city)
    {
        $city->loadMissing('state.country')->loadCount('zipcodes');
        return view('admin.cities.show', compact('city'));
    }

    public function destroy(City $city)
    {
        if ($city->zipcodes()->exists()) {
            return back()->with('error', 'Cannot delete — this city has zip codes linked to it.');
        }
        $city->delete();
        return redirect()->route('admin.cities.index')->with('success', 'City deleted.');
    }

    // ─── AJAX endpoints for drawer create / edit ────────────────────────

    public function ajaxStore(CityRequest $request)
    {
        $city = City::create($request->safe()->only(['state_id', 'name', 'status']));
        $city->loadMissing('state.country')->loadCount('zipcodes');

        return response()->json([
            'ok'      => true,
            'city'    => $this->presentRow($city),
            'message' => 'City created.',
        ]);
    }

    public function ajaxUpdate(CityRequest $request, City $city)
    {
        $city->update($request->safe()->only(['state_id', 'name', 'status']));
        $city->loadMissing('state.country')->loadCount('zipcodes');

        return response()->json([
            'ok'      => true,
            'city'    => $this->presentRow($city),
            'message' => 'City updated.',
        ]);
    }

    public function updateStatus(Request $request, City $city)
    {
        $data = $request->validate([
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'force'  => ['sometimes', 'boolean'],
        ]);

        if ($data['status'] === 'INACTIVE' && empty($data['force'])) {
            $dependents = StatusDependencyChecker::activeDependents($city);
            if (!empty($dependents)) {
                return response()->json(StatusDependencyChecker::buildResponsePayload($city, $dependents));
            }
        }

        $city->update(['status' => $data['status']]);
        return response()->json([
            'ok'      => true,
            'status'  => $city->status,
            'message' => 'Status updated.',
        ]);
    }

    public function ajaxBulk(Request $request)
    {
        $payload = $request->input('cities', []);

        $validator = Validator::make(['cities' => $payload], [
            'cities'            => ['required', 'array', 'min:1', 'max:50'],
            'cities.*.state_id' => ['required', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'cities.*.name'     => ['required', 'string', 'max:100'],
            'cities.*.status'   => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        // City name is unique per state — guard within-batch + against existing rows.
        $validator->after(function ($v) use ($payload) {
            $seen = [];
            foreach ($payload as $i => $row) {
                $stateId = $row['state_id'] ?? null;
                $name    = strtolower(trim($row['name'] ?? ''));
                if (! $stateId || $name === '') continue;

                $key = $stateId . '|' . $name;
                if (isset($seen[$key])) {
                    $v->errors()->add("cities.{$i}.name", 'Duplicate city name for this state in the batch.');
                }
                $seen[$key] = true;

                $exists = City::where('state_id', $stateId)
                    ->whereRaw('LOWER(name) = ?', [$name])
                    ->whereNull('deleted_at')
                    ->exists();
                if ($exists) {
                    $v->errors()->add("cities.{$i}.name", 'A city with this name already exists for the selected state.');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'ok'     => false,
                'errors' => $validator->errors()->messages(),
            ], 422);
        }

        $created = DB::transaction(function () use ($payload) {
            $rows = [];
            foreach ($payload as $row) {
                $city = City::create([
                    'state_id' => $row['state_id'],
                    'name'     => $row['name'],
                    'status'   => $row['status'],
                ]);
                $city->loadMissing('state.country')->loadCount('zipcodes');
                $rows[] = $city;
            }
            return $rows;
        });

        return response()->json([
            'ok'      => true,
            'cities'  => array_map(fn ($c) => $this->presentRow($c), $created),
            'message' => count($created) === 1
                ? 'City created.'
                : count($created) . ' cities created.',
        ]);
    }

    public function ajaxCheckUnique(Request $request)
    {
        $name     = trim((string) $request->input('name', ''));
        $stateId  = $request->integer('state_id') ?: null;
        $ignoreId = $request->integer('ignore_id') ?: null;

        if ($name === '' || ! $stateId) {
            return response()->json(['available' => true]);
        }

        $exists = City::where('state_id', $stateId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereNull('deleted_at')
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message'   => $exists ? 'A city with this name already exists for the selected state.' : null,
        ]);
    }

    private function presentRow(City $c): array
    {
        return [
            'id'             => $c->id,
            'state_id'       => $c->state_id,
            'state_name'     => $c->state?->name ?? '—',
            'country_id'     => $c->state?->country_id,
            'country_name'   => $c->state?->country?->name ?? '—',
            'name'           => $c->name,
            'status'         => $c->status,
            'zipcodes_count' => (int) ($c->zipcodes_count ?? 0),
            'update_url'     => route('admin.cities.ajax.update', $c),
            'destroy_url'    => route('admin.cities.destroy', $c),
            'show_url'       => route('admin.cities.show', $c),
            'status_url'     => route('admin.cities.status', $c),
        ];
    }
}
