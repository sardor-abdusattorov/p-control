<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contract\ContractIndexRequest;
use App\Http\Requests\Contract\ContractStoreRequest;
use App\Http\Requests\Contract\ContractUpdateRequest;
use App\Models\Application;
use App\Models\Approvals;
use App\Models\Chat;
use App\Models\Contract;
use App\Models\Currency;
use App\Models\Message;
use App\Models\Project;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                'update contract' => ['contract.edit', 'contract.update', 'contract.remove-approver', 'contract.update-approvers'],
                'delete contract' => ['contract.destroy', 'contract.destroy-bulk'],
                'view contract' => ['contract.index', 'contract.show'],
                'contract chat' => ['contract.chat', 'contract.send-message', 'contract.get-messages', 'contract.get-all-chats'],
                'approve contract' => ['contract.approve'],
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
                                if ($contract->status == 3) {
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
        $user = auth()->user();
        $statuses = Contract::getStatuses();
        $contracts = Contract::query()->with(['user', 'currency']);
        if (!$user->can('view all contracts')) {
            $contracts->where('user_id', $user->id);
        }
        if ($request->has('search')) {
            $contracts->where('title', 'LIKE', "%" . $request->search . "%");
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
        if (auth()->user()->can('view all applications')) {
            $applications = Application::all();
        }else {
            $applications = auth()->user()->applications;
        }
        $users = User::where('id', '!=', auth()->id())
            ->where('status', 1)
            ->get();

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
            if (!empty($request->recipients)) {
                foreach ($request->recipients as $recipient) {

                    Approvals::create([
                        'approvable_type' => Contract::class,
                        'approvable_id' => $contract->id,
                        'user_id' => $recipient,
                        'approved' => false,
                        'approved_at' => null,
                    ]);

                    $chat = new Chat();
                    $chat->model_type = 'contract';
                    $chat->model_id = $contract->id;
                    $chat->user_id = auth()->id();
                    $chat->receiver_id = $recipient;
                    $chat->name = 'Chat for contract #' . $contract->id;

                    if ($chat->save()) {
                        $messageContent = 'Отправляю контракт на ваше рассмотрение';
                        Message::create([
                            'chat_id' => $chat->id,
                            'user_id' => auth()->id(),
                            'text' => $messageContent,
                            'created_date' => now(),
                            'is_notified' => 0,
                        ]);
                    } else {
                        DB::rollback();
                        activity('contract')
                            ->causedBy(auth()->user())
                            ->performedOn($contract)
                            ->withProperties(['error' => 'Chat creation failed'])
                            ->log('Ошибка при создании чата для контракта');

                        return redirect()->back()->with('error', __('app.label.chat_creation_failed'));
                    }
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

            return redirect()->route('contract.index')->with('success', __('app.label.created_successfully', ['name' => $contract->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            activity('contract')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'contract_number' => $request->contract_number,
                    'title' => $request->title,
                    'project_id' => $request->project_id,
                ])
                ->log('Ошибка при создании контракта');

            return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.contracts')]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        $statuses = Contract::getStatuses();
        $users = User::where('id', '!=', auth()->id())
            ->where('status', 1)
            ->get();
        $files = $contract->getMedia('files');
        $project = Project::find($contract->project_id);
        $application = Application::find($contract->application_id);
        $user = auth()->user();

        $approvals = Approvals::where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
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

        $canApprove = Approvals::where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('user_id', $user->id)
            ->where('approved', false)
            ->exists();

        return Inertia::render('Contract/Show', [
            'title' => $contract->title,
            'files' => $files,
            'users' => $users,
            'statuses' => $statuses,
            'project' => $project,
            'application' => $application,
            'contract' => $contract->load(['user', 'currency']),
            'approvals' => $approvals,
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

            $totalApprovals = Approvals::where('approvable_type', Contract::class)
                ->where('approvable_id', $contract->id)
                ->where('approved', true)
                ->count();

            $totalRecipients = Approvals::where('approvable_type', Contract::class)
                ->where('approvable_id', $contract->id)
                ->count();

            if ($contract->status == 1) {
                $contract->update(['status' => 2]);

                activity('contract')
                    ->causedBy($user)
                    ->performedOn($contract)
                    ->withProperties([
                        'contract_id' => $contract->id,
                        'previous_status' => 1,
                        'new_status' => 2,
                    ])
                    ->log('Статус контракта изменен на "в процессе"');
            }

            if ($totalApprovals >= $totalRecipients) {
                $contract->update(['status' => 3]);

                activity('contract')
                    ->causedBy($user)
                    ->performedOn($contract)
                    ->withProperties([
                        'contract_id' => $contract->id,
                        'previous_status' => 2,
                        'new_status' => 3,
                    ])
                    ->log('Контракт полностью одобрен');
            }

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


    public function chat(Contract $contract)
    {
        $users = User::where('status', 1)->get();
        $currentUser = auth()->user();

        $chats = Chat::where('model_type', 'contract')
            ->where('model_id', $contract->id)
            ->where(function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id)
                    ->orWhere('receiver_id', $currentUser->id);
            })
            ->with(['messages.media'])
            ->get();

        return Inertia::render('Contract/Chat', [
            'title' => __('app.label.contracts'),
            'users' => $users,
            'chats' => $chats,
            'contract' => $contract,
            'breadcrumbs' => [
                ['label' => __('app.label.contracts'), 'href' => route('contract.index')],
                ['label' => $contract->title, 'href' => route('contract.show', $contract->id)],
                ['label' => __('app.label.contract_chat')],
            ],
        ]);
    }

    public function getAllChats(Request $request, $contract_id)
    {
        $currentUser = auth()->user();

        try {
            $chats = Chat::with(['messages' => function ($query) {
                $query->latest('created_date')->limit(1)->with('media');
            }])
                ->where('model_type', 'contract')
                ->where('model_id', $contract_id)
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

    public function sendMessage(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z,txt,csv,mp3,mp4,mov,avi|max:20480',
        ]);


        DB::beginTransaction();

        try {
            if (!empty($request['chat_id'])) {
                $chat = Chat::findOrFail($request['chat_id']);
            } else {
                $chat = Chat::create([
                    'model_type' => 'contract',
                    'model_id' => $contract->id,
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
                    $ext = $file->extension();
                    $name = Str::random(24) . '.' . $ext;
                    $message->addMedia($file)
                        ->usingFileName($name)
                        ->toMediaCollection("message file");
                }
            }

            DB::commit();

            return redirect()->route('contract.chat', [
                'id' => $chat->id,
                'contract' => $contract->id,
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
    public function edit(Contract $contract)
    {
        $currency = Currency::where(['status' => 1])->get();
        $files = $contract->getMedia('files');
        $projects = Project::all();
        if (auth()->user()->role('superadmin')) {
            $applications = Application::all();
        }else {
            $applications = auth()->user()->applications;
        }

        $users = User::where('status', 1)->get();

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
            $originalData = $contract->getOriginal();

            $contract->update([
                'contract_number' => $request->contract_number,
                'title' => $request->title,
                'project_id' => $request->project_id,
                'application_id' => $request->application_id,
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
            DB::rollback();

            activity('contract')
                ->causedBy(auth()->user())
                ->performedOn($contract)
                ->withProperties([
                    'error' => $th->getMessage(),
                    'contract_id' => $contract->id,
                ])
                ->log('Ошибка при обновлении контракта');

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
                ->causedBy(auth()->user())
                ->withProperties([
                    'deleted_contracts' => $deletedContracts,
                ])
                ->log('Массовое удаление контрактов');

            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.contracts')]));
        } catch (\Throwable $th) {

            activity('contract')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $th->getMessage(),
                    'contract_ids' => $request->id,
                ])
                ->log('Ошибка при массовом удалении контрактов');

            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.contracts')]) . $th->getMessage());
        }
    }

    public function removeApprover(Request $request, Contract $contract)
    {
        if (!$request->has('user_id')) {
            return redirect()->back()->with('error', __('app.label.deleted_error', [
                'name' => __('app.label.unknown_user')
            ]));
        }
        $userId = $request->user_id;
        $user = User::find($userId);

        $approval = Approvals::where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
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
        $contract->update(['status' => 1]);

        return redirect()->route('contract.show', ['contract' => $contract->id])
            ->with('success', __('app.label.deleted_successfully', [
                'name' => $user ? $user->name : __('app.label.unknown_user')
            ]));
    }

    public function updateApprovers(Request $request, Contract $contract)
    {
        $existingApprovals = Approvals::where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->get()
            ->keyBy('user_id');

        $newUserIds = collect($request->user_ids);
        $usersToAdd = $newUserIds->diff($existingApprovals->keys());
        $usersToRemove = $existingApprovals->keys()->diff($newUserIds);
        foreach ($usersToAdd as $userId) {
            Approvals::create([
                'approvable_type' => Contract::class,
                'approvable_id' => $contract->id,
                'user_id' => $userId,
                'approved' => false,
            ]);
        }
        $confirmedUsers = Approvals::whereIn('user_id', $usersToRemove)
            ->where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('approved', true)
            ->exists();

        if ($confirmedUsers) {
            return redirect()->route('contract.show', ['contract' => $contract->id])
                ->with('warning', __('app.label.cannot_delete_approved_list'));
        }
        Approvals::whereIn('user_id', $usersToRemove)
            ->where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('approved', false)
            ->delete();

        return redirect()->route('contract.show', ['contract' => $contract->id])
            ->with('success', __('app.label.approvers_updated_successfully'));
    }


}
