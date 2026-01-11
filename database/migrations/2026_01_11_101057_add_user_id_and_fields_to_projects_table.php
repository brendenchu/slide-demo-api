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
        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('user_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('current_step')->after('status')->default('intro');
            $table->json('responses')->after('current_step')->nullable();
        });

        // Assign existing projects to first user if any exist
        $firstUser = \App\Models\User::first();
        if ($firstUser) {
            \Illuminate\Support\Facades\DB::table('projects')
                ->whereNull('user_id')
                ->update(['user_id' => $firstUser->id]);
        }

        // Make user_id not nullable after backfilling
        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'current_step', 'responses']);
        });
    }
};
