<?php

namespace App\Services\Widgets;

use App\Models\Application;

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
            'new' => $this->user->hasRole('superadmin')
                ? (clone $query)->where('status_id', Application::STATUS_NEW)->count()
                : null,
        ];
    }

    /**
     * Get visible applications query based on user permissions
     */
    private function getVisibleApplicationsQuery()
    {
        if ($this->user->can('view all applications')) {
            return Application::query();
        }

        return Application::where('user_id', $this->user->id);
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
