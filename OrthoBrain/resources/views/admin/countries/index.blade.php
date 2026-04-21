@extends('layouts.admin')
@section('title', 'Countries')
@section('page_title', 'Countries')

@section('content')
<section id="countries-list">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Countries</h4>
            <a href="{{ route('admin.countries.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add Country
            </a>
        </div>

        <div class="card-body py-1">
            <form id="countriesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or code..." class="form-control">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>States</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($countries as $c)
                        <tr>
                            <td class="fw-bolder">{{ $c->name }}</td>
                            <td>{{ $c->country_code }}</td>
                            <td>{{ $c->phone_code }}</td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $c->status === 'ACTIVE' ? 'success' : 'danger' }}">
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td>
                                @if ($c->states_count > 0)
                                    {{ $c->states_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.countries.show', $c) }}" class="btn btn-icon btn-sm btn-outline-success" title="View">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('admin.countries.edit', $c) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit">
                                    <i data-feather="edit-2"></i>
                                </a>
                                @if ($c->states_count === 0)
                                    <form method="POST" action="{{ route('admin.countries.destroy', $c) }}" class="d-inline js-delete-form" data-confirm="Delete country '{{ $c->name }}'?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $c->name }}' — it has linked states. Remove them first."><i data-feather="trash-2"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-2">No countries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">{{ $countries->links() }}</div>
    </div>
</section>

@push('scripts')
<script>obAutoFilter('#countriesFilter');</script>
@endpush
@endsection
