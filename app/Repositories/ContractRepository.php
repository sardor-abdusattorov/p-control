<?php

namespace App\Repositories;

use App\Models\Approvals;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ContractRepository
{
    /**
     * Get paginated contracts with filters and access control
     */
    public function paginateWithFilters(array $filters, User $user, int $perPage = 10): LengthAwarePaginator
    {
        $query = Contract::query()->with(['user', 'currency']);

        $this->applyAccessControl($query, $user);
        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['field'] ?? null, $filters['order'] ?? null);

        return $query->paginate($perPage)->appends($filters);
    }

    /**
     * Apply access control based on user permissions
     */
    protected function applyAccessControl(Builder $query, User $user): void
    {
        $canViewAll = $user->can('view all contracts');
        $canApprove = $user->can('approve contract');

        if (!$canViewAll && !$canApprove) {
            // User can only see their own contracts
            $query->where('user_id', $user->id);
            return;
        }

        // If user can approve but not view all, exclude new status contracts
        if ($canApprove && !$canViewAll) {
            $query->where('status', '!=', Contract::STATUS_NEW);
        }
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['contract_number'])) {
            $query->where('contract_number', 'LIKE', '%' . $filters['contract_number'] . '%');
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'LIKE', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', (int) $filters['status']);
        }

        if (!empty($filters['currency_id'])) {
            $query->where('currency_id', (int) $filters['currency_id']);
        }

        if (isset($filters['payment_status']) && $filters['payment_status'] !== '') {
            $query->where('payment_status', (int) $filters['payment_status']);
        }

        // Apply approval filter
        if (!empty($filters['approval_filter'])) {
            $this->applyApprovalFilter($query, $filters['approval_filter']);
        }
    }

    /**
     * Apply approval-specific filters
     */
    protected function applyApprovalFilter(Builder $query, string $filter): void
    {
        if ($filter === 'not_approved_by_me') {
            $query->whereHas('approvals', function ($q) {
                $q->where('user_id', auth()->id())
                    ->whereIn('approved', [
                        Approvals::STATUS_NEW,
                        Approvals::STATUS_PENDING,
                        Approvals::STATUS_REJECTED,
                    ]);
            });
        }

        if ($filter === 'approved_by_me') {
            $query->whereHas('approvals', function ($q) {
                $q->where('user_id', auth()->id())
                    ->where('approved', Approvals::STATUS_APPROVED);
            });
        }
    }

    /**
     * Apply sorting to the query
     */
    protected function applySort(Builder $query, ?string $field, ?string $order): void
    {
        $sortableFields = ['contract_number', 'title', 'user_id', 'status', 'currency_id', 'budget_sum', 'deadline', 'updated_at'];

        if ($field && in_array($field, $sortableFields) && in_array($order, ['asc', 'desc'])) {
            $query->orderBy($field, $order);
        } else {
            $query->latest('updated_at');
        }
    }

    /**
     * Get users list based on access permissions
     */
    public function getAvailableUsers(User $user): Collection
    {
        $canViewAll = $user->can('view all contracts');
        $canApprove = $user->can('approve contract');

        if ($canViewAll || $canApprove) {
            return User::where('status', 1)
                ->when(
                    $canApprove && !$canViewAll,
                    fn($q) => $q->where('id', '!=', $user->id)
                )
                ->get();
        }

        return User::where('id', $user->id)->get();
    }

    /**
     * Get approvals for multiple contracts
     */
    public function getApprovalsByContractIds(array $contractIds): SupportCollection
    {
        return Approvals::where('approvable_type', Contract::class)
            ->whereIn('approvable_id', $contractIds)
            ->with('user')
            ->get()
            ->groupBy('approvable_id')
            ->map(function ($group) {
                return $group->map(function ($approval) {
                    return [
                        'user_id' => $approval->user_id,
                        'user_name' => optional($approval->user)->name,
                        'approved' => $approval->approved,
                        'approved_at' => optional($approval->approved_at)?->format('d.m.Y H:i'),
                        'updated_at' => optional($approval->updated_at)?->format('d.m.Y H:i'),
                        'reason' => $approval->reason,
                    ];
                });
            });
    }

    /**
     * Find contract by ID with relations
     */
    public function findWithRelations(int $id, array $relations = ['user', 'currency']): ?Contract
    {
        return Contract::with($relations)->find($id);
    }

    /**
     * Create a new contract
     */
    public function create(array $data): Contract
    {
        return Contract::create($data);
    }

    /**
     * Update a contract
     */
    public function update(Contract $contract, array $data): bool
    {
        return $contract->update($data);
    }

    /**
     * Delete a contract
     */
    public function delete(Contract $contract): bool
    {
        return $contract->delete();
    }

    /**
     * Delete multiple contracts
     */
    public function deleteBulk(array $ids): int
    {
        return Contract::whereIn('id', $ids)->delete();
    }

    /**
     * Get contracts by IDs
     */
    public function findByIds(array $ids): Collection
    {
        return Contract::whereIn('id', $ids)->get();
    }

    /**
     * Check if contract has non-new approvals
     */
    public function hasNonNewApprovals(Contract $contract): bool
    {
        return $contract->approvals()
            ->where('approved', '!=', Approvals::STATUS_NEW)
            ->exists();
    }
}
