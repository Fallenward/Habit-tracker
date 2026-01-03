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
        Schema::create('habit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('habit_id')->constrained('habits')->onDelete('cascade');
            $table->date('log_date');
            $table->boolean('completed')->default(true);
            $table->timestamps();

            $table->unique(['habit_id', 'log_date']);
            $table->index(['habit_id', 'log_date']);
            $table->index('log_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habit_logs');
    }
};
