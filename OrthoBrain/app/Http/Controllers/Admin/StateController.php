<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StateRequest;
use App\Models\Country;
use App\Models\State;
use App\Support\StatusDependencyChecker;
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
            'cities_count' => 'cities_count',
            'created_at'   => 'states.created_at',
        ];
        $order   = $request->query('order') === 'oldest' ? 'oldest' : 'newest';
        $sortKey = $request->get('sort');
        if ($sortKey && isset($sortable[$sortKey])) {
            $sortCol = $sortable[$sortKey];
            $dir     = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        } else {
            $sortKey = null;
            $sortCol = 'states.created_at';
            $dir     = $order === 'oldest' ? 'asc' : 'desc';
        }

        $query = State::query()
            ->select('states.*')
            ->with('country')
            ->withCount('cities');

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

    public function updateStatus(Request $request, State $state)
    {
        $data = $request->validate([
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'force'  => ['sometimes', 'boolean'],
        ]);

        if ($data['status'] === 'INACTIVE' && empty($data['force'])) {
            $dependents = StatusDependencyChecker::activeDependents($state);
            if (!empty($dependents)) {
                return response()->json(StatusDependencyChecker::buildResponsePayload($state, $dependents));
            }
        }

        $state->update(['status' => $data['status']]);
        return response()->json([
            'ok'      => true,
            'status'  => $state->status,
            'message' => 'Status updated.',
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

        // Name and state_code are unique per country — guard within-batch and against existing rows.
        $validator->after(function ($v) use ($payload) {
            $seenCode = [];
            $seenName = [];
            foreach ($payload as $i => $row) {
                $countryId = $row['country_id'] ?? null;
                $code      = strtolower(trim($row['state_code'] ?? ''));
                $name      = strtolower(trim($row['name'] ?? ''));
                if (! $countryId) continue;

                if ($code !== '') {
                    $key = $countryId . '|' . $code;
                    if (isset($seenCode[$key])) {
                        $v->errors()->add("states.{$i}.state_code", 'Duplicate state code for this country in the batch.');
                    }
                    $seenCode[$key] = true;

                    $exists = State::where('country_id', $countryId)
                        ->whereRaw('LOWER(state_code) = ?', [$code])
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) {
                        $v->errors()->add("states.{$i}.state_code", 'A state with this code already exists for the selected country.');
                    }
                }

                if ($name !== '') {
                    $key = $countryId . '|' . $name;
                    if (isset($seenName[$key])) {
                        $v->errors()->add("states.{$i}.name", 'Duplicate state name for this country in the batch.');
                    }
                    $seenName[$key] = true;

                    $exists = State::where('country_id', $countryId)
                        ->whereRaw('LOWER(name) = ?', [$name])
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) {
                        $v->errors()->add("states.{$i}.name", 'A state with this name already exists for the selected country.');
                    }
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

    public function ajaxCheckUnique(Request $request)
    {
        $field     = $request->input('field') === 'state_code' ? 'state_code' : 'name';
        $value     = trim((string) $request->input('value', ''));
        $countryId = $request->integer('country_id') ?: null;
        $ignoreId  = $request->integer('ignore_id') ?: null;

        if ($value === '' || ! $countryId) {
            return response()->json(['available' => true]);
        }

        $exists = State::where('country_id', $countryId)
            ->whereRaw("LOWER({$field}) = ?", [strtolower($value)])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereNull('deleted_at')
            ->exists();

        $messages = [
            'name'       => 'A state with this name already exists for the selected country.',
            'state_code' => 'A state with this code already exists for the selected country.',
        ];

        return response()->json([
            'available' => ! $exists,
            'message'   => $exists ? $messages[$field] : null,
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
            'status_url'   => route('admin.states.status', $s),
        ];
    }
}
