<?php

use App\Http\Controllers\Admin\Ajax\LookupController;
use App\Http\Controllers\Admin\CasesController as AdminCasesController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\PracticeController as AdminPracticeController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductSubcategoryController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ScannerController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\ZipcodeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CasesController;
use App\Http\Controllers\DashboardController as DoctorDashboardController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\PrescriptionController;
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

Route::get('/forgot-password',  [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->middleware('throttle:5,1');
Route::get('/reset-password/{token}',  [ForgotPasswordController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password',         [ForgotPasswordController::class, 'reset'])->name('password.update');

// Public practice-search (used by the register page autocomplete on Practice Name)
Route::get('/practice-search', [PracticeController::class, 'search'])->name('practice.search');

// ─── Doctor area (authenticated) ──────────────────────────
Route::middleware(['web', 'auth'])
    ->prefix('dev')
    ->name('doctor.')
    ->group(function () {
        // Multi-practice support: switcher, pending-page, add-practice request
        Route::post('/practice/switch',         [\App\Http\Controllers\PracticeMembershipController::class, 'switch'])->name('practice.switch');
        Route::get('/practices/pending',        [\App\Http\Controllers\PracticeMembershipController::class, 'pending'])->name('practices.pending');
        Route::post('/practices/request',       [\App\Http\Controllers\PracticeMembershipController::class, 'request'])->name('practices.request');
        Route::post('/practices/{link}/cancel', [\App\Http\Controllers\PracticeMembershipController::class, 'cancel'])->name('practices.cancel');
        Route::post('/practices/{link}/leave',  [\App\Http\Controllers\PracticeMembershipController::class, 'leave'])->name('practices.leave');
        Route::post('/practices/{link}/primary',[\App\Http\Controllers\PracticeMembershipController::class, 'makePrimary'])->name('practices.primary');

        // Legacy route — login still redirects here. Keep as a redirect to the real list.
        Route::redirect('/cases/list', '/dev/cases')->name('cases.list');

        // Add Case + Case List feature (owner: Devansh)
        Route::get('/cases',                    [CasesController::class, 'index'])->name('cases.index');
        Route::get('/cases/create',             [CasesController::class, 'create'])->name('cases.create');
        Route::post('/cases',                   [CasesController::class, 'store'])->name('cases.store');
        Route::get('/cases/{case}/edit',        [CasesController::class, 'edit'])->name('cases.edit');
        Route::post('/cases/{case}/submit',     [CasesController::class, 'submit'])->name('cases.submit');
        Route::post('/cases/{case}/prescription',[PrescriptionController::class, 'update'])->name('cases.prescription.update');

        Route::get('/profile/index',     [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/index',    [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/additional', [ProfileController::class, 'updateAdditional'])->name('profile.additional.update');

        Route::get('/profile/settings',  [ProfileController::class, 'settings'])->name('profile.settings');
        Route::post('/profile/settings', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Doctor avatar & practice logo uploads (local public storage)
        Route::post('/profile/photo',   [ProfileController::class, 'uploadAvatar'])->name('profile.photo');
        Route::delete('/profile/photo', [ProfileController::class, 'deleteAvatar'])->name('profile.photo.delete');
        Route::post('/practice/photo',   [ProfileController::class, 'uploadPracticeLogo'])->name('practice.photo');
        Route::delete('/practice/photo', [ProfileController::class, 'deletePracticeLogo'])->name('practice.photo.delete');

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

        // Admin profile (account details + avatar)
        Route::get('/profile',            [AdminProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile',           [AdminProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/photo',     [AdminProfileController::class, 'uploadAvatar'])->name('profile.photo');
        Route::delete('/profile/photo',   [AdminProfileController::class, 'deleteAvatar'])->name('profile.photo.delete');

        Route::resource('products', ProductController::class);

        // Product Categories — AJAX endpoints for inline / drawer / bulk flows
        // (must be registered BEFORE the resource so `/ajax` is not captured by {product_category})
        Route::post('product-categories/ajax', [ProductCategoryController::class, 'ajaxStore'])
            ->name('product-categories.ajax.store');
        Route::post('product-categories/ajax/bulk', [ProductCategoryController::class, 'ajaxBulk'])
            ->name('product-categories.ajax.bulk');
        Route::put('product-categories/ajax/{productCategory}', [ProductCategoryController::class, 'ajaxUpdate'])
            ->name('product-categories.ajax.update');
        Route::resource('product-categories', ProductCategoryController::class);

        // Product Subcategories — AJAX endpoints for drawer create / edit flow
        // (must be registered BEFORE the resource so `/ajax` is not captured by {product_subcategory})
        Route::post('product-subcategories/ajax', [ProductSubcategoryController::class, 'ajaxStore'])
            ->name('product-subcategories.ajax.store');
        Route::put('product-subcategories/ajax/{product_subcategory}', [ProductSubcategoryController::class, 'ajaxUpdate'])
            ->name('product-subcategories.ajax.update');
        Route::resource('product-subcategories', ProductSubcategoryController::class)
            ->only(['index', 'show', 'destroy'])
            ->parameters(['product-subcategories' => 'product_subcategory']);
        // Scanners — AJAX endpoints for drawer create / edit flow
        // (must be registered BEFORE the resource so `/ajax` is not captured by {scanner})
        Route::post('scanners/ajax', [ScannerController::class, 'ajaxStore'])
            ->name('scanners.ajax.store');
        Route::put('scanners/ajax/{scanner}', [ScannerController::class, 'ajaxUpdate'])
            ->name('scanners.ajax.update');
        Route::resource('scanners', ScannerController::class)
            ->only(['index', 'show', 'destroy']);

        // Admin Cases — full visibility + edit (owner: Devansh)
        Route::get('/cases',                          [AdminCasesController::class, 'index'])->name('cases.index');
        Route::get('/cases/{case}/edit',              [AdminCasesController::class, 'edit'])->name('cases.edit');
        Route::post('/cases/{case}/status',           [AdminCasesController::class, 'updateStatus'])->name('cases.status');
        Route::post('/cases/{case}/prescription',     [PrescriptionController::class, 'update'])->name('cases.prescription.update');

        // Admin Doctors — review + approve/reject/suspend (PR #17)
        Route::resource('doctors', AdminDoctorController::class)
            ->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
        Route::post('doctors/{doctor}/approve',    [AdminDoctorController::class, 'approve'])->name('doctors.approve');
        Route::post('doctors/{doctor}/reject',     [AdminDoctorController::class, 'reject'])->name('doctors.reject');
        Route::post('doctors/{doctor}/suspend',    [AdminDoctorController::class, 'suspend'])->name('doctors.suspend');
        Route::post('doctors/{doctor}/reactivate', [AdminDoctorController::class, 'reactivate'])->name('doctors.reactivate');

        // Per-doctor-practice-link approval (rendered inside the doctor show page)
        Route::post('doctors/{doctor}/practices/{link}/approve', [\App\Http\Controllers\Admin\DoctorPracticeController::class, 'approve'])->name('doctors.practices.approve');
        Route::post('doctors/{doctor}/practices/{link}/reject',  [\App\Http\Controllers\Admin\DoctorPracticeController::class, 'reject'])->name('doctors.practices.reject');

        // Admin Practices — read-only list + detail (no add)
        Route::resource('practices', AdminPracticeController::class)
            ->only(['index', 'show']);

        // Countries — AJAX drawer endpoints (must precede resource)
        Route::post('countries/ajax', [CountryController::class, 'ajaxStore'])
            ->name('countries.ajax.store');
        Route::put('countries/ajax/{country}', [CountryController::class, 'ajaxUpdate'])
            ->name('countries.ajax.update');
        Route::resource('countries', CountryController::class)
            ->only(['index', 'show', 'destroy']);

        // States — AJAX drawer endpoints
        Route::post('states/ajax', [StateController::class, 'ajaxStore'])
            ->name('states.ajax.store');
        Route::put('states/ajax/{state}', [StateController::class, 'ajaxUpdate'])
            ->name('states.ajax.update');
        Route::resource('states', StateController::class)
            ->only(['index', 'show', 'destroy']);

        // Cities — AJAX drawer endpoints
        Route::post('cities/ajax', [CityController::class, 'ajaxStore'])
            ->name('cities.ajax.store');
        Route::put('cities/ajax/{city}', [CityController::class, 'ajaxUpdate'])
            ->name('cities.ajax.update');
        Route::resource('cities', CityController::class)
            ->only(['index', 'show', 'destroy']);

        // Zipcodes — AJAX drawer endpoints
        Route::post('zipcodes/ajax', [ZipcodeController::class, 'ajaxStore'])
            ->name('zipcodes.ajax.store');
        Route::put('zipcodes/ajax/{zipcode}', [ZipcodeController::class, 'ajaxUpdate'])
            ->name('zipcodes.ajax.update');
        Route::resource('zipcodes', ZipcodeController::class)
            ->only(['index', 'show', 'destroy']);

        Route::prefix('ajax')->name('ajax.')->group(function () {
            Route::get('states',        [LookupController::class, 'statesByCountry'])->name('states');
            Route::get('cities',        [LookupController::class, 'citiesByState'])->name('cities');
            Route::get('subcategories', [LookupController::class, 'subcategoriesByCategory'])->name('subcategories');
        });
    });