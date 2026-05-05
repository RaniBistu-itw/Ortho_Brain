@php
    /**
     * Sortable column header link.
     * Required: $label, $key
     * Optional: $default (sort key used when no ?sort= present), $defaultDir ('asc'|'desc')
     */
    $current     = request('sort', $default ?? null);
    $currentDir  = strtolower(request('dir', $defaultDir ?? 'asc')) === 'desc' ? 'desc' : 'asc';
    $isActive    = $current === $key;
    $nextDir     = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';
    $url         = request()->fullUrlWithQuery(['sort' => $key, 'dir' => $nextDir, 'page' => 1]);
    $iconClass   = $isActive
        ? ($currentDir === 'desc' ? 'bi-arrow-down' : 'bi-arrow-up')
        : 'bi-arrow-down-up';
@endphp
<a href="{{ $url }}" class="ob-th-sort {{ $isActive ? 'is-active' : '' }}">
    <span>{{ $label }}</span>
    <i class="bi {{ $iconClass }}"></i>
</a>
