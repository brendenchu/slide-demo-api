<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop non-unique indexes that are redundant because a unique index
     * already exists on the same column (unique indexes serve as B-tree
     * indexes for lookups).
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropIndex('profiles_public_id_index');
        });

        Schema::table('teams', function (Blueprint $table): void {
            $table->dropIndex('teams_key_index');
            $table->dropIndex('teams_public_id_index');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex('projects_public_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->index('public_id');
        });

        Schema::table('teams', function (Blueprint $table): void {
            $table->index('key');
            $table->index('public_id');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->index('public_id');
        });
    }
};
