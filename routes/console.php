<?php

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

// 1. Automated DDL Smart Sync (Checks latest releases & updates every 2 hours)
Schedule::call(function () {
    if (Setting::get('scraper_cron_enabled', false)) {
        $importer = app(HaxmacImporter::class);
        $result = $importer->syncLatestUpdates(true);
        Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
        Log::info("DDL Smart Sync completed: {$result['new_imported']} new, {$result['updated']} updated.");
    }
})->everyTwoHours()->name('haxmac-smart-sync');

// 2. Automated TorrentMac Smart Sync (Checks latest torrent releases & updates every 2 hours)
Schedule::call(function () {
    if (Setting::get('scraper_cron_enabled', false) && Setting::get('torrentmac_cron_enabled', true)) {
        $importer = app(TorrentmacImporter::class);
        $result = $importer->syncLatestUpdates(1, true);
        Setting::set('torrentmac_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
        Log::info("TorrentMac Smart Sync completed: {$result['new_imported']} new, {$result['updated']} updated.");
    }
})->everyTwoHours()->name('torrentmac-smart-sync');

// 2. Automated Deep Category Sync (Comprehensive crawl at configured time)
Schedule::call(function () {
    if (Setting::get('scraper_cron_enabled', false)) {
        $importer = app(HaxmacImporter::class);
        $categories = Category::where('is_active', true)->get();
        $pages = (int) Setting::get('scraper_cron_pages', 2);

        foreach ($categories as $cat) {
            $importer->importCategory($cat->slug, $pages, true);
        }

        Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
    }
})->dailyAt('03:00')->name('haxmac-deep-sync');
