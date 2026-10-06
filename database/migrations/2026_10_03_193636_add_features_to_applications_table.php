<?php

use App\Models\Application;
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
            $table->longText('features')->nullable()->after('changelog');
        });

        // Clean existing descriptions and extract features
        Application::all()->each(function ($app) {
            $rawDesc = $app->description;
            if ($rawDesc && (str_contains($rawDesc, 'wps-tabs') || str_contains($rawDesc, 'wps-shortcode'))) {
                // If features panel is present, extract it
                if (preg_match('/<div[^>]*class="[^"]*wps-tab-text[^"]*"[^>]*>([\s\S]*?)<\/div>/i', $rawDesc, $match)) {
                    $featuresHtml = trim(strip_tags($match[1], '<p><br><ul><ol><li><strong><b><em><i>'));
                    if (! empty($featuresHtml) && ! str_contains($featuresHtml, '<img')) {
                        $app->features = $featuresHtml;
                    }
                }

                // Strip tabs container and trailing wrappers completely from description
                $cleanDesc = preg_replace('/<div[^>]*class="[^"]*(?:wps-shortcode|wps-tabs)[^"]*"[^>]*>[\s\S]*$/i', '', $rawDesc);
                $cleanDesc = strip_tags(trim($cleanDesc), '<p><br><strong><b><em><i><u><ul><ol><li><h3><h4><blockquote><a>');
                $app->description = trim($cleanDesc);
                $app->saveQuietly();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }
};
