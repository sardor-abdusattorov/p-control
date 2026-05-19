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
use App\Models\Contact;
use App\Models\ContactCategory;
use App\Models\ContactSubcategory;
use App\Models\Contract;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Recipient;
use App\Models\User;
use App\Exports\ContractsExport;
use App\Repositories\ContractRepository;
use App\Services\Contract\ContractApprovalService;
use App\Services\Contract\ContractService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ContractController extends Controller
{
    public function __construct(
        protected ContractRepository $repository,
        protected ContractService $service,
        protected ContractApprovalService $approvalService
    ) {
        $this->middleware('auth');
    }

    public function index(ContractIndexRequest $request)
    {
        $this->authorize('viewAny', Contract::class);

        $user = auth()->user();

        $currency = Currency::where(['status' => 1])->get();
        $statuses = Contract::getStatuses();
        $users = $this->repository->getAvailableUsers($user);

        $perPage = $request->input('perPage', 10);
        $filters = $request->only(['contract_number', 'title', 'field', 'order', 'user_id', 'status', 'currency_id', 'approval_filter']);

        $contracts = $this->repository->paginateWithFilters($filters, $user, $perPage);

        $contractIds = $contracts->pluck('id');
        $approvals = $this->repository->getApprovalsByContractIds($contractIds->toArray());

        return Inertia::render('Contract/Index', [
            'title' => __('app.label.contracts'),
            'filters' => $filters,
            'perPage' => (int)$perPage,
            'contracts' => $contracts,
            'statuses' => $statuses,
            'transactionTypes' => Contract::getTransactionTypes(),
            'currency' => $currency,
            'users' => $users,
            'approvals' => $approvals,
            'availableYears' => ProjectCategory::query()
                ->whereNotNull('year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year'),
            'breadcrumbs' => [['label' => __('app.label.contracts'), 'href' => route('contract.index')]],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('export', Contract::class);

        $years = $this->intArrayFromRequest($request, 'year');
        $statuses = $this->intArrayFromRequest($request, 'status');
        $userIds = $this->intArrayFromRequest($request, 'user_id');
        $currencyIds = $this->intArrayFromRequest($request, 'currency_id');
        $transactionType = $request->filled('transaction_type')
            ? (int) $request->input('transaction_type')
            : null;

        $filenameParts = ['contracts'];
        if (!empty($years)) {
            $filenameParts[] = implode('-', $years);
        }
        if (!empty($statuses)) {
            $filenameParts[] = 'status-' . implode('-', $statuses);
        }
        if ($transactionType !== null) {
            $filenameParts[] = 'type-' . $transactionType;
        }
        if (!empty($userIds)) {
            $filenameParts[] = 'user-' . implode('-', $userIds);
        }
        if (!empty($currencyIds)) {
            $filenameParts[] = 'cur-' . implode('-', $currencyIds);
        }
        $filename = implode('_', $filenameParts) . '.xlsx';

        return Excel::download(
            new ContractsExport(auth()->user(), $years, $statuses, $transactionType, $userIds, $currencyIds),
            $filename
        );
    }

    protected function intArrayFromRequest(Request $request, string $key): array
    {
        $value = $request->input($key);

        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            $items = $value;
        } else {
            $items = explode(',', (string) $value);
        }

        return array_values(array_filter(
            array_map('intval', $items),
            fn ($v) => $v !== 0
        ));
    }

    public function create()
    {
        $this->authorize('create', Contract::class);

        $currency = Currency::where('status', 1)->get();
        $recipients = Recipient::where('user_id', auth()->id())->get();
        $projects = Project::all();
        $types = Application::getTypes();
        $users = User::approverOptions();
        $contacts = Contact::where('owner_id', auth()->id())
            ->select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->get();

        $applications = auth()->user()->can('view all applications')
            ? Application::with('currency')->get()
            : auth()->user()->applications()->with('currency')->get();

        $groupedApplications = $this->groupApplicationsByCurrency($applications);

        return Inertia::render('Contract/Create', [
            'title' => __('app.label.contracts'),
            'breadcrumbs' => [
                ['label' => __('app.label.contracts'), 'href' => route('contract.index')],
                ['label' => __('app.label.create')]
            ],
            'currency' => $currency,
            'projects' => $projects,
            'applications' => $groupedApplications,
            'users' => $users,
            'application_types' => $types,
            'transaction_types' => Contract::getTransactionTypes(),
            'recipients' => $recipients,
            'contacts' => $contacts,
            'categories' => ContactCategory::where('status', 1)->get(),
            'subCategories' => ContactSubcategory::where('status', 1)->get(),
            'countries' => Country::select('id', 'name')->orderBy('name')->get(),
            'statuses' => Contact::getStatuses(),
            'availableYears' => ProjectCategory::query()->distinct()->orderBy('year')->pluck('year'),
        ]);
    }

    public function store(ContractStoreRequest $request)
    {
        $this->authorize('create', Contract::class);

        try {
            $contract = $this->service->create(
                $request->validated(),
                $request->hasFile('files') ? $request->file('files') : null,
                $request->recipients
            );

            return redirect()->route('contract.index')
                ->with('success', __('app.label.created_successfully', ['name' => $contract->title]));
        } catch (\Throwable $th) {
            return redirect()->back()->with(
                'error',
                __('app.label.created_error', ['name' => __('app.label.contracts')]) . ' ' . $th->getMessage()
            );
        }
    }

    public function submit(Contract $contract)
    {
        $this->authorize('submit', $contract);

        try {
            $this->approvalService->submit($contract, auth()->user());

            return back()->with('success', __('app.label.submitted_successfully'));
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('error', __('app.label.submit_failed') . ' ' . $th->getMessage());
        }
    }

    public function show(Contract $contract)
    {
        $this->authorize('view', $contract);

        $types = Application::getTypes();
        $statuses = Contract::getStatuses();
        $transactionTypes = Contract::getTransactionTypes();
        $users = User::approverOptions();

        $files = $contract->getMedia('files');
        $scans = $contract->getMedia('scans');
        $project = Project::find($contract->project_id);

        $application = Application::with(['media', 'user'])->find($contract->application_id);

        $applicationFiles = $application ? $application->getMedia('documents') : collect();
        $applicationApprovals = $application ? $this->getApplicationApprovals($application) : collect();

        $user = auth()->user();
        $approvals = $contract->getFormattedApprovals();
        $canApprove = $this->approvalService->canApprove($contract, $user);
        $blockInfo = $this->approvalService->isBlockedByPreviousOrder($contract, $user);

        return Inertia::render('Contract/Show', [
            'title' => $contract->title,
            'files' => $files,
            'scans' => $scans,
            'application_files' => $applicationFiles,
            'users' => $users,
            'statuses' => $statuses,
            'types' => $types,
            'transaction_types' => $transactionTypes,
            'project' => $project,
            'application' => $application,
            'contract' => $contract->load(['user', 'currency']),
            'approvals' => $approvals,
            'application_approvals' => $applicationApprovals,
            'can_approve' => $canApprove,
            'block_info' => $blockInfo,
            'breadcrumbs' => [
                ['label' => __('app.label.contracts'), 'href' => route('contract.index')],
                ['label' => $contract->title]
            ],
        ]);
    }

    public function confirmContract(Request $request, Contract $contract)
    {
        $this->authorize('approve', Contract::class);

        try {
            $this->approvalService->approve($contract, auth()->user(), $request->input('comment'));

            return redirect()->route('contract.show', $contract->id)
                ->with('success', __('app.label.updated_successfully', ['name' => $contract->title]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancelContract(Request $request, Contract $contract)
    {
        $this->authorize('approve', Contract::class);

        try {
            $this->approvalService->reject($contract, auth()->user(), $request->input('reason'));

            return redirect()->route('contract.show', $contract->id)
                ->with('success', __('app.label.cancelled_successfully', ['name' => $contract->title]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function uploadScan(Contract $contract)
    {
        $this->authorize('uploadScan', $contract);

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
        $this->authorize('uploadScan', $contract);

        try {
            $this->service->uploadScanFiles($contract, $request->file('files', []));

            return redirect()->route('contract.show', $contract->id)
                ->with('success', __('app.label.scans_uploaded_successfully'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function edit(Contract $contract)
    {
        $this->authorize('update', $contract);

        $contacts = Contact::where('owner_id', auth()->id())
            ->select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->get();
        $types = Application::getTypes();
        $currency = Currency::where(['status' => 1])->get();
        $files = $contract->getMedia('files');
        $projects = Project::with('category')->get();

        $applications = auth()->user()->can('view all applications')
            ? Application::with('currency')->get()
            : auth()->user()->applications()->with('currency')->get();

        $groupedApplications = $this->groupApplicationsByCurrency($applications);
        $users = User::approverOptions();

        return inertia('Contract/Edit', [
            'contract' => $contract,
            'currency' => $currency,
            'contacts' => $contacts,
            'projects' => $projects,
            'applications' => $groupedApplications,
            'application_types' => $types,
            'transaction_types' => Contract::getTransactionTypes(),
            'approval_user_ids' => $contract->approvals()
                ->where('approved', '!=', \App\Models\Approvals::STATUS_INVALIDATED)
                ->pluck('user_id')
                ->toArray(),
            'users' => $users,
            'files' => $files,
            'title' => __('app.label.contracts'),
            'breadcrumbs' => [
                ['label' => __('app.label.contracts'), 'href' => route('contract.index')],
                ['label' => $contract->title, 'href' => route('contract.show', $contract->id)],
                ['label' => __('app.label.edit')]
            ],
            'availableYears' => ProjectCategory::query()->distinct()->orderBy('year')->pluck('year'),
        ]);
    }

    public function update(ContractUpdateRequest $request, Contract $contract)
    {
        $this->authorize('update', $contract);

        try {
            $this->service->update(
                $contract,
                $request->validated(),
                $request->hasFile('files') ? $request->file('files') : null,
                $request->recipients,
                $request->input('deleted_old_file_ids')
            );

            return redirect()->route('contract.index')
                ->with('success', __('app.label.updated_successfully', ['name' => $contract->title]));
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroy(Contract $contract)
    {
        $this->authorize('delete', $contract);

        try {
            $contractTitle = $contract->title;
            $this->service->delete($contract);

            return redirect()->route('contract.index')
                ->with('success', __('app.label.deleted_successfully', ['name' => $contractTitle]));
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        $this->authorize('deleteAny', Contract::class);

        try {
            $count = $this->service->deleteBulk($request->id, auth()->user());

            return back()->with('success', __('app.label.deleted_successfully', [
                'name' => $count . ' ' . __('app.label.contracts')
            ]));
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function removeApprover(ContractUserDeleteRequest $request, Contract $contract)
    {
        $this->authorize('manageApprovers', $contract);

        try {
            $userId = $request->user_id;
            $user = User::find($userId);

            $this->approvalService->removeApprover($contract, $userId);

            return redirect()->route('contract.show', ['contract' => $contract->id])
                ->with('success', __('app.label.deleted_successfully', [
                    'name' => $user?->name ?? __('app.label.unknown_user')
                ]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateApprovers(ContractApproversUpdateRequest $request, Contract $contract)
    {
        $this->authorize('manageApprovers', $contract);

        try {
            $this->approvalService->updateApprovers($contract, $request->validated()['user_ids']);

            return redirect()->route('contract.show', ['contract' => $contract->id])
                ->with('success', __('app.label.approvers_updated_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    protected function groupApplicationsByCurrency($applications)
    {
        return $applications
            ->sortByDesc('created_at')
            ->groupBy(fn ($app) => 'Валюта - ' . ($app->currency->name ?? '---'))
            ->map(function ($apps, $currency) {
                return [
                    'label' => $currency,
                    'items' => $apps->map(fn ($app) => [
                        'id' => $app->id,
                        'title' => $app->title,
                        'type' => $app->type,
                        'created_at' => $app->created_at,
                    ])->values(),
                ];
            })->values();
    }

    protected function getApplicationApprovals(Application $application)
    {
        return \App\Models\Approvals::where('approvable_type', Application::class)
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
}
