<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductBrand\ProductBrandIndexRequest;
use App\Http\Requests\ProductBrand\ProductBrandStoreRequest;
use App\Http\Requests\ProductCategory\ProductCategoryUpdateRequest;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductBrandController extends Controller
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

    public function index(ProductBrandIndexRequest $request)
    {
        $brands = ProductBrand::query();

        if ($request->filled('search')) {
            $brands->where('title', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled(['field', 'order'])) {
            $brands->orderBy($request->field, $request->order);
        }

        $perPage = (int) $request->get('perPage', 10);

        return Inertia::render('ProductBrand/Index', [
            'title'       => __('app.label.product_brands'),
            'filters'     => $request->only(['search', 'field', 'order']),
            'perPage'     => $perPage,
            'brands'      => $brands->paginate($perPage),
            'breadcrumbs' => [
                ['label' => __('app.label.product_brands'), 'href' => route('product_brands.index')],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ProductBrand/Create', [
            'title'         => __('app.label.product_brands'),
            'breadcrumbs' => [
                ['label' => __('app.label.product_brands'), 'href' => route('product_brands.index')],
                ['label' => __('app.label.create')]
            ]
        ]);
    }

    public function store(ProductBrandStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $brand = ProductBrand::create([
                'title' => $request->title,
                'sort' => $request->sort ?? 1,
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $brand->addMediaFromRequest('image')
                    ->usingFileName(Str::random(24) . '.' . $file->extension())
                    ->toMediaCollection('product_brand');
            }

            DB::commit();

            return redirect()
                ->route('product_brands.index')
                ->with('success', __('app.label.created_successfully', ['name' => $brand->title]));
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->with('error', __('app.label.created_error', ['name' => __('app.label.product_brands')]) . ' ' . $th->getMessage());
        }
    }

    public function show(ProductBrand $productBrand)
    {
        return Inertia::render('ProductBrand/Show', [
            'brand' => $productBrand,
            'title' => __('app.label.product_brands'),
            'breadcrumbs' => [
                ['label' => __('app.label.product_brands'), 'href' => route('product_brands.index')],
                ['label' => $productBrand->title]
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductBrand $productBrand)
    {
        return inertia('ProductBrand/Edit', [
            'brand' => $productBrand,
            'title' => __('app.label.product_brands'),
            'breadcrumbs' => [
                ['label' => __('app.label.product_brands'), 'href' => route('product_brands.index')],
                ['label' => $productBrand->title, 'href' => route('product_brands.show', $productBrand->id)],
                ['label' => __('app.label.edit')]
            ]
        ]);
    }

    public function update(ProductCategoryUpdateRequest $request, ProductBrand $productBrand)
    {
        DB::beginTransaction();

        try {
            $productBrand->update([
                'title' => $request->title,
                'sort' => $request->sort,
            ]);

            if ($request->hasFile('image')) {
                $productBrand->clearMediaCollection('image');

                $productBrand->addMediaFromRequest('image')
                    ->usingFileName(Str::random(20) . '.' . $request->file('image')->getClientOriginalExtension())
                    ->toMediaCollection('product_brand');
            }

            DB::commit();

            return redirect()
                ->route('product_brands.index')
                ->with('success', __('app.label.updated_successfully', ['title' => $productBrand->title]));

        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with(
                'error',
                __('app.label.updated_error', ['name' => $productBrand->title]) . $th->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductBrand $productBrand)
    {
        DB::beginTransaction();
        try {
            $productBrand->clearMediaCollection('product_brand');
            $productBrand->delete();

            DB::commit();
            return redirect()
                ->route('product_brands.index')
                ->with('success', __('app.label.deleted_successfully', ['name' => $productBrand->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()
                ->with('error', __('app.label.deleted_error', ['name' => $productBrand->title]) . $th->getMessage());
        }
    }

    /**
     * Remove multiple resources from storage.
     */
    public function destroyBulk(Request $request)
    {
        try {
            $brands = ProductBrand::whereIn('id', $request->id)->get();

            foreach ($brands as $brand) {
                $brand->clearMediaCollection('product_brand');
                $brand->delete();
            }

            return back()->with('success', __('app.label.deleted_successfully', [
                'name' => count($request->id) . ' ' . __('app.label.product_brands')
            ]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', [
                    'name' => count($request->id) . ' ' . __('app.label.product_brands')
                ]) . $th->getMessage());
        }
    }



}
