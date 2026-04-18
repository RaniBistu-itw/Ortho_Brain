@extends('layouts.admin')
@section('title', 'Zip Codes')

@section('content')
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Zip Codes</h1>
    <a href="{{ route('admin.zipcodes.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm transition">
        <i class="bi bi-plus-lg"></i> Add Zip Code
    </a>
</div>

<div class="bg-white border border-[#ebe9f1] rounded-lg p-5">
    <form id="zipcodesFilter" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-4">
        <select id="zip_country_id" name="country_id" data-ob-cascade-parent class="js-searchable rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All countries</option>
            @foreach ($countries as $c)<option value="{{ $c->id }}" @selected(request('country_id')==$c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <select id="zip_state_id" name="state_id" data-ob-cascade-parent class="js-searchable rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All states</option>
            @foreach ($states as $s)<option value="{{ $s->id }}" data-country-id="{{ $s->country_id }}" @selected(request('state_id')==$s->id)>{{ $s->name }}</option>@endforeach
        </select>
        <select id="zip_city_id" name="city_id" class="js-searchable rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All cities</option>
            @foreach ($cities as $city)<option value="{{ $city->id }}" data-state-id="{{ $city->state_id }}" @selected(request('city_id')==$city->id)>{{ $city->name }}</option>@endforeach
        </select>
        <input type="text" name="search" placeholder="Search zip..." value="{{ request('search') }}" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        <select name="status" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All statuses</option>
            <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
        </select>
        <a href="{{ route('admin.zipcodes.index') }}" class="px-4 py-2 text-sm text-center border border-[#d8d6de] text-[#6e6b7b] hover:bg-gray-50 rounded-md transition">Clear</a>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead><tr class="border-b border-[#ebe9f1]">
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Zip Code</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">City</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">State</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Country</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Status</th>
                <th class="text-right text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-[#ebe9f1]">
                @forelse ($zipcodes as $z)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-sm font-medium text-[#5e5873]">{{ $z->code }}</td>
                        <td class="px-3 py-3 text-sm">{{ $z->city?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm">{{ $z->city?->state?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm">{{ $z->city?->state?->country?->name ?? '—' }}</td>
                        <td class="px-3 py-3"><span class="px-2 py-1 text-xs font-semibold rounded {{ $z->status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $z->status }}</span></td>
                        <td class="px-3 py-3 text-right">
                            <a href="{{ route('admin.zipcodes.edit', $z) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#5bc0de] text-[#5bc0de] hover:bg-[#5bc0de] hover:text-white transition"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.zipcodes.destroy', $z) }}" class="inline js-delete-form" data-confirm="Delete zip '{{ $z->code }}'?">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded border border-red-400 text-red-500 hover:bg-red-500 hover:text-white transition"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-6 text-center text-sm text-[#b9b9c3]">No zip codes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $zipcodes->links() }}</div>
</div>

@push('scripts')
<script>
// Country → State: preloaded; picking state backfills country.
obPreloadedCascade({
    parent: '#zip_country_id',
    child: '#zip_state_id',
    parentAttr: 'data-country-id',
});
// State → City: preloaded; picking city backfills state, which in turn
// backfills country via the chain above.
obPreloadedCascade({
    parent: '#zip_state_id',
    child: '#zip_city_id',
    parentAttr: 'data-state-id',
});
obAutoFilter('#zipcodesFilter');
</script>
@endpush
@endsection
