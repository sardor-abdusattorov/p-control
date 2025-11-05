<?php

namespace App\Services\Widgets;

use App\Models\Application;
use App\Models\Approvals;

class ApplicationWidget extends AbstractWidget
{
    /**
     * Define permissions required to view this widget
     */
    protected array $permissions = ['view application', 'view all applications', 'create application'];

    /**
     * Get widget data
     */
    public function getData(): array
    {
        $query = $this->getVisibleApplicationsQuery();

        return [
            'total' => $query->count(),
            'approved' => (clone $query)->where('status_id', Application::STATUS_APPROVED)->count(),
            'rejected' => (clone $query)->where('status_id', Application::STATUS_REJECTED)->count(),
            'inProgress' => (clone $query)->where('status_id', Application::STATUS_IN_PROGRESS)->count(),
            'new' => ($this->user->hasRole('superadmin') || $this->user->hasRole('manager'))
                ? (clone $query)->where('status_id', Application::STATUS_NEW)->count()
                : null,
        ];
    }

    /**
     * Get visible applications query based on user role
     */
    private function getVisibleApplicationsQuery()
    {
        if ($this->user->hasRole('superadmin')) {
            return Application::query();
        }

        if ($this->user->hasRole('manager')) {
            return Application::where('user_id', $this->user->id);
        }

        if ($this->user->hasRole(['lawyer', 'accountant', 'accounting'])) {
            $approvableIds = Approvals::where('approvable_type', Application::class)
                ->where('user_id', $this->user->id)
                ->pluck('approvable_id');

            return Application::where(function ($q) use ($approvableIds) {
                $q->whereIn('id', $approvableIds)
                    ->orWhere('type', 2);
            })->where(function ($q) {
                $q->where('status_id', '!=', Application::STATUS_NEW)
                    ->orWhere('type', 2);
            });
        }

        return Application::where('type', 2)
            ->where(function ($q) {
                $q->where('status_id', '!=', Application::STATUS_NEW)
                    ->orWhere('type', 2);
            });
    }

    /**
     * Get widget title
     */
    public function getTitle(): string
    {
        return __('app.label.applications') ?? 'Applications';
    }

    /**
     * Get widget type/identifier
     */
    public function getType(): string
    {
        return 'applications';
    }

    /**
     * Get widget icon
     */
    public function getIcon(): ?string
    {
        return 'inbox';
    }

    /**
     * Get widget color theme
     */
    public function getColor(): string
    {
        return 'blue';
    }
}
