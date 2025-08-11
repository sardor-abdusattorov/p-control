<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\ProductIndexRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->can('manage products')) {
                return redirect()->route('dashboard')->with('error', __('app.deny_access'));
            }
            return $next($request);
        });
    }

    public function index(ProductIndexRequest $request)
    {
        $products = Product::query()->with('user', 'category');

        if ($request->filled(['field', 'order'])) {
            $products->orderBy($request->field, $request->order);
        }

        if ($request->filled('title')) {
            $products->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('inventory_number')) {
            $products->where('inventory_number', 'like', '%' . $request->inventory_number . '%');
        }

        if ($request->filled('category_id')) {
            $products->where('category_id', $request->category_id);
        }

        if ($request->filled('user_id')) {
            $products->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $products->whereIn('status', (array) $request->status);
        }

        $statuses = Product::getStatuses();
        $users = User::where('status', 1)->get();
        $categories = ProductCategory::orderBy('sort', 'asc')->get();

        $perPage = (int) $request->get('perPage', 10);

        return Inertia::render('Product/Index', [
            'title'       => __('app.label.products'),
            'filters'     => $request->only(['search', 'field', 'order']),
            'perPage'     => $perPage,
            'products'      => $products->paginate($perPage),
            'statuses'     => $statuses,
            'categories'   => $categories,
            'users'        => $users,
            'breadcrumbs' => [
                ['label' => __('app.label.products'), 'href' => route('products.index')],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('status', 1)->get();
        $categories = ProductCategory::orderBy('sort', 'asc')->get();
        $brands = ProductBrand::orderBy('sort', 'asc')->get();
        return Inertia::render('Product/Create', [
            'title'         => __('app.label.products'),
            'categories'    => $categories,
            'brands'        => $brands,
            'users'         => $users,
            'breadcrumbs' => [
                ['label' => __('app.label.products'), 'href' => route('products.index')],
                ['label' => __('app.label.create')]
            ]
        ]);
    }


    public function store(ProductStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $product = Product::create([
                'title' => $request->title,
                'description' => $request->description,
                'serial_number' => $request->serial_number,
                'inventory_number' => $request->inventory_number,
                'parameters' => $request->parameters,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'user_id' => $request->user_id,
                'sort' => $request->sort ?? 1,
                'status' => $request->boolean('status'),
            ]);

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', __('app.label.created_successfully', ['name' => $product->title]));
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->with('error', __('app.label.created_error', ['name' => __('app.label.products')]) . ' ' . $th->getMessage());
        }
    }

    public function show(Product $product)
    {
        return Inertia::render('Product/Show', [
            'product' => $product->load('category', 'brand', 'user'),
            'title' => __('app.label.products'),
            'breadcrumbs' => [
                ['label' => __('app.label.products'), 'href' => route('products.index')],
                ['label' => $product->title]
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $users = User::where('status', 1)->get();
        $categories = ProductCategory::orderBy('sort', 'asc')->get();
        $brands = ProductBrand::orderBy('sort', 'asc')->get();
        return inertia('Product/Edit', [
            'product' => $product,
            'title' => __('app.label.products'),
            'categories' => $categories,
            'brands' => $brands,
            'users' => $users,
            'breadcrumbs' => [
                ['label' => __('app.label.products'), 'href' => route('products.index')],
                ['label' => $product->title, 'href' => route('products.show', $product->id)],
                ['label' => __('app.label.edit')]
            ]
        ]);
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        DB::beginTransaction();

        try {
            $product->update([
                'title' => $request->title,
                'description' => $request->description,
                'serial_number' => $request->serial_number,
                'inventory_number' => $request->inventory_number,
                'parameters' => $request->parameters,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'user_id' => $request->user_id,
                'sort' => $request->sort ,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', __('app.label.updated_successfully', ['title' => $product->title]));

        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with(
                'error',
                __('app.label.updated_error', ['name' => $product->title]) . $th->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $product->delete();
            DB::commit();
            return redirect()
                ->route('products.index')
                ->with('success', __('app.label.deleted_successfully', ['name' => $product->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()
                ->with('error', __('app.label.deleted_error', ['name' => $product->title]) . $th->getMessage());
        }
    }

    /**
     * Remove multiple resources from storage.
     */
    public function destroyBulk(Request $request)
    {
        try {
            $products = Product::whereIn('id', $request->id)->get();

            foreach ($products as $product) {
                $product->delete();
            }

            return back()->with('success', __('app.label.deleted_successfully', [
                'name' => count($request->id) . ' ' . __('app.label.products')
            ]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', [
                    'name' => count($request->id) . ' ' . __('app.label.products')
                ]) . $th->getMessage());
        }
    }


}
