<?php

namespace App\Http\Controllers;

use App\Http\Requests\Application\ApplicationIndexRequest;
use App\Http\Requests\Application\ApplicationStoreRequest;
use App\Http\Requests\Application\ApplicationUpdateRequest;
use App\Models\Application;
use App\Models\Project;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ApplicationController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login');
            }
            $permissions = [
                'create application' => ['application.create', 'application.store'],
                'update application' => ['application.edit', 'application.update'],
                'delete application' => ['application.delete', 'application.delete-bulk'],
                'view application' => ['application.index', 'application.show'],
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
    public function index(ApplicationIndexRequest $request)
    {
        $user = auth()->user();
        if ($user->can('view all applications')) {
            $applications = Application::query()->with(['user', 'project']);
        } else {
            $applications = Application::query()->where('user_id', $user->id)->with(['user', 'project']);
        }

        if ($request->has('search')) {
            $applications->where('title', 'LIKE', "%" . $request->search . "%");
        }

        if ($request->has(['field', 'order'])) {
            $applications->orderBy($request->field, $request->order);
        }

        $perPage = $request->has('perPage') ? $request->perPage : 10;

        return Inertia::render('Application/Index', [
            'title'         => __('app.label.applications'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'applications'      => $applications->paginate($perPage),
            'breadcrumbs'   => [['label' => __('app.label.applications'), 'href' => route('application.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::all();

        $recipients = Recipient::where('user_id', auth()->id())->get();

        $users = User::where('id', '!=', auth()->id())->get();

        return Inertia::render('Application/Create', [
            'title' => __('app.label.applications'),
            'projects' => $projects,
            'recipients' => $recipients,
            'users' => $users,
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => __('app.label.create')]
            ],
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ApplicationStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $application = new Application();
            $application->title = $request->title;
            $application->project_id = $request->project_id;
            $application->user_id = auth()->id();
            $application->status_id = 1;
            $application->save();
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ext = $file->extension();
                    $name = Str::random(24) . '.' . $ext;
                    $application->addMedia($file)
                        ->usingFileName($name)
                        ->toMediaCollection("documents");
                }
            }
            DB::commit();
            return redirect()->route('application.index')->with('success', __('app.label.created_successfully', ['name' => $application->title]));

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.application')]) . ' ' . $th->getMessage());
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        $projects = Project::all();
        $files = $application->getMedia('documents');
        $recipients = Recipient::where('user_id', auth()->id())->get();

        $users = User::where('id', '!=', auth()->id())->get();

        return Inertia::render('Application/Show', [
            'title' => $application->title,
            'projects' => $projects,
            'recipients' => $recipients,
            'users' => $users,
            'application' => $application,
            'files' => $files,
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $application->title]
            ],
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Application $application)
    {
        $projects = Project::all();
        $files = $application->getMedia('documents');
        $recipients = Recipient::where('user_id', auth()->id())->get();

        $users = User::where('id', '!=', auth()->id())->get();

        return Inertia::render('Application/Edit', [
            'title' => $application->title,
            'projects' => $projects,
            'recipients' => $recipients,
            'users' => $users,
            'application' => $application,
            'files' => $files,
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $application->title]
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(ApplicationUpdateRequest $request, Application $application)
    {
        DB::beginTransaction();

        try {
            $application->update([
                'title' => $request->input('title'),
                'project_id' => $request->input('project_id'),
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ext = $file->extension();
                    $name = Str::random(24) . '.' . $ext;
                    $application->addMedia($file)
                        ->usingFileName($name)
                        ->toMediaCollection("documents");
                }
            }
            DB::commit();
            return redirect()->route('application.index')->with('success', __('app.label.updated_successfully', ['name' => $application->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $application->title]) . $th->getMessage());
        }
    }



    public function destroy(Application $application)
    {
        DB::beginTransaction();
        try {
            $application->clearMediaCollection('documents');

            $application->delete();

            DB::commit();
            return redirect()->route('application.index')->with('success', __('app.label.deleted_successfully', ['name' => $application->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $application->title]) . $th->getMessage());
        }
    }


    public function destroyBulk(Request $request)
    {
        try {
            $applications = Application::whereIn('id', $request->id)->get();
            foreach ($applications as $application) {
                $application->clearMediaCollection('documents');
                $application->delete();
            }

            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.applications')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.applications')]) . $th->getMessage());
        }
    }
}
