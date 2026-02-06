<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectCategory\ProjectCategoryIndexRequest;
use App\Http\Requests\ProjectCategory\ProjectCategoryStoreRequest;
use App\Http\Requests\ProjectCategory\ProjectCategoryUpdateRequest;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage project categories');
    }

    public function index(ProjectCategoryIndexRequest $request)
    {
        $categories = ProjectCategory::query();

        if ($request->filled('title')) {
            $categories->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('year')) {
            $categories->where('year', $request->year);
        }

        if ($request->filled('status')) {
            $categories->where('status', $request->status);
        }

        if ($request->filled('field') && $request->filled('order')) {
            $categories->orderBy($request->field, $request->order);
        } else {
            $categories->orderBy('sort')->latest();
        }

        $perPage = $request->get('perPage', 10);

        return Inertia::render('ProjectCategory/Index', [
            'title'       => __('app.label.project_categories'),
            'filters'     => $request->only(['title', 'year', 'status', 'field', 'order', 'perPage']),
            'perPage'     => (int) $perPage,
            'statuses'    => ProjectCategory::getStatuses(),
            'categories'  => $categories->paginate($perPage)->withQueryString(),
            'breadcrumbs' => [['label' => __('app.label.project_categories'), 'href' => route('project-categories.index')]],
        ]);
    }

    public function create()
    {
        return Inertia::render('ProjectCategory/Create', [
            'title' => __('app.label.project_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.project_categories'), 'href' => route('project-categories.index')],
                ['label' => __('app.label.create')],
            ],
            'statuses' => ProjectCategory::getStatuses(),
        ]);
    }

    public function store(ProjectCategoryStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $category = new ProjectCategory();
            $category->title = $request->title;
            $category->sort = $request->sort ?? 0;
            $category->year = $request->year;
            $category->status = $request->status;
            $category->save();

            DB::commit();
            return redirect()->route('project-categories.index')->with('success', __('app.label.created_successfully', ['name' => $category->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.project_categories')]) . ' ' . $th->getMessage());
        }
    }

    public function show(ProjectCategory $projectCategory)
    {
        return Inertia::render('ProjectCategory/Show', [
            'category' => $projectCategory,
            'title' => __('app.label.project_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.project_categories'), 'href' => route('project-categories.index')],
                ['label' => $projectCategory->title]
            ]
        ]);
    }

    public function edit(ProjectCategory $projectCategory)
    {
        return inertia('ProjectCategory/Edit', [
            'category' => $projectCategory,
            'title' => __('app.label.project_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.project_categories'), 'href' => route('project-categories.index')],
                ['label' => $projectCategory->id, 'href' => route('project-categories.show', $projectCategory->id)],
                ['label' => __('app.label.edit')]
            ],
            'statuses' => ProjectCategory::getStatuses(),
        ]);
    }

    public function update(ProjectCategoryUpdateRequest $request, ProjectCategory $projectCategory)
    {
        DB::beginTransaction();

        try {
            $projectCategory->update([
                'title' => $request->title,
                'sort' => $request->sort ?? 0,
                'year' => $request->year,
                'status' => $request->status,
            ]);

            DB::commit();
            return redirect()->route('project-categories.index')->with('success', __('app.label.updated_successfully', ['name' => $projectCategory->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $projectCategory->title]) . $th->getMessage());
        }
    }

    public function destroy(ProjectCategory $projectCategory)
    {
        DB::beginTransaction();
        try {
            $projectCategory->delete();
            DB::commit();
            return redirect()->route('project-categories.index')->with('success', __('app.label.deleted_successfully', ['name' => $projectCategory->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $projectCategory->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $categories = ProjectCategory::whereIn('id', $request->id);
            $categories->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.project_categories')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.project_categories')]) . $th->getMessage());
        }
    }
}
