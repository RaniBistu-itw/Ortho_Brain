<?php

use App\Http\Controllers\Admin\Ajax\LookupController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductSubcategoryController;
use App\Http\Controllers\Admin\ScannerController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\ZipcodeController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// ─── Auth routes (preserved from friend's setup) ──────────
Route::view('/', 'login');
Route::view('/login', 'login')->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::view('/register', 'register');
Route::view('/register/doctor', 'register');
Route::redirect('/admin/login', '/login');

Route::match(['get', 'post'], '/forgot-password', function (\Illuminate\Http\Request $request) {
    $status = false;
    if ($request->isMethod('post')) {
        $status = true;
    }
    return view('forgot-password', ['status' => $status]);
});

// ─── Admin area ───────────────────────────────────────────
Route::middleware(['web', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
        Route::resource('product-subcategories', ProductSubcategoryController::class)
            ->except(['show'])
            ->parameters(['product-subcategories' => 'product_subcategory']);
        Route::resource('scanners', ScannerController::class)->except(['show']);

        Route::resource('countries', CountryController::class)->except(['show']);
        Route::resource('states', StateController::class)->except(['show']);
        Route::resource('cities', CityController::class)->except(['show']);
        Route::resource('zipcodes', ZipcodeController::class)->except(['show']);

        Route::prefix('ajax')->name('ajax.')->group(function () {
            Route::get('states',        [LookupController::class, 'statesByCountry'])->name('states');
            Route::get('cities',        [LookupController::class, 'citiesByState'])->name('cities');
            Route::get('subcategories', [LookupController::class, 'subcategoriesByCategory'])->name('subcategories');
        });
    });
