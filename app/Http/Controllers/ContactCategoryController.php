<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCategory\ContactCategoryRequest;
use App\Http\Requests\ContactCategory\ContactCategoryStoreRequest;
use App\Http\Requests\ContactCategory\ContactCategoryUpdateRequest;
use App\Models\ContactCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ContactCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage contact categories');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ContactCategoryRequest $request)
    {
        $categories = ContactCategory::query();
        $statuses = ContactCategory::getStatuses();

        // 🔍 Фильтрация по полям
        if ($request->filled('title')) {
            $categories->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('info')) {
            $categories->where('info', 'like', '%' . $request->info . '%');
        }

        if ($request->filled('status')) {
            $categories->where('status', $request->status);
        }

        if ($request->filled('field') && $request->filled('order')) {
            $categories->orderBy($request->field, $request->order);
        } else {
            $categories->latest();
        }

        $perPage = $request->get('perPage', 10);

        return Inertia::render('ContactCategory/Index', [
            'title'       => __('app.label.contact_categories'),
            'filters'     => $request->only(['title', 'info', 'status', 'field', 'order', 'perPage']),
            'perPage'     => (int) $perPage,
            'statuses'    => $statuses,
            'categories'  => $categories->paginate($perPage)->withQueryString(),
            'breadcrumbs' => [['label' => __('app.label.contact_categories'), 'href' => route('contact-categories.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ContactCategory/Create', [
            'title' => __('app.label.contact_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.contact_categories'), 'href' => route('contact-categories.index')],
                ['label' => __('app.label.create')],
            ],
            'statuses' => ContactCategory::getStatuses(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactCategoryStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $category = new ContactCategory();
            $category->title = $request->title;
            $category->info = $request->info;
            $category->status = $request->status;
            $category->save();

            DB::commit();
            return redirect()->route('contact-categories.index')->with('success', __('app.label.created_successfully', ['name' => $category->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.contact_categories')]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactCategory $contactCategory)
    {
        return Inertia::render('ContactCategory/Show', [
            'category' => $contactCategory,
            'title' => __('app.label.contact_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.contact_categories'), 'href' => route('contact-categories.index')],
                ['label' => $contactCategory->title]
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactCategory $contactCategory)
    {
        return inertia('ContactCategory/Edit', [
            'category' => $contactCategory,
            'title' => __('app.label.contact_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.contact_categories'), 'href' => route('contact-categories.index')],
                ['label' => $contactCategory->id, 'href' => route('contact-categories.show', $contactCategory->id)],
                ['label' => __('app.label.edit')]
            ],
            'statuses' => ContactCategory::getStatuses(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactCategoryUpdateRequest $request, ContactCategory $contactCategory)
    {
        DB::beginTransaction();

        try {
            $contactCategory->update([
                'title' => $request->title,
                'info' => $request->info,
                'status' => $request->status
            ]);

            DB::commit();
            return redirect()->route('contact-categories.index')->with('success', __('app.label.updated_successfully', ['name' => $contactCategory->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $contactCategory->title]) . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactCategory $contactCategory)
    {
        DB::beginTransaction();
        try {
            $contactCategory->delete();
            DB::commit();
            return redirect()->route('contact-categories.index')->with('success', __('app.label.deleted_successfully', ['name' => $contactCategory->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $contactCategory->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $contactCategory = ContactCategory::whereIn('id', $request->id);
            $contactCategory->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.contact_categories')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.contact_categories')]) . $th->getMessage());
        }
    }
}
