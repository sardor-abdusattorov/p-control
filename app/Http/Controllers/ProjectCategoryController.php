<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectCategory\ProjectCategoryIndexRequest;
use App\Http\Requests\ProjectCategory\ProjectCategoryStoreRequest;
use App\Http\Requests\ProjectCategory\ProjectCategoryUpdateRequest;
use App\Models\ProjectCategory;
use App\Repositories\ProjectCategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectCategoryController extends Controller
{
    protected ProjectCategoryRepository $repository;

    public function __construct(ProjectCategoryRepository $repository)
    {
        $this->repository = $repository;
        $this->middleware('permission:manage project categories');
    }

    public function index(ProjectCategoryIndexRequest $request)
    {
        $this->authorize('viewAny', ProjectCategory::class);

        $perPage = $request->get('perPage', 10);
        $filters = $request->only(['title', 'year', 'status', 'field', 'order', 'perPage']);

        return Inertia::render('ProjectCategory/Index', [
            'title'       => __('app.label.project_categories'),
            'filters'     => $filters,
            'perPage'     => (int) $perPage,
            'statuses'    => ProjectCategory::getStatuses(),
            'categories'  => $this->repository->paginateWithFilters($filters, $perPage),
            'breadcrumbs' => [['label' => __('app.label.project_categories'), 'href' => route('project-categories.index')]],
        ]);
    }

    public function create()
    {
        $this->authorize('create', ProjectCategory::class);

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
        $this->authorize('create', ProjectCategory::class);

        DB::beginTransaction();

        try {
            $category = $this->repository->create([
                'title' => $request->title,
                'sort' => $request->sort ?? 0,
                'year' => $request->year,
                'status' => $request->status,
            ]);

            DB::commit();
            return redirect()->route('project-categories.index')->with('success', __('app.label.created_successfully', ['name' => $category->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.project_categories')]) . ' ' . $th->getMessage());
        }
    }

    public function show(ProjectCategory $projectCategory)
    {
        $this->authorize('view', $projectCategory);

        return Inertia::render('ProjectCategory/Show', [
            'category' => $projectCategory,
            'statuses' => ProjectCategory::getStatuses(),
            'title' => __('app.label.project_categories'),
            'breadcrumbs' => [
                ['label' => __('app.label.project_categories'), 'href' => route('project-categories.index')],
                ['label' => $projectCategory->title]
            ]
        ]);
    }

    public function edit(ProjectCategory $projectCategory)
    {
        $this->authorize('update', $projectCategory);

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
        $this->authorize('update', $projectCategory);

        DB::beginTransaction();

        try {
            $this->repository->update($projectCategory, [
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
        $this->authorize('delete', $projectCategory);

        DB::beginTransaction();
        try {
            $this->repository->delete($projectCategory);
            DB::commit();
            return redirect()->route('project-categories.index')->with('success', __('app.label.deleted_successfully', ['name' => $projectCategory->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $projectCategory->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        $this->authorize('deleteAny', ProjectCategory::class);

        try {
            $this->repository->deleteBulk($request->id);
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.project_categories')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.project_categories')]) . $th->getMessage());
        }
    }

    /**
     * Get categories by year (for dependent dropdown in Projects form).
     */
    public function byYear($year)
    {
        return $this->repository->getByYear($year);
    }
}
