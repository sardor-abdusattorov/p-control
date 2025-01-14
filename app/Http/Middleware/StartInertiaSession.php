<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Session\Middleware\StartSession;

class StartInertiaSession extends StartSession
{
    /**
     * Store the current URL for the request if necessary.
     *
     * @param Request $request
     * @param  Session  $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session): void
    {
        if ($request->isMethod('GET') &&
            $request->route() instanceof Route &&
            ! ($request->ajax() && ! $request->inertia()) &&
            ! $request->prefetch() &&
            ! $request->isPrecognitive()) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }
}
