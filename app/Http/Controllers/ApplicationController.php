<?php

namespace App\Http\Controllers;

use App\Http\Requests\Application\ApplicationApproversUpdateRequest;
use App\Http\Requests\Application\ApplicationIndexRequest;
use App\Http\Requests\Application\ApplicationStoreRequest;
use App\Http\Requests\Application\ApplicationUpdateRequest;
use App\Http\Requests\Application\ApplicationUserDeleteRequest;
use App\Http\Requests\Application\ScanFileUploadRequest;
use App\Models\Application;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Recipient;
use App\Models\User;
use App\Repositories\ApplicationRepository;
use App\Services\Application\ApplicationApprovalService;
use App\Services\Application\ApplicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationRepository $repository,
        protected ApplicationService $service,
        protected ApplicationApprovalService $approvalService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ApplicationIndexRequest $request)
    {
        $this->authorize('viewAny', Application::class);

        $user = auth()->user();

        $types = Application::getTypes();
        $statuses = Application::getStatuses();
        $projects = Project::all();
        $users = $this->repository->getAvailableUsers($user);

        $perPage = $request->input('perPage', 10);
        $filters = $request->only(['title', 'field', 'order', 'project_id', 'user_id', 'status_id', 'type']);

        $applications = $this->repository->paginateWithFilters($filters, $user, $perPage);

        return Inertia::render('Application/Index', [
            'title'        => __('app.label.applications'),
            'filters'      => $filters,
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
        $this->authorize('create', Application::class);

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
        $this->authorize('create', Application::class);

        try {
            $application = $this->service->create(
                $request->validated(),
                $request->hasFile('files') ? $request->file('files') : null,
                $request->recipients
            );

            return redirect()->route('application.index')
                ->with('success', __('app.label.created_successfully', ['name' => $application->title]));

        } catch (\Throwable $th) {
            return redirect()->back()->with(
                'error',
                __('app.label.created_error', ['name' => __('app.label.application')]) . ' ' . $th->getMessage()
            );
        }
    }

    /**
     * Submit application for approval
     */
    public function submit(Application $application)
    {
        $this->authorize('submit', $application);

        try {
            $this->approvalService->submit($application, auth()->user());

            return back()->with('success', __('app.label.submitted_successfully'));

        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('error', __('app.label.submit_failed') . ' ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        $this->authorize('view', $application);

        $application->load(['user', 'currency']);
        $project = Project::find($application->project_id);
        $files = $application->getMedia('documents');
        $scans = $application->getMedia('scans');
        $statuses = Application::getStatuses();

        $user = auth()->user();
        $users = User::approverOptions();
        $types = Application::getTypes();

        $approvals = $application->getFormattedApprovals();
        $canApprove = $this->approvalService->canApprove($application, $user);

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

    /**
     * Approve application
     */
    public function confirmApplication(Request $request, Application $application)
    {
        $this->authorize('approve', Application::class);

        try {
            $this->approvalService->approve($application, auth()->user(), $request->input('comment'));

            return redirect()->route('application.show', $application->id)
                ->with('success', __('app.label.updated_successfully', ['name' => $application->title]));

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Reject application
     */
    public function cancelApplication(Request $request, Application $application)
    {
        $this->authorize('approve', Application::class);

        try {
            $this->approvalService->reject($application, auth()->user(), $request->input('reason'));

            return redirect()->route('application.show', $application->id)
                ->with('success', __('app.label.cancelled_successfully', ['name' => $application->title]));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('app.label.cancel_error', ['name' => $application->title]));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Application $application)
    {
        $this->authorize('update', $application);

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
                ->where('approved', '!=', \App\Models\Approvals::STATUS_INVALIDATED)
                ->pluck('user_id')
                ->toArray(),
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('application.index')],
                ['label' => $application->id, 'href' => route('application.show', $application->id)],
                ['label' => __('app.label.edit')]
            ],
        ]);
    }

    /**
     * Show upload scan form
     */
    public function uploadScan(Application $application)
    {
        $this->authorize('uploadScan', $application);

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

    /**
     * Upload scan files
     */
    public function uploadScanFiles(ScanFileUploadRequest $request, Application $application)
    {
        $this->authorize('uploadScan', $application);

        try {
            $this->service->uploadScanFiles($application, $request->file('files', []));

            return redirect()->route('application.show', $application->id)
                ->with('success', __('app.label.scans_uploaded_successfully'));

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApplicationUpdateRequest $request, Application $application)
    {
        $this->authorize('update', $application);

        try {
            $this->service->update(
                $application,
                $request->validated(),
                $request->hasFile('files') ? $request->file('files') : null,
                $request->recipients,
                $request->input('deleted_old_file_ids')
            );

            return redirect()->route('application.index')
                ->with('success', __('app.label.updated_successfully', ['name' => $application->title]));

        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.updated_error', ['name' => $application->title]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Application $application)
    {
        $this->authorize('delete', $application);

        try {
            $this->service->delete($application);

            return redirect()->route('application.index')
                ->with('success', __('app.label.deleted_successfully', ['name' => $application->title]));

        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => $application->title]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Bulk delete applications
     */
    public function destroyBulk(Request $request)
    {
        $this->authorize('deleteAny', Application::class);

        try {
            $count = $this->service->deleteBulk($request->id, auth()->user());

            return back()->with('success', __('app.label.deleted_successfully', [
                'name' => $count . ' ' . __('app.label.applications')
            ]));

        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', [
                    'name' => count($request->id) . ' ' . __('app.label.applications')
                ]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Remove approver from application
     */
    public function removeApprover(ApplicationUserDeleteRequest $request, Application $application)
    {
        $this->authorize('manageApprovers', $application);

        if (!$request->has('user_id')) {
            return redirect()->back()->with('error', __('app.label.deleted_error', [
                'name' => __('app.label.unknown_user')
            ]));
        }

        try {
            $this->approvalService->removeApprover($application, $request->user_id);

            $user = User::find($request->user_id);

            return redirect()->route('application.show', ['application' => $application->id])
                ->with('success', __('app.label.deleted_successfully', [
                    'name' => $user?->name ?? __('app.label.unknown_user')
                ]));

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Update approvers list
     */
    public function updateApprovers(ApplicationApproversUpdateRequest $request, Application $application)
    {
        $this->authorize('manageApprovers', $application);

        $this->approvalService->updateApprovers($application, $request->validated()['user_ids']);

        return redirect()->route('application.show', ['application' => $application->id])
            ->with('success', __('app.label.approvers_updated_successfully'));
    }
}
