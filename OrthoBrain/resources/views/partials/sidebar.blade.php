@php
    $is = fn ($pattern) => request()->routeIs($pattern)
        ? 'is-active bg-vuexy-primary/10 text-vuexy-primary border-l-4 border-vuexy-primary'
        : 'text-[#6e6b7b] hover:bg-gray-50 border-l-4 border-transparent';
@endphp
<aside class="w-[260px] bg-white border-r border-[#ebe9f1] fixed top-0 left-0 h-screen flex flex-col shadow-[0_0_20px_rgba(0,0,0,0.05)] z-50">

    {{-- Brand --}}
    <div class="p-5 border-b border-[#ebe9f1] flex flex-col items-center">
        <svg width="44" height="38" viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mb-[-4px]">
            <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff"/>
            <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7" stroke="#b8b8b8"/>
            <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none"/>
        </svg>
        <div class="text-lg font-medium tracking-tight mt-1 flex items-start">
            <span class="text-[#5bc0de]">ortho</span><span class="text-[#8cc63f]">brain</span><span class="text-[#8cc63f] text-xs ml-[1px] mt-1">™</span>
        </div>
        <div class="text-[10px] italic text-[#6e6b7b] mt-1 text-center">
            Orthodontics for Your Dental Practice
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-3">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <button type="button" data-ob-section="manage-types" class="w-full flex items-center justify-between px-5 pt-4 pb-1.5 text-[10px] font-semibold uppercase tracking-wider text-[#b9b9c3] hover:text-[#6e6b7b] transition cursor-pointer select-none">
            <span>Manage Types</span>
            <i class="bi bi-chevron-down ob-chevron transition-transform text-[11px]"></i>
        </button>
        <div data-ob-section-body="manage-types">
            <a href="{{ route('admin.product-categories.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.product-categories.*') }}">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="{{ route('admin.product-subcategories.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.product-subcategories.*') }}">
                <i class="bi bi-tag"></i> Sub Categories
            </a>
            <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.products.*') }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <a href="{{ route('admin.scanners.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.scanners.*') }}">
                <i class="bi bi-upc-scan"></i> Scanners
            </a>
        </div>

        <button type="button" data-ob-section="locations" class="w-full flex items-center justify-between px-5 pt-4 pb-1.5 text-[10px] font-semibold uppercase tracking-wider text-[#b9b9c3] hover:text-[#6e6b7b] transition cursor-pointer select-none">
            <span>Locations</span>
            <i class="bi bi-chevron-down ob-chevron transition-transform text-[11px]"></i>
        </button>
        <div data-ob-section-body="locations">
            <a href="{{ route('admin.countries.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.countries.*') }}">
                <i class="bi bi-globe"></i> Countries
            </a>
            <a href="{{ route('admin.states.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.states.*') }}">
                <i class="bi bi-map"></i> States
            </a>
            <a href="{{ route('admin.cities.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.cities.*') }}">
                <i class="bi bi-building"></i> Cities
            </a>
            <a href="{{ route('admin.zipcodes.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm transition {{ $is('admin.zipcodes.*') }}">
                <i class="bi bi-mailbox"></i> Zip Codes
            </a>
        </div>
    </nav>
</aside>
