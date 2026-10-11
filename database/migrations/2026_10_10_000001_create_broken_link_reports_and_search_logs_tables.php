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
        Schema::create('broken_link_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('ddl');
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('status', 20)->default('pending'); // pending, resolved, dismissed
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->integer('results_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['query']);
            $table->index(['results_count', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('broken_link_reports');
    }
};
