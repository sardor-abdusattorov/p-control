<?php

namespace App\Services\Widgets;

use App\Models\User;

abstract class AbstractWidget
{
    protected User $user;
    protected array $permissions = [];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Check if user has permission to view this widget
     */
    public function canView(): bool
    {
        if (empty($this->permissions)) {
            return true;
        }

        foreach ($this->permissions as $permission) {
            if ($this->user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get widget data
     */
    abstract public function getData(): array;

    /**
     * Get widget title
     */
    abstract public function getTitle(): string;

    /**
     * Get widget type/identifier
     */
    abstract public function getType(): string;

    /**
     * Get widget icon (optional)
     */
    public function getIcon(): ?string
    {
        return null;
    }

    /**
     * Get widget color theme (optional)
     */
    public function getColor(): string
    {
        return 'gray';
    }

    /**
     * Convert widget to array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'data' => $this->getData(),
            'canView' => $this->canView(),
        ];
    }
}
