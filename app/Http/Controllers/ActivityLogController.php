<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityLog\ActivityLogIndexRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login');
            }
            $permissions = [
                'view logs' => ['logs.index', 'logs.show'],
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

    public function index(ActivityLogIndexRequest $request)
    {
        $logs = ActivityLog::query();
        if ($request->has('search')) {
            $logs->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $logs->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;

        return Inertia::render('ActivityLog/Index', [
            'title'         => __('app.label.logs'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'logs'      => $logs->paginate($perPage),
            'breadcrumbs'   => [['label' => __('app.label.logs'), 'href' => route('logs.index')]],
        ]);
    }
}
