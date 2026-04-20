@extends('layouts.admin')
@section('title', 'States')
@section('page_title', 'States')

@section('content')
<section id="states-list">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">States</h4>
            <a href="{{ route('admin.states.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add State
            </a>
        </div>

        <div class="card-body py-1">
            <form id="statesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-4">
                    <select name="country_id" class="js-searchable form-select">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->id }}" @selected(request('country_id')==$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.states.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Cities</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($states as $s)
                        <tr>
                            <td>{{ $s->country->name ?? '—' }}</td>
                            <td class="fw-bolder">{{ $s->name }}</td>
                            <td>{{ $s->state_code }}</td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $s->status === 'ACTIVE' ? 'success' : 'danger' }}">{{ $s->status }}</span>
                            </td>
                            <td>
                                @if ($s->cities_count > 0)
                                    {{ $s->cities_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.states.show', $s) }}" class="btn btn-icon btn-sm btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                <a href="{{ route('admin.states.edit', $s) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit"><i data-feather="edit-2"></i></a>
                                @if ($s->cities_count === 0)
                                    <form method="POST" action="{{ route('admin.states.destroy', $s) }}" class="d-inline js-delete-form" data-confirm="Delete state '{{ $s->name }}'?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger" disabled title="Has linked cities — cannot delete"><i data-feather="trash-2"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-2">No states found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">{{ $states->links() }}</div>
    </div>
</section>

@push('scripts')
<script>obAutoFilter('#statesFilter');</script>
@endpush
@endsection
