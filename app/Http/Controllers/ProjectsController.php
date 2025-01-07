<?php

namespace App\Http\Controllers;

use App\Http\Requests\Projects\ProjectsIndexRequest;
use App\Http\Requests\Projects\ProjectsStoreRequest;
use App\Http\Requests\Projects\ProjectsUpdateRequest;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
                'view project' => ['projects.index', 'projects.show'],
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
        $projects = Project::query()
            ->with(['user', 'currency', 'status']);

        if ($request->has('search')) {
            $projects->where(function ($query) use ($request) {
                $query->where('title', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('project_number', 'LIKE', "%" . $request->search . "%");
            });
        }

        if ($request->has(['field', 'order'])) {
            $projects->orderBy($request->field, $request->order);
        }

        $perPage = $request->has('perPage') ? $request->perPage : 10;

        return Inertia::render('Projects/Index', [
            'title'         => __('app.label.projects'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'projects'      => $projects->paginate($perPage),
            'breadcrumbs'   => [['label' => __('app.label.projects'), 'href' => route('projects.index')]],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();

        return Inertia::render('Projects/Create', [
            'title' => __('app.label.projects'),
            'breadcrumbs' => [
                ['label' => __('app.label.projects'), 'href' => route('projects.index')],
                ['label' => __('app.label.create')]
            ],
            'users' => $users
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
            $project->budget_sum = $request->budget_sum;
            $project->budget_balance = $request->budget_sum;
            $project->user_id = $request->user_id;
            $project->currency_id = 1;
            $project->status_id = 1;
            $project->deadline = Carbon::parse($request->deadline)->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
            $project->project_year = Carbon::parse($request->project_year)->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
            $project->save();

            DB::commit();
            return redirect()->route('projects.index')->with('success', __('app.label.created_successfully', ['name' => $project->title]));

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.project')]) . ' ' . $th->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return Inertia::render('Projects/Show', [
            'project' => $project->load(['user', 'status', 'currency']),
            'title' => __('app.label.projects'),
            'breadcrumbs' => [
                ['label' => __('app.label.projects'), 'href' => route('projects.index')],
                ['label' => $project->title]
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $users = User::all();
        return inertia('Projects/Edit', [
            'project' => $project,
            'users' => $users,
            'title' => __('app.label.projects'),
            'breadcrumbs' => [
                ['label' => __('app.label.projects'), 'href' => route('projects.index')],
                ['label' => $project->title]
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectsUpdateRequest $request, Project $project)
    {
        DB::beginTransaction();
        try {
            $project->update([
                'project_number' => $request->project_number,
                'budget_sum' => $request->budget_sum,
                'budget_balance' => $request->budget_sum,
                'title' => $request->title,
                'user_id' => $request->user_id,
                'deadline' => Carbon::parse($request->deadline)->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                'project_year' => Carbon::parse($request->project_year)->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
            ]);

            DB::commit();
            return redirect()->route('projects.index')->with('success', __('app.label.updated_successfully', ['name' => $project->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $project->title]) . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        DB::beginTransaction();
        try {
            $project->delete();
            DB::commit();
            return redirect()->route('projects.index')->with('success', __('app.label.deleted_successfully', ['name' => $project->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $project->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $project = Project::whereIn('id', $request->id);
            $project->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.project')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.projects')]) . $th->getMessage());
        }
    }
}
