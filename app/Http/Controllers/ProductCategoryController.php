<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCategory\ProductCategoryIndexRequest;
use App\Http\Requests\ProductCategory\ProductCategoryStoreRequest;
use App\Http\Requests\ProductCategory\ProductCategoryUpdateRequest;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductCategoryController extends Controller
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

    public function index(ProductCategoryIndexRequest $request)
    {
        $categories = ProductCategory::query();

        if ($request->filled('search')) {
            $categories->where('title', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled(['field', 'order'])) {
            $categories->orderBy($request->field, $request->order);
        }

        $perPage = (int) $request->get('perPage', 10);

        return Inertia::render('ProductCategory/Index', [
            'title'       => __('app.label.product_categories'),
            'filters'     => $request->only(['search', 'field', 'order']),
            'perPage'     => $perPage,
            'categories'      => $categories->paginate($perPage),
            'breadcrumbs' => [
                ['label' => __('app.label.product_categories'), 'href' => route('product_categories.index')],
            ],
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ProductCategory/Create', [
            'title'         => __('app.label.product_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.product_categories'), 'href' => route('product_categories.index')],
                ['label' => __('app.label.create')]
            ]
        ]);
    }

    public function store(ProductCategoryStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $category = ProductCategory::create([
                'title' => $request->title,
                'sort' => $request->sort ?? 1,
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $category->addMediaFromRequest('image')
                    ->usingFileName(Str::random(24) . '.' . $file->extension())
                    ->toMediaCollection('image');
            }

            DB::commit();

            return redirect()
                ->route('product_categories.index')
                ->with('success', __('app.label.created_successfully', ['name' => $category->title]));
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->with('error', __('app.label.created_error', ['name' => __('app.label.product_categories')]) . ' ' . $th->getMessage());
        }
    }

    public function show(ProductCategory $productCategory)
    {
        return Inertia::render('ProductCategory/Show', [
            'category' => $productCategory,
            'title' => __('app.label.product_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.product_categories'), 'href' => route('product_categories.index')],
                ['label' => $productCategory->title]
            ]
        ]);
    }

    public function edit(ProductCategory $productCategory)
    {
        return inertia('ProductCategory/Edit', [
            'category' => $productCategory,
            'title' => __('app.label.product_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.product_categories'), 'href' => route('product_categories.index')],
                ['label' => $productCategory->title, 'href' => route('product_categories.show', $productCategory->id)],
                ['label' => __('app.label.edit')]
            ]
        ]);
    }

    public function update(ProductCategoryUpdateRequest $request, ProductCategory $productCategory)
    {
        DB::beginTransaction();

        try {
            $productCategory->update([
                'title' => $request->title,
                'sort' => $request->sort,
            ]);

            if ($request->hasFile('image')) {
                $productCategory->clearMediaCollection('image');

                $productCategory->addMediaFromRequest('image')
                    ->usingFileName(Str::random(20) . '.' . $request->file('image')->getClientOriginalExtension())
                    ->toMediaCollection('image');
            }

            DB::commit();

            return redirect()
                ->route('product_categories.index')
                ->with('success', __('app.label.updated_successfully', ['title' => $productCategory->title]));

        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with(
                'error',
                __('app.label.updated_error', ['name' => $productCategory->title]) . $th->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        DB::beginTransaction();
        try {
            $productCategory->clearMediaCollection('image');
            $productCategory->delete();

            DB::commit();
            return redirect()
                ->route('product_categories.index')
                ->with('success', __('app.label.deleted_successfully', ['name' => $productCategory->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()
                ->with('error', __('app.label.deleted_error', ['name' => $productCategory->title]) . $th->getMessage());
        }
    }

    /**
     * Remove multiple resources from storage.
     */
    public function destroyBulk(Request $request)
    {
        try {
            $categories = ProductCategory::whereIn('id', $request->id)->get();

            foreach ($categories as $category) {
                $category->clearMediaCollection('image');
                $category->delete();
            }

            return back()->with('success', __('app.label.deleted_successfully', [
                'name' => count($request->id) . ' ' . __('app.label.product_categories')
            ]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', [
                    'name' => count($request->id) . ' ' . __('app.label.product_product_categories')
                ]) . $th->getMessage());
        }
    }
}

