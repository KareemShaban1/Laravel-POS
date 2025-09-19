<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
       
            ['name' => 'view users','guard_name' => 'web'],
            ['name' => 'create user','guard_name' => 'web'],
            ['name' => 'update user','guard_name' => 'web'],
            ['name' => 'delete user','guard_name' => 'web'],
            
            ['name'=> 'view roles','guard_name' => 'web'],
            ['name'=> 'create role','guard_name' => 'web'],
            ['name'=> 'update role','guard_name' => 'web'],
            ['name'=> 'delete role','guard_name' => 'web'],
            
            ['name' => 'view categories','guard_name' => 'web'],
            ['name' => 'create category','guard_name' => 'web'],
            ['name' => 'update category','guard_name' => 'web'],
            ['name' => 'delete category','guard_name' => 'web'],
            
            ['name'=> 'view products','guard_name' => 'web'],
            ['name'=> 'create product','guard_name' => 'web'],
            ['name'=> 'update product','guard_name' => 'web'],
            ['name'=> 'delete product','guard_name' => 'web'],
            
            ['name'=> 'view settings','guard_name' => 'web'],
            ['name'=> 'create setting','guard_name' => 'web'],
            ['name'=> 'update setting','guard_name' => 'web'],
            ['name'=> 'delete setting','guard_name' => 'web'],
            
            ['name'=> 'view orders','guard_name' => 'web'],
            ['name'=> 'create order','guard_name' => 'web'],
            ['name'=> 'update order','guard_name' => 'web'],
            ['name'=> 'delete order','guard_name' => 'web'],
            
            ['name'=> 'view customers','guard_name' => 'web'],
            ['name'=> 'create customer','guard_name' => 'web'],
            ['name'=> 'update customer','guard_name' => 'web'],
            ['name'=> 'delete customer','guard_name' => 'web'],
            
            ['name'=> 'view pos','guard_name' => 'web'],
            ['name'=> 'create pos','guard_name' => 'web'],
            ['name'=> 'update pos','guard_name' => 'web'],
            ['name'=> 'delete pos','guard_name' => 'web'],


        ];
        // Create permissions
        // Permission::insert($permissions);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
        
        
        // Create roles and assign existing permissions
        $role = Role::create(['name' => 'user']);
        $role->givePermissionTo(['name'=> 'create user','name'=> 'update user','name'=> 'delete user','name'=> 'view users']);

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());
    }
}
