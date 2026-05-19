<?php

namespace App\Services\Widgets;

use App\Models\Contract;

class ContractWidget extends AbstractWidget
{
    protected array $permissions = ['view contract', 'view all contracts', 'create contract'];

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

    private function getVisibleContractsQuery()
    {
        if ($this->user->can('view all contracts')) {
            return Contract::query();
        }

        return Contract::where('user_id', $this->user->id);
    }

    public function getTitle(): string
    {
        return __('app.label.contracts') ?? 'Contracts';
    }

    public function getType(): string
    {
        return 'contracts';
    }

    public function getIcon(): ?string
    {
        return 'document';
    }

    public function getColor(): string
    {
        return 'green';
    }
}
