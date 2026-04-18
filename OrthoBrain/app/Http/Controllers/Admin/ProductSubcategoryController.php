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
        ]);
    }

    public function create()
    {
        return view('admin.product-subcategories.create', [
            'subcategory' => new ProductSubcategory(['status' => true]),
            'categories'  => ProductCategory::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function store(ProductSubcategoryRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] === 'ACTIVE';
        ProductSubcategory::create($data);
        return redirect()->route('admin.product-subcategories.index')->with('success', 'Sub-category created.');
    }

    public function edit(ProductSubcategory $productSubcategory)
    {
        return view('admin.product-subcategories.edit', [
            'subcategory' => $productSubcategory,
            'categories'  => ProductCategory::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function update(ProductSubcategoryRequest $request, ProductSubcategory $productSubcategory)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] === 'ACTIVE';
        $productSubcategory->update($data);
        return redirect()->route('admin.product-subcategories.index')->with('success', 'Sub-category updated.');
    }

    public function destroy(ProductSubcategory $productSubcategory)
    {
        if ($productSubcategory->products()->exists()) {
            return back()->with('error', 'Cannot delete — this sub-category has products linked to it.');
        }
        $productSubcategory->delete();
        return redirect()->route('admin.product-subcategories.index')->with('success', 'Sub-category deleted.');
    }
}
