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
        Schema::table('applications', function (Blueprint $table) {
            $table->index(['published', 'downloads'], 'apps_pub_downloads_idx');
            $table->index(['published', 'released_at'], 'apps_pub_released_idx');
            $table->index(['published', 'created_at'], 'apps_pub_created_idx');
            $table->index(['published', 'category_id', 'downloads'], 'apps_pub_cat_downloads_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('apps_pub_downloads_idx');
            $table->dropIndex('apps_pub_released_idx');
            $table->dropIndex('apps_pub_created_idx');
            $table->dropIndex('apps_pub_cat_downloads_idx');
        });
    }
};
