<?php

namespace App\Services\Widgets;

use App\Models\Product;

class ProductWidget extends AbstractWidget
{
    /**
     * Define permissions required to view this widget
     */
    protected array $permissions = ['view products', 'manage products'];

    /**
     * Get widget data
     */
    public function getData(): array
    {
        // All users see only THEIR products (including superadmin)
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

    /**
     * Get widget title
     */
    public function getTitle(): string
    {
        return __('app.label.products') ?? 'Products';
    }

    /**
     * Get widget type/identifier
     */
    public function getType(): string
    {
        return 'products';
    }

    /**
     * Get widget icon
     */
    public function getIcon(): ?string
    {
        return 'cube';
    }

    /**
     * Get widget color theme
     */
    public function getColor(): string
    {
        return 'purple';
    }
}
