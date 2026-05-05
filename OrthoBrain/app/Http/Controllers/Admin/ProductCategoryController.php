<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductCategoryRequest;
use App\Models\ProductCategory;
use App\Support\StatusDependencyChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $sortable = [
            'name'                => 'name',
            'subcategories_count' => 'subcategories_count',
            'products_count'      => 'products_count',
            'created_at'          => 'created_at',
        ];
        $order   = $request->query('order') === 'oldest' ? 'oldest' : 'newest';
        $sortKey = $request->get('sort');
        if ($sortKey && isset($sortable[$sortKey])) {
            $sort = $sortable[$sortKey];
            $dir  = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        } else {
            $sort = 'created_at';
            $dir  = $order === 'oldest' ? 'asc' : 'desc';
        }

        $categories = ProductCategory::withCount(['subcategories', 'products'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'    => ProductCategory::count(),
            'active'   => ProductCategory::where('status', 'ACTIVE')->count(),
            'inactive' => ProductCategory::where('status', 'INACTIVE')->count(),
        ];

        return view('admin.product-categories.index', compact('categories', 'stats'));
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

    // ─── AJAX endpoints for inline / drawer / bulk flows ────────────────

    public function ajaxStore(ProductCategoryRequest $request)
    {
        $category = ProductCategory::create($request->validated());
        return response()->json([
            'ok'       => true,
            'category' => $this->presentRow($category->loadCount(['subcategories', 'products'])),
            'message'  => 'Category created.',
        ]);
    }

    public function ajaxUpdate(ProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $productCategory->update($request->validated());
        $productCategory->loadCount(['subcategories', 'products']);
        return response()->json([
            'ok'       => true,
            'category' => $this->presentRow($productCategory),
            'message'  => 'Category updated.',
        ]);
    }

    public function updateStatus(Request $request, ProductCategory $productCategory)
    {
        $data = $request->validate([
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'force'  => ['sometimes', 'boolean'],
        ]);

        if ($data['status'] === 'INACTIVE' && empty($data['force'])) {
            $dependents = StatusDependencyChecker::activeDependents($productCategory);
            if (!empty($dependents)) {
                return response()->json(StatusDependencyChecker::buildResponsePayload($productCategory, $dependents));
            }
        }

        $productCategory->update(['status' => $data['status']]);
        return response()->json([
            'ok'      => true,
            'status'  => $productCategory->status,
            'message' => 'Status updated.',
        ]);
    }

    public function ajaxBulk(Request $request)
    {
        $payload = $request->input('categories', []);

        $validator = Validator::make(['categories' => $payload], [
            'categories'          => ['required', 'array', 'min:1', 'max:50'],
            'categories.*.name'   => ['required', 'string', 'max:255'],
            'categories.*.status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        // Name is unique — guard both within-batch dupes and against existing rows.
        $validator->after(function ($v) use ($payload) {
            $seen = [];
            foreach ($payload as $i => $row) {
                $name = strtolower(trim($row['name'] ?? ''));
                if ($name === '') continue;

                if (isset($seen[$name])) {
                    $v->errors()->add("categories.{$i}.name", 'Duplicate category name in this batch.');
                }
                $seen[$name] = true;

                $exists = ProductCategory::whereRaw('LOWER(name) = ?', [$name])
                    ->whereNull('deleted_at')
                    ->exists();
                if ($exists) {
                    $v->errors()->add("categories.{$i}.name", 'A category with this name already exists.');
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
                $c = ProductCategory::create([
                    'name'   => $row['name'],
                    'status' => $row['status'],
                ]);
                $c->loadCount(['subcategories', 'products']);
                $rows[] = $c;
            }
            return $rows;
        });

        return response()->json([
            'ok'         => true,
            'categories' => array_map(fn ($c) => $this->presentRow($c), $created),
            'message'    => count($created) === 1
                ? 'Category created.'
                : count($created) . ' categories created.',
        ]);
    }

    // Live "is this name taken?" check fired while the user types in the drawer/bulk row.
    // Returns 200 always (taken state in JSON) so a stale request never lights up a generic toast.
    public function ajaxCheckUnique(Request $request)
    {
        $name     = trim((string) $request->input('name', ''));
        $ignoreId = $request->integer('ignore_id') ?: null;

        if ($name === '') {
            return response()->json(['available' => true]);
        }

        $exists = ProductCategory::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereNull('deleted_at')
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message'   => $exists ? 'A category with this name already exists.' : null,
        ]);
    }

    private function presentRow(ProductCategory $c): array
    {
        return [
            'id'                  => $c->id,
            'name'                => $c->name,
            'status'              => $c->status,
            'subcategories_count' => (int) ($c->subcategories_count ?? 0),
            'products_count'      => (int) ($c->products_count ?? 0),
            'edit_url'            => route('admin.product-categories.ajax.update', $c),
            'destroy_url'         => route('admin.product-categories.destroy', $c),
            'show_url'            => route('admin.product-categories.show', $c),
            'status_url'          => route('admin.product-categories.status', $c),
        ];
    }
}
