@extends('layouts.admin')
@section('title', 'Cities')

@section('content')
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Cities</h1>
    <a href="{{ route('admin.cities.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm transition">
        <i class="bi bi-plus-lg"></i> Add City
    </a>
</div>

<div class="bg-white border border-[#ebe9f1] rounded-lg p-5">
    <form id="citiesFilter" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
        <select id="filter_country_id" name="country_id" data-ob-cascade-parent class="js-searchable rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All countries</option>
            @foreach ($countries as $c)<option value="{{ $c->id }}" @selected(request('country_id')==$c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <select id="filter_state_id" name="state_id" class="js-searchable rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All states</option>
            @foreach ($states as $s)<option value="{{ $s->id }}" data-country-id="{{ $s->country_id }}" @selected(request('state_id')==$s->id)>{{ $s->name }}</option>@endforeach
        </select>
        <input type="text" name="search" placeholder="Search city..." value="{{ request('search') }}" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        <select name="status" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All statuses</option>
            <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
        </select>
        <a href="{{ route('admin.cities.index') }}" class="px-4 py-2 text-sm text-center border border-[#d8d6de] text-[#6e6b7b] hover:bg-gray-50 rounded-md transition">Clear</a>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead><tr class="border-b border-[#ebe9f1]">
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Country</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">State</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Name</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Status</th>
                <th class="text-right text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-[#ebe9f1]">
                @forelse ($cities as $city)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-sm">{{ $city->state?->country?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm">{{ $city->state?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm text-[#5e5873]">{{ $city->name }}</td>
                        <td class="px-3 py-3"><span class="px-2 py-1 text-xs font-semibold rounded {{ $city->status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $city->status }}</span></td>
                        <td class="px-3 py-3 text-right">
                            <a href="{{ route('admin.cities.show', $city) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#8cc63f] text-[#8cc63f] hover:bg-[#8cc63f] hover:text-white transition" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.cities.edit', $city) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#5bc0de] text-[#5bc0de] hover:bg-[#5bc0de] hover:text-white transition" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if ($city->zipcodes_count === 0)
                                <form method="POST" action="{{ route('admin.cities.destroy', $city) }}" class="inline js-delete-form" data-confirm="Delete city '{{ $city->name }}'?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded border border-red-400 text-red-500 hover:bg-red-500 hover:text-white transition"><i class="bi bi-trash"></i></button>
                                </form>
                            @else
                                <span class="text-xs text-[#b9b9c3] ml-1">{{ $city->zipcodes_count }} zip(s)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-[#b9b9c3]">No cities found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $cities->links() }}</div>
</div>

@push('scripts')
<script>
obPreloadedCascade({
    parent: '#filter_country_id',
    child: '#filter_state_id',
    parentAttr: 'data-country-id',
});
obAutoFilter('#citiesFilter');
</script>
@endpush
@endsection
