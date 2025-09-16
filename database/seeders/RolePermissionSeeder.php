<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'manage-knowledge-base',
            'manage-helpdesk',
            'generate-ai-answers',
            'manage-tags',
            'view-helpdesk',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        $supportAgent = Role::firstOrCreate(['name' => 'support-agent']);
        $supportAgent->syncPermissions(['manage-helpdesk', 'view-helpdesk', 'generate-ai-answers']);

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions(['manage-knowledge-base', 'manage-tags']);

        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions(['view-helpdesk']);
    }
}
