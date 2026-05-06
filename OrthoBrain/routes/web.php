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
use App\Http\Controllers\AI\ImageAnalysisController;
use App\Http\Controllers\AI\SmilePreviewController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CaseMediaController;
use App\Http\Controllers\CasePdfController;
use App\Http\Controllers\CasesController;
use App\Http\Controllers\DashboardController as DoctorDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ZipcodeSearchController;
use Illuminate\Support\Facades\Route;

// ─── Public / shared auth routes ──────────────────────────
Route::view('/', 'landing')->name('landing');
Route::view('/login', 'login')->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware(['recaptcha:login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register',         [\App\Http\Controllers\Auth\RegisterController::class, 'show']);
Route::get('/register/doctor',  [\App\Http\Controllers\Auth\RegisterController::class, 'show']);
Route::post('/register',        [\App\Http\Controllers\Auth\RegisterController::class, 'store'])
    ->middleware(['throttle:register', 'recaptcha:register']);

// Email verification (post-registration; no auth required since user
// hasn't logged in yet — they're proving they own the inbox).
Route::get('/verify-email',                  [EmailVerificationController::class, 'showForm'])->name('verify-email.show');
Route::post('/verify-email',                 [EmailVerificationController::class, 'submitOtp'])
    ->middleware('throttle:verify-otp')
    ->name('verify-email.submit');
Route::get('/verify-email/link/{user}',      [EmailVerificationController::class, 'verifyLink'])
    ->middleware('signed')
    ->name('verify-email.link');
Route::post('/verify-email/resend',          [EmailVerificationController::class, 'resend'])
    ->middleware('throttle:resend-verification')
    ->name('verify-email.resend');

Route::get('/contact-support',         [\App\Http\Controllers\SupportController::class, 'show'])->name('support.show');
Route::post('/contact-support',        [\App\Http\Controllers\SupportController::class, 'store'])->name('support.send');
Route::get('/contact-support/thanks',  [\App\Http\Controllers\SupportController::class, 'thanks'])->name('support.thanks');

Route::redirect('/admin/login', '/login');

Route::get('/forgot-password',  [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])
    ->middleware(['throttle:password-reset', 'recaptcha:reset']);
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

        // Doctor dashboard (landing page after login). Behind active.practice so a
        // paused-only doctor lands on the pending notice instead of a dashboard
        // they can't actually use.
        Route::middleware(['active.practice'])->group(function () {
            Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
        });

        // Lightweight ZIP/postal lookup used by the case-wizard shipping
        // address combobox. Replaces the previous practice of inlining
        // ~600 zipcodes (102 KB) into every case-edit page render.
        Route::get('/zipcodes/search', [ZipcodeSearchController::class, 'search'])
            ->name('zipcodes.search');

        // Notifications — bell dropdown + "View All" modal actions
        Route::get('/notifications',                  [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all',        [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/notifications',               [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/notifications/{notification}',    [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::delete('/notifications/pending/{link}',    [NotificationController::class, 'cancelPending'])->name('notifications.pending.cancel');

        // Legacy route — kept as a redirect to the real list.
        Route::redirect('/cases/list', '/dev/cases')->name('cases.list');

        // Help / FAQ — plain static page.
        Route::view('/help', 'doctor.help')->name('help.index');

        // Cases require an approved active practice — middleware redirects to pending page if none.
        // AI vision endpoints live inside this group too: they operate on cases and need
        // currentPractice() to be non-null for the per-practice authorization filter.
        Route::middleware(['active.practice'])->group(function () {
            Route::get('/cases',                    [CasesController::class, 'index'])->name('cases.index');
            Route::get('/cases/create',             [CasesController::class, 'create'])->name('cases.create');
            Route::post('/cases',                   [CasesController::class, 'store'])->name('cases.store');
            Route::get('/cases/{case}/edit',        [CasesController::class, 'edit'])->name('cases.edit');
            Route::post('/cases/{case}/submit',     [CasesController::class, 'submit'])->name('cases.submit');
            Route::post('/cases/{case}/shipping',   [CasesController::class, 'saveShipping'])->name('cases.shipping.save');
            Route::post('/cases/{case}/impressions',[CasesController::class, 'saveImpressions'])->name('cases.impressions.save');
            Route::post('/cases/{case}/additional', [CasesController::class, 'saveAdditionalInfo'])->name('cases.additional.save');
            Route::post('/cases/{case}/prescription',[PrescriptionController::class, 'update'])->name('cases.prescription.update');
            Route::post('/cases/{case}/patient',     [PatientController::class, 'upsert'])->name('cases.patient.upsert');
            Route::match(['get', 'post'], '/cases/{case}/export.pdf', [CasePdfController::class, 'export'])->name('cases.export.pdf');

            // Patient autocomplete — typing in the search field on the case
            // wizard. Returns up to 8 matches, scoped to the doctor's roster
            // for the current practice.
            Route::get('/patients/search', [PatientController::class, 'search'])
                ->name('patients.search');

            // Case media (photographs / x-rays) — server-side persistence so drafts
            // survive across browsers and admins can see uploaded files.
            // Uses POST (not DELETE) for the destroy endpoint to dodge the PHP 8.3
            // request_parse_body() fatal on DELETE requests — see
            // project_php_version_gotcha.md memory + Docs/api.md "Gotchas" §7.
            Route::post('/cases/{case}/media/upload', [CaseMediaController::class, 'upload'])
                ->middleware('throttle:60,1')
                ->name('cases.media.upload');
            Route::post('/cases/{case}/media/{section}/{tile_id}/destroy', [CaseMediaController::class, 'destroy'])
                ->where('section', 'photograph|xray')
                ->name('cases.media.destroy');
            Route::post('/cases/{case}/media/reorder', [CaseMediaController::class, 'reorder'])
                ->middleware('throttle:60,1')
                ->name('cases.media.reorder');

            // AI vision — photo QC + Perfect Smile Plan generation. Throttled per user
            // to keep accidental retry loops from blowing through the free-tier quota.
            Route::post('/cases/{case}/photos/classify',      [ImageAnalysisController::class, 'classify'])
                ->middleware('throttle:30,60')
                ->name('cases.photos.classify');
            Route::post('/cases/{case}/smile-plan/generate',  [ImageAnalysisController::class, 'smilePlan'])
                ->middleware('throttle:10,60')
                ->name('cases.smile-plan.generate');

            // AI image-edit — before/after smile visualisation. Rate-limited to protect free-tier quota.
            Route::post('/cases/{case}/smile-preview/generate',
                [SmilePreviewController::class, 'generate']
            )->middleware('throttle:5,1')->name('cases.smile-preview.generate');
        });

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
        Route::post('/profile/address/{address}/set-default', [ProfileController::class, 'addressSetDefault'])->name('profile.address.set-default');
    });

// ─── Admin area ───────────────────────────────────────────
Route::middleware(['web', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Admin notifications — bell dropdown "View All" feed (today's activity)
        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])
            ->name('notifications.index');

        // Admin profile (account details + avatar)
        Route::get('/profile',            [AdminProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile',           [AdminProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/photo',     [AdminProfileController::class, 'uploadAvatar'])->name('profile.photo');
        Route::delete('/profile/photo',   [AdminProfileController::class, 'deleteAvatar'])->name('profile.photo.delete');

        Route::post('products/{product}/status', [ProductController::class, 'updateStatus'])
            ->name('products.status');
        Route::resource('products', ProductController::class);

        // Product Categories — AJAX endpoints for inline / drawer / bulk flows
        // (must be registered BEFORE the resource so `/ajax` is not captured by {product_category})
        Route::post('product-categories/ajax', [ProductCategoryController::class, 'ajaxStore'])
            ->name('product-categories.ajax.store');
        Route::post('product-categories/ajax/bulk', [ProductCategoryController::class, 'ajaxBulk'])
            ->name('product-categories.ajax.bulk');
        Route::post('product-categories/ajax/check-unique', [ProductCategoryController::class, 'ajaxCheckUnique'])
            ->name('product-categories.ajax.check-unique');
        Route::put('product-categories/ajax/{productCategory}', [ProductCategoryController::class, 'ajaxUpdate'])
            ->name('product-categories.ajax.update');
        Route::post('product-categories/{productCategory}/status', [ProductCategoryController::class, 'updateStatus'])
            ->name('product-categories.status');
        Route::resource('product-categories', ProductCategoryController::class);

        // Product Subcategories — AJAX endpoints for drawer create / edit flow
        // (must be registered BEFORE the resource so `/ajax` is not captured by {product_subcategory})
        Route::post('product-subcategories/ajax', [ProductSubcategoryController::class, 'ajaxStore'])
            ->name('product-subcategories.ajax.store');
        Route::post('product-subcategories/ajax/bulk', [ProductSubcategoryController::class, 'ajaxBulk'])
            ->name('product-subcategories.ajax.bulk');
        Route::post('product-subcategories/ajax/check-unique', [ProductSubcategoryController::class, 'ajaxCheckUnique'])
            ->name('product-subcategories.ajax.check-unique');
        Route::put('product-subcategories/ajax/{product_subcategory}', [ProductSubcategoryController::class, 'ajaxUpdate'])
            ->name('product-subcategories.ajax.update');
        Route::post('product-subcategories/{product_subcategory}/status', [ProductSubcategoryController::class, 'updateStatus'])
            ->name('product-subcategories.status');
        Route::resource('product-subcategories', ProductSubcategoryController::class)
            ->only(['index', 'show', 'destroy'])
            ->parameters(['product-subcategories' => 'product_subcategory']);
        // Scanners — AJAX endpoints for drawer create / edit flow
        // (must be registered BEFORE the resource so `/ajax` is not captured by {scanner})
        Route::post('scanners/ajax', [ScannerController::class, 'ajaxStore'])
            ->name('scanners.ajax.store');
        Route::put('scanners/ajax/{scanner}', [ScannerController::class, 'ajaxUpdate'])
            ->name('scanners.ajax.update');
        Route::post('scanners/{scanner}/status', [ScannerController::class, 'updateStatus'])
            ->name('scanners.status');
        Route::resource('scanners', ScannerController::class)
            ->only(['index', 'show', 'destroy']);

        // Admin Cases — full visibility + edit (owner: Devansh)
        Route::get('/cases',                          [AdminCasesController::class, 'index'])->name('cases.index');
        Route::get('/cases/{case}/edit',              [AdminCasesController::class, 'edit'])->name('cases.edit');
        Route::post('/cases/{case}/status',           [AdminCasesController::class, 'updateStatus'])->name('cases.status');
        Route::post('/cases/{case}/prescription',     [PrescriptionController::class, 'update'])->name('cases.prescription.update');
        Route::match(['get', 'post'], '/cases/{case}/export.pdf', [CasePdfController::class, 'export'])->name('cases.export.pdf');

        // AI vision — admins can trigger classification / smile plan on any case
        Route::post('/cases/{case}/photos/classify',      [ImageAnalysisController::class, 'classify'])->name('cases.photos.classify');
        Route::post('/cases/{case}/smile-plan/generate',  [ImageAnalysisController::class, 'smilePlan'])->name('cases.smile-plan.generate');
        Route::post('/cases/{case}/smile-preview/generate',
            [SmilePreviewController::class, 'generate']
        )->middleware('throttle:5,1')->name('cases.smile-preview.generate');

        // Admin Doctors — review + approve/reject/suspend (PR #17)
        Route::resource('doctors', AdminDoctorController::class)
            ->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
        Route::post('doctors/{doctor}/approve',    [AdminDoctorController::class, 'approve'])->name('doctors.approve');
        Route::post('doctors/{doctor}/reject',     [AdminDoctorController::class, 'reject'])->name('doctors.reject');
        Route::post('doctors/{doctor}/suspend',    [AdminDoctorController::class, 'suspend'])->name('doctors.suspend');
        Route::post('doctors/{doctor}/reactivate', [AdminDoctorController::class, 'reactivate'])->name('doctors.reactivate');

        // Per-doctor-practice-link approval (legacy — used by admin doctor show page).
        Route::post('doctors/{doctor}/practices/{link}/approve', [\App\Http\Controllers\Admin\DoctorPracticeController::class, 'approve'])->name('doctors.practices.approve');
        Route::post('doctors/{doctor}/practices/{link}/reject',  [\App\Http\Controllers\Admin\DoctorPracticeController::class, 'reject'])->name('doctors.practices.reject');

        // Unified pivot-status endpoint — approve / reject / suspend / reactivate.
        Route::post('doctors/{doctor}/practices/{link}/status', [\App\Http\Controllers\Admin\DoctorPracticeController::class, 'updatePivotStatus'])->name('doctors.practices.status');

        // Admin Practices — list + detail + edit (no add/destroy)
        Route::post('practices/{practice}/status', [AdminPracticeController::class, 'updateStatus'])
            ->name('practices.status');
        Route::post('practices/{practice}/doctors/bulk', [AdminPracticeController::class, 'bulkPendingAction'])
            ->name('practices.doctors.bulk');
        Route::resource('practices', AdminPracticeController::class)
            ->only(['index', 'show', 'edit', 'update']);

        // Countries — AJAX drawer endpoints (must precede resource)
        Route::post('countries/ajax', [CountryController::class, 'ajaxStore'])
            ->name('countries.ajax.store');
        Route::post('countries/ajax/bulk', [CountryController::class, 'ajaxBulk'])
            ->name('countries.ajax.bulk');
        Route::post('countries/ajax/check-unique', [CountryController::class, 'ajaxCheckUnique'])
            ->name('countries.ajax.check-unique');
        Route::put('countries/ajax/{country}', [CountryController::class, 'ajaxUpdate'])
            ->name('countries.ajax.update');
        Route::post('countries/{country}/status', [CountryController::class, 'updateStatus'])
            ->name('countries.status');
        Route::resource('countries', CountryController::class)
            ->only(['index', 'show', 'destroy']);

        // States — AJAX drawer endpoints
        Route::post('states/ajax', [StateController::class, 'ajaxStore'])
            ->name('states.ajax.store');
        Route::post('states/ajax/bulk', [StateController::class, 'ajaxBulk'])
            ->name('states.ajax.bulk');
        Route::post('states/ajax/check-unique', [StateController::class, 'ajaxCheckUnique'])
            ->name('states.ajax.check-unique');
        Route::put('states/ajax/{state}', [StateController::class, 'ajaxUpdate'])
            ->name('states.ajax.update');
        Route::post('states/{state}/status', [StateController::class, 'updateStatus'])
            ->name('states.status');
        Route::resource('states', StateController::class)
            ->only(['index', 'show', 'destroy']);

        // Cities — AJAX drawer endpoints
        Route::post('cities/ajax', [CityController::class, 'ajaxStore'])
            ->name('cities.ajax.store');
        Route::post('cities/ajax/bulk', [CityController::class, 'ajaxBulk'])
            ->name('cities.ajax.bulk');
        Route::post('cities/ajax/check-unique', [CityController::class, 'ajaxCheckUnique'])
            ->name('cities.ajax.check-unique');
        Route::put('cities/ajax/{city}', [CityController::class, 'ajaxUpdate'])
            ->name('cities.ajax.update');
        Route::post('cities/{city}/status', [CityController::class, 'updateStatus'])
            ->name('cities.status');
        Route::resource('cities', CityController::class)
            ->only(['index', 'show', 'destroy']);

        // Zipcodes — AJAX drawer endpoints
        Route::post('zipcodes/ajax', [ZipcodeController::class, 'ajaxStore'])
            ->name('zipcodes.ajax.store');
        Route::post('zipcodes/ajax/check-unique', [ZipcodeController::class, 'ajaxCheckUnique'])
            ->name('zipcodes.ajax.check-unique');
        Route::put('zipcodes/ajax/{zipcode}', [ZipcodeController::class, 'ajaxUpdate'])
            ->name('zipcodes.ajax.update');
        Route::post('zipcodes/{zipcode}/status', [ZipcodeController::class, 'updateStatus'])
            ->name('zipcodes.status');
        Route::resource('zipcodes', ZipcodeController::class)
            ->only(['index', 'show', 'destroy']);

        Route::prefix('ajax')->name('ajax.')->group(function () {
            Route::get('states',         [LookupController::class, 'statesByCountry'])->name('states');
            Route::get('cities',         [LookupController::class, 'citiesByState'])->name('cities');
            Route::get('subcategories',  [LookupController::class, 'subcategoriesByCategory'])->name('subcategories');
            Route::get('doctors/search', [LookupController::class, 'doctorSearch'])->name('doctors-search');
            Route::get('patients/search', [LookupController::class, 'patientSearch'])->name('patients-search');
        });
    });