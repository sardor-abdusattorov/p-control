<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCategory\ContactCategoryStoreRequest;
use App\Http\Requests\ContactSubCategory\ContactSubIndexRequest;
use App\Http\Requests\ContactSubCategory\ContactSubStoreRequest;
use App\Http\Requests\ContactSubCategory\ContactSubUpdateRequest;
use App\Models\ContactCategory;
use App\Models\ContactSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ContactSubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ContactSubIndexRequest $request)
    {
        $query = ContactSubcategory::with('category'); // Загрузка связанной категории

        // 🔍 Фильтрация
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('info')) {
            $query->where('info', 'like', '%' . $request->info . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 📊 Сортировка
        if ($request->filled('field') && $request->filled('order')) {
            $query->orderBy($request->field, $request->order);
        } else {
            $query->latest(); // По умолчанию по дате создания
        }

        $perPage = $request->get('perPage', 10);

        return Inertia::render('SubCategories/Index', [
            'title'         => __('app.label.contact_subcategories'),
            'filters'       => $request->only(['title', 'info', 'category_id', 'status', 'field', 'order', 'perPage']),
            'perPage'       => (int) $perPage,
            'statuses'      => ContactSubcategory::getStatuses(),
            'categories'    => ContactCategory::where('status', 1)->get(),
            'subCategories' => $query->paginate($perPage)->withQueryString(),
            'breadcrumbs'   => [
                ['label' => __('app.label.contact_subcategories'), 'href' => route('contact-subcategories.index')],
            ],
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('SubCategories/Create', [
            'title' => __('app.label.contact_subcategories'),
            'breadcrumbs' => [
                ['label' => __('app.label.contact_subcategories'), 'href' => route('contact-subcategories.index')],
                ['label' => __('app.label.create')],
            ],
            'statuses' => ContactSubcategory::getStatuses(),
            'categories' => ContactCategory::where('status', 1)->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactSubStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $sub_category = new ContactSubcategory();
            $sub_category->title = $request->title;
            $sub_category->info = $request->info;
            $sub_category->category_id = $request->category_id;
            $sub_category->status = $request->status;
            $sub_category->save();

            DB::commit();
            return redirect()->route('contact-subcategories.index')->with('success', __('app.label.created_successfully', ['name' => $sub_category->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.contact_subcategories')]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactSubcategory $contactSubcategory)
    {
        $category = ContactCategory::where('id', $contactSubcategory->category_id)->first();

        return Inertia::render('SubCategories/Show', [
            'subCategory' => $contactSubcategory,
            'title'       => __('app.label.contact_subcategories'),
            'category'    => $category,
            'breadcrumbs' => [
                ['label' => __('app.label.contact_subcategories'), 'href' => route('contact-subcategories.index')],
                ['label' => $contactSubcategory->title]
            ]
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactSubcategory $contactSubcategory)
    {
        return inertia('SubCategories/Edit', [
            'subCategory' => $contactSubcategory,
            'title'       => __('app.label.contact_subcategories'),
            'breadcrumbs' => [
                ['label' => __('app.label.contact_subcategories'), 'href' => route('contact-subcategories.index')],
                ['label' => $contactSubcategory->id, 'href' => route('contact-subcategories.show', $contactSubcategory->id)],
                ['label' => __('app.label.edit')]
            ],
            'statuses' => ContactSubcategory::getStatuses(),
            'categories' => ContactCategory::where('status', 1)->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactSubUpdateRequest $request, ContactSubcategory $contactSubcategory)
    {
        DB::beginTransaction();

        try {
            $contactSubcategory->update([
                'title' => $request->title,
                'info' => $request->info,
                'category_id' => $request->category_id,
                'status' => $request->status
            ]);

            DB::commit();
            return redirect()->route('contact-subcategories.index')->with('success', __('app.label.updated_successfully', ['name' => $contactSubcategory->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $contactSubcategory->title]) . $th->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactSubcategory $contactSubcategory)
    {
        DB::beginTransaction();
        try {
            $contactSubcategory->delete();
            DB::commit();
            return redirect()->route('contact-subcategories.index')->with('success', __('app.label.deleted_successfully', ['name' => $contactSubcategory->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $contactSubcategory->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $contactSubcategory = ContactSubcategory::whereIn('id', $request->id);
            $contactSubcategory->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.contact_subcategories')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.contact_categories')]) . $th->getMessage());
        }
    }
}
