@extends('layouts.admin')

@section('title', 'Cases')
@section('page_title', 'Cases')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-1">
        <h4 class="card-title mb-0">All Cases</h4>

        <form method="GET" action="{{ route('admin.cases.index') }}" class="d-flex flex-wrap align-items-center gap-50">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:160px;">
            <option value="">All statuses</option>
            @foreach($statusOptions as $s)
              <option value="{{ $s }}" @selected($statusFilter === $s)>{{ $s }}</option>
            @endforeach
          </select>

          <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:200px;">
            <option value="">All doctors</option>
            @foreach($doctors as $doc)
              <option value="{{ $doc->id }}" @selected((string) $doctorFilter === (string) $doc->id)>
                {{ trim($doc->first_name . ' ' . $doc->last_name) }} — {{ $doc->practice?->name ?? '—' }}
              </option>
            @endforeach
          </select>

          @if($statusFilter || $doctorFilter)
            <a href="{{ route('admin.cases.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
          @endif
        </form>
      </div>

      <div class="card-body">
        @if($cases->isEmpty())
          <p class="text-muted mb-0">No cases match the current filters.</p>
        @else
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead>
                <tr>
                  <th>Case ID</th>
                  <th>Code</th>
                  <th>Doctor</th>
                  <th>Practice</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Submitted</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($cases as $case)
                  <tr>
                    <td><span class="fw-bolder">#{{ $case->id }}</span></td>
                    <td>{{ $case->case_code ?? '—' }}</td>
                    <td>
                      @if($case->doctor)
                        {{ trim($case->doctor->first_name . ' ' . $case->doctor->last_name) }}
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>{{ $case->doctor?->practice?->name ?? '—' }}</td>
                    <td>
                      @php
                        $cls = match($case->status) {
                          'DRAFT' => 'badge bg-light-secondary',
                          'SUBMITTED' => 'badge bg-light-info',
                          'IN_REVIEW' => 'badge bg-light-warning',
                          'APPROVED' => 'badge bg-light-success',
                          'REJECTED' => 'badge bg-light-danger',
                          default => 'badge bg-light-secondary',
                        };
                      @endphp
                      <span class="{{ $cls }}">{{ $case->status }}</span>
                    </td>
                    <td>{{ $case->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $case->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="text-end">
                      <a href="{{ route('admin.cases.edit', $case->id) }}" class="btn btn-sm btn-outline-primary">
                        Open
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="mt-2">
            {{ $cases->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
