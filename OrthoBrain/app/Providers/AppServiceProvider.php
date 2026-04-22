<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Observers\DoctorObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Doctor::observe(DoctorObserver::class);
    }
}
