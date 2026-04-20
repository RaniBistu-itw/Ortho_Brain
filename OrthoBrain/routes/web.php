<?php

use App\Http\Controllers\Admin\Ajax\LookupController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductSubcategoryController;
use App\Http\Controllers\Admin\ScannerController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\ZipcodeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController as DoctorDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Public / shared auth routes ──────────────────────────
Route::view('/', 'login');
Route::view('/login', 'login')->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register',         [\App\Http\Controllers\Auth\RegisterController::class, 'show']);
Route::get('/register/doctor',  [\App\Http\Controllers\Auth\RegisterController::class, 'show']);
Route::post('/register',        [\App\Http\Controllers\Auth\RegisterController::class, 'store']);

Route::redirect('/admin/login', '/login');

Route::match(['get', 'post'], '/forgot-password', function (\Illuminate\Http\Request $request) {
    $status = $request->isMethod('post');
    return view('forgot-password', ['status' => $status]);
});

// ─── Doctor area (authenticated) ──────────────────────────
Route::middleware(['web', 'auth'])
    ->prefix('dev')
    ->name('doctor.')
    ->group(function () {
        Route::get('/cases/list', [DoctorDashboardController::class, 'index'])->name('cases.list');

        Route::get('/profile/index',     [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/index',    [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/profile/settings',  [ProfileController::class, 'settings'])->name('profile.settings');
        Route::post('/profile/settings', [ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('/profile/address/create',        [ProfileController::class, 'addressCreate'])->name('profile.address.create');
        Route::post('/profile/address/store',        [ProfileController::class, 'addressStore'])->name('profile.address.store');
        Route::get('/profile/address/zip-lookup',    [ProfileController::class, 'addressZipLookup'])->name('profile.address.zip-lookup');
        Route::get('/profile/address/{address}',     [ProfileController::class, 'addressShow'])->name('profile.address.show');
        Route::get('/profile/address/{address}/edit',[ProfileController::class, 'addressEdit'])->name('profile.address.edit');
        Route::put('/profile/address/{address}',     [ProfileController::class, 'addressUpdate'])->name('profile.address.update');
    });

// ─── Admin area ───────────────────────────────────────────
Route::middleware(['web', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class);
        Route::resource('product-categories', ProductCategoryController::class);
        Route::resource('product-subcategories', ProductSubcategoryController::class)
            ->parameters(['product-subcategories' => 'product_subcategory']);
        Route::resource('scanners', ScannerController::class);

        Route::resource('countries', CountryController::class);
        Route::resource('states',    StateController::class);
        Route::resource('cities',    CityController::class);
        Route::resource('zipcodes',  ZipcodeController::class);

        Route::prefix('ajax')->name('ajax.')->group(function () {
            Route::get('states',        [LookupController::class, 'statesByCountry'])->name('states');
            Route::get('cities',        [LookupController::class, 'citiesByState'])->name('cities');
            Route::get('subcategories', [LookupController::class, 'subcategoriesByCategory'])->name('subcategories');
        });
    });