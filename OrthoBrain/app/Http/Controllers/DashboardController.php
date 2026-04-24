<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // When adding stats/queries here, scope by currentPractice()->id so each
        // practice gets its own dashboard. Route must be in the active.practice
        // middleware group so currentPractice() is guaranteed non-null.
        return view('dashboard');
    }
}
