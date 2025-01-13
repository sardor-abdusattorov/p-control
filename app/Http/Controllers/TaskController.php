<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\TaskIndexRequest;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if (!$user) {
                return redirect()->route('login');
            }

            $permissions = [
                'create task' => ['task.create', 'task.store'],
                'update task' => ['task.edit', 'task.update'],
                'delete task' => ['task.destroy', 'task.destroy-bulk'],
                'view task' => ['task.index', 'task.show'],
                'start task' => ['task.start'],
                'complete task' => ['task.complete'],
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
    public function index(TaskIndexRequest $request)
    {
        $user = auth()->user();
        $users = User::all()->pluck('name', 'id')->toArray();
        if ($user->can('view all tasks')) {
            $tasks = Task::query();
        } else {
            $tasks = Task::query()->where('assigned_user', $user->id);
        }
        if ($request->has('search')) {
            $tasks->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $tasks->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;
        $tasks = $tasks->paginate($perPage);
        $statuses = Task::getStatuses();
        $priorities = Task::getPriorities();

        return Inertia::render('Task/Index', [
            'title'         => __('app.label.tasks'),
            'statuses'      => $statuses,
            'users'      => $users,
            'priorities'    => $priorities,
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'tasks'         => $tasks,
            'breadcrumbs'   => [['label' => __('app.label.tasks'), 'href' => route('task.index')]],
        ]);
    }


    /**
     * Show the form for creating a new resource.
         */
    public function create()
    {
        $projects = Project::all();
        $user = Auth::user();
        $users = User::where('id', '!=', $user->id)->get();
        $statuses = Task::getStatuses();
        $priorities = Task::getPriorities();

        return Inertia::render('Task/Create', [
            'title' => __('app.label.tasks'),
            'breadcrumbs' => [
                ['label' => __('app.label.tasks'), 'href' => route('task.index')],
                ['label' => __('app.label.create')]
            ],
            'projects' => $projects,
            'users' => $users,
            'statuses' => $statuses,
            'priorities' => $priorities,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $task = new Task();
            $task->project_id = $request->project_id;
            $task->name = $request->name;
            $task->description = $request->description;
            $task->assigned_user = $request->assigned_user;
            $task->status = 1;
            $task->priority = $request->priority;
            $task->user_id = auth()->user()->id;
            $task->due_date = Carbon::parse($request->due_date)
                ->timezone(config('app.timezone'))
                ->format('Y-m-d H:i:s');

            if ($task->save()) {
                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $ext = $file->extension();
                        $name = Str::random(24) . '.' . $ext;
                        $task->addMedia($file)
                            ->usingFileName($name)
                            ->toMediaCollection("task files");
                    }
                }

                Notification::create([
                    'user_id' => auth()->user()->id,
                    'receiver_id' => $task->assigned_user,
                    'model' => 'task',
                    'model_id' => $task->id,
                    'is_read' => false,
                    'action' => 'create',
                ]);
                activity('task')
                    ->causedBy(auth()->user())
                    ->performedOn($task)
                    ->withProperties([
                        'task_id' => $task->id,
                        'name' => $task->name,
                        'project_id' => $task->project_id,
                        'assigned_user' => $task->assigned_user,
                        'priority' => $task->priority,
                        'due_date' => $task->due_date,
                    ])
                    ->log('Создана задача');

                DB::commit();

                return redirect()->route('task.index')->with('success', __('app.label.created_successfully', ['name' => $task->name]));
            } else {
                DB::rollback();
                return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.tasks')]));
            }
        } catch (\Throwable $th) {
            DB::rollback();

            // Логирование ошибки
            activity('task')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'project_id' => $request->project_id,
                    'name' => $request->name,
                ])
                ->log('Ошибка при создании задачи');

            return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.tasks')]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task->load('taskCompletion');
        $files = $task->getMedia('task files');
        $statuses = Task::getStatuses();
        $priorities = Task::getPriorities();
        $project = Project::where('id', $task->project_id)->first();
        $users = User::all()->pluck('name', 'id')->toArray();

        return Inertia::render('Task/Show', [
            'title' => $task->title,
            'files' => $files,
            'users' => $users,
            'task' => $task,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'project' => $project,
            'breadcrumbs' => [
                ['label' => __('app.label.tasks'), 'href' => route('task.index')],
                ['label' => $task->name],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $files = $task->getMedia('task files');
        $projects = Project::all();
        if (Auth::user()->hasRole('superadmin')) {
            $users = User::all();
        } else {
            $users = User::where('id', '!=', Auth::id())->get();
        }
        $statuses = Task::getStatuses();
        $priorities = Task::getPriorities();
        return inertia('Task/Edit', [
            'task' => $task,
            'projects' => $projects,
            'users' => $users,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'files' => $files,
            'title' => __('app.label.tasks'),
            'breadcrumbs' => [
                ['label' => __('app.label.tasks'), 'href' => route('task.index')],
                ['label' => $task->name]
            ]
        ]);
    }

    public function start(Task $task)
    {
        if (!$task) {
            return redirect()->back()->with('error', __('app.label.task_not_found'));
        }

        $task->status = 2;

        if ($task->save()) {
            Notification::create([
                'user_id' => auth()->user()->id,
                'receiver_id' => $task->user_id,
                'model' => 'task',
                'model_id' => $task->id,
                'is_read' => false,
                'action' => 'start',
            ]);

            // Логирование старта задачи
            activity('task')
                ->causedBy(auth()->user())
                ->performedOn($task)
                ->withProperties([
                    'task_id' => $task->id,
                    'name' => $task->name,
                    'status' => $task->status,
                ])
                ->log('Задача запущена');
        }

        return redirect()->route('task.show', $task->id)
            ->with('success', __('app.label.started_successfully', ['name' => $task->name]));
    }

    public function complete(Request $request, Task $task)
    {
        $request->validate([
            'completion_note' => 'nullable|string',
        ]);

        TaskCompletion::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'completion_note' => $request->completion_note,
            'completed_at' => now(),
        ]);

        $task->status = 3;
        $task->save();

        Notification::create([
            'user_id' => auth()->user()->id,
            'receiver_id' => $task->user_id,
            'model' => 'task',
            'model_id' => $task->id,
            'is_read' => false,
            'action' => 'complete',
        ]);

        // Логирование завершения задачи
        activity('task')
            ->causedBy(auth()->user())
            ->performedOn($task)
            ->withProperties([
                'task_id' => $task->id,
                'name' => $task->name,
                'completion_note' => $request->completion_note,
                'status' => $task->status,
            ])
            ->log('Задача завершена');

        return redirect()->route('task.show', $task->id)
            ->with('success', __('app.label.task_completed_successfully', ['name' => $task->name]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskUpdateRequest $request, Task $task)
    {
        DB::beginTransaction();

        try {
            $originalTask = $task->getOriginal(); // Получаем данные до обновления

            $task->update([
                'project_id' => $request->project_id,
                'name' => $request->name,
                'description' => $request->description,
                'assigned_user' => $request->assigned_user,
                'status' => $request->status,
                'priority' => $request->priority,
                'due_date' => Carbon::parse($request->due_date)->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ext = $file->extension();
                    $name = Str::random(24) . '.' . $ext;
                    $task->addMedia($file)
                        ->usingFileName($name)
                        ->toMediaCollection("task files");
                }
            }

            // Логирование обновления задачи
            activity('task')
                ->causedBy(auth()->user())
                ->performedOn($task)
                ->withProperties([
                    'before' => $originalTask,
                    'after' => $task->getChanges(), // Данные, которые изменились
                ])
                ->log('Задача обновлена');

            DB::commit();

            return redirect()->route('task.index')->with('success', __('app.label.updated_successfully', ['name' => $task->name]));
        } catch (\Throwable $th) {
            DB::rollback();

            // Логирование ошибки
            activity('task')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'task_id' => $task->id,
                ])
                ->log('Ошибка при обновлении задачи');

            return back()->with('error', __('app.label.updated_error', ['name' => $task->name]) . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        DB::beginTransaction();

        try {
            $taskName = $task->name;
            $task->clearMediaCollection('task files');
            $task->delete();
            activity('task')
                ->causedBy(auth()->user())
                ->performedOn($task)
                ->withProperties([
                    'task_id' => $task->id,
                    'name' => $taskName,
                ])
                ->log('Удалена задача');

            DB::commit();

            return redirect()->route('task.index')->with('success', __('app.label.deleted_successfully', ['name' => $taskName]));
        } catch (\Throwable $th) {
            DB::rollback();
            activity('task')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'task_id' => $task->id,
                ])
                ->log('Ошибка при удалении задачи');

            return back()->with('error', __('app.label.deleted_error', ['name' => $task->name]) . $th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try {
            $tasks = Task::whereIn('id', $request->id)->get();
            $deletedTasks = []; // Массив для хранения успешно удаленных задач

            foreach ($tasks as $task) {
                $taskName = $task->name; // Сохраняем имя задачи для логов
                $task->clearMediaCollection('task files');
                $task->delete();

                // Добавляем задачу в список успешно удаленных
                $deletedTasks[] = [
                    'task_id' => $task->id,
                    'name' => $taskName,
                ];
            }

            // Логирование массового удаления задач
            activity('task')
                ->causedBy(auth()->user())
                ->withProperties([
                    'deleted_tasks' => $deletedTasks,
                ])
                ->log('Массовое удаление задач');

            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.tasks')]));
        } catch (\Throwable $th) {
            // Логирование ошибки при массовом удалении
            activity('task')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'task_ids' => $request->id,
                ])
                ->log('Ошибка при массовом удалении задач');

            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.tasks')]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $tasks = Task::whereIn('id', $request->id)->get();
            $deletedTasks = [];

            foreach ($tasks as $task) {
                $deletedTasks[] = [
                    'task_id' => $task->id,
                    'name' => $task->name,
                ];
                $task->clearMediaCollection('task files');
                $task->delete();
            }
            activity('task')
                ->causedBy(auth()->user())
                ->withProperties([
                    'deleted_tasks' => $deletedTasks,
                ])
                ->log('Массовое удаление задач');

            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.tasks')]));
        } catch (\Throwable $th) {
            activity('task')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'task_ids' => $request->id,
                ])
                ->log('Ошибка при массовом удалении задач');

            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.tasks')]) . $th->getMessage());
        }
    }

}
