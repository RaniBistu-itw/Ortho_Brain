<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StateRequest;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function index(Request $request)
    {
        $states = State::with('country')->withCount('cities')
            ->when($request->filled('country_id'), fn ($q) => $q->where('country_id', $request->integer('country_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search');
                $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('state_code', 'like', "%{$s}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
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
