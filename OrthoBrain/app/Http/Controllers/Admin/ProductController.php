<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductSubcategory;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $products = Product::with(['category', 'subcategory', 'images'])
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
            'stats' => [
                'total'    => Product::count(),
                'active'   => Product::where('status', 'ACTIVE')->count(),
                'inactive' => Product::where('status', 'INACTIVE')->count(),
            ],
        ]);
    }

    public function create()
    {
        $product = new Product(['status' => 'ACTIVE', 'from_step' => 1]);
        $product->setRelation('images', collect());

        return view('admin.products.create', [
            'product'    => $product,
            'categories' => ProductCategory::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->safe()->except(['images', 'remove_image_ids', 'image_order']);

        DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);
            $this->attachUploadedImages($product, $request->file('images', []), 0);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->loadMissing(['category', 'subcategory', 'images']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->loadMissing('images');

        return view('admin.products.edit', [
            'product'    => $product,
            'categories' => ProductCategory::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->safe()->except(['images', 'remove_image_ids', 'image_order']);

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data);

            // 1. Remove explicitly-deleted images (and their files).
            $removeIds = $request->input('remove_image_ids', []);
            if (! empty($removeIds)) {
                $toDelete = $product->images()->whereIn('id', $removeIds)->get();
                foreach ($toDelete as $img) {
                    $this->images->delete($img->s3_key);
                    $img->delete();
                }
            }

            // 2. Apply new sort order for images the admin rearranged on the form.
            //    `image_order` is an array of existing ProductImage ids in their new order.
            $order = $request->input('image_order', []);
            if (! empty($order)) {
                foreach ($order as $idx => $imageId) {
                    if (in_array($imageId, $removeIds)) continue;
                    $product->images()->where('id', $imageId)->update(['sort_order' => $idx]);
                }
            }

            // 3. Append newly uploaded files at the end of the current ordering.
            $startAt = (int) ($product->images()->max('sort_order')) + 1;
            $this->attachUploadedImages($product, $request->file('images', []), $startAt);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete files for every image before the product is removed — FK cascade
        // cleans up the DB rows automatically, but the files on disk are ours to
        // clean. Soft-deleting the product would orphan them otherwise.
        foreach ($product->images as $img) {
            $this->images->delete($img->s3_key);
        }

        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /**
     * Store uploaded files, append ProductImage rows starting at $startAt.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile|null>  $files
     */
    private function attachUploadedImages(Product $product, array $files, int $startAt): void
    {
        $sort = $startAt;
        foreach ($files as $file) {
            $key = $this->images->store($file, 'products');
            if (! $key) continue;

            ProductImage::create([
                'product_id' => $product->id,
                's3_key'     => $key,
                'sort_order' => $sort++,
            ]);
        }
    }
}
