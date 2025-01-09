<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contract\ContractIndexRequest;
use App\Http\Requests\Contract\ContractStoreRequest;
use App\Http\Requests\Contract\ContractUpdateRequest;
use App\Models\Application;
use App\Models\Contract;
use App\Models\ContractApproval;
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
                'update contract' => ['contract.edit', 'contract.update'],
                'delete contract' => ['contract.destroy', 'contract.destroy-bulk'],
                'view contract' => ['contract.index', 'contract.show'],
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

    public function index(ContractIndexRequest $request)
    {
        $user = auth()->user();
        $statuses = Contract::getStatuses();
        $contracts = Contract::query()->with(['user', 'currency']);
        if (!$user->can('view all contracts')) {
            $contracts->where('user_id', $user->id);
        }
        if ($request->has('search')) {
            $contracts->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $contracts->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;
        $contracts = $contracts->paginate($perPage);
        return Inertia::render('Contract/Index', [
            'title'         => __('app.label.contracts'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'contracts'     => $contracts,
            'statuses'      => $statuses,
            'breadcrumbs'   => [['label' => __('app.label.contracts'), 'href' => route('contract.index')]],
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
        if (auth()->user()->can('get all application')) {
            $applications = Application::all();
        }else {
            $applications = auth()->user()->applications;
        }
        $users = User::where('id', '!=', auth()->id())->get();

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
            'recipients' => $recipients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContractStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $contract = new Contract();
            $contract->contract_number = $request->contract_number;
            $contract->title = $request->title;
            $contract->project_id = $request->project_id;
            $contract->application_id = $request->application_id ?? null;
            $contract->currency_id = $request->currency_id;
            $contract->user_id = Auth()->user()->id;
            $contract->budget_sum = $request->budget_sum;
            $contract->status = 1;
            $contract->deadline = Carbon::parse($request->deadline)->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
            $contract->save();
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ext = $file->extension();
                    $name = Str::random(24) . '.' . $ext;
                    $contract->addMedia($file)
                        ->usingFileName($name)
                        ->toMediaCollection("files");
                }
            }

            $recipients = $request->recipients;
            foreach ($recipients as $user) {
                ContractApproval::create([
                    'user_id' => $user,
                    'contract_id' => $contract->id,
                    'status' => ContractApproval::STATUS_NEW,
                ]);
            }
            DB::commit();
            return redirect()->route('contract.index')->with('success', __('app.label.created_successfully', ['name' => $contract->title]));

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.contracts')]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        $statuses = Contract::getStatuses();
        $files = $contract->getMedia('files');
        $project = Project::where('id', $contract->project_id)->first();
        $application = Application::where('id', $contract->application_id)->first();
        return Inertia::render('Contract/Show', [
            'title' => $contract->title,
            'files' => $files,
            'statuses' => $statuses,
            'project' => $project,
            'application' => $application,
            'contract' => $contract->load(['user', 'currency']),
            'breadcrumbs' => [
                ['label' => __('app.label.applications'), 'href' => route('contract.index')],
                ['label' => $contract->title]
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        $currency = Currency::where(['status' => 1])->get();
        $files = $contract->getMedia('files');
        $projects = Project::all();
        if (auth()->user()->can('get all application')) {
            $applications = Application::all();
        }else {
            $applications = auth()->user()->applications;
        }

        $users = User::all();

        return inertia('Contract/Edit', [
            'contract' => $contract,
            'currency' => $currency,
            'projects' => $projects,
            'applications' => $applications,
            'users' => $users,
            'files' => $files,
            'title' => __('app.label.contracts'),
            'breadcrumbs' => [
                ['label' => __('app.label.contracts'), 'href' => route('contract.index')],
                ['label' => $contract->title]
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
            $contract->update([
                'contract_number' => $request->contract_number,
                'title' => $request->title,
                'project_id' => $request->project_id,
                'application_id' => $request->application_id,
                'user_id' => $request->user_id,
                'currency_id' => $request->currency_id,
                'budget_sum' => $request->budget_sum,
                'deadline' => Carbon::parse($request->deadline)->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
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
            DB::commit();
            return redirect()->route('contract.index')->with('success', __('app.label.updated_successfully', ['name' => $contract->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $contract->title]) . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        DB::beginTransaction();
        try {
            $contract->clearMediaCollection('files');
            $contract->delete();

            DB::commit();
            return redirect()->route('contract.index')->with('success', __('app.label.deleted_successfully', ['name' => $contract->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $contract->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $contracts = Contract::whereIn('id', $request->id)->get();
            foreach ($contracts as $contract) {
                $contract->clearMediaCollection('files');
                $contract->delete();
            }
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.contracts')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.contracts')]) . $th->getMessage());
        }
    }

}
