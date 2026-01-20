<?php

namespace Modules\Restaurant\Database\Seeders;


use App\Models\Widgets;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RestaurantRolePermissionSeed extends Seeder
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
            "restaurant-all-menus",

            "restaurant-menu-category-all",
            "restaurant-menu-category-create",
            "restaurant-menu-category-edit",
            "restaurant-menu-category-delete",
            "restaurant-menu-category-bulk_delete",
            "restaurant-menu-category-trash-all",
            "restaurant-menu-category-trash_restore",
            "restaurant-menu-category-trash_delete",
            "restaurant-menu-category-trash_bulk_delete",

            "restaurant-menu-sub-category-all",
            "restaurant-menu-sub-category-create",
            "restaurant-menu-sub-category-edit",
            "restaurant-menu-sub-category-delete",
            "restaurant-menu-subcategory-bulk_action",
            "restaurant-menu-subcategory-trash-all",
            "restaurant-menu-subcategory-trash_restore",
            "restaurant-menu-subcategory-trash_delete",
            "restaurant-menu-subcategory-trash_bulk_delete",

            "restaurant-menu-attribute-all",
            "restaurant-menu-attribute-create",
            "restaurant-menu-attribute-edit",
            "restaurant-menu-attribute-delete",
            "restaurant-menu-attribute-bulk_action",

            "restaurant-menu-manage-all",
            "restaurant-menu-manage-create",
            "restaurant-menu-manage-edit",
            "restaurant-menu-manage-delete",

            "restaurant-menu-tax-all",
            "restaurant-menu-tax-create",
            "restaurant-menu-tax-edit",
            "restaurant-menu-tax-delete",
            "restaurant-menu-tax-bulk_action",

            "restaurant-menu-attribute-all",
            "restaurant-menu-approved-all",
            "restaurant-menu-cancel-requested-all",
            "restaurant-menu-canceled-all",
            "restaurant-menu-report-all",
            "restaurant-menu-pending-orders",
            "restaurant-menu-cancel-requested-orders",
            "restaurant-menu-approved-orders",
            "restaurant-menu-canceled-orders",
            "restaurant-menu-order_report",
            "restaurant-menu-order-status-update",
            "restaurant-menu-order-report_search_export",

        ];

        if (in_array('restaurant',$check_feature_name)) {
            foreach ($permissions as $permission){
                \Spatie\Permission\Models\Permission::updateOrCreate(['name' => $permission,'guard_name' => 'admin']);
            }
            $demo_permissions = [];
            $role = Role::updateOrCreate(['name' => 'Super Admin','guard_name' => 'admin'],['name' => 'Super Admin','guard_name' => 'admin']);
            $role->syncPermissions($demo_permissions);
        }

    }

}


