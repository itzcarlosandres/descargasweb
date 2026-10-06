<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('version')->nullable();
            $table->string('size')->nullable();
            $table->string('developer')->nullable();
            $table->string('license')->nullable();
            $table->string('platform')->nullable();
            $table->string('icon')->nullable();
            $table->string('screenshot')->nullable();
            $table->string('download_url')->nullable();
            $table->string('download_url_external')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('popular')->default(false);
            $table->boolean('published')->default(true);
            $table->unsignedBigInteger('downloads')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('category_id');
            $table->index('featured');
            $table->index('popular');
            $table->index('published');
            $table->index('downloads');
            $table->index('rating');
            $table->index(['published', 'featured']);
            $table->index(['published', 'popular']);
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['name', 'short_description', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
