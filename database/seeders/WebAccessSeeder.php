<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class WebAccessSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $newPermissions = [];
        foreach (config('web_permissions') as $permissions) {
            foreach (array_keys($permissions) as $name) {
                $existing = Permission::where('name', $name)->where('guard_name', 'admin')->exists();
                $permission = Permission::findOrCreate($name, 'admin');
                if (! $existing) {
                    $newPermissions[] = $permission;
                }
            }
        }

        Permission::findOrCreate('super admin', 'admin');
        $role = Role::find(1);
        if ($role && ($role->name !== 'super-admin' || $role->guard_name !== 'admin')) {
            throw new \RuntimeException('Role ID 1 is already occupied by another role.');
        }
        if (! $role && Role::where('name', 'super-admin')->where('guard_name', 'admin')->exists()) {
            throw new \RuntimeException('super-admin already exists with an ID other than 1.');
        }
        $newRole = ! $role;
        if ($newRole) {
            $role = new Role();
            $role->forceFill(['id' => 1, 'name' => 'super-admin', 'guard_name' => 'admin'])->save();
        }
        if ($newRole) {
            $role->syncPermissions(Permission::where('guard_name', 'admin')->get());
        } else {
            $role->givePermissionTo('super admin');
            $role->givePermissionTo($newPermissions);
        }

        $admin = User::find(1);
        if ($admin && $admin->email !== 'admin@admin.com') {
            throw new \RuntimeException('User ID 1 is already occupied by another account.');
        }
        if (! $admin && User::where('email', 'admin@admin.com')->exists()) {
            throw new \RuntimeException('admin@admin.com already exists with an ID other than 1.');
        }
        if (! $admin) {
            $admin = new User();
            $admin->forceFill([
                'id' => 1,
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin'),
            ])->save();
        }
        $admin->syncRoles($role);
        $admin->forceFill(['account_type' => 'admin', 'roles_name' => 'super-admin'])->save();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
