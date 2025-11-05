<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\ProductIndexRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{
    protected ProductRepository $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(ProductIndexRequest $request)
    {
        $this->authorize('viewAny', Product::class);

        $user = auth()->user();
        $filters = $request->only(['title', 'inventory_number', 'serial_number', 'category_id', 'brand_id', 'user_id', 'status', 'field', 'order']);
        $perPage = (int) $request->get('perPage', 10);

        $products = $this->repository->paginateWithFilters($filters, $user, $perPage);
        $users = $this->repository->getAvailableUsers($user);
        $categories = ProductCategory::orderBy('sort', 'asc')->get();
        $statuses = Product::getStatuses();

        return Inertia::render('Product/Index', [
            'title'       => __('app.label.products'),
            'filters'     => $filters,
            'perPage'     => $perPage,
            'products'    => $products,
            'statuses'    => $statuses,
            'categories'  => $categories,
            'users'       => $users,
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
        $this->authorize('create', Product::class);

        $user = auth()->user();
        $users = $this->repository->getAvailableUsers($user);
        $categories = ProductCategory::orderBy('sort', 'asc')->get();
        $brands = ProductBrand::orderBy('sort', 'asc')->get();

        return Inertia::render('Product/Create', [
            'title'       => __('app.label.products'),
            'categories'  => $categories,
            'brands'      => $brands,
            'users'       => $users,
            'breadcrumbs' => [
                ['label' => __('app.label.products'), 'href' => route('products.index')],
                ['label' => __('app.label.create')]
            ]
        ]);
    }

    public function store(ProductStoreRequest $request)
    {
        $this->authorize('create', Product::class);

        DB::beginTransaction();

        try {
            $product = $this->repository->create([
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
        $this->authorize('view', $product);

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
        $this->authorize('update', $product);

        $user = auth()->user();
        $users = $this->repository->getAvailableUsers($user);
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
        $this->authorize('update', $product);

        DB::beginTransaction();

        try {
            $this->repository->update($product, [
                'title' => $request->title,
                'description' => $request->description,
                'serial_number' => $request->serial_number,
                'inventory_number' => $request->inventory_number,
                'parameters' => $request->parameters,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'user_id' => $request->user_id,
                'sort' => $request->sort,
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
        $this->authorize('delete', $product);

        DB::beginTransaction();
        try {
            $this->repository->delete($product);
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
        $this->authorize('deleteAny', Product::class);

        try {
            $products = $this->repository->findByIds($request->id);

            foreach ($products as $product) {
                $this->authorize('delete', $product);
            }

            $this->repository->deleteBulk($request->id);

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
