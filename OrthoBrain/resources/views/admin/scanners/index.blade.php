@extends('layouts.admin')
@section('title', 'Scanners')
@section('page_title', 'Scanners')

@section('content')
<section id="scanners-list">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Scanners</h4>
            <a href="{{ route('admin.scanners.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add Scanner
            </a>
        </div>

        <div class="card-body py-1">
            <form id="scannersFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-5"><input type="text" name="search" placeholder="Search scanner..." value="{{ request('search') }}" class="form-control"></div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3"><a href="{{ route('admin.scanners.index') }}" class="btn btn-outline-secondary w-100">Clear</a></div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Portal Link</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($scanners as $sc)
                        <tr>
                            <td class="fw-bolder">{{ $sc->name }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($sc->description, 60) }}</td>
                            <td>
                                @if ($sc->portal_link)
                                    <a href="{{ $sc->portal_link }}" target="_blank" class="text-primary">{{ \Illuminate\Support\Str::limit($sc->portal_link, 40) }}</a>
                                @else — @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $sc->status === 'ACTIVE' ? 'success' : 'danger' }}">{{ $sc->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.scanners.show', $sc) }}" class="btn btn-icon btn-sm btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                <a href="{{ route('admin.scanners.edit', $sc) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit"><i data-feather="edit-2"></i></a>
                                <form method="POST" action="{{ route('admin.scanners.destroy', $sc) }}" class="d-inline js-delete-form" data-confirm="Delete scanner '{{ $sc->name }}'?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-2">No scanners found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $scanners->links() }}</div>
    </div>
</section>

@push('scripts')<script>obAutoFilter('#scannersFilter');</script>@endpush
@endsection
