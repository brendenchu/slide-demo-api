<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        foreach (RoleEnum::getInstances() as $role) {
            Role::create([
                'name' => $role,
            ]);
        }

        // Create Permissions
        foreach (PermissionEnum::getInstances() as $permission) {
            Permission::create([
                'name' => $permission,
            ]);
        }

        // Assign Permissions to Roles
        $role = Role::findByName(RoleEnum::SuperAdmin->value);
        $role->givePermissionTo(PermissionEnum::getInstances());

        $role = Role::findByName(RoleEnum::Admin->value);
        $role->givePermissionTo([
            PermissionEnum::ViewAnyUser,
            PermissionEnum::ViewUser,
            PermissionEnum::CreateUser,
            PermissionEnum::UpdateUser,
            PermissionEnum::DeleteUser,
            PermissionEnum::ViewAnyTeam,
            PermissionEnum::ViewTeam,
            PermissionEnum::CreateTeam,
            PermissionEnum::UpdateTeam,
            PermissionEnum::DeleteTeam,
            PermissionEnum::ViewAnyProject,
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
            PermissionEnum::UpdateProject,
            PermissionEnum::DeleteProject,
        ]);

        $role = Role::findByName(RoleEnum::Consultant->value);
        $role->givePermissionTo([
            PermissionEnum::ViewAnyUser,
            PermissionEnum::ViewUser,
            PermissionEnum::ViewAnyTeam,
            PermissionEnum::ViewTeam,
            PermissionEnum::ViewAnyProject,
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
            PermissionEnum::UpdateProject,
            PermissionEnum::DeleteProject,
        ]);

        $role = Role::findByName(RoleEnum::Client->value);
        $role->givePermissionTo([
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
            PermissionEnum::UpdateProject,
        ]);

        $role = Role::findByName(RoleEnum::Guest->value);
        $role->givePermissionTo([
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
        ]);

    }
}
