@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<section id="dashboard-overview">
    <div class="row">
        @php
            $tiles = [
                ['label' => 'Products',       'icon' => 'box',      'key' => 'products',      'route' => 'admin.products.index',              'color' => 'primary'],
                ['label' => 'Categories',    'icon' => 'tag',      'key' => 'categories',    'route' => 'admin.product-categories.index',    'color' => 'success'],
                ['label' => 'Sub Categories','icon' => 'tag',      'key' => 'subcategories', 'route' => 'admin.product-subcategories.index', 'color' => 'info'],
                ['label' => 'Scanners',      'icon' => 'cpu',      'key' => 'scanners',      'route' => 'admin.scanners.index',              'color' => 'warning'],
                ['label' => 'Countries',     'icon' => 'globe',    'key' => 'countries',     'route' => 'admin.countries.index',             'color' => 'primary'],
                ['label' => 'States',        'icon' => 'map',      'key' => 'states',        'route' => 'admin.states.index',                'color' => 'success'],
                ['label' => 'Cities',        'icon' => 'map-pin',  'key' => 'cities',        'route' => 'admin.cities.index',                'color' => 'info'],
                ['label' => 'Zip Codes',     'icon' => 'mail',     'key' => 'zipcodes',      'route' => 'admin.zipcodes.index',              'color' => 'warning'],
            ];
        @endphp

        @foreach ($tiles as $tile)
            <div class="col-xl-3 col-md-6 col-sm-6">
                <a href="{{ route($tile['route']) }}" class="text-body text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="fw-bolder mb-0">{{ $stats[$tile['key']] }}</h2>
                                    <p class="card-text mb-0">{{ $tile['label'] }}</p>
                                </div>
                                <div class="avatar bg-light-{{ $tile['color'] }} p-50 m-0">
                                    <div class="avatar-content">
                                        <i data-feather="{{ $tile['icon'] }}" class="font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>
@endsection
