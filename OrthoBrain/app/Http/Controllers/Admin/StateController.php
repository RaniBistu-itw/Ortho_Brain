<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StateRequest;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StateController extends Controller
{
    public function index(Request $request)
    {
        $sortable = [
            'country'      => 'country_name',
            'name'         => 'states.name',
            'state_code'   => 'states.state_code',
            'status'       => 'states.status',
            'cities_count' => 'cities_count',
        ];
        $sortKey = $request->get('sort');
        $sortCol = $sortable[$sortKey] ?? 'states.name';
        $dir     = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = State::query()
            ->with('country')
            ->withCount('cities')
            ->select('states.*');

        if ($sortKey === 'country') {
            $query->leftJoin('countries', 'countries.id', '=', 'states.country_id')
                  ->orderBy('countries.name', $dir);
        } else {
            $query->orderBy($sortCol, $dir);
        }

        $states = $query
            ->when($request->filled('country_id'), fn ($q) => $q->where('states.country_id', $request->integer('country_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search');
                $q->where(fn ($w) => $w->where('states.name', 'like', "%{$s}%")->orWhere('states.state_code', 'like', "%{$s}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('states.status', $request->string('status')))
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'    => State::count(),
            'active'   => State::where('status', 'ACTIVE')->count(),
            'inactive' => State::where('status', 'INACTIVE')->count(),
        ];

        return view('admin.states.index', [
            'states'    => $states,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
            'stats'     => $stats,
        ]);
    }

    public function show(State $state)
    {
        $state->loadMissing('country')->loadCount('cities');
        return view('admin.states.show', compact('state'));
    }

    public function destroy(State $state)
    {
        if ($state->cities()->exists()) {
            return back()->with('error', 'Cannot delete — this state has cities linked to it.');
        }
        $state->delete();
        return redirect()->route('admin.states.index')->with('success', 'State deleted.');
    }

    // ─── AJAX endpoints for drawer create / edit ────────────────────────

    public function ajaxStore(StateRequest $request)
    {
        $state = State::create($request->validated());
        $state->loadMissing('country')->loadCount('cities');

        return response()->json([
            'ok'      => true,
            'state'   => $this->presentRow($state),
            'message' => 'State created.',
        ]);
    }

    public function ajaxUpdate(StateRequest $request, State $state)
    {
        $state->update($request->validated());
        $state->loadMissing('country')->loadCount('cities');

        return response()->json([
            'ok'      => true,
            'state'   => $this->presentRow($state),
            'message' => 'State updated.',
        ]);
    }

    public function ajaxBulk(Request $request)
    {
        $payload = $request->input('states', []);

        $validator = Validator::make(['states' => $payload], [
            'states'              => ['required', 'array', 'min:1', 'max:50'],
            'states.*.country_id' => ['required', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'states.*.name'       => ['required', 'string', 'max:100'],
            'states.*.state_code' => ['required', 'string', 'max:100'],
            'states.*.status'     => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        // state_code is unique per country — guard both within-batch and against existing rows.
        $validator->after(function ($v) use ($payload) {
            $seen = [];
            foreach ($payload as $i => $row) {
                $countryId = $row['country_id'] ?? null;
                $code      = $row['state_code'] ?? '';
                if (! $countryId || $code === '') continue;

                $key = $countryId . '|' . strtolower(trim($code));
                if (isset($seen[$key])) {
                    $v->errors()->add("states.{$i}.state_code", 'Duplicate state code for this country in the batch.');
                }
                $seen[$key] = true;

                $exists = State::where('country_id', $countryId)
                    ->where('state_code', $code)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($exists) {
                    $v->errors()->add("states.{$i}.state_code", 'A state with this code already exists for the selected country.');
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
                $s = State::create([
                    'country_id' => $row['country_id'],
                    'name'       => $row['name'],
                    'state_code' => $row['state_code'],
                    'status'     => $row['status'],
                ]);
                $s->loadMissing('country')->loadCount('cities');
                $rows[] = $s;
            }
            return $rows;
        });

        return response()->json([
            'ok'      => true,
            'states'  => array_map(fn ($s) => $this->presentRow($s), $created),
            'message' => count($created) === 1
                ? 'State created.'
                : count($created) . ' states created.',
        ]);
    }

    private function presentRow(State $s): array
    {
        return [
            'id'           => $s->id,
            'country_id'   => $s->country_id,
            'country_name' => $s->country?->name ?? '—',
            'name'         => $s->name,
            'state_code'   => $s->state_code,
            'status'       => $s->status,
            'cities_count' => (int) ($s->cities_count ?? 0),
            'update_url'   => route('admin.states.ajax.update', $s),
            'destroy_url'  => route('admin.states.destroy', $s),
            'show_url'     => route('admin.states.show', $s),
        ];
    }
}
