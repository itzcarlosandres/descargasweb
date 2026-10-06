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
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('application_id')->constrained('reviews')->onDelete('cascade');
            $table->unsignedInteger('upvotes')->default(0)->after('approved');
            $table->unsignedInteger('downvotes')->default(0)->after('upvotes');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['upvotes', 'downvotes']);
        });
    }
};
