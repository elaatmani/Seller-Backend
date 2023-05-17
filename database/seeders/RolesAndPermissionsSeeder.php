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

        // Create permissions New
        //Account
        Permission::create(['name' => 'update_account', 'description' => 'Update account infos']);
        Permission::create(['name' => 'access_to_account', 'description' => 'Access to account infos']);

        //Users
        Permission::create(['name' => 'show_all_users', 'description' => 'View all users']);
        Permission::create(['name' => 'view_user', 'description' => 'View a specific user']);
        Permission::create(['name' => 'create_user', 'description' => 'Create a new user']);
        Permission::create(['name' => 'update_user', 'description' => 'Update a user']);
        Permission::create(['name' => 'update_user_status', 'description' => 'Update a user status']);
        Permission::create(['name' => 'delete_user', 'description' => 'Delete a user']);
        Permission::create(['name' => 'access_to_users', 'description' => 'Access to user management']);

        //Warehouse
        Permission::create(['name' => 'show_all_warehouses', 'description' => 'View all warehouses']);
        Permission::create(['name' => 'view_warehouse', 'description' => 'View a specific warehouse']);
        Permission::create(['name' => 'create_warehouse', 'description' => 'Create a new warehouse']);
        Permission::create(['name' => 'update_warehouse', 'description' => 'Update a warehouse']);
        Permission::create(['name' => 'delete_warehouse', 'description' => 'Delete a warehouse']);
        Permission::create(['name' => 'access_to_warehouse', 'description' => 'Access to warehouse managment']);

        //Shops
        Permission::create(['name' => 'show_all_shops', 'description' => 'View all shops']);
        Permission::create(['name' => 'view_shop', 'description' => 'View a specific shop']);
        Permission::create(['name' => 'create_shop', 'description' => 'Create a new shop']);
        Permission::create(['name' => 'update_shop', 'description' => 'Update a shop']);
        Permission::create(['name' => 'delete_shop', 'description' => 'Delete a shop']);
        Permission::create(['name' => 'access_to_shop', 'description' => 'Access to shop managment']);

        //Roles
        Permission::create(['name' => 'show_all_roles', 'description' => 'View all roles']);
        Permission::create(['name' => 'view_role', 'description' => 'View a specific role']);
        Permission::create(['name' => 'create_role', 'description' => 'Create a new role']);
        Permission::create(['name' => 'update_role', 'description' => 'Update a role']);
        Permission::create(['name' => 'delete_role', 'description' => 'Delete a role']);
        Permission::create(['name' => 'access_to_roles', 'description' => 'Access to role management']);


        //Products
        Permission::create(['name' => 'show_all_products', 'description' => 'View all products']);
        Permission::create(['name' => 'view_product', 'description' => 'View a specific product']);
        Permission::create(['name' => 'create_product', 'description' => 'Create a new product']);
        Permission::create(['name' => 'update_product', 'description' => 'Update a product']);
        Permission::create(['name' => 'delete_product', 'description' => 'Delete a product']);
        Permission::create(['name' => 'access_to_products', 'description' => 'Access to product management']);

        //Inventory
        Permission::create(['name' => 'show_all_inventory_states', 'description' => 'View all inventory states']);
        Permission::create(['name' => 'show_all_inventory_movements', 'description' => 'View all inventory movements']);
        Permission::create(['name' => 'view_inventory_movement', 'description' => 'View a specific inventory movement']);
        Permission::create(['name' => 'create_inventory_movement', 'description' => 'Create a new inventory movement']);
        Permission::create(['name' => 'update_inventory_movement', 'description' => 'Update an inventory movement']);
        Permission::create(['name' => 'confirmation_inventory_movement', 'description' => 'Confirmation an inventory movement']);
        Permission::create(['name' => 'delete_inventory_movement', 'description' => 'Delete an inventory movement']);
        Permission::create(['name' => 'access_to_inventory', 'description' => 'Access to inventory management']);

        //Sales
        Permission::create(['name' => 'show_all_sales', 'description' => 'View all sales']);
        Permission::create(['name' => 'view_sale', 'description' => 'View a specific sale']);
        Permission::create(['name' => 'create_sale', 'description' => 'Create a new sale']);
        Permission::create(['name' => 'reset_sale', 'description' => 'Resetting a specific sale']);
        Permission::create(['name' => 'update_sale', 'description' => 'Update a sale']);
        Permission::create(['name' => 'delete_sale', 'description' => 'Delete a sale']);
        Permission::create(['name' => 'access_to_sales', 'description' => 'Access to sales management']);

        //Factorisation 
        Permission::create(['name' => 'show_all_factorisations', 'description' => 'View all factorisations']);
        Permission::create(['name' => 'update_factorisation', 'description' => 'Update a factorisation']);
        Permission::create(['name' => 'delete_factorisation', 'description' => 'Delete a factorisation']);
        Permission::create(['name' => 'access_to_factorisations', 'description' => 'Access to factorisations management']);

        //Expidation
        Permission::create(['name' => 'show_all_expidations', 'description' => 'View all expidations']);
        Permission::create(['name' => 'handle_expidation', 'description' => 'Create a ticket']);


        //Orders
        Permission::create(['name' => 'show_all_orders', 'description' => 'View all orders']);
        Permission::create(['name' => 'view_order', 'description' => 'View a specific order']);
        Permission::create(['name' => 'update_order', 'description' => 'Update an order']);
        Permission::create(['name' => 'access_to_orders', 'description' => 'Access to order management']);

        //Delivery
        Permission::create(['name' => 'show_all_deliveries', 'description' => 'View all deliveries']);
        Permission::create(['name' => 'show_delivery_inventory_movement', 'description' => 'View attached inventory movement']);
        Permission::create(['name' => 'access_to_delivery', 'description' => 'Access to delivery']);



        // create roles and assign created permissions NEW


        // for admin
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo([
            'update_account',
            'access_to_account',

            'show_all_users',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'update_user_status',
            'access_to_users',

            'show_all_shops',
            'view_shop',
            'create_shop',
            'update_shop',
            'delete_shop',
            'access_to_shop',

            'show_all_warehouses',
            'view_warehouse',
            'create_warehouse',
            'update_warehouse',
            'delete_warehouse',
            'access_to_warehouse',

            'show_all_roles',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
            'access_to_roles',

            'show_all_products',
            'view_product',
            'create_product',
            'update_product',
            'delete_product',
            'access_to_products',

            'show_all_inventory_states',
            'show_all_inventory_movements',
            'view_inventory_movement',
            'create_inventory_movement',
            'update_inventory_movement',
            'confirmation_inventory_movement',
            'delete_inventory_movement',
            'access_to_inventory',

            'show_all_sales',
            'view_sale',
            'create_sale',
            'update_sale',
            'update_order',
            'reset_sale',
            'view_order',
            'delete_sale',
            'access_to_sales',

            'show_all_factorisations',
            'update_factorisation',
            'delete_factorisation',
            'access_to_factorisations',

            'show_all_expidations',
            'handle_expidation'
        ]);


        //for agente
        $role = Role::create(['name' => 'agente']);
        $role->givePermissionTo([
            'show_all_orders',
            'update_order',
            'access_to_orders'
        ]);


        // // for delivery
        $role = Role::create(['name' => 'delivery']);
        $role->givePermissionTo([
            'show_all_deliveries',
            'access_to_delivery',
            'confirmation_inventory_movement',
            'update_order',

            'show_all_inventory_movements',

            'access_to_inventory',
            'show_delivery_inventory_movement'
        ]);




        //------------------------------------------------------------------------------------------------------------------------------//
        // create permissions OLD

        // Permission::create(['name' => 'users_show']);
        // Permission::create(['name' => 'users_create']);
        // Permission::create(['name' => 'users_update']);
        // Permission::create(['name' => 'users_delete']);
        // Permission::create(['name' => 'users_access']);

        // Permission::create(['name' => 'user_show']);
        // Permission::create(['name' => 'user_update']);
        // Permission::create(['name' => 'user_access']);


        // Permission::create(['name' => 'product_show']);
        // Permission::create(['name' => 'product_create']);
        // Permission::create(['name' => 'product_update']);
        // Permission::create(['name' => 'product_delete']);
        // Permission::create(['name' => 'product_access']);

        // Permission::create(['name' => 'inventory_state_show']);
        // Permission::create(['name' => 'inventory_state_create']);
        // Permission::create(['name' => 'inventory_state_update']);
        // Permission::create(['name' => 'inventory_state_delete']);
        // Permission::create(['name' => 'inventory_state_access']);

        // Permission::create(['name' => 'inventory_movement_show']);
        // Permission::create(['name' => 'inventory_movement_create']);
        // Permission::create(['name' => 'inventory_movement_update']);
        // Permission::create(['name' => 'inventory_movement_delete']);
        // Permission::create(['name' => 'inventory_movement_access']);

        // Permission::create(['name' => 'sale_show']);
        // Permission::create(['name' => 'sale_create']);
        // Permission::create(['name' => 'sale_update']);
        // Permission::create(['name' => 'sale_delete']);
        // Permission::create(['name' => 'sale_access']);

        // Permission::create(['name' => 'order_show']);
        // Permission::create(['name' => 'order_create']);
        // Permission::create(['name' => 'order_update']);
        // Permission::create(['name' => 'order_delete']);
        // Permission::create(['name' => 'order_access']);

        // Permission::create(['name' => 'result_show']);
        // Permission::create(['name' => 'result_create']);
        // Permission::create(['name' => 'result_update']);
        // Permission::create(['name' => 'result_delete']);
        // Permission::create(['name' => 'result_access']);



        // create roles and assign created permissions OLD

        // for admin
        // $role = Role::create(['name' => 'admin']);
        // $role->givePermissionTo([
        //     'user_show', 'user_update', 'user_access',
        //     'users_show', 'users_create', 'users_update', 'users_delete', 'users_access',
        //     'product_show', 'product_create', 'product_update', 'product_delete', 'product_access',
        //     'inventory_state_show', 'inventory_state_create', 'inventory_state_update', 'inventory_state_delete', 'inventory_state_access',
        //     'inventory_movement_show', 'inventory_movement_create', 'inventory_movement_update', 'inventory_movement_delete', 'inventory_movement_access',
        //     'sale_show', 'sale_create', 'sale_update', 'sale_delete', 'sale_access',
        // ]);


        // // for agente
        // $role = Role::create(['name' => 'agente']);
        // $role->givePermissionTo([
        //     'user_show', 'user_update', 'user_access',
        //     'order_show', 'order_update', 'order_access'
        // ]);


        // // for delivery
        // $role = Role::create(['name' => 'delivery']);
        // $role->givePermissionTo([
        //     'user_show', 'user_update', 'user_access',
        //     'result_show', 'result_update', 'result_access'
        // ]);
    }
}
