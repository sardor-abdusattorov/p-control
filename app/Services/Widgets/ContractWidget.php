<?php

namespace App\Services\Widgets;

use App\Models\Contract;
use App\Models\Approvals;

class ContractWidget extends AbstractWidget
{
    /**
     * Define permissions required to view this widget
     */
    protected array $permissions = ['view contract', 'view all contracts', 'create contract'];

    /**
     * Get widget data
     */
    public function getData(): array
    {
        $query = $this->getVisibleContractsQuery();

        return [
            'total' => $query->count(),
            'approved' => (clone $query)->where('status', Contract::STATUS_APPROVED)->count(),
            'rejected' => (clone $query)->where('status', Contract::STATUS_REJECTED)->count(),
            'inProgress' => (clone $query)->where('status', Contract::STATUS_IN_PROGRESS)->count(),
            'new' => ($this->user->hasRole('superadmin') || $this->user->hasRole('manager'))
                ? (clone $query)->where('status', Contract::STATUS_NEW)->count()
                : null,
        ];
    }

    /**
     * Get visible contracts query based on user role
     */
    private function getVisibleContractsQuery()
    {
        if ($this->user->hasRole('superadmin')) {
            return Contract::query();
        }

        if ($this->user->hasRole('manager')) {
            return Contract::where('user_id', $this->user->id);
        }

        if ($this->user->hasRole(['lawyer', 'accountant', 'accounting'])) {
            $approvableIds = Approvals::where('approvable_type', Contract::class)
                ->where('user_id', $this->user->id)
                ->pluck('approvable_id');

            return Contract::whereIn('id', $approvableIds)
                ->where('status', '!=', Contract::STATUS_NEW);
        }

        return Contract::where('id', 0);
    }

    /**
     * Get widget title
     */
    public function getTitle(): string
    {
        return __('app.label.contracts') ?? 'Contracts';
    }

    /**
     * Get widget type/identifier
     */
    public function getType(): string
    {
        return 'contracts';
    }

    /**
     * Get widget icon
     */
    public function getIcon(): ?string
    {
        return 'document';
    }

    /**
     * Get widget color theme
     */
    public function getColor(): string
    {
        return 'green';
    }
}
