<?php

namespace App\Repositories;

use App\Models\Application;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ApplicationRepository
{
    /**
     * Get paginated applications with filters and access control
     */
    public function paginateWithFilters(array $filters, User $user, int $perPage = 10): LengthAwarePaginator
    {
        $query = Application::query()->with(['user', 'project']);

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
        $canViewAll = $user->can('view all applications');
        $canApprove = $user->can('approve application');

        if (!$canViewAll && !$canApprove) {
            // User can only see their own applications
            $query->where('user_id', $user->id);
            return;
        }

        // If user can approve but not view all, apply special logic
        if ($canApprove && !$canViewAll) {
            $query->where(function ($q) {
                $q->where('type', '!=', Application::TYPE_REQUEST)
                    ->orWhere(function ($q2) {
                        $q2->where('type', Application::TYPE_REQUEST)
                            ->where('status_id', '!=', Application::STATUS_NEW);
                    });
            });
        }
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['title'])) {
            $query->where('title', 'LIKE', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status_id'])) {
            $query->whereIn('status_id', (array) $filters['status_id']);
        }

        if (!empty($filters['type'])) {
            $query->whereIn('type', (array) $filters['type']);
        }
    }

    /**
     * Apply sorting to the query
     */
    protected function applySort(Builder $query, ?string $field, ?string $order): void
    {
        $sortableFields = ['title', 'user_id', 'project_id', 'status_id', 'type'];

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
        $canViewAll = $user->can('view all applications');
        $canApprove = $user->can('approve application');

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
     * Find application by ID with relations
     */
    public function findWithRelations(int $id, array $relations = ['user', 'currency']): ?Application
    {
        return Application::with($relations)->find($id);
    }

    /**
     * Create a new application
     */
    public function create(array $data): Application
    {
        return Application::create($data);
    }

    /**
     * Update an application
     */
    public function update(Application $application, array $data): bool
    {
        return $application->update($data);
    }

    /**
     * Delete an application
     */
    public function delete(Application $application): bool
    {
        return $application->delete();
    }

    /**
     * Delete multiple applications
     */
    public function deleteBulk(array $ids): int
    {
        return Application::whereIn('id', $ids)->delete();
    }

    /**
     * Get applications by IDs
     */
    public function findByIds(array $ids): Collection
    {
        return Application::whereIn('id', $ids)->get();
    }

    /**
     * Check if application has non-new approvals
     */
    public function hasNonNewApprovals(Application $application): bool
    {
        return $application->approvals()
            ->where('approved', '!=', \App\Models\Approvals::STATUS_NEW)
            ->exists();
    }
}
