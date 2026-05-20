<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'update approved contract',
            'guard_name' => 'web',
        ]);

        $superadmin = Role::where('name', 'superadmin')->first();
        if ($superadmin && !$superadmin->hasPermissionTo($permission)) {
            $superadmin->givePermissionTo($permission);
        }

        app()['cache']->forget('spatie.permission.cache');
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'update approved contract')->first();
        if ($permission) {
            $permission->delete();
        }

        app()['cache']->forget('spatie.permission.cache');
    }
};
