@extends('layouts.admin')
@section('title', 'Zip Codes')
@section('page_title', 'Zip Codes')

@section('content')
<section id="zipcodes-list">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Zip Codes</h4>
            <a href="{{ route('admin.zipcodes.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add Zip Code
            </a>
        </div>

        <div class="card-body py-1">
            <form id="zipcodesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-2">
                    <select id="zip_country_id" name="country_id" data-ob-cascade-parent class="js-searchable form-select">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)<option value="{{ $c->id }}" @selected(request('country_id')==$c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="zip_state_id" name="state_id" data-ob-cascade-parent class="js-searchable form-select">
                        <option value="">All states</option>
                        @foreach ($states as $s)<option value="{{ $s->id }}" data-country-id="{{ $s->country_id }}" @selected(request('state_id')==$s->id)>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="zip_city_id" name="city_id" class="js-searchable form-select">
                        <option value="">All cities</option>
                        @foreach ($cities as $city)<option value="{{ $city->id }}" data-state-id="{{ $city->state_id }}" @selected(request('city_id')==$city->id)>{{ $city->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" placeholder="Search zip..." value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.zipcodes.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Zip Code</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($zipcodes as $z)
                        <tr>
                            <td class="fw-bolder">{{ $z->code }}</td>
                            <td>{{ $z->city?->name ?? '—' }}</td>
                            <td>{{ $z->city?->state?->name ?? '—' }}</td>
                            <td>{{ $z->city?->state?->country?->name ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $z->status === 'ACTIVE' ? 'success' : 'danger' }}">{{ $z->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.zipcodes.show', $z) }}" class="btn btn-icon btn-sm btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                <a href="{{ route('admin.zipcodes.edit', $z) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit"><i data-feather="edit-2"></i></a>
                                <form method="POST" action="{{ route('admin.zipcodes.destroy', $z) }}" class="d-inline js-delete-form" data-confirm="Delete zip '{{ $z->code }}'?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-2">No zip codes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $zipcodes->links() }}</div>
    </div>
</section>

@push('scripts')
<script>
obPreloadedCascade({ parent: '#zip_country_id', child: '#zip_state_id', parentAttr: 'data-country-id' });
obPreloadedCascade({ parent: '#zip_state_id',   child: '#zip_city_id',  parentAttr: 'data-state-id' });
obAutoFilter('#zipcodesFilter');
</script>
@endpush
@endsection
