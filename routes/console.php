<?php

use App\Models\Application;
use App\Models\BrokenLinkReport;
use App\Models\Category;
use App\Models\Setting;
use App\Services\LinkHealth\AutoLinkHealerService;
use App\Services\LinkHealth\LinkHealthCheckerService;
use App\Services\Scraper\HaxmacImporter;
use App\Services\Scraper\TorrentmacImporter;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('scraper:sync {--force : Ejecutar incluso si el cron está desactivado en la configuración} {--fill-drafts=0 : Rellenar la cola con N borradores del catálogo}', function (HaxmacImporter $haxmac, TorrentmacImporter $torrentmac) {
    $isEnabled = filter_var(Setting::get('scraper_cron_enabled', false), FILTER_VALIDATE_BOOLEAN) || $this->option('force');

    if (! $isEnabled) {
        $this->warn('El cron del scraper está desactivado en la configuración. Usa --force para ejecutar manualmente.');

        return 1;
    }

    $limit = (int) Setting::get('scraper_cron_limit', 10);
    $halfLimit = (int) ceil($limit / 2);
    $dripMode = filter_var(Setting::get('scraper_drip_feed_mode', false), FILTER_VALIDATE_BOOLEAN);

    // 1. Si se solicita rellenar borradores manualmente o la cola está seca en modo goteo
    $requestedFill = (int) $this->option('fill-drafts');
    $currentDrafts = Application::where('published', false)->count();
    $fillNeeded = $requestedFill > 0 ? $requestedFill : ($dripMode && $currentDrafts < $limit ? $limit : 0);

    if ($fillNeeded > 0) {
        $halfFill = (int) ceil($fillNeeded / 2);
        $this->info("Rellenando Cola Draft con {$fillNeeded} programas del catálogo histórico (página HaxMac: ".Setting::get('scraper_haxmac_backlog_page', 4).', TorrentMac: '.Setting::get('scraper_torrentmac_backlog_page', 4).')...');
        $bHax = $haxmac->importBacklogBatch($halfFill, true, true);
        $bTor = $torrentmac->importBacklogBatch($halfFill, true, true);
        $this->info("✓ Cola Draft reabastecida: +{$bHax['imported']} DDL, +{$bTor['imported']} Torrent. Total en cola: ".Application::where('published', false)->count());
    }

    // 2. Sincronización de novedades desde portadas
    $this->info("Iniciando sincronización DDL de portada (HaxMac, hasta {$halfLimit} apps)...");
    $ddlResult = $haxmac->syncLatestUpdates(true, $halfLimit, 3);
    Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
    $this->info("✓ DDL completado: {$ddlResult['new_imported']} nuevos, {$ddlResult['updated']} actualizados.");

    if (filter_var(Setting::get('torrentmac_cron_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
        $this->info("Iniciando sincronización Torrent de portada (TorrentMac, hasta {$halfLimit} apps)...");
        $torrentResult = $torrentmac->syncLatestUpdates(1, true, $halfLimit, 3);
        Setting::set('torrentmac_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
        $this->info("✓ Torrent completado: {$torrentResult['new_imported']} nuevos, {$torrentResult['updated']} actualizados.");
    }

    // 3. Si no hay modo goteo y no se alcanzaron novedades, rellenar el déficit directamente publicado
    if (! $dripMode) {
        $totalNew = $ddlResult['new_imported'] + ($torrentResult['new_imported'] ?? 0);
        $deficit = $limit - $totalNew;
        if ($deficit > 0) {
            $halfDeficit = (int) ceil($deficit / 2);
            $this->info("No hubo suficientes novedades ({$totalNew}/{$limit}). Extrayendo {$deficit} programas antiguos del catálogo histórico...");
            $haxDirect = $haxmac->importBacklogBatch($halfDeficit, false, true);
            $torDirect = $torrentmac->importBacklogBatch($halfDeficit, false, true);
            $this->info("✓ Publicados desde archivo: +{$haxDirect['imported']} DDL, +{$torDirect['imported']} Torrent.");
        }
    } else {
        // 4. Goteo de drafts (publicación escalonada equilibrada 50% Torrent y 50% DDL)
        $dripResult = Application::releaseDripBatch($limit);
        if ($dripResult['published_count'] > 0) {
            $remaining = Application::where('published', false)->count();
            $this->info("✓ Goteo Draft: {$dripResult['published_count']} apps publicadas ({$dripResult['torrent_count']} Torrent, {$dripResult['ddl_count']} DDL). Quedan {$remaining} en cola draft.");
        }
    }

    $this->info('Sincronización finalizada correctamente.');

    return 0;
})->purpose('Ejecuta la sincronización automática de novedades del scraper');

Artisan::command('scraper:fill-drafts {count=10 : Cantidad de programas a cargar desde el catálogo}', function (HaxmacImporter $haxmac, TorrentmacImporter $torrentmac) {
    $count = max(2, (int) $this->argument('count'));
    $half = (int) ceil($count / 2);

    $this->info("Extrayendo {$count} aplicaciones desde el catálogo histórico para la Cola Draft...");
    $hax = $haxmac->importBacklogBatch($half, true, true);
    $tor = $torrentmac->importBacklogBatch($half, true, true);

    $total = $hax['imported'] + $tor['imported'];
    $currentDrafts = Application::where('published', false)->count();

    $this->info("✓ Se importaron {$total} nuevos borradores (+{$hax['imported']} DDL, +{$tor['imported']} Torrent).");
    $this->info("✓ Total actual en Cola Draft: {$currentDrafts} aplicaciones listas para goteo.");

    return 0;
})->purpose('Carga programas antiguos desde el archivo de HaxMac y TorrentMac a la Cola Draft');

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

// 3. Automated Drip Publishing (Publicación gradual equilibrada de la Cola Draft)
$dripEvent = Schedule::call(function () {
    if (filter_var(Setting::get('scraper_cron_enabled', false), FILTER_VALIDATE_BOOLEAN)) {
        $limit = (int) Setting::get('scraper_cron_limit', 10);
        $currentDrafts = Application::where('published', false)->count();

        // Si la cola de drafts tiene menos que el límite, rellenarla automáticamente desde el backlog
        if ($currentDrafts < $limit) {
            $needed = max($limit, 10);
            $half = (int) ceil($needed / 2);
            app(HaxmacImporter::class)->importBacklogBatch($half, true, true);
            app(TorrentmacImporter::class)->importBacklogBatch($half, true, true);
        }

        $dripResult = Application::releaseDripBatch($limit);
        if ($dripResult['published_count'] > 0) {
            $remaining = Application::where('published', false)->count();
            Log::info("Drip Publishing (Goteo): Se publicaron {$dripResult['published_count']} apps ({$dripResult['torrent_count']} Torrent, {$dripResult['ddl_count']} DDL). Quedan {$remaining} en Cola Draft.");
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

// 5. Automated Telegram Drip Dispatcher (1 app per minute if auto pilot is active)
Schedule::call(function () {
    app(TelegramBotService::class)->dispatchCron();
})->everyMinute()->name('telegram-auto-pilot')->withoutOverlapping(10);

Artisan::command('telegram:dispatch {--force : Despacha incluso si el piloto automático está apagado}', function (TelegramBotService $telegram) {
    if ($this->option('force')) {
        $result = $telegram->dispatchNext();
    } else {
        $result = $telegram->dispatchCron();
    }

    if (! empty($result['success'])) {
        $this->info($result['message']);

        return 0;
    }

    if (! empty($result['skipped'])) {
        $this->warn($result['reason']);

        return 0;
    }

    $this->error($result['message'] ?? 'No se pudo despachar.');

    return 1;
})->purpose('Despacha 1 aplicación pendiente hacia el canal de Telegram');

Artisan::command('links:auto-repair {--limit=10 : Máximo de reportes a procesar}', function (AutoLinkHealerService $healer) {
    $limit = (int) $this->option('limit');
    $reports = BrokenLinkReport::with('application')
        ->where('status', 'pending')
        ->latest()
        ->limit($limit)
        ->get();

    if ($reports->isEmpty()) {
        $this->info('No hay reportes de enlaces caídos pendientes.');

        return 0;
    }

    $this->info("Iniciando auto-reparación inteligente para {$reports->count()} reportes...");
    $repaired = 0;

    foreach ($reports as $report) {
        if (! $report->application) {
            continue;
        }

        $this->line("Procesando: {$report->application->name} (v{$report->application->version})...");
        $result = $healer->healApplication($report->application, $report);

        if (! empty($result['success'])) {
            $this->info("✓ {$result['message']}");
            $repaired++;
        } else {
            $this->warn("✕ {$result['message']}");
        }
    }

    $this->info("Finalizado: {$repaired} de {$reports->count()} enlaces reparados automáticamente.");

    return 0;
})->purpose('Rastrea y auto-repara enlaces caídos reportados buscando en Torrentmac y Haxmac');

Artisan::command('links:check-health {--limit=20 : Número de aplicaciones a comprobar}', function (LinkHealthCheckerService $checker) {
    $limit = (int) $this->option('limit');
    $apps = Application::where('published', true)->inRandomOrder()->limit($limit)->get();

    $this->info("Comprobando salud de enlaces en {$apps->count()} aplicaciones...");
    $deadFound = 0;

    foreach ($apps as $app) {
        $health = $checker->checkApplication($app);
        if (! $health['has_working_link'] && $health['checked_count'] > 0) {
            $this->error("✕ Enlace caído detectado en: {$app->name}");
            BrokenLinkReport::create([
                'application_id' => $app->id,
                'type' => 'broken_link',
                'notes' => 'Detector automático: ningún servidor de descarga responde correctamente.',
                'ip_address' => '127.0.0.1 (Health-Checker)',
                'status' => 'pending',
            ]);
            $deadFound++;
        } else {
            $this->line("✓ {$app->name} - OK ({$health['checked_count']} enlaces)");
        }
    }

    $this->info("Diagnóstico finalizado: {$deadFound} enlaces caídos registrados en reportes.");

    return 0;
})->purpose('Comprueba la salud de los enlaces de descarga y registra reportes automáticos si están caídos');
