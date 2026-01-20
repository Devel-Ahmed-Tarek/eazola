<?php

namespace Modules\Knowledgebase\Database\Seeders;


use App\Models\Widgets;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class knowledgeRolePermissionSeed extends Seeder
{
    public static function run()
    {
        $package = tenant()->payment_log()->first()?->package()->first() ?? [];

        $all_features = $package->plan_features ?? [];

        $payment_log = tenant()->payment_log()?->first() ?? [];


        if(empty($all_features) && @$payment_log->status != 'trial'){
            return;
        }
        $check_feature_name = $all_features->pluck('feature_name')->toArray();


        $permissions = [
            "knowledgebase-category-list",
            "knowledgebase-category-create",
            "knowledgebase-category-edit",
            "knowledgebase-category-delete",
        ];

        if(moduleExists('Knowledgebase'))
        {
            if (in_array('knowledgebase',$check_feature_name)) {
                foreach ($permissions as $permission) {
                    \Spatie\Permission\Models\Permission::updateOrCreate(['name' => $permission,'guard_name' => 'admin']);
                }
                $demo_permissions = [];
                $role = Role::updateOrCreate(['name' => 'Super Admin','guard_name' => 'admin'],['name' => 'Super Admin','guard_name' => 'admin']);
                $role->syncPermissions($demo_permissions);
            }
        }

    }

}


