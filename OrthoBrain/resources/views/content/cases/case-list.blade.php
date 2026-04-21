@extends('layouts.app')

@section('title', 'Cases')
@section('page_title', 'Cases')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">Cases</h4>
        <a href="{{ route('doctor.cases.create') }}" class="btn btn-primary">
          <i data-feather="plus" class="me-1"></i> New Case
        </a>
      </div>
      <div class="card-body">
        @if($cases->isEmpty())
          <p class="text-muted mb-0">No cases yet. Click <strong>New Case</strong> to get started.</p>
        @else
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead>
                <tr>
                  <th>Case ID</th>
                  <th>Case code</th>
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
                      @php
                        $statusClass = match($case->status) {
                          'DRAFT' => 'badge bg-light-secondary',
                          'SUBMITTED' => 'badge bg-light-info',
                          'IN_REVIEW' => 'badge bg-light-warning',
                          'APPROVED' => 'badge bg-light-success',
                          'REJECTED' => 'badge bg-light-danger',
                          default => 'badge bg-light-secondary',
                        };
                      @endphp
                      <span class="{{ $statusClass }}">{{ $case->status }}</span>
                    </td>
                    <td>{{ $case->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $case->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="text-end">
                      <a href="{{ route('doctor.cases.edit', $case->id) }}" class="btn btn-sm btn-outline-primary">
                        {{ $case->status === 'DRAFT' ? 'Continue' : 'View' }}
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var flashRaw = sessionStorage.getItem('caseSubmittedFlash')
  if (!flashRaw) return
  sessionStorage.removeItem('caseSubmittedFlash')

  try {
    var flash = JSON.parse(flashRaw)
    if (!flash.message) return
    showSuccessToast(flash.message)
  } catch (err) {
    console.error('Failed to parse flash:', err)
  }
})

function showSuccessToast(message) {
  var toastHtml = '<div class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-4"' +
    ' role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1080;">' +
    '<div class="d-flex">' +
    '<div class="toast-body">' + message + '</div>' +
    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
    '</div></div>'
  var wrapper = document.createElement('div')
  wrapper.innerHTML = toastHtml
  var toastEl = wrapper.firstElementChild
  document.body.appendChild(toastEl)
  var toast = new bootstrap.Toast(toastEl, { delay: 5000 })
  toast.show()
  toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove() })
}
</script>
@endpush
