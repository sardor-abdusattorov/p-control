<?php

namespace App\Http\Controllers;

use App\Http\Requests\Application\ApplicationApproversUpdateRequest;
use App\Http\Requests\Application\ApplicationIndexRequest;
use App\Http\Requests\Application\ApplicationStoreRequest;
use App\Http\Requests\Application\ApplicationUpdateRequest;
use App\Http\Requests\Application\ApplicationUserDeleteRequest;
use App\Http\Requests\Application\ScanFileUploadRequest;
use App\Models\Application;
use App\Models\Approvals;
use App\Models\Currency;
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
                'submit application' => ['application.submit'],
                'update application' => ['application.edit', 'application.update', 'application.remove-approver', 'application.update-approvers', 'application.upload-scan', 'application.upload-scan.store'],
                'delete application' => ['application.destroy', 'application.destroy-bulk'],
                'view application' => ['application.index', 'application.show'],
                'approve application' => ['application.approve', 'application.cancel'],
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
        if ($user->can('view all applications') || $user->can('approve application')) {
            $users = User::where('status', 1)
                ->when(
                    $user->can('approve application') && !$user->can('view all applications'),
                    fn($q) => $q->where('id', '!=', $user->id)
                )
                ->get();

            $applications->when(
                $user->can('approve application') && !$user->can('view all applications'),
                function ($query) {
                    $query->where(function ($q) {
                        $q->where('type', '!=', Application::TYPE_REQUEST)
                            ->orWhere(function ($q2) {
                                $q2->where('type', Application::TYPE_REQUEST)
                                    ->where('status_id', '!=', Application::STATUS_NEW);
                            });
                    });
                }
            );
        } else {
            $applications->where('user_id', $user->id);
            $users = User::where('id', $user->id)->get();
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
        } else {
            $applications->latest('updated_at');
        }
        $perPage = $request->input('perPage', 10);
        $applications = $applications->paginate($perPage)->appends($request->only(['title', 'field', 'order', 'project_id', 'user_id', 'status_id', 'type']));

        return Inertia::render('Application/Index', [
            'title'        => __('app.label.applications'),
            'filters'      => $request->only(['title', 'field', 'order', 'project_id', 'user_id', 'status_id', 'type']),
            'perPage'      => (int) $perPage,
            'applications' => $applications,
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
        $currency = Currency::where(['status' => 1])->get();
        $projects = Project::all();
        $recipients = Recipient::where('user_id', auth()->id())->get();
        $users = User::approverOptions();
        $types = Application::getTypes();

        return Inertia::render('Application/Create', [
            'title' => __('app.label.applications'),
            'projects' => $projects,
            'recipients' => $recipients,
            'currency' => $currency,
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
            $application = Application::create([
                'title' => $request->title,
                'project_id' => $request->project_id,
                'currency_id' => $request->currency_id,
                'user_id' => auth()->id(),
                'status_id' => Application::STATUS_NEW,
                'type' => $request->type,
            ]);
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
                        ->toMediaCollection('documents');
                }
            }
            if (!empty($request->recipients)) {
                foreach ($request->recipients as $recipientId) {
                    $application->approvals()->create([
                        'user_id' => $recipientId,
                        'approved' => Approvals::STATUS_NEW,
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('application.index')
                ->with('success', __('app.label.created_successfully', ['name' => $application->title]));
        } catch (\Throwable $th) {
            DB::rollBack();
            activity('application')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                ])
                ->log('Ошибка при создании заявки');

            return redirect()->back()->with(
                'error',
                __('app.label.created_error', ['name' => __('app.label.application')]) . ' ' . $th->getMessage()
            );
        }
    }

    public function submit(Application $application)
    {
        $user = auth()->user();

        if (!$user->can('submit application')) {
            abort(403, __('app.label.permission_denied'));
        }

        if ($application->status_id !== 1) {
            return redirect()->back()->with('error', __('app.label.cannot_submit_non_draft'));
        }

        DB::beginTransaction();

        try {
            $application->update(['status_id' => 2]);

            $application->approvals()
                ->where('approved', Approvals::STATUS_NEW)
                ->update([
                    'approved' => Approvals::STATUS_PENDING,
                    'approved_at' => null,
                ]);

            activity('application')
                ->causedBy($user)
                ->performedOn($application)
                ->log('Заявка отправлена на согласование');

            DB::commit();

            return back()->with('success', __('app.label.submitted_successfully'));

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('app.label.submit_failed') . ' ' . $th->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        $application->load(['user', 'currency']);
        $project = Project::find($application->project_id);
        $files = $application->getMedia('documents');
        $scans = $application->getMedia('scans');
        $statuses = Application::getStatuses();

        $user = auth()->user();
        $users = User::approverOptions();
        $types = Application::getTypes();

        $approvals = $application->getFormattedApprovals();

        $canApprove = Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', $user->id)
            ->where('approved', Approvals::STATUS_PENDING)
            ->exists();

        return Inertia::render('Application/Show', [
            'title' => $application->title,
            'project' => $project,
            'statuses' => $statuses,
            'application' => $application,
            'users' => $users,
            'types' => $types,
            'scans' => $scans,
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
        $user = auth()->user();

        $approval = Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', $user->id)
            ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', __('app.label.not_recipient'));
        }

        if (in_array($approval->approved, [Approvals::STATUS_APPROVED, Approvals::STATUS_REJECTED])) {
            return redirect()->back()->with('error', __('app.label.already_approved'));
        }

        $approval->update([
            'approved' => Approvals::STATUS_APPROVED,
            'approved_at' => now(),
            'reason' => $request->input('comment'),
        ]);

        $this->checkAndUpdateApplicationStatus($application);

        activity('application')
            ->causedBy($user)
            ->performedOn($application)
            ->withProperties([
                'application_id' => $application->id,
                'title' => $application->title,
                'approved_by' => $user->id,
                'approved_at' => now()->format('d.m.Y H:i'),
            ])
            ->log('Пользователь подтвердил заявку');

        return redirect()->route('application.show', $application->id)
            ->with('success', __('app.label.updated_successfully', ['name' => $application->title]));
    }

    public function cancelApplication(Request $request, Application $application)
    {
        try {
            $user = auth()->user();

            // Найти approval текущего пользователя
            $approval = Approvals::where('approvable_type', Application::class)
                ->where('approvable_id', $application->id)
                ->where('user_id', $user->id)
                ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
                ->first();

            if (!$approval) {
                return redirect()->back()->with('error', __('app.label.not_recipient'));
            }

            if ($approval->approved === Approvals::STATUS_REJECTED && $approval->reason) {
                return redirect()->back()->with('error', __('app.label.already_rejected'));
            }

            // Обновляем текущего согласующего
            $approval->update([
                'approved' => Approvals::STATUS_REJECTED,
                'reason' => $request->input('reason'),
                'approved_at' => now(),
            ]);

            // Лог: пользователь отказал
            activity('application')
                ->causedBy($user)
                ->performedOn($application)
                ->withProperties([
                    'application_id' => $application->id,
                    'reason' => $request->input('reason'),
                    'rejected_by' => $user->id,
                    'rejected_at' => now()->format('d.m.Y H:i'),
                ])
                ->log('Пользователь отклонил заявку');

            // Обновляем статус заявки
            if ($application->status_id !== Application::STATUS_NEW) {
                $application->update(['status_id' => Application::STATUS_REJECTED]);

                activity('application')
                    ->causedBy($user)
                    ->performedOn($application)
                    ->withProperties([
                        'application_id' => $application->id,
                        'previous_status' => $application->status_id,
                        'new_status' => Application::STATUS_REJECTED,
                    ])
                    ->log('Заявка отклонена после отказа согласующего');
            }

            // Массово отклоняем остальных согласующих
            Approvals::where('approvable_type', Application::class)
                ->where('approvable_id', $application->id)
                ->where('user_id', '!=', $user->id)
                ->where('approved', Approvals::STATUS_NEW)
                ->update([
                    'approved' => Approvals::STATUS_REJECTED,
                    'reason' => 'Автоматически отклонено после отказа одного из согласующих',
                    'approved_at' => now(),
                ]);

            return redirect()->route('application.show', $application->id)
                ->with('success', __('app.label.cancelled_successfully', ['name' => $application->title]));

        } catch (\Exception $e) {
            activity('application')
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties([
                    'error' => $e->getMessage(),
                    'application_id' => $application->id,
                    'title' => $application->title,
                ])
                ->log('Ошибка при отклонении заявки');

            return redirect()->back()->with('error', __('app.label.cancel_error', ['name' => $application->title]));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Application $application)
    {

        if ($application->status_id === Application::STATUS_APPROVED) {
            return redirect()->route('application.show', $application->id)
                ->with('error', __('app.label.cannot_edit_approved'));
        }

        $currency = Currency::where(['status' => 1])->get();
        $types = Application::getTypes();
        $projects = Project::all();
        $files = $application->getMedia('documents');
        $recipients = Recipient::where('user_id', auth()->id())->get();
        $users = User::approverOptions();

        return Inertia::render('Application/Edit', [
            'title' => $application->title,
            'projects' => $projects,
            'recipients' => $recipients,
            'users' => $users,
            'currency' => $currency,
            'types' => $types,
            'application' => $application,
            'files' => $files,
            'approval_user_ids' => $application->approvals()
                ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
                ->pluck('user_id')
                ->toArray(),
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $application->id, 'href' => route('application.show', $application->id)],
                ['label' => __('app.label.edit')]
            ],
        ]);
    }

    public function uploadScan(Application $application)
    {
        if (
            $application->status_id !== Application::STATUS_APPROVED ||
            $application->type == Application::TYPE_MEMO
        ) {
            abort(403, 'Доступ запрещён.');
        }
        $scans = $application->getMedia('scans');

        return Inertia::render('Application/UploadScan', [
            'title' => $application->title,
            'application' => $application,
            'scans' => $scans,
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $application->id, 'href' => route('application.show', $application->id)],
                    ['label' => __('app.label.upload_scan')],
            ],
        ]);
    }

    public function uploadScanFiles(ScanFileUploadRequest $request, Application $application)
    {
        if ($application->status_id !== Application::STATUS_APPROVED || $application->type == Application::TYPE_MEMO) {
            return redirect()->back()->with('error', __('app.label.cannot_upload_scan'));
        }

        foreach ($request->file('files', []) as $file) {
            $name = Str::random(24) . '.' . $file->getClientOriginalExtension();

            $application->addMedia($file)
                ->usingFileName($name)
                ->toMediaCollection('scans');
        }
        activity('application')
            ->causedBy(auth()->user())
            ->performedOn($application)
            ->withProperties([
                'application_id' => $application->id,
                'uploaded_files' => collect($request->file('files'))->pluck('name'),
            ])
            ->log('Загружены скан-копии в одобренную заявку');

        return redirect()->route('application.show', $application->id)
            ->with('success', __('app.label.scans_uploaded_successfully'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(ApplicationUpdateRequest $request, Application $application)
    {
        DB::beginTransaction();
        try {
            $isNew = $application->status_id === Application::STATUS_NEW;

            if ($application->status_id === Application::STATUS_APPROVED) {
                return back()->with('error', __('app.label.cannot_update_approved'));
            }

            if ($isNew) {
                $application->approvals()->delete();

                foreach ($request->recipients ?? [] as $recipientId) {
                    $application->approvals()->create([
                        'user_id' => $recipientId,
                        'approved' => Approvals::STATUS_NEW,
                    ]);
                }
            } else {
                $application->approvals()->update(['approved' => Approvals::STATUS_INVALIDATED]);

                if ($request->input('type') != 2) {
                    foreach ($request->recipients ?? [] as $recipientId) {
                        $application->approvals()->create([
                            'user_id' => $recipientId,
                            'approved' => Approvals::STATUS_NEW,
                        ]);
                    }
                }
            }

            $application->update([
                'title' => $request->input('title'),
                'project_id' => $request->input('project_id'),
                'type' => $request->input('type'),
                'currency_id' => $request->input('currency_id'),
                'status_id' => Application::STATUS_NEW,
            ]);

            if ($request->filled('deleted_old_file_ids')) {
                foreach ($request->input('deleted_old_file_ids') as $fileId) {
                    $media = $application->media()->where('id', $fileId)->first();
                    if ($media) {
                        $media->delete();
                    }
                }
            }

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
                    'updated_fields' => $request->only(['title', 'project_id', 'type']),
                    'application_id' => $application->id,
                ])
                ->log('Обновлена заявка');

            DB::commit();

            return redirect()->route('application.index')
                ->with('success', __('app.label.updated_successfully', ['name' => $application->title]));

        } catch (\Throwable $th) {
            DB::rollBack();

            activity('application')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'application_id' => $application->id,
                ])
                ->log('Ошибка при обновлении заявки');

            return back()->with('error', __('app.label.updated_error', ['name' => $application->title]) . ' ' . $th->getMessage());
        }
    }


    public function destroy(Application $application)
    {
        if (
            $application->type == Application::TYPE_REQUEST &&
            $application->status_id !== Application::STATUS_NEW
        ) {
            return back()->with('error', __('app.label.cannot_delete_approved_request'));
        }

        $hasNonNewApprovals = $application->approvals()
            ->where('approved', '!=', Approvals::STATUS_NEW)
            ->exists();

        if ($hasNonNewApprovals) {
            return back()->with('error', __('app.label.cannot_delete_has_progress'));
        }

        DB::beginTransaction();

        try {
            $application->approvals()->delete();
            $application->clearMediaCollection('documents');
            $application->clearMediaCollection('scans');
            $application->delete();

            activity('application')
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties([
                    'application_id' => $application->id,
                    'title' => $application->title,
                ])
                ->log('Удалена заявка и её черновики согласований');

            DB::commit();

            return redirect()->route('application.index')
                ->with('success', __('app.label.deleted_successfully', ['name' => $application->title]));
        } catch (\Throwable $th) {
            DB::rollBack();

            activity('application')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'application_id' => $application->id,
                ])
                ->log('Ошибка при удалении заявки');

            return back()->with('error', __('app.label.deleted_error', ['name' => $application->title]) . ' ' . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('superadmin')) {
            abort(403, __('app.label.permission_denied'));
        }

        try {
            $applications = Application::whereIn('id', $request->id)->get();

            foreach ($applications as $application) {
                $application->approvals()->delete();

                $application->clearMediaCollection('documents');
                $application->clearMediaCollection('scans');

                $application->delete();

                activity('application')
                    ->causedBy($user)
                    ->performedOn($application)
                    ->withProperties([
                        'application_id' => $application->id,
                        'title' => $application->title,
                    ])
                    ->log('Удалена заявка (bulk)');
            }

            return back()->with('success', __('app.label.deleted_successfully', [
                'name' => count($request->id) . ' ' . __('app.label.applications')
            ]));
        } catch (\Throwable $th) {
            activity('application')
                ->causedBy($user)
                ->withProperties([
                    'error' => $th->getMessage(),
                    'application_ids' => $request->id,
                ])
                ->log('Ошибка при массовом удалении заявок');

            return back()->with('error', __('app.label.deleted_error', [
                    'name' => count($request->id) . ' ' . __('app.label.applications')
                ]) . ' ' . $th->getMessage());
        }
    }

    public function removeApprover(ApplicationUserDeleteRequest $request, Application $application)
    {
        if (!$request->has('user_id')) {
            return redirect()->back()->with('error', __('app.label.deleted_error', [
                'name' => __('app.label.unknown_user')
            ]));
        }

        $userId = $request->user_id;
        $user = User::find($userId);

        $approval = Approvals::valid()
            ->where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', $userId)
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', __('app.label.not_found', [
                'name' => __('app.label.approver')
            ]));
        }
        if ($application->status_id !== 1) {
            if ($approval->approved === Approvals::STATUS_APPROVED) {
                return redirect()->back()->with('warning', __('app.label.cannot_delete_approved', [
                    'name' => $user?->name ?? __('app.label.unknown_user')
                ]));
            }
        }

        $approval->delete();

        return redirect()->route('application.show', ['application' => $application->id])
            ->with('success', __('app.label.deleted_successfully', [
                'name' => $user?->name ?? __('app.label.unknown_user')
            ]));
    }

    public function updateApprovers(ApplicationApproversUpdateRequest $request, Application $application)
    {
        $validated = $request->validated();
        $newUserIds = collect($validated['user_ids']);

        $existingApprovals = Approvals::valid()
            ->where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->get()
            ->keyBy('user_id');

        $usersToAdd = $newUserIds->diff($existingApprovals->keys());
        $usersToRemove = $existingApprovals->keys()->diff($newUserIds);

        foreach ($usersToAdd as $userId) {
            Approvals::create([
                'approvable_type' => Application::class,
                'approvable_id'   => $application->id,
                'user_id'         => $userId,
                'approved'        => $application->status_id === Application::STATUS_NEW
                    ? Approvals::STATUS_NEW
                    : Approvals::STATUS_PENDING,
            ]);
        }

        $deletableStatuses = match ($application->status_id) {
            Application::STATUS_NEW => [Approvals::STATUS_NEW],
            Application::STATUS_IN_PROGRESS => [Approvals::STATUS_PENDING],
            default => [],
        };

        if (!empty($deletableStatuses)) {
            Approvals::whereIn('user_id', $usersToRemove)
                ->where('approvable_type', Application::class)
                ->where('approvable_id', $application->id)
                ->whereIn('approved', $deletableStatuses)
                ->delete();
        }

        return redirect()->route('application.show', ['application' => $application->id])
            ->with('success', __('app.label.approvers_updated_successfully'));
    }

    protected function checkAndUpdateApplicationStatus(Application $application)
    {
        $approvals = $application->approvals()
            ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
            ->get();
        if ($approvals->where('approved', Approvals::STATUS_REJECTED)->isNotEmpty()) {
            $application->update(['status_id' => Application::STATUS_REJECTED]);
            return;
        }
        if ($approvals->every(fn($a) => $a->approved === Approvals::STATUS_APPROVED)) {
            $application->update(['status_id' => Application::STATUS_APPROVED]);
            return;
        }
    }


}
