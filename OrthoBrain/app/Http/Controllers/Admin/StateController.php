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

        return view('admin.states.index', [
            'states'    => $states,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.states.create', [
            'state'     => new State(['status' => 'ACTIVE']),
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function store(StateRequest $request)
    {
        State::create($request->validated());
        return redirect()->route('admin.states.index')->with('success', 'State created successfully.');
    }

    public function show(State $state)
    {
        $state->loadMissing('country')->loadCount('cities');
        return view('admin.states.show', compact('state'));
    }

    public function edit(State $state)
    {
        return view('admin.states.edit', [
            'state'     => $state,
            'countries' => Country::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function update(StateRequest $request, State $state)
    {
        $state->update($request->validated());
        return redirect()->route('admin.states.index')->with('success', 'State updated successfully.');
    }

    public function destroy(State $state)
    {
        if ($state->cities()->exists()) {
            return back()->with('error', 'Cannot delete — this state has cities linked to it.');
        }
        $state->delete();
        return redirect()->route('admin.states.index')->with('success', 'State deleted.');
    }
}
