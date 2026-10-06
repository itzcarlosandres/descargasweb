<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index('slug');
        });

        Schema::create('application_tag', function (Blueprint $table) {
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['application_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_tag');
        Schema::dropIfExists('tags');
    }
};
