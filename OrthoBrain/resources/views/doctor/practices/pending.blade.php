@extends('layouts.app')

@section('title', 'Practices Pending Approval')
@section('page_title', 'Practices Pending Approval')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-warning d-flex align-items-start">
            <i class="bi bi-hourglass-split me-2 mt-1" style="font-size:1.2rem;"></i>
            <div>
                <strong>Your account is approved, but you don't have any active practice yet.</strong>
                <div class="mt-1">An admin still needs to approve at least one of your practice requests before you can start working on cases.</div>
            </div>
        </div>

        <h5 class="mt-4">Pending Requests</h5>
        @if($pending->isEmpty())
            <p class="text-muted">No pending requests.</p>
        @else
            <ul class="list-group">
                @foreach($pending as $p)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-hourglass-split text-warning me-2"></i>
                            <strong>{{ $p->name }}</strong>
                            <small class="text-muted d-block">Requested {{ \Carbon\Carbon::parse($p->pivot->requested_at ?? $p->pivot->created_at)->diffForHumans() }}</small>
                        </div>
                        <form method="POST" action="{{ route('doctor.practices.cancel', $p->pivot->id) }}"
                              onsubmit="return confirm('Cancel this request?')" class="m-0">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" type="submit">Cancel</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($rejected->isNotEmpty())
            <h5 class="mt-4">Recently Rejected</h5>
            <ul class="list-group">
                @foreach($rejected as $p)
                    <li class="list-group-item">
                        <i class="bi bi-x-circle text-danger me-2"></i>
                        <strong>{{ $p->name }}</strong>
                        @if($p->pivot->rejection_reason)
                            <small class="text-muted d-block">Reason: {{ $p->pivot->rejection_reason }}</small>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <hr class="my-4">
        <h5>Request Another Practice</h5>
        <form method="POST" action="{{ route('doctor.practices.request') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-9 position-relative">
                <label class="form-label">Search by name</label>
                <input type="hidden" name="practice_id" id="req-practice-id" required>
                <input type="text" id="req-practice-name" class="form-control" placeholder="Type a practice name…" autocomplete="off" oninput="reqPracticeInput(event)">
                <div id="req-practice-menu" class="list-group position-absolute w-100" style="z-index:10;max-height:240px;overflow:auto;display:none;"></div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
    let reqTimer = null;
    async function reqPracticeInput(e) {
        document.getElementById('req-practice-id').value = '';
        const q = e.target.value.trim();
        clearTimeout(reqTimer);
        const menu = document.getElementById('req-practice-menu');
        if (q.length < 2) { menu.style.display = 'none'; menu.innerHTML = ''; return; }
        reqTimer = setTimeout(async () => {
            const res = await fetch('{{ route('practice.search') }}?q=' + encodeURIComponent(q),
                { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const items = await res.json();
            menu.innerHTML = items.length
                ? items.map(p => `<button type="button" class="list-group-item list-group-item-action" onclick="reqPickPractice(${p.id}, ${JSON.stringify(p.name)})">${escapeHtml(p.label)}</button>`).join('')
                : '<div class="list-group-item text-muted">No matching practice.</div>';
            menu.style.display = 'block';
        }, 250);
    }
    function reqPickPractice(id, name) {
        document.getElementById('req-practice-id').value = id;
        document.getElementById('req-practice-name').value = name;
        document.getElementById('req-practice-menu').style.display = 'none';
    }
    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
</script>
@endsection
