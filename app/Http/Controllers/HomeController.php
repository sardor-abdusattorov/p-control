<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Approvals;
use App\Models\Contract;
use App\Models\User;
use Inertia\Inertia;

class HomeController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        $applicationsQuery = $this->getVisibleApplicationsQuery($user);
        $contractsQuery = $this->getVisibleContractsQuery($user);

        return Inertia::render('Dashboard', [
            'applicationsCount' => $applicationsQuery->count(),
            'approvedApplicationsCount' => (clone $applicationsQuery)->where('status_id', Application::STATUS_APPROVED)->count(),
            'rejectedApplicationsCount' => (clone $applicationsQuery)->where('status_id', Application::STATUS_REJECTED)->count(),
            'inProgressApplicationsCount' => (clone $applicationsQuery)->where('status_id', Application::STATUS_IN_PROGRESS)->count(),
            'newApplicationsCount' => $user->hasRole('superadmin') || $user->hasRole('manager')
                ? (clone $applicationsQuery)->where('status_id', Application::STATUS_NEW)->count()
                : null,

            'contractsCount' => $contractsQuery->count(),
            'approvedContractsCount' => (clone $contractsQuery)->where('status', Contract::STATUS_APPROVED)->count(),
            'rejectedContractsCount' => (clone $contractsQuery)->where('status', Contract::STATUS_REJECTED)->count(),
            'inProgressContractsCount' => (clone $contractsQuery)->where('status', Contract::STATUS_IN_PROGRESS)->count(),
            'newContractsCount' => $user->hasRole('superadmin') || $user->hasRole('manager')
                ? (clone $contractsQuery)->where('status', Contract::STATUS_NEW)->count()
                : null,
        ]);
    }


    private function getVisibleContractsQuery(User $user)
    {
        if ($user->hasRole('superadmin')) {
            return Contract::query();
        }

        if ($user->hasRole('manager')) {
            return Contract::where('user_id', $user->id);
        }

        if ($user->hasRole(['lawyer', 'accountant', 'accounting'])) {
            $approvableIds = Approvals::where('approvable_type', Contract::class)
                ->where('user_id', $user->id)
                ->pluck('approvable_id');

            return Contract::whereIn('id', $approvableIds)
                ->where('status', '!=', Contract::STATUS_NEW);
        }

        return Contract::where('id', 0);
    }

    private function getVisibleApplicationsQuery(User $user)
    {
        if ($user->hasRole('superadmin')) {
            return Application::query();
        }

        if ($user->hasRole('manager')) {
            return Application::where('user_id', $user->id);
        }

        if ($user->hasRole(['lawyer', 'accountant', 'accounting'])) {
            $approvableIds = Approvals::where('approvable_type', Application::class)
                ->where('user_id', $user->id)
                ->pluck('approvable_id');

            return Application::where(function ($q) use ($approvableIds) {
                $q->whereIn('id', $approvableIds)
                    ->orWhere(function ($q2) {
                        $q2->where('type', 2)
                            ->where('status_id', '!=', Application::STATUS_NEW);
                    });
            });
        }

        return Application::where('type', 2)
            ->where(function ($q) {
                $q->where('status_id', '!=', Application::STATUS_NEW)
                    ->orWhere('type', 2);
            });
    }


}
