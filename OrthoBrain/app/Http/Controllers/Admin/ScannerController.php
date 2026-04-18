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
        $scanners = Scanner::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.scanners.index', compact('scanners'));
    }

    public function create()
    {
        return view('admin.scanners.create', ['scanner' => new Scanner(['status' => 'ACTIVE'])]);
    }

    public function store(ScannerRequest $request)
    {
        Scanner::create($request->validated());
        return redirect()->route('admin.scanners.index')->with('success', 'Scanner created successfully.');
    }

    public function edit(Scanner $scanner)
    {
        return view('admin.scanners.edit', compact('scanner'));
    }

    public function update(ScannerRequest $request, Scanner $scanner)
    {
        $scanner->update($request->validated());
        return redirect()->route('admin.scanners.index')->with('success', 'Scanner updated successfully.');
    }

    public function destroy(Scanner $scanner)
    {
        $scanner->delete();
        return redirect()->route('admin.scanners.index')->with('success', 'Scanner deleted.');
    }
}
