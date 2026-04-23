<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Practice;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    private const ALLOWED_STATUSES = ['ACTIVE', 'INACTIVE'];

    public function index(Request $request)
    {
        $status = strtoupper((string) $request->query('status', ''));
        $status = in_array($status, self::ALLOWED_STATUSES, true) ? $status : null;

        $search = trim((string) $request->query('search', ''));

        $practices = Practice::query()
            ->with([
                'owner:id,first_name,last_name',
                'city:id,name',
                'state:id,name',
                'country:id,name',
            ])
            ->withCount('members')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when(
                $request->filled('country_id'),
                fn ($q) => $q->where('country_id', $request->integer('country_id'))
            )
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('website', 'like', $like)
                      ->orWhere('phone_number', 'like', $like);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Practice::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.practices.index', [
            'practices'     => $practices,
            'countries'     => Country::orderBy('name')->get(['id', 'name']),
            'currentStatus' => $status,
            'statusCounts'  => $statusCounts,
            'totalCount'    => $statusCounts->sum(),
        ]);
    }

    public function show(Practice $practice)
    {
        $practice->load([
            'owner',
            'country',
            'state',
            'city',
            'zipcode',
            'members',
        ]);

        return view('admin.practices.show', [
            'practice' => $practice,
        ]);
    }
}
