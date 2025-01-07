<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Contract;
use App\Models\Task;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->can('view all tasks')) {
            $completedTasksCount = Task::where('status', 3)->count();
            $pendingTasksCount = Task::where('status', 2)->count();
            $totalTasksCount = Task::count();
        } else {
            $completedTasksCount = Task::where('assigned_user', $user->id)
                ->where('status', 3)
                ->count();
            $pendingTasksCount = Task::where('assigned_user', $user->id)
                ->where('status', 2)
                ->count();
            $totalTasksCount = Task::where('assigned_user', $user->id)->count();
        }

        if ($user->can('view all applications')) {
            $applicationsCount = Application::count();
        } else {
            $applicationsCount = Application::where('user_id', $user->id)->count();
        }

        if ($user->can('view all contracts')) {
            $contractsCount = Contract::count();
        } else {
            $contractsCount = Contract::where('user_id', $user->id)->count();
        }

        return Inertia::render('Dashboard', [
            'completedTasksCount' => $completedTasksCount,
            'totalTasksCount' => $totalTasksCount,
            'pendingTasksCount' => $pendingTasksCount,
            'applicationsCount' => $applicationsCount,
            'contractsCount' => $contractsCount,
        ]);
    }


}
