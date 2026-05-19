<?php

namespace App\Services;

use App\Models\User;
use App\Services\Widgets\AbstractWidget;
use App\Services\Widgets\ApplicationWidget;
use App\Services\Widgets\ContractWidget;
use App\Services\Widgets\ProductWidget;

class WidgetService
{
    protected User $user;
    protected array $availableWidgets = [
        ApplicationWidget::class,
        ContractWidget::class,
        ProductWidget::class,
    ];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getWidgets(): array
    {
        $widgets = [];

        foreach ($this->availableWidgets as $widgetClass) {
            $widget = new $widgetClass($this->user);

            if ($widget->canView()) {
                $widgets[] = $widget->toArray();
            }
        }

        return $widgets;
    }

    public function getWidget(string $type): ?array
    {
        foreach ($this->availableWidgets as $widgetClass) {
            $widget = new $widgetClass($this->user);

            if ($widget->getType() === $type && $widget->canView()) {
                return $widget->toArray();
            }
        }

        return null;
    }

    public function registerWidget(string $widgetClass): void
    {
        if (!in_array($widgetClass, $this->availableWidgets)) {
            $this->availableWidgets[] = $widgetClass;
        }
    }
}
