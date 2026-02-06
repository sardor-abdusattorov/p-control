<?php

namespace App\Http\Controllers;

use App\Exports\ProjectContractsExport;
use App\Http\Requests\Contract\ContractRelatedRequest;
use App\Http\Requests\Projects\ProjectsIndexRequest;
use App\Http\Requests\Projects\ProjectsStoreRequest;
use App\Http\Requests\Projects\ProjectsUpdateRequest;
use App\Models\Contract;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ProjectsController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login');
            }
            $permissions = [
                'create project' => ['projects.create', 'projects.store'],
                'update project' => ['projects.edit', 'projects.update'],
                'delete project' => ['projects.destroy', 'projects.destroy-bulk'],
                'view project' => ['projects.index', 'projects.show', 'projects.related-contracts', 'projects.contracts.export'],
            ];
            foreach ($permissions as $permission => $routes) {
                if ($user->can($permission)) {
                    foreach ($routes as $route) {
                        if ($request->routeIs($route)) {
                            return $next($request);
                        }
                    }
                }
            }
            return redirect()->route('dashboard')->with('error', __('app.deny_access'));
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ProjectsIndexRequest $request)
    {
        $user = auth()->user();
        $statuses = Contract::getStatuses();
        $currencies = Currency::where(['status' => 1])->get();
        $projectsQuery = Project::query()->with(['category', 'currency', 'contracts' => function ($query) use ($user) {
            if (!$user->can('view all contracts')) {
                $query->where('user_id', $user->id);
            }
        }]);
        $contractsQuery = Contract::query();
        if (!$user->can('view all contracts')) {
            $contractsQuery->where('user_id', $user->id);
        }
        if ($request->has('search')) {
            $projectsQuery->where('title', 'LIKE', "%" . $request->search . "%")
                ->orWhere('project_number', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            if ($request->field === 'project_number') {
                $projectsQuery->orderByRaw('CAST(project_number AS UNSIGNED) ' . $request->order);
            } else {
                $projectsQuery->orderBy($request->field, $request->order);
            }
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;
        $projects = $projectsQuery->paginate($perPage);

        return Inertia::render('Projects/Index', [
            'title'       => __('app.label.projects'),
            'filters'     => $request->all(['search', 'field', 'order']),
            'perPage'     => (int) $perPage,
            'projects'    => $projects,
            'statuses'    => $statuses,
            'contracts'   => $contractsQuery->get(),
            'currencies'  => $currencies,
            'breadcrumbs' => [
                ['label' => __('app.label.projects'), 'href' => route('projects.index')],
            ],
        ]);
    }


    public function relatedContracts(ContractRelatedRequest $request, Project $project)
    {
        $user = auth()->user();
        $statuses = Contract::getStatuses();
        $contractsQuery = Contract::query()
            ->where('project_id', $project->id)
            ->with('currency');
        if (!$user->can('view all contracts')) {
            $contractsQuery->where('user_id', $user->id);
        }
        if ($request->has('search')) {
            $contractsQuery->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $contractsQuery->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;
        $contracts = $contractsQuery->paginate($perPage);

        return Inertia::render('Projects/RelatedContract', [
            'title'         => __('app.label.contracts'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'contracts'     => $contracts,
            'statuses'      => $statuses,
            'project'       => $project,
            'breadcrumbs'   => [
                ['label' => __('app.label.projects'), 'href' => route('projects.index')],
                ['label' => $project->id, 'href' => route('projects.show', $project->id)],
                ['label' => __('app.label.related_contracts')]
            ]
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Projects/Create', [
            'title' => __('app.label.projects'),
            'breadcrumbs' => [
                ['label' => __('app.label.projects'), 'href' => route('projects.index')],
                ['label' => __('app.label.create')]
            ],
            'categories' => ProjectCategory::where('status', 1)->orderBy('sort')->get(),
            'statuses' => Project::getStatuses(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectsStoreRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $project = new Project();
            $project->title = $request->title;
            $project->project_number = $request->project_number;
            $project->category_id = $request->category_id;
            $project->sort = $request->sort ?? 0;
            $project->status_id = $request->status_id ?? 1;
            $project->save();

            activity('project')
                ->causedBy(Auth::user())
                ->performedOn($project)
                ->withProperties([
                    'project_id' => $project->id,
                    'title' => $project->title,
                    'project_number' => $project->project_number,
                ])
                ->log('Создан проект');

            DB::commit();

            return redirect()->route('projects.index')->with('success', __('app.label.created_successfully', ['name' => $project->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            activity('project')
                ->causedBy(Auth::user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'title' => $request->title,
                ])
                ->log('Ошибка при создании проекта');

            return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.project')]) . ' ' . $th->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $statuses = Contract::getStatuses();
        return Inertia::render('Projects/Show', [
            'project' => $project->load(['category', 'currency']),
            'statuses' => $statuses,
            'title' => __('app.label.projects'),
            'breadcrumbs' => [
                ['label' => __('app.label.projects'), 'href' => route('projects.index')],
                ['label' => $project->id]
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return inertia('Projects/Edit', [
            'project' => $project,
            'title' => __('app.label.projects'),
            'breadcrumbs' => [
                ['label' => __('app.label.projects'), 'href' => route('projects.index')],
                ['label' => $project->id, 'href' => route('projects.show', $project->id)],
                ['label' => __('app.label.edit')]
            ],
            'categories' => ProjectCategory::where('status', 1)->orderBy('sort')->get(),
            'statuses' => Project::getStatuses(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectsUpdateRequest $request, Project $project)
    {
        DB::beginTransaction();

        try {
            $originalData = $project->getOriginal();
            $project->update([
                'project_number' => $request->project_number,
                'title' => $request->title,
                'category_id' => $request->category_id,
                'sort' => $request->sort ?? 0,
                'status_id' => $request->status_id,
            ]);

            activity('project')
                ->causedBy(auth()->user())
                ->performedOn($project)
                ->withProperties([
                    'before' => $originalData,
                    'after' => $project->getChanges(),
                ])
                ->log('Проект обновлен');

            DB::commit();

            return redirect()->route('projects.index')->with('success', __('app.label.updated_successfully', ['name' => $project->id]));
        } catch (\Throwable $th) {
            DB::rollback();

            activity('project')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'project_id' => $project->id,
                ])
                ->log('Ошибка при обновлении проекта');

            return back()->with('error', __('app.label.updated_error', ['name' => $project->id]) . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        DB::beginTransaction();

        try {
            $projectName = $project->title;
            $project->delete();
            activity('project')
                ->causedBy(auth()->user())
                ->performedOn($project)
                ->withProperties([
                    'project_id' => $project->id,
                    'title' => $projectName,
                ])
                ->log('Проект удален');
            DB::commit();
            return redirect()->route('projects.index')->with('success', __('app.label.deleted_successfully', ['name' => $projectName]));
        } catch (\Throwable $th) {
            DB::rollback();

            activity('project')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'project_id' => $project->id,
                ])
                ->log('Ошибка при удалении проекта');

            return back()->with('error', __('app.label.deleted_error', ['name' => $project->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $projects = Project::whereIn('id', $request->id)->get();
            $deletedProjects = [];

            foreach ($projects as $project) {
                $deletedProjects[] = [
                    'project_id' => $project->id,
                    'title' => $project->title,
                ];
                $project->delete();
            }
            activity('project')
                ->causedBy(auth()->user())
                ->withProperties([
                    'deleted_projects' => $deletedProjects,
                ])
                ->log('Массовое удаление проектов');

            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.projects')]));
        } catch (\Throwable $th) {
            activity('project')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'project_ids' => $request->id,
                ])
                ->log('Ошибка при массовом удалении проектов');

            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.projects')]) . $th->getMessage());
        }
    }


    public function exportContracts(Project $project)
    {
        return Excel::download(new ProjectContractsExport($project, auth()->user()), 'contracts_project_' . $project->id . '.xlsx');
    }

    public function byYear($year)
    {
        return ProjectCategory::query()
            ->where('year', $year)
            ->where('status', 1)
            ->orderBy('sort')
            ->get(['id', 'title']);
    }


}
