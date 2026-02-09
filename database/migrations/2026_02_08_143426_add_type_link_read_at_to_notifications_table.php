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
        Schema::table('notifications', function (Blueprint $table): void {
            $table->string('type')->nullable()->after('content');
            $table->string('link')->nullable()->after('type');
            $table->timestamp('read_at')->nullable()->after('link');

            $table->index(['recipient_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex(['recipient_id', 'read_at']);
            $table->dropColumn(['type', 'link', 'read_at']);
        });
    }
};
