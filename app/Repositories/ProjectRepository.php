<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    public function paginateWithFilters(array $filters, User $user, int $perPage = 100): LengthAwarePaginator
    {
        $query = Project::query()->with(['category', 'currency', 'contracts' => function ($q) use ($user) {
            if (!$user->can('view all contracts')) {
                $q->where('user_id', $user->id);
            }
        }]);

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['field'] ?? null, $filters['order'] ?? null);

        return $query->paginate($perPage)->appends($filters);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('project_number', 'LIKE', '%' . $filters['search'] . '%');
            });
        }
    }

    protected function applySort(Builder $query, ?string $field, ?string $order): void
    {
        $sortableFields = ['title', 'project_number', 'category_id', 'sort', 'status_id'];

        if ($field && in_array($field, $sortableFields) && in_array($order, ['asc', 'desc'])) {
            if ($field === 'project_number') {
                $query->orderByRaw('CAST(project_number AS UNSIGNED) ' . $order);
            } else {
                $query->orderBy($field, $order);
            }
        } else {
            $query->latest('updated_at');
        }
    }

    public function findWithRelations(int $id, array $relations = ['category', 'currency']): ?Project
    {
        return Project::with($relations)->find($id);
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    public function deleteBulk(array $ids): int
    {
        return Project::whereIn('id', $ids)->delete();
    }

    public function findByIds(array $ids): Collection
    {
        return Project::whereIn('id', $ids)->get();
    }
}
