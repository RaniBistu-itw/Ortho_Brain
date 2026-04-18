<header class="bg-white border-b border-[#ebe9f1] px-6 py-3 flex justify-between items-center">
    <div class="text-sm text-[#6e6b7b]">@yield('page_subtitle', '')</div>
    <div class="flex items-center gap-4">
        <span class="text-sm text-[#5e5873] font-medium">
            {{ auth()->user()->admin?->first_name ?? 'Admin' }} {{ auth()->user()->admin?->last_name ?? '' }}
        </span>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-red-600 border border-red-300 rounded-md hover:bg-red-50 transition">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</header>
