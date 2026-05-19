<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function paginateWithFilters(array $filters, User $user, int $perPage = 10): LengthAwarePaginator
    {
        $query = Product::query()->with(['user', 'category', 'brand']);

        $this->applyAccessControl($query, $user);
        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['field'] ?? null, $filters['order'] ?? null);

        return $query->paginate($perPage)->appends($filters);
    }

    protected function applyAccessControl(Builder $query, User $user): void
    {
        if ($user->hasRole('superadmin')) {
            return;
        }

        if ($user->can('manage products')) {
            return;
        }

        $query->where('user_id', $user->id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['title'])) {
            $query->where('title', 'LIKE', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['inventory_number'])) {
            $query->where('inventory_number', 'LIKE', '%' . $filters['inventory_number'] . '%');
        }

        if (!empty($filters['serial_number'])) {
            $query->where('serial_number', 'LIKE', '%' . $filters['serial_number'] . '%');
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->whereIn('status', (array) $filters['status']);
        }
    }

    protected function applySort(Builder $query, ?string $field, ?string $order): void
    {
        $sortableFields = ['title', 'user_id', 'category_id', 'brand_id', 'status', 'sort', 'created_at', 'updated_at'];

        if ($field && in_array($field, $sortableFields) && in_array($order, ['asc', 'desc'])) {
            $query->orderBy($field, $order);
        } else {
            $query->latest('updated_at');
        }
    }

    public function getAvailableUsers(User $user): Collection
    {
        if ($user->hasRole('superadmin') || $user->can('manage products')) {
            return User::where('status', User::STATUS_ACTIVE)->get();
        }

        return User::where('id', $user->id)->get();
    }

    public function findWithRelations(int $id, array $relations = ['user', 'category', 'brand']): ?Product
    {
        return Product::with($relations)->find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function deleteBulk(array $ids): int
    {
        return Product::whereIn('id', $ids)->delete();
    }

    public function findByIds(array $ids): Collection
    {
        return Product::whereIn('id', $ids)->get();
    }
}
