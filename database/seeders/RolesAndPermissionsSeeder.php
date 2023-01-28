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

        // create permissions
        Permission::create(['name' => 'user_show']);
        Permission::create(['name' => 'user_create']);
        Permission::create(['name' => 'user_update']);
        Permission::create(['name' => 'user_delete']);   
        Permission::create(['name' => 'user_access']);

        Permission::create(['name' => 'product_show']);
        Permission::create(['name' => 'product_create']);
        Permission::create(['name' => 'product_update']);
        Permission::create(['name' => 'product_delete']);   
        Permission::create(['name' => 'product_access']);

        Permission::create(['name' => 'inventory_state_show']);
        Permission::create(['name' => 'inventory_state_create']);
        Permission::create(['name' => 'inventory_state_update']);
        Permission::create(['name' => 'inventory_state_delete']);   
        Permission::create(['name' => 'inventory_state_access']);

        Permission::create(['name' => 'inventory_movement_show']);
        Permission::create(['name' => 'inventory_movement_create']);
        Permission::create(['name' => 'inventory_movement_update']);
        Permission::create(['name' => 'inventory_movement_delete']);   
        Permission::create(['name' => 'inventory_movement_access']);

        Permission::create(['name' => 'sale_show']);
        Permission::create(['name' => 'sale_create']);
        Permission::create(['name' => 'sale_update']);
        Permission::create(['name' => 'sale_delete']);   
        Permission::create(['name' => 'sale_access']);

        Permission::create(['name' => 'order_show']);
        Permission::create(['name' => 'order_create']);
        Permission::create(['name' => 'order_update']);
        Permission::create(['name' => 'order_delete']);   
        Permission::create(['name' => 'order_access']);

        Permission::create(['name' => 'result_show']);
        Permission::create(['name' => 'result_create']);
        Permission::create(['name' => 'result_update']);
        Permission::create(['name' => 'result_delete']);   
        Permission::create(['name' => 'result_access']);



        // create roles and assign created permissions

        // for admin
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['user_show','user_create' ,'user_update' ,'user_delete' ,'user_access', 
        'product_show','product_create' ,'product_update' ,'product_delete' ,'product_access',
        'inventory_state_show','inventory_state_create' ,'inventory_state_update' ,'inventory_state_delete' ,'inventory_state_access',
        'inventory_movement_show','inventory_movement_create' ,'inventory_movement_update' ,'inventory_movement_delete' ,'inventory_movement_access',
        'sale_show','sale_create' ,'sale_update' ,'sale_delete' ,'sale_access',
         ]);


        // for agente
        $role = Role::create(['name' => 'agente'])
            ->givePermissionTo(['order_show','order_create' ,'order_update' ,'order_delete' ,'order_access']);

        // for delivery
        $role = Role::create(['name' => 'delivery']);
        $role->givePermissionTo(['result_show','result_create' ,'result_update' ,'result_delete' ,'result_access']);
    }
}