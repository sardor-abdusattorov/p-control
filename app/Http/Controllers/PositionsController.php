<?php

namespace App\Http\Controllers;

use App\Http\Requests\Positions\PositionsIndexRequest;
use App\Http\Requests\Positions\PositionsStoreRequest;
use App\Http\Requests\Positions\PositionsUpdateRequest;
use App\Models\Department;
use App\Models\Position;
use App\Models\PositionsDepartment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PositionsController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->can('manage position')) {
                return redirect()->route('dashboard')->with('error', __('app.deny_access'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PositionsIndexRequest $request)
    {
        $positions = Position::query();
        if ($request->has('search')) {
            $positions->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $positions->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;

        return Inertia::render('Positions/Index', [
            'title'         => __('app.label.positions'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'positions'      => $positions->paginate($perPage),
            'breadcrumbs'   => [['label' => __('app.label.positions'), 'href' => route('positions.index')]],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::where('status', 1)->get();

        return Inertia::render('Positions/Create', [
            'departments' => $departments,
            'title'         => __('app.label.positions'),
            'breadcrumbs' => [
                ['label' => __('app.label.positions'), 'href' => route('positions.index')],
                ['label' => __('app.label.create')]
            ]
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(PositionsStoreRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $position = new Position();
            $position->name = $request->name;
            $position->save();

            if (!empty($request->departments)) {
                foreach ($request->departments as $departmentId) {
                    $positionDepartment = new PositionsDepartment();
                    $positionDepartment->position_id = $position->id;
                    $positionDepartment->department_id = $departmentId;
                    $positionDepartment->save();
                }
            }

            DB::commit();

            return redirect()->route('positions.index')->with('success', __('app.label.created_successfully', ['name' => $position->name]));

        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('error', __('app.label.created_error', ['name' => __('app.label.position')]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        return Inertia::render('Positions/Show', [
            'position' => $position,
            'title' => __('app.label.positions'),
            'breadcrumbs' => [
                ['label' => __('app.label.positions'), 'href' => route('positions.index')],
                ['label' => $position->name]
            ]
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $position = Position::with('departments')->findOrFail($id);
        $departments = Department::where('status', 1)->get();

        return inertia('Positions/Edit', [
            'position' => $position,
            'departments' => $departments,
            'title' => __('app.label.positions'),

            'breadcrumbs' => [
                ['label' => __('app.label.positions'), 'href' => route('positions.index')],
                ['label' => $id, 'href' => route('positions.show', $id)],
                ['label' => __('app.label.edit')]
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PositionsUpdateRequest $request, Position $position)
    {
        DB::beginTransaction();

        try {
            $position->update([
                'name' => $request->name,
            ]);
            if (!empty($request->departments)) {
                $position->departments()->delete();
                foreach ($request->departments as $departmentId) {
                    $positionDepartment = new PositionsDepartment();
                    $positionDepartment->position_id = $position->id;
                    $positionDepartment->department_id = $departmentId;
                    $positionDepartment->save();
                }
            } else {
                $position->departments()->delete();
            }

            DB::commit();

            return redirect()->route('positions.index')->with('success', __('app.label.updated_successfully', ['name' => $position->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $position->name]) . ' ' . $th->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        DB::beginTransaction();
        try {
            $position->departments()->delete();
            $position->delete();

            DB::commit();
            return redirect()->route('positions.index')->with('success', __('app.label.deleted_successfully', ['name' => $position->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('positions.index')->with('error', __('app.label.deleted_error', ['name' => $position->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        DB::beginTransaction();
        try {
            $positions = Position::whereIn('id', $request->id)->get();

            foreach ($positions as $position) {
                $position->departments()->delete();
                $position->delete();
            }

            DB::commit();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.department')]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.department')]) . $th->getMessage());
        }
    }
}
