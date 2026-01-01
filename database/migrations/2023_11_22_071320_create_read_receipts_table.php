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
        Schema::create('read_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->string('public_id')->unique()->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('read_receipts');
    }
};
