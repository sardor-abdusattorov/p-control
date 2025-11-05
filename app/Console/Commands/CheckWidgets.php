<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Services\WidgetService;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CheckWidgets extends Command
{
    protected $signature = 'widgets:check {user_id?}';
    protected $description = 'Check widget system configuration and permissions';

    public function handle()
    {
        $this->info('=== Widget System Diagnostic ===');
        $this->newLine();

        // 1. Check permissions
        $this->info('1. Checking product permissions:');
        $permissions = Permission::whereIn('name', [
            'view products',
            'manage products',
            'create products',
            'update products',
            'delete products'
        ])->pluck('name')->toArray();

        if (count($permissions) > 0) {
            $this->info("✓ Found " . count($permissions) . " product permissions:");
            foreach ($permissions as $perm) {
                $this->line("  - $perm");
            }
        } else {
            $this->error('✗ NO product permissions found!');
            $this->warn('  Run: php artisan db:seed --class=ProductPermissionsSeeder');
            return 1;
        }

        $this->newLine();

        // 2. Check user
        $userId = $this->argument('user_id') ?? auth()->id() ?? 1;
        $user = User::find($userId);

        if (!$user) {
            $this->error("✗ User with ID $userId not found!");
            return 1;
        }

        $this->info("2. Checking user: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // 3. Check user permissions
        $this->info('3. User permissions:');
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        if (count($userPermissions) > 0) {
            $this->info("✓ User has " . count($userPermissions) . " permissions");

            $hasViewProducts = $user->can('view products');
            $hasManageProducts = $user->can('manage products');

            $this->line("  - Can 'view products': " . ($hasViewProducts ? '✓ YES' : '✗ NO'));
            $this->line("  - Can 'manage products': " . ($hasManageProducts ? '✓ YES' : '✗ NO'));

            if (!$hasViewProducts && !$hasManageProducts) {
                $this->warn("\n⚠ User does NOT have required permissions for ProductWidget!");
                $this->warn("  Give user one of these permissions:");
                $this->warn("  - view products");
                $this->warn("  - manage products");
            }
        } else {
            $this->error('✗ User has NO permissions!');
        }

        $this->newLine();

        // 4. Check roles
        $this->info('4. User roles:');
        $roles = $user->getRoleNames();
        if ($roles->count() > 0) {
            $this->info("✓ Roles: " . $roles->implode(', '));
        } else {
            $this->warn('⚠ User has NO roles');
        }

        $this->newLine();

        // 5. Check products
        $this->info('5. Products in database:');
        $totalProducts = Product::count();
        $userProducts = Product::where('user_id', $user->id)->count();

        $this->line("  Total products: $totalProducts");
        $this->line("  User's products: $userProducts");

        if ($totalProducts == 0) {
            $this->warn("\n⚠ No products in database! Widget will show empty data.");
        }

        $this->newLine();

        // 6. Test WidgetService
        $this->info('6. Testing WidgetService:');
        try {
            $widgetService = new WidgetService($user);
            $widgets = $widgetService->getWidgets();

            $this->info("✓ WidgetService returned " . count($widgets) . " widget(s)");

            if (count($widgets) > 0) {
                foreach ($widgets as $widget) {
                    $this->newLine();
                    $this->line("  Widget Type: {$widget['type']}");
                    $this->line("  Title: {$widget['title']}");
                    $this->line("  Can View: " . ($widget['canView'] ? '✓ YES' : '✗ NO'));

                    if (isset($widget['data']['statistics'])) {
                        $stats = $widget['data']['statistics'];
                        $this->line("  Statistics:");
                        $this->line("    - Total: {$stats['total']}");
                        $this->line("    - Active: {$stats['active']}");
                        $this->line("    - Inactive: {$stats['inactive']}");
                    }
                }
            } else {
                $this->error("\n✗ NO widgets returned!");
                $this->error("  Reason: User doesn't have required permissions");
            }

        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        $this->newLine();
        $this->info('=== End Diagnostic ===');

        return 0;
    }
}
