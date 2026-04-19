@extends('layouts.admin')
@section('title', 'Scanners')

@section('content')
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Scanners</h1>
    <a href="{{ route('admin.scanners.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm transition">
        <i class="bi bi-plus-lg"></i> Add Scanner
    </a>
</div>

<div class="bg-white border border-[#ebe9f1] rounded-lg p-5">
    <form id="scannersFilter" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <input type="text" name="search" placeholder="Search scanner..." value="{{ request('search') }}" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        <select name="status" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All statuses</option>
            <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
        </select>
        <a href="{{ route('admin.scanners.index') }}" class="px-4 py-2 text-sm text-center border border-[#d8d6de] text-[#6e6b7b] hover:bg-gray-50 rounded-md transition">Clear</a>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead><tr class="border-b border-[#ebe9f1]">
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Name</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Description</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Portal Link</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Status</th>
                <th class="text-right text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-[#ebe9f1]">
                @forelse ($scanners as $sc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-sm font-medium text-[#5e5873]">{{ $sc->name }}</td>
                        <td class="px-3 py-3 text-sm">{{ \Illuminate\Support\Str::limit($sc->description, 60) }}</td>
                        <td class="px-3 py-3 text-sm">
                            @if ($sc->portal_link)<a href="{{ $sc->portal_link }}" target="_blank" class="text-[#5bc0de] hover:underline">{{ \Illuminate\Support\Str::limit($sc->portal_link, 40) }}</a>
                            @else — @endif
                        </td>
                        <td class="px-3 py-3"><span class="px-2 py-1 text-xs font-semibold rounded {{ $sc->status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $sc->status }}</span></td>
                        <td class="px-3 py-3 text-right">
                            <a href="{{ route('admin.scanners.show', $sc) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#8cc63f] text-[#8cc63f] hover:bg-[#8cc63f] hover:text-white transition" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.scanners.edit', $sc) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#5bc0de] text-[#5bc0de] hover:bg-[#5bc0de] hover:text-white transition" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.scanners.destroy', $sc) }}" class="inline js-delete-form" data-confirm="Delete scanner '{{ $sc->name }}'?">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded border border-red-400 text-red-500 hover:bg-red-500 hover:text-white transition"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-[#b9b9c3]">No scanners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $scanners->links() }}</div>
</div>

@push('scripts')
<script>obAutoFilter('#scannersFilter');</script>
@endpush
@endsection
