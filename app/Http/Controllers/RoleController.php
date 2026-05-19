<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\RoleIndexRequest;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Requests\Role\RoleUpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoleController extends Controller
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

    public function index(RoleIndexRequest $request)
    {
        $roles = Role::query();
        if ($request->has('search')) {
            $roles->where('name', 'LIKE', "%" . $request->search . "%");
            $roles->orWhere('guard_name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $roles->orderBy($request->field, $request->order);
        }
        $roles->with('permissions');
        $role = auth()->user()->roles->pluck('name')[0];
        $permissions = Permission::latest();
        if ($role != 'superadmin') {
            $permissions = Permission::whereNotIn('name', ['create permission', 'read permission', 'update permission', 'delete permission'])->latest();
            $roles->where('name', '<>', 'superadmin');
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;
        return Inertia::render('Role/Index', [
            'title'         => __('app.label.role'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'roles'         => $roles->paginate($perPage),
            'permissions'   => $permissions->get(),
            'breadcrumbs'   => [['label' => __('app.label.role'), 'href' => route('role.index')]],
        ]);
    }

    public function create()
    {
    }

    public function store(RoleStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name'          => $request->name,
            ]);
            $role->givePermissionTo($request->permissions);
            DB::commit();
            return back()->with('success', __('app.label.created_successfully', ['name' => $role->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.role')]) . $th->getMessage());
        }
    }

    public function show(Role $role)
    {
    }

    public function edit(Role $role)
    {
    }

    public function update(RoleUpdateRequest $request, Role $role)
    {
        DB::beginTransaction();
        try {
            $role->update([
                'name'          => $request->name,
            ]);
            $role->syncPermissions($request->permissions);
            DB::commit();
            return back()->with('success', __('app.label.updated_successfully', ['name' => $role->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $role->name]) . $th->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        try {
            $role->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => $role->name]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => $role->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $role = Role::whereIn('id', $request->id);
            $role->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.role')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.role')]) . $th->getMessage());
        }
    }
}
