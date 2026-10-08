<?php

use App\Models\Application;
use App\Models\Category;
use App\Models\Setting;
use App\Services\Scraper\HaxmacImporter;
use App\Services\Scraper\TorrentmacImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('scraper:sync {--force : Ejecutar incluso si el cron está desactivado en la configuración}', function (HaxmacImporter $haxmac, TorrentmacImporter $torrentmac) {
    $isEnabled = filter_var(Setting::get('scraper_cron_enabled', false), FILTER_VALIDATE_BOOLEAN) || $this->option('force');

    if (! $isEnabled) {
        $this->warn('El cron del scraper está desactivado en la configuración. Usa --force para ejecutar manualmente.');

        return 1;
    }

    $this->info('Iniciando sincronización DDL (HaxMac)...');
    $limit = (int) Setting::get('scraper_cron_limit', 10);
    $ddlResult = $haxmac->syncLatestUpdates(true, $limit);
    Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
    $this->info("✓ DDL completado: {$ddlResult['new_imported']} nuevos, {$ddlResult['updated']} actualizados.");

    if (filter_var(Setting::get('torrentmac_cron_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
        $this->info('Iniciando sincronización Torrent (TorrentMac)...');
        $torrentResult = $torrentmac->syncLatestUpdates(1, true);
        Setting::set('torrentmac_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
        $this->info("✓ Torrent completado: {$torrentResult['new_imported']} nuevos, {$torrentResult['updated']} actualizados.");
    }

    // Goteo de drafts (publicación escalonada de la Cola Draft)
    $drafts = Application::where('published', false)
        ->orderBy('id', 'asc')
        ->limit($limit)
        ->get();

    if ($drafts->isNotEmpty()) {
        foreach ($drafts as $draftApp) {
            $draftApp->update([
                'published' => true,
                'released_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $remaining = Application::where('published', false)->count();
        $this->info("✓ Goteo Draft: {$drafts->count()} apps publicadas en el portal. Quedan {$remaining} en cola draft.");
    }

    $this->info('Sincronización finalizada correctamente.');

    return 0;
})->purpose('Ejecuta la sincronización automática de novedades del scraper');

// Dynamic frequency and time settings from DB
$frequency = (string) Setting::get('scraper_cron_frequency', '2hours');
$deepTime = (string) Setting::get('scraper_cron_time', '03:00');

$applyFrequency = function ($event, string $freq) {
    return match ($freq) {
        'hourly' => $event->hourly(),
        '4hours' => $event->cron('0 */4 * * *'),
        'daily' => $event->daily(),
        default => $event->everyTwoHours(),
    };
};

// 1. Automated DDL Smart Sync (Checks latest releases & updates)
$haxmacEvent = Schedule::call(function () {
    if (filter_var(Setting::get('scraper_cron_enabled', false), FILTER_VALIDATE_BOOLEAN)) {
        $importer = app(HaxmacImporter::class);
        $limit = (int) Setting::get('scraper_cron_limit', 10);
        $result = $importer->syncLatestUpdates(true, $limit);
        Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
        Log::info("DDL Smart Sync completed: {$result['new_imported']} new, {$result['updated']} updated (límite: {$limit}).");
    }
})->name('haxmac-smart-sync')->withoutOverlapping(60);

$applyFrequency($haxmacEvent, $frequency);

// 2. Automated TorrentMac Smart Sync (Checks latest torrent releases & updates)
$torrentmacEvent = Schedule::call(function () {
    if (filter_var(Setting::get('scraper_cron_enabled', false), FILTER_VALIDATE_BOOLEAN)
        && filter_var(Setting::get('torrentmac_cron_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
        $importer = app(TorrentmacImporter::class);
        $result = $importer->syncLatestUpdates(1, true);
        Setting::set('torrentmac_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
        Log::info("TorrentMac Smart Sync completed: {$result['new_imported']} new, {$result['updated']} updated.");
    }
})->name('torrentmac-smart-sync')->withoutOverlapping(60);

$applyFrequency($torrentmacEvent, $frequency);

// 3. Automated Drip Publishing (Publicación gradual de la Cola Draft)
$dripEvent = Schedule::call(function () {
    if (filter_var(Setting::get('scraper_cron_enabled', false), FILTER_VALIDATE_BOOLEAN)) {
        $limit = (int) Setting::get('scraper_cron_limit', 10);
        $drafts = Application::where('published', false)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        if ($drafts->isNotEmpty()) {
            foreach ($drafts as $app) {
                $app->update([
                    'published' => true,
                    'released_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $remaining = Application::where('published', false)->count();
            Log::info("Drip Publishing (Goteo): Se publicaron {$drafts->count()} apps. Quedan {$remaining} en Cola Draft.");
        }
    }
})->name('drip-publish-cron')->withoutOverlapping(60);

$applyFrequency($dripEvent, $frequency);

// 3. Automated Deep Category Sync (Comprehensive crawl at configured time)
Schedule::call(function () {
    if (filter_var(Setting::get('scraper_cron_enabled', false), FILTER_VALIDATE_BOOLEAN)) {
        $importer = app(HaxmacImporter::class);
        $categories = Category::where('is_active', true)->get();
        $pages = (int) Setting::get('scraper_cron_pages', 2);

        foreach ($categories as $cat) {
            $importer->importCategory($cat->slug, $pages, true);
        }

        Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
    }
})->dailyAt($deepTime)->name('haxmac-deep-sync')->withoutOverlapping(120);

// 4. Automated Daily Sitemap Generation
Schedule::command('sitemap:generate')->dailyAt('04:00')->name('generate-sitemap');
