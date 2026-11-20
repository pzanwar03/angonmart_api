<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('clients');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = Permission::query()->where('name', 'customer')->value('guard_name') ?? 'api';

        $permission = Permission::firstOrCreate([
            'name' => 'client',
            'guard_name' => $guard,
        ]);

        Role::firstOrCreate([
            'name' => 'client',
            'guard_name' => $guard,
        ])->syncPermissions([$permission]);
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::query()->where('name', 'client')->first();
        if ($role) {
            $role->delete();
        }

        $permission = Permission::query()->where('name', 'client')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
