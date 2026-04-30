<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductSubcategoryRequest;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductSubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $subTable = (new ProductSubcategory)->getTable();
        $catTable = (new \App\Models\ProductCategory)->getTable();

        $sortable = [
            'category'       => "{$catTable}.name",
            'name'           => "{$subTable}.name",
            'products_count' => 'products_count',
            'created_at'     => "{$subTable}.created_at",
        ];
        $order   = $request->query('order') === 'oldest' ? 'oldest' : 'newest';
        $sortKey = $request->get('sort');
        if ($sortKey && isset($sortable[$sortKey])) {
            $sortCol = $sortable[$sortKey];
            $dir     = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        } else {
            $sortKey = null;
            $sortCol = "{$subTable}.created_at";
            $dir     = $order === 'oldest' ? 'asc' : 'desc';
        }

        $query = ProductSubcategory::query()
            ->select("{$subTable}.*")
            ->with('category')
            ->withCount('products');

        if ($sortKey === 'category') {
            $query->leftJoin($catTable, "{$catTable}.id", '=', "{$subTable}.category_id")
                  ->orderBy($sortCol, $dir);
        } else {
            $query->orderBy($sortCol, $dir);
        }

        $subcategories = $query
            ->when($request->filled('category_id'), fn ($q) => $q->where("{$subTable}.category_id", $request->integer('category_id')))
            ->when($request->filled('search'), fn ($q) => $q->where("{$subTable}.name", 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where("{$subTable}.status", $request->input('status') === 'ACTIVE'))
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

    public function updateStatus(Request $request, ProductSubcategory $productSubcategory)
    {
        $data = $request->validate([
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $productSubcategory->update(['status' => $data['status'] === 'ACTIVE']);
        return response()->json([
            'ok'      => true,
            'status'  => $productSubcategory->status ? 'ACTIVE' : 'INACTIVE',
            'message' => 'Status updated.',
        ]);
    }

    public function ajaxBulk(Request $request)
    {
        $payload = $request->input('subcategories', []);

        $validator = Validator::make(['subcategories' => $payload], [
            'subcategories'               => ['required', 'array', 'min:1', 'max:50'],
            'subcategories.*.category_id' => ['required', 'integer', Rule::exists('products_category', 'id')->whereNull('deleted_at')],
            'subcategories.*.name'        => ['required', 'string', 'max:100'],
            'subcategories.*.status'      => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        // Sub-category name is unique per category — guard within-batch + against existing rows.
        $validator->after(function ($v) use ($payload) {
            $seen = [];
            foreach ($payload as $i => $row) {
                $categoryId = $row['category_id'] ?? null;
                $name       = strtolower(trim($row['name'] ?? ''));
                if (! $categoryId || $name === '') continue;

                $key = $categoryId . '|' . $name;
                if (isset($seen[$key])) {
                    $v->errors()->add("subcategories.{$i}.name", 'Duplicate sub-category name for this category in the batch.');
                }
                $seen[$key] = true;

                $exists = ProductSubcategory::where('category_id', $categoryId)
                    ->whereRaw('LOWER(name) = ?', [$name])
                    ->whereNull('deleted_at')
                    ->exists();
                if ($exists) {
                    $v->errors()->add("subcategories.{$i}.name", 'A sub-category with this name already exists for the selected category.');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'ok'     => false,
                'errors' => $validator->errors()->messages(),
            ], 422);
        }

        $created = DB::transaction(function () use ($payload) {
            $rows = [];
            foreach ($payload as $row) {
                $sub = ProductSubcategory::create([
                    'category_id' => $row['category_id'],
                    'name'        => $row['name'],
                    'status'      => $row['status'] === 'ACTIVE',
                ]);
                $sub->loadMissing('category')->loadCount('products');
                $rows[] = $sub;
            }
            return $rows;
        });

        return response()->json([
            'ok'            => true,
            'subcategories' => array_map(fn ($s) => $this->presentRow($s), $created),
            'message'       => count($created) === 1
                ? 'Sub-category created.'
                : count($created) . ' sub-categories created.',
        ]);
    }

    public function ajaxCheckUnique(Request $request)
    {
        $name       = trim((string) $request->input('name', ''));
        $categoryId = $request->integer('category_id') ?: null;
        $ignoreId   = $request->integer('ignore_id') ?: null;

        if ($name === '' || ! $categoryId) {
            return response()->json(['available' => true]);
        }

        $exists = ProductSubcategory::where('category_id', $categoryId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereNull('deleted_at')
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message'   => $exists ? 'A sub-category with this name already exists for the selected category.' : null,
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
            'status_url'     => route('admin.product-subcategories.status', $s),
        ];
    }
}
