<?php

use App\Enums\Account\TeamRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Spatie Role records for each TeamRole case
        foreach (TeamRole::cases() as $teamRole) {
            Role::findOrCreate($teamRole->value, 'web');
        }

        // Migrate existing team role data to Spatie model_has_roles
        $teams = DB::table('teams')->get();

        foreach ($teams as $team) {
            $members = DB::table('users_teams')->where('team_id', $team->id)->get();

            foreach ($members as $member) {
                if ($team->owner_id !== null && (int) $member->user_id === (int) $team->owner_id) {
                    $role = TeamRole::Owner;
                } elseif ((bool) $member->is_admin) {
                    $role = TeamRole::Admin;
                } else {
                    $role = TeamRole::Member;
                }

                $spatieRole = Role::findByName($role->value, 'web');

                DB::table('model_has_roles')->insert([
                    'team_id' => $team->id,
                    'role_id' => $spatieRole->id,
                    'model_type' => User::class,
                    'model_id' => $member->user_id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Remove all team-scoped role assignments
        DB::table('model_has_roles')->where('team_id', '!=', 0)->delete();

        // Remove TeamRole definitions
        foreach (TeamRole::cases() as $teamRole) {
            Role::query()->where('name', $teamRole->value)->where('guard_name', 'web')->delete();
        }
    }
};
