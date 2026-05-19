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

    abstract public function getData(): array;

    abstract public function getTitle(): string;

    abstract public function getType(): string;

    public function getIcon(): ?string
    {
        return null;
    }

    public function getColor(): string
    {
        return 'gray';
    }

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
