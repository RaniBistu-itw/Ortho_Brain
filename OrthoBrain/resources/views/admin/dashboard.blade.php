@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-semibold text-[#5e5873] mb-5">Dashboard</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
    @foreach ([
        ['label' => 'Products',       'icon' => 'box-seam',  'key' => 'products',      'route' => 'admin.products.index',             'color' => '#5bc0de'],
        ['label' => 'Categories',     'icon' => 'tags',      'key' => 'categories',    'route' => 'admin.product-categories.index',   'color' => '#8cc63f'],
        ['label' => 'Sub Categories', 'icon' => 'tag',       'key' => 'subcategories', 'route' => 'admin.product-subcategories.index','color' => '#5bc0de'],
        ['label' => 'Scanners',       'icon' => 'upc-scan',  'key' => 'scanners',      'route' => 'admin.scanners.index',             'color' => '#8cc63f'],
        ['label' => 'Countries',      'icon' => 'globe',     'key' => 'countries',     'route' => 'admin.countries.index',            'color' => '#5bc0de'],
        ['label' => 'States',         'icon' => 'map',       'key' => 'states',        'route' => 'admin.states.index',               'color' => '#8cc63f'],
        ['label' => 'Cities',         'icon' => 'building',  'key' => 'cities',        'route' => 'admin.cities.index',               'color' => '#5bc0de'],
        ['label' => 'Zip Codes',      'icon' => 'mailbox',   'key' => 'zipcodes',      'route' => 'admin.zipcodes.index',             'color' => '#8cc63f'],
    ] as $tile)
        <a href="{{ route($tile['route']) }}"
           class="bg-white border border-[#ebe9f1] rounded-lg p-5 hover:shadow-md transition no-underline">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#6e6b7b] uppercase tracking-wide">{{ $tile['label'] }}</div>
                    <div class="text-2xl font-semibold text-[#5e5873] mt-1">{{ $stats[$tile['key']] }}</div>
                </div>
                <div class="w-11 h-11 flex items-center justify-center rounded-full" style="background-color: {{ $tile['color'] }}15;">
                    <i class="bi bi-{{ $tile['icon'] }} text-xl" style="color: {{ $tile['color'] }};"></i>
                </div>
            </div>
        </a>
    @endforeach
</div>
@endsection
