<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScannerRequest;
use App\Models\Scanner;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index(Request $request)
    {
        $sortable = [
            'name'   => 'name',
            'status' => 'status',
        ];
        $sort = $request->get('sort');
        $sort = $sortable[$sort] ?? 'name';
        $dir  = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $scanners = Scanner::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'    => Scanner::count(),
            'active'   => Scanner::where('status', 'ACTIVE')->count(),
            'inactive' => Scanner::where('status', 'INACTIVE')->count(),
        ];

        return view('admin.scanners.index', compact('scanners', 'stats'));
    }

    public function show(Scanner $scanner)
    {
        return view('admin.scanners.show', compact('scanner'));
    }

    public function destroy(Scanner $scanner)
    {
        $scanner->delete();
        return redirect()->route('admin.scanners.index')->with('success', 'Scanner deleted.');
    }

    // ─── AJAX endpoints for drawer create / edit ────────────────────────

    public function ajaxStore(ScannerRequest $request)
    {
        $scanner = Scanner::create($request->validated());

        return response()->json([
            'ok'      => true,
            'scanner' => $this->presentRow($scanner),
            'message' => 'Scanner created.',
        ]);
    }

    public function ajaxUpdate(ScannerRequest $request, Scanner $scanner)
    {
        $scanner->update($request->validated());

        return response()->json([
            'ok'      => true,
            'scanner' => $this->presentRow($scanner),
            'message' => 'Scanner updated.',
        ]);
    }

    private function presentRow(Scanner $s): array
    {
        return [
            'id'              => $s->id,
            'name'            => $s->name,
            'description'     => (string) ($s->description ?? ''),
            'portal_link'     => (string) ($s->portal_link ?? ''),
            'portal_password' => (string) ($s->portal_password ?? ''),
            'status'          => $s->status,
            'update_url'      => route('admin.scanners.ajax.update', $s),
            'destroy_url'     => route('admin.scanners.destroy', $s),
            'show_url'        => route('admin.scanners.show', $s),
        ];
    }
}
