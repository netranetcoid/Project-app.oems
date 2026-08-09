<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class EbupotVendorSeeder extends Seeder
{
    public function run(): void
    {
        $names=['ebupot-vendor.view','ebupot-vendor.manage','ebupot-vendor.print','ebupot-vendor.whatsapp'];
        foreach($names as $name) Permission::firstOrCreate(['name'=>$name,'guard_name'=>'web']);
        Role::query()->whereIn('name',['Developer','Super Admin','Owner','Management','HR','Admin','Finance'])->each(fn(Role $role)=>$role->givePermissionTo($names));
        $parent=DB::table('menus')->where('code','hr')->value('id');
        $module=DB::table('modules')->where('code','payroll')->value('id') ?: DB::table('modules')->value('id');
        if($parent){
            $values=['module_id'=>$module,'parent_id'=>$parent,'name'=>'e-Bupot Vendor','label'=>'e-Bupot Vendor','icon'=>'ri ri-file-list-3-line','route_name'=>'finance.ebupot-vendors.index','permission_name'=>'ebupot-vendor.view','sort_order'=>11,'level'=>2,'type'=>'menu','target'=>'_self','is_active'=>true,'is_system'=>true,'open_in_new_tab'=>false,'updated_at'=>now()];
            $existing=DB::table('menus')->where('code','finance-ebupot-vendor')->first();
            if($existing)DB::table('menus')->where('id',$existing->id)->update($values);else DB::table('menus')->insert($values+['code'=>'finance-ebupot-vendor','created_at'=>now()]);
        }
    }
}
