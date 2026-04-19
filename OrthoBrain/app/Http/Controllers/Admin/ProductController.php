<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ImageUploadService $images)
    {
    }

    public function index(Request $request)
    {
        // If only the subcategory is picked, infer its parent category so the
        // filter UI stays consistent ("child always knows its parent").
        if ($request->filled('subcategory_id') && ! $request->filled('category_id')) {
            $catId = ProductSubcategory::whereKey($request->integer('subcategory_id'))
                ->value('category_id');
            if ($catId) {
                $request->merge(['category_id' => $catId]);
            }
        }

        $products = Product::with(['category', 'subcategory'])
            ->when($request->filled('category_id'),
                fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('subcategory_id'),
                fn ($q) => $q->where('subcategory_id', $request->integer('subcategory_id')))
            ->when($request->filled('status'),
                fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'),
                fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products'     => $products,
            'categories'   => ProductCategory::where('status', 'ACTIVE')->orderBy('name')->get(),
            'subcategories' => ProductSubcategory::where('status', true)->orderBy('name')->get(['id', 'name', 'category_id']),
        ]);
    }

    public function create()
    {
        return view('admin.products.create', [
            'product'    => new Product(['status' => 'ACTIVE', 'from_step' => 1]),
            'categories' => ProductCategory::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->safe()->except(['image']);
        $data['image_s3_key'] = $this->images->store($request->file('image'), 'products');

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->loadMissing(['category', 'subcategory']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product'    => $product,
            'categories' => ProductCategory::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->safe()->except(['image']);

        if ($request->hasFile('image')) {
            $this->images->delete($product->image_s3_key);
            $data['image_s3_key'] = $this->images->store($request->file('image'), 'products');
        }

        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->images->delete($product->image_s3_key);
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}