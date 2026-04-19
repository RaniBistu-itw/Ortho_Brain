@extends('layouts.admin')
@section('title', 'States')

@section('content')
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">States</h1>
    <a href="{{ route('admin.states.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm transition">
        <i class="bi bi-plus-lg"></i> Add State
    </a>
</div>

<div class="bg-white border border-[#ebe9f1] rounded-lg p-5">
    <form id="statesFilter" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
        <select name="country_id" class="js-searchable rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All countries</option>
            @foreach ($countries as $c)<option value="{{ $c->id }}" @selected(request('country_id')==$c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}"
               class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        <select name="status" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All statuses</option>
            <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
        </select>
        <a href="{{ route('admin.states.index') }}" class="px-4 py-2 text-sm text-center border border-[#d8d6de] text-[#6e6b7b] hover:bg-gray-50 rounded-md transition">Clear</a>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-[#ebe9f1]">
                    <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Country</th>
                    <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Name</th>
                    <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Code</th>
                    <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Status</th>
                    <th class="text-right text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe9f1]">
                @forelse ($states as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-sm">{{ $s->country->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm text-[#5e5873]">{{ $s->name }}</td>
                        <td class="px-3 py-3 text-sm">{{ $s->state_code }}</td>
                        <td class="px-3 py-3"><span class="px-2 py-1 text-xs font-semibold rounded {{ $s->status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $s->status }}</span></td>
                        <td class="px-3 py-3 text-right">
                            <a href="{{ route('admin.states.show', $s) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#8cc63f] text-[#8cc63f] hover:bg-[#8cc63f] hover:text-white transition" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.states.edit', $s) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#5bc0de] text-[#5bc0de] hover:bg-[#5bc0de] hover:text-white transition" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if ($s->cities_count === 0)
                                <form method="POST" action="{{ route('admin.states.destroy', $s) }}" class="inline js-delete-form" data-confirm="Delete state '{{ $s->name }}'?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded border border-red-400 text-red-500 hover:bg-red-500 hover:text-white transition"><i class="bi bi-trash"></i></button>
                                </form>
                            @else
                                <span class="text-xs text-[#b9b9c3] ml-1">{{ $s->cities_count }} cit{{ $s->cities_count === 1 ? 'y' : 'ies' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-[#b9b9c3]">No states found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $states->links() }}</div>
</div>

@push('scripts')
<script>obAutoFilter('#statesFilter');</script>
@endpush
@endsection
