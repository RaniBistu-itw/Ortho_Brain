@php
    $bannerDoctor = auth()->user()?->doctor;
    $bannerPending = $bannerDoctor ? $bannerDoctor->pendingPractices()->count() : 0;
@endphp

@if($bannerPending > 0 && (request()->route()?->getName() !== 'doctor.practices.pending'))
    <div class="alert alert-warning d-flex align-items-center justify-content-between mb-2"
         style="border-left:4px solid #ff9f43;background:#fff6ed;color:#6e4b13;">
        <div>
            <i class="bi bi-hourglass-split me-2"></i>
            <strong>{{ $bannerPending }}</strong>
            practice {{ \Illuminate\Support\Str::plural('request', $bannerPending) }}
            awaiting admin approval.
        </div>
        <a href="{{ route('doctor.profile.index') }}?tab=practices" class="btn btn-sm btn-warning">View status</a>
    </div>
@endif
