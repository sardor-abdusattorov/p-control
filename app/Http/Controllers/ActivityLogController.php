<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityLog\ActivityLogIndexRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\ActivityLogger;

class ActivityLogController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->can('view logs')) {
                return redirect()->route('dashboard')->with('error', __('app.deny_access'));
            }
            return $next($request);
        });
    }


    /**
     * Display a listing of the resource.
     */
    public function index(ActivityLogIndexRequest $request)
    {
        $logs = ActivityLog::query()
            ->with('user');

        if ($request->has('search')) {
            $logs->where('description', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $logs->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 50;

        return Inertia::render('ActivityLog/Index', [
            'title'         => __('app.label.logs'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'logs'      => $logs->paginate($perPage),
            'breadcrumbs'   => [['label' => __('app.label.logs'), 'href' => route('logs.index')]],
        ]);
    }

    /**
     * Display the specified resource.
     */

    public function show(ActivityLog $log)
    {

        if (!$log) {
            abort(404, 'Лог не найден');
        }

        $log->load(['user']);

        return Inertia::render('ActivityLog/Show', [
            'title' => $log->log_name ?? 'Детали лога',
            'activityLog' => $log,
            'breadcrumbs' => [
                ['label' => __('app.label.logs'), 'href' => route('logs.index')],
                ['label' => $log->log_name ?? 'Лог'],
            ],
        ]);
    }



}
