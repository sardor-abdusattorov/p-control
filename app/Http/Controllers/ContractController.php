<?php

namespace App\Http\Controllers;

use Altwaireb\World\Models\Country;
use App\Http\Requests\Contract\ContractApproversUpdateRequest;
use App\Http\Requests\Contract\ContractIndexRequest;
use App\Http\Requests\Contract\ContractScanRequest;
use App\Http\Requests\Contract\ContractStoreRequest;
use App\Http\Requests\Contract\ContractUpdateRequest;
use App\Http\Requests\Contract\ContractUserDeleteRequest;
use App\Models\Application;
use App\Models\Approvals;

use App\Models\Contact;
use App\Models\ContactCategory;
use App\Models\ContactSubcategory;
use App\Models\Contract;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login');
            }

            $permissions = [
                'create contract' => ['contract.create', 'contract.store'],
                'submit contract' => ['contract.submit'],
                'update contract' => ['contract.edit', 'contract.update', 'contract.remove-approver', 'contract.update-approvers', 'contract.upload-scan', 'contract.upload-scan.store'],
                'delete contract' => ['contract.destroy', 'contract.destroy-bulk'],
                'view contract' => ['contract.index', 'contract.show'],
                'approve contract' => ['contract.approve', 'application.cancel'],
            ];

            foreach ($permissions as $permission => $routes) {
                if ($user->can($permission)) {
                    foreach ($routes as $route) {
                        if ($request->routeIs($route)) {
                            if ($permission === 'update contract') {
                                $contract = $request->route('contract');
                                if ($user->hasRole('superadmin')) {
                                    return $next($request);
                                }
                                if (!$contract || $contract->user_id !== $user->id) {
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

    public function index(ContractIndexRequest $request)
    {
        $currency = Currency::where(['status' => 1])->get();
        $user = auth()->user();
        $statuses = Contract::getStatuses();
        $contracts = Contract::query()->with(['user', 'currency']);

        if ($user->can('view all contracts') || $user->can('approve contract')) {
            $users = User::where('status', 1)
                ->when(
                    $user->can('approve contract') && !$user->can('view all contracts'),
                    fn($q) => $q->where('id', '!=', $user->id)
                )
                ->get();

            $contracts->when(
                $user->can('approve contract') && !$user->can('view all contracts'),
                function ($query) {
                    $query->where('status', '!=', Contract::STATUS_NEW);
                }
            );
        } else {
            $contracts->where('user_id', $user->id);
            $users = User::where('id', $user->id)->get();
        }

        if ($request->filled('contract_number')) {
            $contracts->where('contract_number', 'LIKE', "%" . $request->contract_number . "%");
        }

        if ($request->filled('title')) {
            $contracts->where('title', 'LIKE', "%" . $request->title . "%");
        }

        if ($request->filled('user_id')) {
            $contracts->where('user_id', (int)$request->user_id);
        }

        if ($request->filled('status')) {
            $contracts->where('status', (int)$request->status);
        }

        if ($request->filled('currency_id')) {
            $contracts->where('currency_id', (int)$request->currency_id);
        }

        if ($request->has(['field', 'order'])) {
            $contracts->orderBy($request->field, $request->order);
        } else {
            $contracts->latest('updated_at');
        }

        $perPage = $request->input('perPage', 10);
        $contracts = $contracts->paginate($perPage)->appends($request->query());

        $contractIds = $contracts->pluck('id');
        $approvals = Approvals::where('approvable_type', Contract::class)
            ->whereIn('approvable_id', $contractIds)
            ->with('user')
            ->get()
            ->groupBy('approvable_id')
            ->map(function ($group) {
                return $group->map(function ($approval) {
                    return [
                        'user_id' => $approval->user_id,
                        'user_name' => optional($approval->user)->name,
                        'approved' => $approval->approved,
                        'approved_at' => optional($approval->approved_at)?->format('d.m.Y H:i'),
                        'updated_at' => optional($approval->updated_at)?->format('d.m.Y H:i'),
                        'reason' => $approval->reason,
                    ];
                });
            });

        return Inertia::render('Contract/Index', [
            'title' => __('app.label.contracts'),
            'filters' => $request->all(['search', 'field', 'order', 'user_id', 'status', 'currency_id', 'contract_number', 'title']),
            'perPage' => (int)$perPage,
            'contracts' => $contracts,
            'statuses' => $statuses,
            'currency' => $currency,
            'users' => $users,
            'approvals' => $approvals,
            'breadcrumbs' => [['label' => __('app.label.contracts'), 'href' => route('contract.index')]],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currency = Currency::where(['status' => 1])->get();
        $recipients = Recipient::where('user_id', auth()->id())->get();
        $projects = Project::all();
        $contacts = Contact::where('owner_id', auth()->id())
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        if (auth()->user()->can('view all applications')) {
            $applications = Application::all();
        } else {
            $applications = auth()->user()->applications;
        }
        $users = User::approverOptions();
        $types = Application::getTypes();

        return Inertia::render('Contract/Create', [
            'title' => __('app.label.contracts'),
            'breadcrumbs' => [
                ['label' => __('app.label.contracts'), 'href' => route('contract.index')],
                ['label' => __('app.label.create')]
            ],
            'currency' => $currency,
            'projects' => $projects,
            'applications' => $applications,
            'users' => $users,
            'application_types' => $types,
            'recipients' => $recipients,
            'contacts'          => $contacts,
            'categories'      => ContactCategory::where('status', 1)->get(),
            'subCategories'   => ContactSubcategory::where('status', 1)->get(),
            'countries'       => Country::select('id', 'name')->orderBy('name')->get(),
            'statuses' => Contact::getStatuses(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContractStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $contract = Contract::create([
                'contract_number' => $request->contract_number,
                'title' => $request->title,
                'project_id' => $request->project_id,
                'application_id' => $request->application_id ?? null,
                'currency_id' => $request->currency_id,
                'user_id' => auth()->id(),
                'budget_sum' => $request->budget_sum,
                'status' => Contract::STATUS_NEW,
                'deadline' => Carbon::parse($request->deadline)
                    ->timezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s'),
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ext = $file->extension();
                    $name = Str::random(24) . '.' . $ext;

                    $contract->addMedia($file)
                        ->usingFileName($name)
                        ->toMediaCollection("files");
                }
            }

            if (!empty($request->recipients)) {
                foreach ($request->recipients as $recipientId) {
                    $contract->approvals()->create([
                        'user_id' => $recipientId,
                        'approved' => Approvals::STATUS_NEW,
                    ]);
                }
            }

            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
                ->withProperties([
                    'contract_id' => $contract->id,
                    'title' => $contract->title,
                    'contract_number' => $contract->contract_number,
                    'project_id' => $contract->project_id,
                    'budget_sum' => $contract->budget_sum,
                    'status' => $contract->status,
                ])
                ->log('Создан контракт');

            DB::commit();

            return redirect()->route('contract.index')
                ->with('success', __('app.label.created_successfully', ['name' => $contract->title]));

        } catch (\Throwable $th) {
            DB::rollBack();

            activity('contract')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'contract_number' => $request->contract_number,
                    'title' => $request->title,
                    'project_id' => $request->project_id,
                ])
                ->log('Ошибка при создании контракта');

            return redirect()->back()->with(
                'error',
                __('app.label.created_error', ['name' => __('app.label.contracts')]) . ' ' . $th->getMessage()
            );
        }
    }

    public function submit(Contract $contract)
    {
        $user = auth()->user();
        if ($contract->status !== 1) {
            return redirect()->back()->with('error', __('app.label.cannot_submit_non_draft'));
        }

        if (!$user->can('submit contract')) {
            abort(403, __('app.label.permission_denied'));
        }

        DB::beginTransaction();
        try {
            $contract->update(['status' => 2]);

            $contract->approvals()
                ->where('approved', Approvals::STATUS_NEW)
                ->update([
                    'approved' => Approvals::STATUS_PENDING,
                    'approved_at' => null,
                ]);

            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
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
    public function show(Contract $contract)
    {
        $types = Application::getTypes();
        $statuses = Contract::getStatuses();
        $users = User::approverOptions();

        $files = $contract->getMedia('files');
        $scans = $contract->getMedia('scans');
        $project = Project::find($contract->project_id);

        $application = Application::with(['media', 'user'])->find($contract->application_id);

        if ($application) {
            $applicationFiles = $application->getMedia('documents');
        } else {
            $applicationFiles = collect();
        }

        $user = auth()->user();

        $approvals = $contract->getFormattedApprovals();

        $applicationApprovals = collect();

        if ($application) {
            $applicationApprovals = Approvals::where('approvable_type', Application::class)
                ->where('approvable_id', $application->id)
                ->with('user')
                ->get()
                ->map(function ($approval) {
                    return [
                        'user_id' => $approval->user_id,
                        'user_name' => optional($approval->user)->name,
                        'approved' => $approval->approved,
                        'approved_at' => optional($approval->approved_at)?->format('d.m.Y H:i'),
                        'updated_at' => optional($approval->updated_at)?->format('d.m.Y H:i'),
                        'reason' => $approval->reason,
                    ];
                });
        }

        $canApprove = Approvals::where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('user_id', $user->id)
            ->where('approved', Approvals::STATUS_PENDING)
            ->exists();

        return Inertia::render('Contract/Show', [
            'title' => $contract->title,
            'files' => $files,
            'scans' => $scans,
            'application_files' => $applicationFiles,
            'users' => $users,
            'statuses' => $statuses,
            'types' => $types,
            'project' => $project,
            'application' => $application,
            'contract' => $contract->load(['user', 'currency']),
            'approvals' => $approvals,
            'application_approvals' => $applicationApprovals,
            'can_approve' => $canApprove,
            'breadcrumbs' => [
                ['label' => __('app.label.contracts'), 'href' => route('contract.index')],
                ['label' => $contract->title]
            ],
        ]);
    }

    public function confirmContract(Request $request, Contract $contract)
    {
        try {
            $user = auth()->user();
            $approval = Approvals::where('approvable_type', Contract::class)
                ->where('approvable_id', $contract->id)
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

            $this->checkAndUpdateContractStatus($contract);

            activity('contract')
                ->causedBy($user)
                ->performedOn($contract)
                ->withProperties([
                    'contract_id' => $contract->id,
                    'title' => $contract->title,
                    'approved_by' => $user->id,
                    'approved_at' => now()->format('d.m.Y H:i'),
                ])
                ->log('Пользователь подтвердил контракт');


            return redirect()->route('contract.show', $contract->id)
                ->with('success', __('app.label.updated_successfully', ['name' => $contract->title]));

        } catch (\Exception $e) {
            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
                ->withProperties([
                    'error' => $e->getMessage(),
                    'contract_id' => $contract->id,
                    'title' => $contract->title,
                ])
                ->log('Ошибка при подтверждении контракта');

            return redirect()->back()->with('error', __('app.label.updated_error', ['name' => $contract->title]));
        }
    }

    public function cancelContract(Request $request, Contract $contract)
    {
        try {
            $user = auth()->user();

            $approval = Approvals::where('approvable_type', Contract::class)
                ->where('approvable_id', $contract->id)
                ->where('user_id', $user->id)
                ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
                ->first();

            if (!$approval) {
                return redirect()->back()->with('error', __('app.label.not_recipient'));
            }

            if ($approval->approved === Approvals::STATUS_REJECTED && $approval->reason) {
                return redirect()->back()->with('error', __('app.label.already_rejected'));
            }

            $approval->update([
                'approved' => Approvals::STATUS_REJECTED,
                'reason' => $request->input('reason'),
                'approved_at' => now(),
            ]);

            activity('contract')
                ->causedBy($user)
                ->performedOn($contract)
                ->withProperties([
                    'contract_id' => $contract->id,
                    'reason' => $request->input('reason'),
                    'rejected_by' => $user->id,
                    'rejected_at' => now()->format('d.m.Y H:i'),
                ])
                ->log('Пользователь отклонил контракт');

            if ($contract->status !== Contract::STATUS_NEW) {
                $contract->update(['status' => Contract::STATUS_REJECTED]);

                activity('contract')
                    ->causedBy($user)
                    ->performedOn($contract)
                    ->withProperties([
                        'contract_id' => $contract->id,
                        'previous_status' => $contract->status,
                        'new_status' => Contract::STATUS_REJECTED,
                    ])
                    ->log('Контракт отклонён после отказа согласующего');
            }

            return redirect()->route('contract.show', $contract->id)
                ->with('success', __('app.label.cancelled_successfully', ['name' => $contract->title]));

        } catch (\Exception $e) {
            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
                ->withProperties([
                    'error' => $e->getMessage(),
                    'contract_id' => $contract->id,
                    'title' => $contract->title,
                ])
                ->log('Ошибка при отклонении контракта');

            return redirect()->back()->with('error', __('app.label.cancel_error', ['name' => $contract->title]));
        }
    }


    public function uploadScan(Contract $contract)
    {
        if ($contract->status !== Contract::STATUS_APPROVED) {
            abort(403, 'Доступ запрещён.');
        }
        $scans = $contract->getMedia('scans');

        return Inertia::render('Contract/UploadScan', [
            'title' => $contract->title,
            'contract' => $contract,
            'scans' => $scans,
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $contract->id, 'href' => route('contract.show', $contract->id)],
                ['label' => __('app.label.upload_scan')],
            ],
        ]);
    }

    public function uploadScanFiles(ContractScanRequest $request, Contract $contract)
    {
        if ($contract->status !== Contract::STATUS_APPROVED) {
            return redirect()->back()->with('error', __('app.label.cannot_upload_scan'));
        }

        foreach ($request->file('files', []) as $file) {
            $name = Str::random(24) . '.' . $file->getClientOriginalExtension();

            $contract->addMedia($file)
                ->usingFileName($name)
                ->toMediaCollection('scans');
        }
        activity('contract')
            ->causedBy(auth()->user())
            ->performedOn($contract)
            ->withProperties([
                'application_id' => $contract->id,
                'uploaded_files' => collect($request->file('files'))->pluck('name'),
            ])
            ->log('Загружены скан-копии в одобренную заявку');

        return redirect()->route('contract.show', $contract->id)
            ->with('success', __('app.label.scans_uploaded_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        $types = Application::getTypes();
        $currency = Currency::where(['status' => 1])->get();
        $files = $contract->getMedia('files');
        $projects = Project::all();
        if (auth()->user()->can('view all applications')) {
            $applications = Application::all();
        } else {
            $applications = auth()->user()->applications;
        }
        $users = User::approverOptions();

        return inertia('Contract/Edit', [
            'contract' => $contract,
            'currency' => $currency,
            'projects' => $projects,
            'applications' => $applications,
            'application_types' => $types,
            'approval_user_ids' => $contract->approvals()
                ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
                ->pluck('user_id')
                ->toArray(),
            'users' => $users,
            'files' => $files,
            'title' => __('app.label.contracts'),
            'breadcrumbs' => [
                ['label' => __('app.label.contracts'), 'href' => route('contract.index')],
                ['label' => $contract->title, 'href' => route('contract.show', $contract->id)],
                ['label' => __('app.label.edit')]
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContractUpdateRequest $request, Contract $contract)
    {
        DB::beginTransaction();

        try {
            $isNew = $contract->status === Contract::STATUS_NEW;

            if ($contract->status === Contract::STATUS_APPROVED) {
                return back()->with('error', __('app.label.cannot_update_approved'));
            }

            $originalData = $contract->getOriginal();

            if ($isNew) {
                $contract->approvals()->delete();

                foreach ($request->recipients ?? [] as $recipientId) {
                    $contract->approvals()->create([
                        'user_id' => $recipientId,
                        'approved' => Approvals::STATUS_NEW,
                    ]);
                }
            } else {
                $contract->approvals()->update(['approved' => Approvals::STATUS_INVALIDATED]);

                if ($request->input('type') != 2) {
                    foreach ($request->recipients ?? [] as $recipientId) {
                        $contract->approvals()->create([
                            'user_id' => $recipientId,
                            'approved' => Approvals::STATUS_NEW,
                        ]);
                    }
                }
            }

            $contract->update([
                'contract_number' => $request->contract_number,
                'title' => $request->title,
                'project_id' => $request->project_id,
                'application_id' => $request->application_id,
                'currency_id' => $request->currency_id,
                'budget_sum' => $request->budget_sum,
                'status' => Contract::STATUS_NEW,
                'deadline' => Carbon::parse($request->deadline)->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
            ]);

            if ($request->filled('deleted_old_file_ids')) {
                foreach ($request->input('deleted_old_file_ids') as $fileId) {
                    $media = $contract->media()->where('id', $fileId)->first();
                    if ($media) {
                        $media->delete();
                    }
                }
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ext = $file->extension();
                    $name = Str::random(24) . '.' . $ext;

                    $contract->addMedia($file)
                        ->usingFileName($name)
                        ->toMediaCollection("files");
                }
            }

            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
                ->withProperties([
                    'before' => $originalData,
                    'after' => $contract->getChanges(),
                ])
                ->log('Контракт обновлен');

            DB::commit();

            return redirect()->route('contract.index')->with('success', __('app.label.updated_successfully', ['name' => $contract->title]));
        } catch (\Throwable $th) {
            DB::rollBack();

            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
                ->withProperties([
                    'error' => $th->getMessage(),
                    'contract_id' => $contract->id,
                ])
                ->log('Ошибка при обновлении контракта');

            return back()->with('error', __('app.label.updated_error', ['name' => $contract->title]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        DB::beginTransaction();

        try {
            $contractTitle = $contract->title;
            $contract->clearMediaCollection('files');
            $contract->delete();
            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
                ->withProperties([
                    'contract_id' => $contract->id,
                    'title' => $contractTitle,
                ])
                ->log('Контракт удален');

            DB::commit();

            return redirect()->route('contract.index')->with('success', __('app.label.deleted_successfully', ['name' => $contractTitle]));
        } catch (\Throwable $th) {
            DB::rollback();
            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
                ->withProperties([
                    'error' => $th->getMessage(),
                    'contract_id' => $contract->id,
                ])
                ->log('Ошибка при удалении контракта');

            return back()->with('error', __('app.label.deleted_error', ['name' => $contract->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('superadmin')) {
            abort(403, __('app.label.permission_denied'));
        }

        DB::beginTransaction();

        try {
            $contracts = Contract::whereIn('id', $request->id)->get();
            $deletedContracts = [];

            foreach ($contracts as $contract) {
                $deletedContracts[] = [
                    'contract_id' => $contract->id,
                    'title' => $contract->title,
                ];

                $contract->clearMediaCollection('files');
                $contract->delete();
            }

            activity('contract')
                ->causedBy($user)
                ->withProperties([
                    'deleted_contracts' => $deletedContracts,
                ])
                ->log('Массовое удаление контрактов');

            DB::commit();

            return back()->with('success', __('app.label.deleted_successfully', [
                'name' => count($request->id) . ' ' . __('app.label.contracts')
            ]));
        } catch (\Throwable $th) {
            DB::rollBack();

            activity('contract')
                ->causedBy($user)
                ->withProperties([
                    'error' => $th->getMessage(),
                    'contract_ids' => $request->id,
                ])
                ->log('Ошибка при массовом удалении контрактов');

            return back()->with('error', __('app.label.deleted_error', [
                    'name' => count($request->id) . ' ' . __('app.label.contracts')
                ]) . ' ' . $th->getMessage());
        }
    }

    public function removeApprover(ContractUserDeleteRequest $request, Contract $contract)
    {
        if (!$request->has('user_id')) {
            return redirect()->back()->with('error', __('app.label.deleted_error', [
                'name' => __('app.label.unknown_user')
            ]));
        }

        $userId = $request->user_id;
        $user = User::find($userId);

        $approval = Approvals::valid()
            ->where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('user_id', $userId)
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', __('app.label.not_found', [
                'name' => __('app.label.approver')
            ]));
        }
        if ($contract->status !== 1) {
            if ($approval->approved === Approvals::STATUS_APPROVED) {
                return redirect()->back()->with('warning', __('app.label.cannot_delete_approved', [
                    'name' => $user?->name ?? __('app.label.unknown_user')
                ]));
            }
        }

        $approval->delete();

        return redirect()->route('contract.show', ['contract' => $contract->id])
            ->with('success', __('app.label.deleted_successfully', [
                'name' => $user?->name ?? __('app.label.unknown_user')
            ]));
    }

    public function updateApprovers(ContractApproversUpdateRequest $request, Contract $contract)
    {
        $validated    = $request->validated();
        $newUserIds   = collect($validated['user_ids']);

        $existingApprovals = Approvals::valid()
            ->where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->get()
            ->keyBy('user_id');

        $usersToAdd    = $newUserIds->diff($existingApprovals->keys());
        $usersToRemove = $existingApprovals->keys()->diff($newUserIds);

        foreach ($usersToAdd as $userId) {
            Approvals::create([
                'approvable_type' => Contract::class,
                'approvable_id'   => $contract->id,
                'user_id'         => $userId,
                'approved'        => $contract->status === Contract::STATUS_NEW
                    ? Approvals::STATUS_NEW
                    : Approvals::STATUS_PENDING,
            ]);
        }

        $deletableStatuses = match ($contract->status) {
            Contract::STATUS_NEW         => [Approvals::STATUS_NEW],
            Contract::STATUS_IN_PROGRESS => [Approvals::STATUS_PENDING],
            default                      => [],
        };

        if (!empty($deletableStatuses)) {
            Approvals::whereIn('user_id', $usersToRemove)
                ->where('approvable_type', Contract::class)
                ->where('approvable_id', $contract->id)
                ->whereIn('approved', $deletableStatuses)
                ->delete();
        }

        return redirect()->route('contract.show', ['contract' => $contract->id])
            ->with('success', __('app.label.approvers_updated_successfully'));
    }

    protected function checkAndUpdateContractStatus(Contract $contract)
    {
        $approvals = $contract->approvals()
            ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
            ->get();
        if ($approvals->where('approved', Approvals::STATUS_REJECTED)->isNotEmpty()) {
            $contract->update(['status' => Application::STATUS_REJECTED]);
            return;
        }
        if ($approvals->every(fn($a) => $a->approved === Approvals::STATUS_APPROVED)) {
            $contract->update(['status' => Application::STATUS_APPROVED]);
            return;
        }
    }


}
