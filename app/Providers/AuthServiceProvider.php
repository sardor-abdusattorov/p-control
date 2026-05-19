<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Contract;
use App\Policies\ApplicationPolicy;
use App\Policies\ContractPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Application::class => \App\Policies\ApplicationPolicy::class,
        \App\Models\Contract::class => \App\Policies\ContractPolicy::class,
        \App\Models\Product::class => \App\Policies\ProductPolicy::class,
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\ProjectCategory::class => \App\Policies\ProjectCategoryPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
