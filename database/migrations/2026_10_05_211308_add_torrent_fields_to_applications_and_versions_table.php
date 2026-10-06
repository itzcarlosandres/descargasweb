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
            $table->string('torrent_url', 500)->nullable()->after('download_url_external');
            $table->string('torrent_file_path', 500)->nullable()->after('torrent_url');
            $table->text('magnet_link')->nullable()->after('torrent_file_path');
            $table->boolean('has_torrent')->default(false)->after('popular');
        });

        Schema::table('application_versions', function (Blueprint $table) {
            $table->string('torrent_url', 500)->nullable()->after('download_url');
            $table->string('torrent_file_path', 500)->nullable()->after('torrent_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_versions', function (Blueprint $table) {
            $table->dropColumn(['torrent_url', 'torrent_file_path']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['torrent_url', 'torrent_file_path', 'magnet_link', 'has_torrent']);
        });
    }
};
