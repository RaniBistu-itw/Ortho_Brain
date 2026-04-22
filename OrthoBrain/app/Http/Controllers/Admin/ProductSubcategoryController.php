<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductSubcategoryRequest;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;

class ProductSubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $subcategories = ProductSubcategory::with('category')->withCount('products')
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.product-subcategories.index', [
            'subcategories' => $subcategories,
            'categories'    => ProductCategory::where('status', 'ACTIVE')->orderBy('name')->get(),
            'stats'         => [
                'total'    => ProductSubcategory::count(),
                'active'   => ProductSubcategory::where('status', true)->count(),
                'inactive' => ProductSubcategory::where('status', false)->count(),
            ],
        ]);
    }

    public function show(ProductSubcategory $productSubcategory)
    {
        $productSubcategory->loadMissing('category')->loadCount('products');
        return view('admin.product-subcategories.show', ['subcategory' => $productSubcategory]);
    }

    public function destroy(ProductSubcategory $productSubcategory)
    {
        if ($productSubcategory->products()->exists()) {
            return back()->with('error', 'Cannot delete — this sub-category has products linked to it.');
        }
        $productSubcategory->delete();
        return redirect()->route('admin.product-subcategories.index')->with('success', 'Sub-category deleted.');
    }

    // ─── AJAX endpoints for drawer create / edit ────────────────────────

    public function ajaxStore(ProductSubcategoryRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] === 'ACTIVE';
        $sub = ProductSubcategory::create($data);
        $sub->loadMissing('category')->loadCount('products');

        return response()->json([
            'ok'          => true,
            'subcategory' => $this->presentRow($sub),
            'message'     => 'Sub-category created.',
        ]);
    }

    public function ajaxUpdate(ProductSubcategoryRequest $request, ProductSubcategory $productSubcategory)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] === 'ACTIVE';
        $productSubcategory->update($data);
        $productSubcategory->loadMissing('category')->loadCount('products');

        return response()->json([
            'ok'          => true,
            'subcategory' => $this->presentRow($productSubcategory),
            'message'     => 'Sub-category updated.',
        ]);
    }

    private function presentRow(ProductSubcategory $s): array
    {
        return [
            'id'             => $s->id,
            'category_id'    => $s->category_id,
            'category_name'  => $s->category?->name ?? '—',
            'name'           => $s->name,
            'description'    => (string) ($s->description ?? ''),
            'status'         => $s->status ? 'ACTIVE' : 'INACTIVE',
            'products_count' => (int) ($s->products_count ?? 0),
            'update_url'     => route('admin.product-subcategories.ajax.update', $s),
            'destroy_url'    => route('admin.product-subcategories.destroy', $s),
            'show_url'       => route('admin.product-subcategories.show', $s),
        ];
    }
}
