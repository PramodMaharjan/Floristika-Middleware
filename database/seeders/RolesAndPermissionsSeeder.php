<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'view orders']);
        Permission::create(['name' => 'edit orders']);

        // Create roles and assign permissions
        $role1 = Role::create(['name' => 'super admin']);
        $role1->givePermissionTo('create users');
        $role1->givePermissionTo('view orders');
        $role1->givePermissionTo('edit orders');

        $role2 = Role::create(['name' => 'admin']);
        $role2->givePermissionTo('view orders');
        $role2->givePermissionTo('edit orders');

        $role3 = Role::create(['name' => 'viewer']);
        $role3->givePermissionTo('view orders');

    }
}
