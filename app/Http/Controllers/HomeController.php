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

        // Applications
        if ($user->can('view all applications')) {
            $applicationsCount = Application::count();
            $approvedApplicationsCount = Application::where('status_id', Application::STATUS_APPROVED)->count();
            $rejectedApplicationsCount = Application::where('status_id', Application::STATUS_REJECTED)->count();
            $inProgressApplicationsCount = Application::where('status_id', Application::STATUS_IN_PROGRESS)->count();
            $newApplicationsCount = Application::where('status_id', Application::STATUS_NEW)->count();
        } else {
            $applicationsCount = Application::where('user_id', $user->id)->count();
            $approvedApplicationsCount = Application::where('user_id', $user->id)->where('status_id', Application::STATUS_APPROVED)->count();
            $rejectedApplicationsCount = Application::where('user_id', $user->id)->where('status_id', Application::STATUS_REJECTED)->count();
            $inProgressApplicationsCount = Application::where('user_id', $user->id)->where('status_id', Application::STATUS_IN_PROGRESS)->count();
            $newApplicationsCount = Application::where('user_id', $user->id)->where('status_id', Application::STATUS_NEW)->count();
        }

        // Contracts
        if ($user->can('view all contracts')) {
            $contractsCount = Contract::count();
            $approvedContractsCount = Contract::where('status', Contract::STATUS_APPROVED)->count();
            $rejectedContractsCount = Contract::where('status', Contract::STATUS_REJECTED)->count();
            $inProgressContractsCount = Contract::where('status', Contract::STATUS_IN_PROGRESS)->count();
            $newContractsCount = Contract::where('status', Contract::STATUS_NEW)->count();
        } else {
            $contractsCount = Contract::where('user_id', $user->id)->count();
            $approvedContractsCount = Contract::where('user_id', $user->id)->where('status', Contract::STATUS_APPROVED)->count();
            $rejectedContractsCount = Contract::where('user_id', $user->id)->where('status', Contract::STATUS_REJECTED)->count();
            $inProgressContractsCount = Contract::where('user_id', $user->id)->where('status', Contract::STATUS_IN_PROGRESS)->count();
            $newContractsCount = Contract::where('user_id', $user->id)->where('status', Contract::STATUS_NEW)->count();
        }

        return Inertia::render('Dashboard', [
            // Applications data
            'applicationsCount' => $applicationsCount,
            'approvedApplicationsCount' => $approvedApplicationsCount,
            'rejectedApplicationsCount' => $rejectedApplicationsCount,
            'inProgressApplicationsCount' => $inProgressApplicationsCount,
            'newApplicationsCount' => $newApplicationsCount,

            // Contracts data
            'contractsCount' => $contractsCount,
            'approvedContractsCount' => $approvedContractsCount,
            'rejectedContractsCount' => $rejectedContractsCount,
            'inProgressContractsCount' => $inProgressContractsCount,
            'newContractsCount' => $newContractsCount,
        ]);
    }

}
