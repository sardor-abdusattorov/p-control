<?php

namespace App\Services\Widgets;

use App\Models\Product;

class ProductWidget extends AbstractWidget
{
    protected array $permissions = ['view products', 'manage products'];

    public function getData(): array
    {
        $products = Product::with(['category', 'brand'])
            ->where('user_id', $this->user->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->latest()
            ->limit(5)
            ->get();

        $totalCount = Product::where('user_id', $this->user->id)->count();
        $activeCount = Product::where('user_id', $this->user->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->count();
        $inactiveCount = Product::where('user_id', $this->user->id)
            ->where('status', Product::STATUS_INACTIVE)
            ->count();

        return [
            'total' => $totalCount,
            'active' => $activeCount,
            'inactive' => $inactiveCount,
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'description' => $product->description,
                    'serial_number' => $product->serial_number,
                    'inventory_number' => $product->inventory_number,
                    'category' => $product->category?->name,
                    'brand' => $product->brand?->name,
                    'status' => $product->status,
                ];
            }),
        ];
    }

    public function getTitle(): string
    {
        return __('app.label.products') ?? 'Products';
    }

    public function getType(): string
    {
        return 'products';
    }

    public function getIcon(): ?string
    {
        return 'cube';
    }

    public function getColor(): string
    {
        return 'purple';
    }
}
