<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add additional indexes for query optimization
        // Note: projects.status, users.email already indexed by previous migration

        Schema::table('projects', function (Blueprint $table): void {
            $table->index('public_id'); // For WHERE public_id lookups
            $table->index('updated_at'); // For latest() ordering
        });

        Schema::table('teams', function (Blueprint $table): void {
            $table->index('public_id'); // For WHERE public_id lookups
            $table->index('status'); // For status filtering
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index('created_at'); // For latest() ordering in admin panel
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex(['public_id']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('teams', function (Blueprint $table): void {
            $table->dropIndex(['public_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['created_at']);
        });
    }
};
