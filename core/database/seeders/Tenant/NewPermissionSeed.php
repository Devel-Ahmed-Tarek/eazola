<?php

namespace Database\Seeders\Tenant;

use App\Mail\TenantCredentialMail;
use App\Models\Admin;
use App\Models\Language;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class NewPermissionSeed extends Seeder
{

    public static function run()
    {

        $permissions = [
            'advertisement-list', 'advertisement-create', 'advertisement-edit', 'advertisement-delete', 'advertisement-settings'
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::updateOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }
        $demo_permissions = [];
        $role = Role::updateOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin'], ['name' => 'Super Admin', 'guard_name' => 'admin']);
        $role->syncPermissions($demo_permissions);
    }
}
