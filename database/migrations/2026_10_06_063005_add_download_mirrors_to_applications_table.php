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
            $table->json('download_mirrors')->nullable()->after('download_url_external');
        });

        if (Schema::hasTable('application_versions')) {
            Schema::table('application_versions', function (Blueprint $table) {
                $table->json('download_mirrors')->nullable()->after('download_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('download_mirrors');
        });

        if (Schema::hasTable('application_versions') && Schema::hasColumn('application_versions', 'download_mirrors')) {
            Schema::table('application_versions', function (Blueprint $table) {
                $table->dropColumn('download_mirrors');
            });
        }
    }
};
