<?php

namespace App\Repositories;

use App\Models\ProjectCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProjectCategoryRepository
{
    /**
     * Get paginated project categories with filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = ProjectCategory::query();

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['field'] ?? null, $filters['order'] ?? null);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['title'])) {
            $query->where('title', 'LIKE', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (isset($filters['status']) && $filters['status'] !== null && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
    }

    /**
     * Apply sorting to the query
     */
    protected function applySort(Builder $query, ?string $field, ?string $order): void
    {
        $sortableFields = ['title', 'sort', 'year', 'status', 'created_at'];

        if ($field && in_array($field, $sortableFields) && in_array($order, ['asc', 'desc'])) {
            $query->orderBy($field, $order);
        } else {
            $query->orderBy('sort')->latest();
        }
    }

    /**
     * Find project category by ID
     */
    public function find(int $id): ?ProjectCategory
    {
        return ProjectCategory::find($id);
    }

    /**
     * Get active categories ordered by sort
     */
    public function getActive(): Collection
    {
        return ProjectCategory::where('status', ProjectCategory::STATUS_ACTIVE)
            ->orderBy('sort')
            ->get();
    }

    /**
     * Create a new project category
     */
    public function create(array $data): ProjectCategory
    {
        return ProjectCategory::create($data);
    }

    /**
     * Update a project category
     */
    public function update(ProjectCategory $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * Delete a project category
     */
    public function delete(ProjectCategory $category): bool
    {
        return $category->delete();
    }

    /**
     * Delete multiple project categories
     */
    public function deleteBulk(array $ids): int
    {
        return ProjectCategory::whereIn('id', $ids)->delete();
    }

    /**
     * Get categories by IDs
     */
    public function findByIds(array $ids): Collection
    {
        return ProjectCategory::whereIn('id', $ids)->get();
    }

    /**
     * Get active categories by year
     */
    public function getByYear(int $year): Collection
    {
        return ProjectCategory::where('year', $year)
            ->where('status', ProjectCategory::STATUS_ACTIVE)
            ->orderBy('sort')
            ->get(['id', 'title']);
    }
}
