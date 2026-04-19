<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::withCount(['subcategories', 'products'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.product-categories.create', ['category' => new ProductCategory(['status' => 'ACTIVE'])]);
    }

    public function store(ProductCategoryRequest $request)
    {
        ProductCategory::create($request->validated());
        return redirect()->route('admin.product-categories.index')->with('success', 'Category created.');
    }

    public function show(ProductCategory $productCategory)
    {
        $productCategory->loadCount(['subcategories', 'products']);
        return view('admin.product-categories.show', ['category' => $productCategory]);
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('admin.product-categories.edit', ['category' => $productCategory]);
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $productCategory->update($request->validated());
        return redirect()->route('admin.product-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->subcategories()->exists() || $productCategory->products()->exists()) {
            return back()->with('error', 'Cannot delete — this category has sub-categories or products linked to it.');
        }
        $productCategory->delete();
        return redirect()->route('admin.product-categories.index')->with('success', 'Category deleted.');
    }
}
