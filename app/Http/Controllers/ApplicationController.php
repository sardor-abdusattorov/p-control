<?php

namespace App\Http\Controllers;

use App\Http\Requests\Application\ApplicationIndexRequest;
use App\Http\Requests\Application\ApplicationStoreRequest;
use App\Http\Requests\Application\ApplicationUpdateRequest;
use App\Models\Application;
use App\Models\Approvals;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Project;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                'update application' => ['application.edit', 'application.update', 'application.remove-approver', 'application.update-approvers'],
                'delete application' => ['application.destroy', 'application.destroy-bulk'],
                'view application' => ['application.index', 'application.show'],
                'application chat' => ['application.chat', 'application.send-message', 'application.get-messages', 'application.get-all-chats'],
                'approve application' => ['application.approve'],
            ];

            foreach ($permissions as $permission => $routes) {
                if ($user->can($permission)) {
                    foreach ($routes as $route) {
                        if ($request->routeIs($route)) {
                            if ($permission === 'update application') {
                                $application = $request->route('application');
                                if ($user->hasRole('superadmin')) {
                                    return $next($request);
                                }
                                if (!$application || $application->user_id !== $user->id) {
                                    return redirect()->route('dashboard')->with('error', __('app.deny_access'));
                                }
                            }

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

        $types = Application::getTypes();
        $statuses = Application::getStatuses();
        $projects = Project::all();

        $applications = Application::query()->with(['user', 'project']);

        if (!$user->can('view all applications')) {
            $applications->where('user_id', $user->id);
            $users = User::where('id', $user->id)->get();
        } else {
            $users = User::where('status', 1)->get();
        }

        if ($request->filled('title')) {
            $applications->where('title', 'LIKE', "%" . $request->title . "%");
        }
        if ($request->filled('project_id')) {
            $applications->where('project_id', $request->project_id);
        }
        if ($request->filled('user_id')) {
            $applications->where('user_id', $request->user_id);
        }
        if ($request->filled('status_id')) {
            $applications->whereIn('status_id', (array) $request->status_id);
        }
        if ($request->filled('type')) {
            $applications->whereIn('type', (array) $request->type);
        }

        $sortableFields = ['title', 'user_id', 'project_id', 'status_id', 'type'];
        if ($request->filled('field') && in_array($request->field, $sortableFields) && in_array($request->order, ['asc', 'desc'])) {
            $applications->orderBy($request->field, $request->order);
        }

        $perPage = $request->input('perPage', 10);

        return Inertia::render('Application/Index', [
            'title'        => __('app.label.applications'),
            'filters'      => $request->only(['title', 'field', 'order', 'project_id', 'user_id', 'status_id', 'type']),
            'perPage'      => (int) $perPage,
            'applications' => $applications->paginate($perPage),
            'statuses'     => $statuses,
            'types'        => $types,
            'users'        => $users,
            'projects'     => $projects,
            'breadcrumbs'  => [['label' => __('app.label.applications'), 'href' => route('application.index')]],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::all();
        $recipients = Recipient::where('user_id', auth()->id())->get();
        $users = User::where('id', '!=', auth()->id())->where('status', 1)->get();
        $types = Application::getTypes();

        return Inertia::render('Application/Create', [
            'title' => __('app.label.applications'),
            'projects' => $projects,
            'recipients' => $recipients,
            'users' => $users,
            'types' => $types,
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
            $application->type = $request->type;
            $application->save();

            activity('application')
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties([
                    'title' => $application->title,
                    'project_id' => $application->project_id,
                    'user_id' => $application->user_id,
                ])
                ->log('Создана заявка');

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ext = $file->extension();
                    $name = Str::random(24) . '.' . $ext;
                    $application->addMedia($file)
                        ->usingFileName($name)
                        ->toMediaCollection("documents");
                }
            }

            if (!empty($request->recipients)) {
                foreach ($request->recipients as $recipient) {

                    Approvals::create([
                        'approvable_type' => Application::class,
                        'approvable_id' => $application->id,
                        'user_id' => $recipient,
                        'approved' => false,
                        'approved_at' => null,
                    ]);

                    $chat = new Chat();
                    $chat->model_type = 'application';
                    $chat->model_id = $application->id;
                    $chat->user_id = auth()->id();
                    $chat->receiver_id = $recipient;
                    $chat->name = 'Chat for application #' . $application->id;

                    if ($chat->save()) {
                        $messageContent = 'Отправляю заявку на ваше рассмотрение';
                        $message = Message::create([
                            'chat_id' => $chat->id,
                            'user_id' => auth()->id(),
                            'text' => $messageContent,
                            'created_date' => now(),
                            'is_notified' => 0
                        ]);
                    } else {
                        DB::rollback();
                        return redirect()->back()->with('error', __('app.label.chat_creation_failed'));
                    }
                }
            }

            DB::commit();

            return redirect()->route('application.index')
                ->with('success', __('app.label.created_successfully', ['name' => $application->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            activity('application')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                ])
                ->log('Ошибка при создании заявки');
            return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.application')]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        $application->load(['user']);
        $project = Project::where([
            'id' => $application->project_id,
        ])->first();
        $files = $application->getMedia('documents');
        $statuses = Application::getStatuses();
        $user = auth()->user();
        $users = User::where('id', '!=', auth()->id())
            ->where('status', 1)
            ->get();
        $types = Application::getTypes();

        $approvals = Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->with('user')
            ->get()
            ->map(function ($approval) {
                return [
                    'user_id' => $approval->user_id,
                    'user_name' => optional($approval->user)->name,
                    'approved' => (bool) $approval->approved,
                    'approved_at' => optional($approval->approved_at)->format('d.m.Y H:i'),
                ];
            });

        $canApprove = Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', $user->id)
            ->where('approved', false)
            ->exists();

        return Inertia::render('Application/Show', [
            'title' => $application->title,
            'project' => $project,
            'statuses' => $statuses,
            'application' => $application,
            'users' => $users,
            'types' => $types,
            'approvals' => $approvals,
            'can_approve' => $canApprove,
            'files' => $files,
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $application->title]
            ],
        ]);
    }

    public function confirmApplication(Request $request, Application $application)
    {
        try {
            $user = auth()->user();
            $approval = Approvals::where('approvable_type', Application::class)
                ->where('approvable_id', $application->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$approval) {
                return redirect()->back()->with('error', __('app.label.not_recipient'));
            }

            if ($approval->approved) {
                return redirect()->back()->with('error', __('app.label.already_approved'));
            }

            $approval->update([
                'approved' => true,
                'approved_at' => now(),
            ]);

            activity('application')
                ->causedBy($user)
                ->performedOn($application)
                ->withProperties([
                    'contract_id' => $application->id,
                    'title' => $application->title,
                    'approved_by' => $user->id,
                    'approved_at' => now()->format('d.m.Y H:i'),
                ])
                ->log('Пользователь подтвердил заявку');

            $totalApprovals = Approvals::where('approvable_type', Application::class)
                ->where('approvable_id', $application->id)
                ->where('approved', true)
                ->count();

            $totalRecipients = Approvals::where('approvable_type', Application::class)
                ->where('approvable_id', $application->id)
                ->count();

            if ($application->status_id == 1) {
                $application->update(['status_id' => 2]);

                activity('application')
                    ->causedBy($user)
                    ->performedOn($application)
                    ->withProperties([
                        'application_id' => $application->id,
                        'previous_status' => 1,
                        'new_status' => 2,
                    ])
                    ->log('Статус заявки изменен на "в процессе"');
            }

            // 🛑 Проверяем, чтобы минимум 2 человека одобрили
            if ($totalApprovals >= $totalRecipients) {
                if ($totalApprovals < 2) {
                    return redirect()->back()->with('warning', __('app.label.minimum_two_approvals_required'));
                }

                $application->update(['status_id' => 3]);

                activity('application')
                    ->causedBy($user)
                    ->performedOn($application)
                    ->withProperties([
                        'application_id' => $application->id,
                        'previous_status' => 2,
                        'new_status' => 3,
                    ])
                    ->log('Заявка полностью одобрена');
            }

            return redirect()->route('application.show', $application->id)
                ->with('success', __('app.label.updated_successfully', ['name' => $application->title]));

        } catch (\Exception $e) {
            activity('application')
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties([
                    'error' => $e->getMessage(),
                    'application_id' => $application->id,
                    'title' => $application->title,
                ])
                ->log('Ошибка при подтверждении заявки');

            return redirect()->back()->with('error', __('app.label.updated_error', ['name' => $application->title]));
        }
    }



    public function chat(Application $application)
    {
        $users = User::where('status', 1)->get();
        $currentUser = auth()->user();

        $chats = Chat::where('model_type', 'application')
            ->where('model_id', $application->id)
            ->where(function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id)
                    ->orWhere('receiver_id', $currentUser->id);
            })
            ->with(['messages.media'])
            ->get();

        return Inertia::render('Application/Chat', [
            'title' => __('app.label.applications'),
            'users' => $users,
            'chats' => $chats,
            'application' => $application,
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $application->title, 'href' => route('application.show', $application->id)],
                ['label' => __('app.label.application_chat')],
            ],
        ]);
    }

    public function getAllChats(Request $request, $applicationId)
    {
        $currentUser = auth()->user();

        try {
            $chats = Chat::with(['messages' => function ($query) {
                $query->latest('created_date')->limit(1)->with('media');
            }])
                ->where('model_type', 'application')
                ->where('model_id', $applicationId)
                ->where(function ($query) use ($currentUser) {
                    $query->where('user_id', $currentUser->id)
                        ->orWhere('receiver_id', $currentUser->id);
                })
                ->get();

            return response()->json(['chats' => $chats]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при загрузке чатов'], 500);
        }
    }

    public function getMessages(Request $request, $chat_id)
    {
        $messages = Message::where('chat_id', $chat_id)
            ->with('media')
            ->orderBy('created_date', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request, Application $application)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'files.*' => 'file|mimes:jpg,png,pdf,docx|max:2048',
        ]);

        DB::beginTransaction();

        try {
            if (!empty($request['chat_id'])) {

                $chat = Chat::findOrFail($request['chat_id']);
            } else {

                $chat = Chat::create([
                    'model_type' => 'application',
                    'model_id' => $application->id,
                    'user_id' => auth()->id(),
                    'receiver_id' => $request['receiver_id'],
                    'name' => 'test',
                ]);
            }

            $message = Message::create([
                'chat_id' => $chat->id,
                'user_id' => auth()->id(),
                'text' => $validated['message'],
                'created_date' => now(),
                'is_notified' => 0,
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $message->addMedia($file)
                        ->toMediaCollection('message file');
                }
            }

            DB::commit();

            return redirect()->route('application.chat', [
                'id' => $chat->id,
                'application' => $application->id,
            ])->with('success', 'Message sent successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return back()->with('error', 'An error occurred. Please try again later.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Application $application)
    {
        $types = Application::getTypes();
        $projects = Project::all();
        $files = $application->getMedia('documents');
        $recipients = Recipient::where('user_id', auth()->id())->get();

        $users = User::where('id', '!=', auth()->id())
            ->where('status', 1)
            ->get();

        return Inertia::render('Application/Edit', [
            'title' => $application->title,
            'projects' => $projects,
            'recipients' => $recipients,
            'users' => $users,
            'types' => $types,
            'application' => $application,
            'files' => $files,
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $application->id, 'href' => route('application.show', $application->id)],
                ['label' => __('app.label.edit')]
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
                'type' => $request->input('type'),
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

            activity('application')
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties([
                    'updated_fields' => $request->only(['title', 'project_id']),
                    'application_id' => $application->id,
                ])
                ->log('Обновлена заявка');

            DB::commit();

            return redirect()->route('application.index')->with('success', __('app.label.updated_successfully', ['name' => $application->title]));
        } catch (\Throwable $th) {
            DB::rollback();

            // Логирование ошибки
            activity('application')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'application_id' => $application->id,
                ])
                ->log('Ошибка при обновлении заявки');

            return back()->with('error', __('app.label.updated_error', ['name' => $application->title]) . $th->getMessage());
        }
    }


    public function destroy(Application $application)
    {
        DB::beginTransaction();

        try {
            $application->clearMediaCollection('documents');
            $application->delete();

            // Логирование удаления
            activity('application')
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties([
                    'application_id' => $application->id,
                    'title' => $application->title,
                ])
                ->log('Удалена заявка');

            DB::commit();

            return redirect()->route('application.index')->with('success', __('app.label.deleted_successfully', ['name' => $application->title]));
        } catch (\Throwable $th) {
            DB::rollback();

            // Логирование ошибки
            activity('application')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'application_id' => $application->id,
                ])
                ->log('Ошибка при удалении заявки');

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
                activity('application')
                    ->causedBy(auth()->user())
                    ->performedOn($application)
                    ->withProperties([
                        'application_id' => $application->id,
                        'title' => $application->title,
                    ])
                    ->log('Удалена заявка (bulk)');
            }

            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.applications')]));
        } catch (\Throwable $th) {
            activity('application')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'application_ids' => $request->id,
                ])
                ->log('Ошибка при массовом удалении заявок');

            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.applications')]) . $th->getMessage());
        }
    }

    public function removeApprover(Request $request, Application $application)
    {
        if (!$request->has('user_id')) {
            return redirect()->back()->with('error', __('app.label.deleted_error', [
                'name' => __('app.label.unknown_user')
            ]));
        }
        $userId = $request->user_id;
        $user = User::find($userId);

        $approval = Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', $userId)
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', __('app.label.not_found', [
                'name' => __('app.label.approver')
            ]));
        }

        if ($approval->approved) {
            return redirect()->back()->with('warning', __('app.label.cannot_delete_approved', [
                'name' => $user ? $user->name : __('app.label.unknown_user')
            ]));
        }

        $approval->delete();
        $application->update(['status_id' => 1]);

        return redirect()->route('application.show', ['application' => $application->id])
            ->with('success', __('app.label.deleted_successfully', [
                'name' => $user ? $user->name : __('app.label.unknown_user')
            ]));
    }

    public function updateApprovers(Request $request, Application $application)
    {
        $existingApprovals = Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->get()
            ->keyBy('user_id');

        $newUserIds = collect($request->user_ids);
        $usersToAdd = $newUserIds->diff($existingApprovals->keys());
        $usersToRemove = $existingApprovals->keys()->diff($newUserIds);
        foreach ($usersToAdd as $userId) {
            Approvals::create([
                'approvable_type' => Application::class,
                'approvable_id' => $application->id,
                'user_id' => $userId,
                'approved' => false,
            ]);
        }
        $confirmedUsers = Approvals::whereIn('user_id', $usersToRemove)
            ->where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('approved', true)
            ->exists();

        if ($confirmedUsers) {
            return redirect()->route('application.show', ['application' => $application->id])
                ->with('warning', __('app.label.cannot_delete_approved_list'));
        }
        Approvals::whereIn('user_id', $usersToRemove)
            ->where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('approved', false)
            ->delete();

        return redirect()->route('application.show', ['application' => $application->id])
            ->with('success', __('app.label.approvers_updated_successfully'));
    }

}
