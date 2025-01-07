<?php

namespace App\Http\Controllers;

use App\Http\Requests\Status\StatusIndexRequest;
use App\Http\Requests\Status\StatusStoreRequest;
use App\Http\Requests\Status\StatusUpdateRequest;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatusController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->can('manage status')) {
                return redirect()->route('dashboard')->with('error', __('app.deny_access'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(StatusIndexRequest $request)
    {
        $statuses = Status::query();
        if ($request->has('search')) {
            $statuses->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $statuses->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;

        return Inertia::render('Status/Index', [
            'title'         => __('app.label.status'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'statuses'      => $statuses->paginate($perPage),
            'breadcrumbs'   => [['label' => __('app.label.status'), 'href' => route('status.index')]],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Status/Create', [
            'title'         => __('app.label.status'),
            'breadcrumbs' => [
                ['label' => __('app.label.status'), 'href' => route('status.index')],
                ['label' => __('app.label.create')]
            ],
            'statusOptions' => Status::getStatuses(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StatusStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $status = new Status();
            $status->name = $request->name;
            $status->status = $request->status;
            $status->save();

            DB::commit();
            return redirect()->route('status.index')->with('success', __('app.label.created_successfully', ['name' => $status->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.status')]) . ' ' . $th->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Status $status)
    {
        return Inertia::render('Status/Show', [
            'status' => $status,
            'title' => __('app.label.status'),
            'breadcrumbs' => [
                ['label' => __('app.label.status'), 'href' => route('status.index')],
                ['label' => $status->name]
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Status $status)
    {
        return inertia('Status/Edit', [
            'status' => $status,
            'title' => __('app.label.status'),
            'breadcrumbs' => [
                ['label' => __('app.label.status'), 'href' => route('status.index')],
                ['label' => $status->name]
            ],
            'statusOptions' => Status::getStatuses(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StatusUpdateRequest $request, Status $status)
    {
        DB::beginTransaction();

        try {
            $status->update([
                'name' => $request->name,
                'status' => $request->status
            ]);

            DB::commit();
            return redirect()->route('status.index')->with('success', __('app.label.updated_successfully', ['name' => $status->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $status->name]) . $th->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Status $status)
    {
        DB::beginTransaction();
        try {
            $status->delete();
            DB::commit();
            return redirect()->route('status.index')->with('success', __('app.label.deleted_successfully', ['name' => $status->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $status->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $status = Status::whereIn('id', $request->id);
            $status->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.status')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.status')]) . $th->getMessage());
        }
    }
}
