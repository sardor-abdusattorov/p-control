<?php

namespace App\Http\Controllers;

use App\Services\WidgetService;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get widgets for the user
        $widgetService = new WidgetService($user);
        $widgets = $widgetService->getWidgets();

        return Inertia::render('Dashboard', [
            'widgets' => $widgets,
        ]);
    }
}
