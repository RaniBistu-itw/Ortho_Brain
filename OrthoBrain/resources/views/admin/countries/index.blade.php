@extends('layouts.admin')
@section('title', 'Countries')

@section('content')
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Countries</h1>
    <a href="{{ route('admin.countries.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm transition">
        <i class="bi bi-plus-lg"></i> Add Country
    </a>
</div>

<div class="bg-white border border-[#ebe9f1] rounded-lg p-5">
    <form id="countriesFilter" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <input type="text" name="search" placeholder="Search name or code..." value="{{ request('search') }}"
               class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] focus:ring-2 focus:ring-[#5bc0de]/20 outline-none">
        <select name="status" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All statuses</option>
            <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
        </select>
        <a href="{{ route('admin.countries.index') }}" class="px-4 py-2 text-sm text-center border border-[#d8d6de] text-[#6e6b7b] hover:bg-gray-50 rounded-md transition">Clear</a>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-[#ebe9f1]">
                    <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase tracking-wider px-3 py-3">Name</th>
                    <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase tracking-wider px-3 py-3">Code</th>
                    <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase tracking-wider px-3 py-3">Phone</th>
                    <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase tracking-wider px-3 py-3">Status</th>
                    <th class="text-right text-xs font-semibold text-[#6e6b7b] uppercase tracking-wider px-3 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe9f1]">
                @forelse ($countries as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-sm text-[#5e5873]">{{ $c->name }}</td>
                        <td class="px-3 py-3 text-sm">{{ $c->country_code }}</td>
                        <td class="px-3 py-3 text-sm">{{ $c->phone_code }}</td>
                        <td class="px-3 py-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded {{ $c->status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $c->status }}</span>
                        </td>
                        <td class="px-3 py-3 text-right">
                            <a href="{{ route('admin.countries.show', $c) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#8cc63f] text-[#8cc63f] hover:bg-[#8cc63f] hover:text-white transition" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.countries.edit', $c) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#5bc0de] text-[#5bc0de] hover:bg-[#5bc0de] hover:text-white transition" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if ($c->states_count === 0)
                                <form method="POST" action="{{ route('admin.countries.destroy', $c) }}" class="inline js-delete-form" data-confirm="Delete country '{{ $c->name }}'?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded border border-red-400 text-red-500 hover:bg-red-500 hover:text-white transition"><i class="bi bi-trash"></i></button>
                                </form>
                            @else
                                <span class="text-xs text-[#b9b9c3] ml-1">{{ $c->states_count }} state(s)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-[#b9b9c3]">No countries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $countries->links() }}</div>
</div>

@push('scripts')
<script>obAutoFilter('#countriesFilter');</script>
@endpush
@endsection
