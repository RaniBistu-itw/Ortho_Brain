<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryRequest;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $sortable = [
            'name'         => 'name',
            'country_code' => 'country_code',
            'phone_code'   => 'phone_code',
            'status'       => 'status',
            'states_count' => 'states_count',
        ];
        $sort = $request->get('sort');
        $sort = isset($sortable[$sort]) ? $sortable[$sort] : 'name';
        $dir  = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $countries = Country::withCount('states')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search');
                $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('country_code', 'like', "%{$s}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy($sort, $dir)
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

    public function ajaxBulk(Request $request)
    {
        $payload = $request->input('countries', []);

        $validator = Validator::make(['countries' => $payload], [
            'countries'                => ['required', 'array', 'min:1', 'max:50'],
            'countries.*.name'         => ['required', 'string', 'max:100'],
            'countries.*.country_code' => ['required', 'string', 'max:10', Rule::unique('countries', 'country_code')->whereNull('deleted_at')],
            'countries.*.phone_code'   => ['required', 'string', 'max:10'],
            'countries.*.status'       => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        // Catch duplicates within the same batch (server-side guard for the unique index).
        $validator->after(function ($v) use ($payload) {
            $seen = [];
            foreach ($payload as $i => $row) {
                $code = strtolower(trim($row['country_code'] ?? ''));
                if ($code === '') continue;
                if (isset($seen[$code])) {
                    $v->errors()->add("countries.{$i}.country_code", 'Duplicate country code in this batch.');
                }
                $seen[$code] = true;
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
                $c = Country::create([
                    'name'         => $row['name'],
                    'country_code' => $row['country_code'],
                    'phone_code'   => $row['phone_code'],
                    'status'       => $row['status'],
                ]);
                $c->loadCount('states');
                $rows[] = $c;
            }
            return $rows;
        });

        return response()->json([
            'ok'        => true,
            'countries' => array_map(fn ($c) => $this->presentRow($c), $created),
            'message'   => count($created) === 1
                ? 'Country created.'
                : count($created) . ' countries created.',
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
