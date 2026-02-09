<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * When teams is enabled in config, the original Spatie migration already creates
     * the team_id columns. This migration only applies changes if the columns are missing
     * (i.e. the original migration ran with teams disabled).
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'];

        // If team_id already exists on the roles table, the original Spatie migration
        // handled everything — nothing to do here.
        if (Schema::hasColumn($tableNames['roles'], $teamForeignKey)) {
            return;
        }

        // Add team_id to roles table
        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->unsignedBigInteger($teamForeignKey)->nullable()->after('id');
            $table->index($teamForeignKey, 'roles_team_foreign_key_index');
        });

        // Drop existing unique and recreate with team_id
        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->dropUnique('roles_name_guard_name_unique');
            $table->unique([$teamForeignKey, 'name', 'guard_name']);
        });

        // Add team_id to model_has_roles and model_has_permissions tables
        $this->addTeamColumnToPivotTable($tableNames['model_has_roles'], $teamForeignKey);
        $this->addTeamColumnToPivotTable($tableNames['model_has_permissions'], $teamForeignKey);

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'];

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
            if (Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                $table->dropIndex('roles_team_foreign_key_index');
                $table->dropColumn($teamForeignKey);
            }
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey): void {
            if (Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                $table->dropColumn($teamForeignKey);
            }
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey): void {
            if (Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                $table->dropColumn($teamForeignKey);
            }
        });
    }

    /**
     * Add team_id column to a pivot table.
     */
    private function addTeamColumnToPivotTable(string $tableName, string $teamForeignKey): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($teamForeignKey): void {
            if (! Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                $table->unsignedBigInteger($teamForeignKey)->default(0);
                $table->index($teamForeignKey, $table->getTable() . '_team_foreign_key_index');
            }
        });
    }
};
