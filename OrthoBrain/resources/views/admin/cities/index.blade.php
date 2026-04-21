@extends('layouts.admin')
@section('title', 'Cities')
@section('page_title', 'Cities')

@section('content')
<section id="cities-list">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Cities</h4>
            <a href="{{ route('admin.cities.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add City
            </a>
        </div>

        <div class="card-body py-1">
            <form id="citiesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-3">
                    <select id="filter_country_id" name="country_id" data-ob-cascade-parent class="js-searchable form-select">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->id }}" @selected(request('country_id')==$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter_state_id" name="state_id" class="js-searchable form-select">
                        <option value="">All states</option>
                        @foreach ($states as $s)
                            <option value="{{ $s->id }}" data-country-id="{{ $s->country_id }}" @selected(request('state_id')==$s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" placeholder="Search city..." value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>State</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Zip Codes</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cities as $city)
                        <tr>
                            <td>{{ $city->state?->country?->name ?? '—' }}</td>
                            <td>{{ $city->state?->name ?? '—' }}</td>
                            <td class="fw-bolder">{{ $city->name }}</td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $city->status === 'ACTIVE' ? 'success' : 'danger' }}">{{ $city->status }}</span>
                            </td>
                            <td>
                                @if ($city->zipcodes_count > 0)
                                    {{ $city->zipcodes_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.cities.show', $city) }}" class="btn btn-icon btn-sm btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit"><i data-feather="edit-2"></i></a>
                                @if ($city->zipcodes_count === 0)
                                    <form method="POST" action="{{ route('admin.cities.destroy', $city) }}" class="d-inline js-delete-form" data-confirm="Delete city '{{ $city->name }}'?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $city->name }}' — it has linked zip codes. Remove them first."><i data-feather="trash-2"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-2">No cities found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $cities->links() }}</div>
    </div>
</section>

@push('scripts')
<script>
obPreloadedCascade({
    parent: '#filter_country_id',
    child: '#filter_state_id',
    parentAttr: 'data-country-id',
});
obAutoFilter('#citiesFilter');
</script>
@endpush
@endsection
