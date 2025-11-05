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
        // Add more widgets here as needed
    ];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get all widgets available for the current user
     */
    public function getWidgets(): array
    {
        $widgets = [];

        foreach ($this->availableWidgets as $widgetClass) {
            /** @var AbstractWidget $widget */
            $widget = new $widgetClass($this->user);

            if ($widget->canView()) {
                $widgets[] = $widget->toArray();
            }
        }

        return $widgets;
    }

    /**
     * Get a specific widget by type
     */
    public function getWidget(string $type): ?array
    {
        foreach ($this->availableWidgets as $widgetClass) {
            /** @var AbstractWidget $widget */
            $widget = new $widgetClass($this->user);

            if ($widget->getType() === $type && $widget->canView()) {
                return $widget->toArray();
            }
        }

        return null;
    }

    /**
     * Register a new widget
     */
    public function registerWidget(string $widgetClass): void
    {
        if (!in_array($widgetClass, $this->availableWidgets)) {
            $this->availableWidgets[] = $widgetClass;
        }
    }
}
