<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('version');
            $table->text('changelog')->nullable();
            $table->string('download_url')->nullable();
            $table->string('size')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_versions');
    }
};
