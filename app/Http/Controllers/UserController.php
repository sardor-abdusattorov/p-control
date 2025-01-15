<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Department;
use App\Models\Position;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->can('manage rbac')) {
                return redirect()->route('dashboard')->with('error', __('app.deny_access'));
            }

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(UserIndexRequest $request)
    {
        $users = User::query();
        if ($request->has('search')) {
            $users->where('name', 'LIKE', "%" . $request->search . "%")
                ->orWhere('email', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $users->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;
        $role = auth()->user()->roles->pluck('name')[0];
        $roles = Role::get();

        if ($role != 'superadmin') {
            $users->whereHas('roles', function ($query) {
                $query->where('name', '<>', 'superadmin');
            });
            $roles = Role::where('name', '<>', 'superadmin')->get();
        }
        $users = $users->with(['roles', 'department'])->paginate($perPage);

        return Inertia::render('User/Index', [
            'title'         => __('app.label.user'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'users'         => $users,
            'roles'         => $roles,
            'breadcrumbs'   => [['label' => __('app.label.user'), 'href' => route('user.index')]],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $roles = Role::get();
        $users = User::where('id', '!=', auth()->user()->id)->get();
        $departments = Department::where('status', 1)->get();
        $positions = Position::select('positions.id', 'positions.name', 'positions_department.department_id')
            ->join('positions_department', 'positions.id', '=', 'positions_department.position_id')
            ->get();

        return Inertia::render('User/Create', [
            'roles'         => $roles,
            'users'         => $users,
            'departments'   => $departments,
            'positions'     => $positions,
            'title'         => __('app.label.user'),
            'breadcrumbs'   => [
                ['label' => __('app.label.user'), 'href' => route('user.index')],
                ['label' => __('app.label.create')]
            ],
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(UserStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $role = Role::find($request->role);
            if ($role) {
                $user->assignRole($role);
            } else {
                DB::rollback();
                return back()->with('error', 'Роль с таким ID не существует.');
            }
            if ($request->hasFile('image')) {
                $user->addMediaFromRequest('image')
                    ->toMediaCollection('profile_image');
            }
            $user->department_id = $request->department_id;
            $user->position_id = $request->position_id;
            $user->telegram_id = $request->telegram_id;
            $user->save();
            if (isset($request->recipients)) {
                $user->recipients()->delete();

                foreach ($request->recipients as $recipientId) {
                    Recipient::create([
                        'user_id' => $user->id,
                        'recipient_id' => $recipientId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('user.index')->with('success', __('app.label.created_successfully', ['name' => $user->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.user')]) . ' ' . $th->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show(User $user)
    {
        $image = $user->getFirstMediaUrl('profile_image');
        $position = Position::where('id', $user->position_id)->first();
        $department = Department::where('id', $user->department_id)->first();
        if (empty($image)) {
            $image = '/images/no_image.png';
        }
        return Inertia::render('User/Show', [
            'title' => __('app.label.user'),
            'breadcrumbs' => [
                ['label' => __('app.label.user'), 'href' => route('user.index')],
                ['label' => $user->name]
            ],
            'user' => $user,
            'image' => $image,
            'department' => $department,
            'position' => $position,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function edit(User $user)
    {
        $roles = Role::get();
        $users = User::where('id', '!=', auth()->user()->id)->get();
        $departments = Department::where('status', 1)->get();
        $positions = Position::select('positions.id', 'positions.name', 'positions_department.department_id')
            ->join('positions_department', 'positions.id', '=', 'positions_department.position_id')
            ->get();
        $recipients = $user->recipients;
        $userRole = $user->getRole();

        return inertia('User/Edit', [
            'roles'         => $roles,
            'users'         => $users,
            'departments'   => $departments,
            'positions'     => $positions,
            'user'         => $user,
            'userRole'         => $userRole,
            'recipients' => $recipients,
            'title' => __('app.label.user'),
            'breadcrumbs' => [
                ['label' => __('app.label.user'), 'href' => route('user.index')],
                ['label' => $user->name]
            ]
        ]);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $userData = [
                'name'          => $request->name,
                'email'         => $request->email,
                'department_id' => $request->department_id,
                'position_id'   => $request->position_id,
                'telegram_id'   => $request->telegram_id,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);
            if ($request->hasFile('image')) {
                $user->clearMediaCollection('profile_image');
                $user->addMediaFromRequest('image')
                    ->toMediaCollection('profile_image');
            }

            if (isset($request->recipients)) {
                $user->recipients()->delete();
                foreach ($request->recipients as $recipientId) {
                    Recipient::create([
                        'user_id' => $user->id,
                        'recipient_id' => $recipientId,
                    ]);
                }
            }

            if ($request->has('role') && $request->role) {
                $role = Role::find($request->role);
                if ($role) {
                    $user->syncRoles([$role->name]);
                } else {
                    DB::rollback();
                    return back()->with('error', 'Роль с таким ID не существует.');
                }
            }
            DB::commit();
            return redirect()->route('user.index')->with('success', __('app.label.updated_successfully', ['name' => $user->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $user->name]) . ' ' . $th->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy(User $user)
    {
        try {
            $user->clearMediaCollection();
            $user->recipients()->delete();
            $user->delete();
            DB::commit();
            return redirect()->route('user.index')->with('success', __('app.label.deleted_successfully', ['name' => $user->name]));
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', __('app.label.deleted_error', ['name' => $user->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $user = User::whereIn('id', $request->id);
            $user->clearMediaCollection();
            $user->recipients()->delete();
            $user->delete();
            DB::commit();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.user')]));
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.user')]) . $th->getMessage());
        }
    }
}
