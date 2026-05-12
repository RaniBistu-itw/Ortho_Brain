<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Health\Facades\Health;

class SystemHealthController extends Controller
{
    public function index()
    {
        $checks = Health::registeredChecks();

        // Run every check fresh and pair it with its check object for labelling.
        $results = $checks->map(fn ($check) => [
            'label'  => $check->getLabel(),
            'name'   => $check->getName(),
            'result' => $check->run(),
        ])->values();

        $checkedAt = now();

        return view('admin.system-health', compact('results', 'checkedAt'));
    }
}
