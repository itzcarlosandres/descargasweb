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
            $table->boolean('telegram_sent')->default(false)->index()->after('published');
            $table->timestamp('telegram_sent_at')->nullable()->after('telegram_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['telegram_sent']);
            $table->dropColumn(['telegram_sent', 'telegram_sent_at']);
        });
    }
};
