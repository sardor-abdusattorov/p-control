<?php

namespace App\Repositories;

use App\Models\Application;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ApplicationRepository
{
    public function paginateWithFilters(array $filters, User $user, int $perPage = 10): LengthAwarePaginator
    {
        $query = Application::query()->with(['user', 'project']);

        $this->applyAccessControl($query, $user);
        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['field'] ?? null, $filters['order'] ?? null);

        return $query->paginate($perPage)->appends($filters);
    }

    protected function applyAccessControl(Builder $query, User $user): void
    {
        $canViewAll = $user->can('view all applications');
        $canApprove = $user->can('approve application');

        if (!$canViewAll && !$canApprove) {
            $query->where('user_id', $user->id);
            return;
        }

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

    protected function applySort(Builder $query, ?string $field, ?string $order): void
    {
        $sortableFields = ['title', 'user_id', 'project_id', 'status_id', 'type'];

        if ($field && in_array($field, $sortableFields) && in_array($order, ['asc', 'desc'])) {
            $query->orderBy($field, $order);
        } else {
            $query->latest('updated_at');
        }
    }

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

    public function findWithRelations(int $id, array $relations = ['user', 'currency']): ?Application
    {
        return Application::with($relations)->find($id);
    }

    public function create(array $data): Application
    {
        return Application::create($data);
    }

    public function update(Application $application, array $data): bool
    {
        return $application->update($data);
    }

    public function delete(Application $application): bool
    {
        return $application->delete();
    }

    public function deleteBulk(array $ids): int
    {
        return Application::whereIn('id', $ids)->delete();
    }

    public function findByIds(array $ids): Collection
    {
        return Application::whereIn('id', $ids)->get();
    }

    public function hasNonNewApprovals(Application $application): bool
    {
        return $application->approvals()
            ->where('approved', '!=', \App\Models\Approvals::STATUS_NEW)
            ->exists();
    }
}
