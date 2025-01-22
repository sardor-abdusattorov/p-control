<?php

namespace App\Http\Controllers;

use App\Http\Requests\Departments\DepartmentsIndexRequest;
use App\Http\Requests\Departments\DepartmentsStoreRequest;
use App\Http\Requests\Departments\DepartmentsUpdateRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DepartmentsController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->can('manage department')) {
                return redirect()->route('dashboard')->with('error', __('app.deny_access'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(DepartmentsIndexRequest $request)
    {
        $departments = Department::query();
        if ($request->has('search')) {
            $departments->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $departments->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;

        $users = User::where('status', 1)->get();

        return Inertia::render('Departments/Index', [
            'title'         => __('app.label.departments'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'departments'   => $departments->paginate($perPage),
            'users'         => $users,
            'breadcrumbs'   => [['label' => __('app.label.departments'), 'href' => route('departments.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('status', 1)->get();
        return Inertia::render('Departments/Create', [
            'users'         => $users,
            'title'         => __('app.label.departments'),
            'statusOptions' => Department::getStatuses(),
            'breadcrumbs' => [
                ['label' => __('app.label.departments'), 'href' => route('departments.index')],
                ['label' => __('app.label.create')]
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentsStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $department = new Department();
            $department->name = $request->name;
            $department->head_of_department = $request->head_of_department ?? null;
            $department->status = $request->status ?? 1;
            $department->save();

            DB::commit();
            return redirect()->route('departments.index')->with('success', __('app.label.created_successfully', ['name' => $department->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.status')]) . ' ' . $th->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $headOfDepartmentName = $department->head_of_department
            ? User::where('id', $department->head_of_department)->value('name')
            : null;

        return Inertia::render('Departments/Show', [
            'department' => $department,
            'head_of_department_name' => $headOfDepartmentName,
            'title' => __('app.label.departments'),
            'breadcrumbs' => [
                ['label' => __('app.label.departments'), 'href' => route('departments.index')],
                ['label' => $department->name]
            ]
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $users = User::where('status', 1)->get();
        return inertia('Departments/Edit', [
            'users'         => $users,
            'department' => $department,
            'title' => __('app.label.departments'),
            'statusOptions' => Department::getStatuses(),
            'breadcrumbs' => [
                ['label' => __('app.label.departments'), 'href' => route('departments.index')],
                ['label' => $department->id, 'href' => route('departments.show', $department->id)],
                ['label' => __('app.label.edit')]
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentsUpdateRequest $request, Department $department)
    {
        DB::beginTransaction();

        try {
            $department->update([
                'name' => $request->name,
                'head_of_department' => $request->head_of_department,
                'status' => $request->status
            ]);

            DB::commit();
            return redirect()->route('departments.index')->with('success', __('app.label.updated_successfully', ['name' => $department->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $department->name]) . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        DB::beginTransaction();
        try {
            $department->delete();
            DB::commit();
            return redirect()->route('departments.index')->with('success', __('app.label.deleted_successfully', ['name' => $department->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $department->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $department = Department::whereIn('id', $request->id);
            $department->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.department')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.department')]) . $th->getMessage());
        }
    }
}
