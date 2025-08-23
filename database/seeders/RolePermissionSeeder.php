<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create roles
        $RoleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $RoleAdmin      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $RoleUser       = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // get permissions
        $permissions = Permission::pluck('name')->toArray();
        $userPermissions = [
            'view-any Model',
            'view Model',
            'create Model',
            'update Model',
        ];
        foreach ($userPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web'
            ]);
        }

        // set permissions for role
        $RoleSuperAdmin->syncPermissions($permissions);
        $RoleAdmin->syncPermissions($permissions);
        $RoleUser->syncPermissions($userPermissions);

        // set role for user
        $roleMap = [
            'superadmin' => 'Super Admin',
            'admin'      => 'admin',
            'user'       => 'user',
        ];

        foreach ($roleMap as $username => $role) {
            $user = User::where('username', $username)->first();
            if ($user) {
                $user->syncRoles($role);
            }
        }
    }
}
