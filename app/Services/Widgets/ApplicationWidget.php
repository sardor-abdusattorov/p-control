<?php

namespace App\Services\Widgets;

use App\Models\Application;

class ApplicationWidget extends AbstractWidget
{
    protected array $permissions = ['view application', 'view all applications', 'create application'];

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

    private function getVisibleApplicationsQuery()
    {
        if ($this->user->can('view all applications')) {
            return Application::query();
        }

        return Application::where('user_id', $this->user->id);
    }

    public function getTitle(): string
    {
        return __('app.label.applications') ?? 'Applications';
    }

    public function getType(): string
    {
        return 'applications';
    }

    public function getIcon(): ?string
    {
        return 'inbox';
    }

    public function getColor(): string
    {
        return 'blue';
    }
}
