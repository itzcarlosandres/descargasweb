<?php

namespace App\Console\Commands;

use App\Services\Scraper\TorrentmacImporter;
use Illuminate\Console\Command;

class TorrentmacScrapeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'torrentmac:scrape
                            {--app= : Importar un torrent específico por URL o slug}
                            {--category=all : Importar aplicaciones de una categoría específica (apps, games, etc.)}
                            {--sync-latest : Sincronizar automáticamente novedades desde la portada}
                            {--pending-updates : Listar únicamente las aplicaciones locales que tienen nueva versión en TorrentMac}
                            {--only-updates : Sincronizar y actualizar únicamente los programas con versión superior}
                            {--pages=1 : Número de páginas a recorrer}
                            {--disk= : Destino de almacenamiento específico: local o r2}
                            {--no-images : No descargar las imágenes ni capturas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extrae aplicaciones, archivos torrent a Cloudflare R2 / Local y enriquece fichas con IA desde TorrentMac';

    /**
     * Execute the console command.
     */
    public function handle(TorrentmacImporter $importer): int
    {
        $downloadImages = ! $this->option('no-images');

        if ($disk = $this->option('disk')) {
            $importer->setExplicitDisk($disk);
        }

        // 1. Pending updates audit
        if ($this->option('pending-updates')) {
            $pages = max(1, (int) $this->option('pages'));
            $this->info("Buscando aplicaciones con actualizaciones pendientes en TorrentMac ({$pages} páginas)...");
            $pending = $importer->getPendingUpdates($pages);

            if (empty($pending)) {
                $this->info('✓ Todos tus programas de TorrentMac están al día. No hay actualizaciones pendientes.');

                return Command::SUCCESS;
            }

            $this->warn('Se encontraron '.count($pending).' aplicaciones que requieren actualización:');
            foreach ($pending as $item) {
                $localVer = $item['local_version'] ? "v{$item['local_version']}" : 'desconocida';
                $newVer = $item['version'] ? "v{$item['version']}" : 'N/A';
                $this->line("  • <info>{$item['clean_name']}</info> (Local: <comment>{$localVer}</comment> → Torrent: <fg=green>{$newVer}</>)");
                $this->line("    URL: {$item['url']}");
            }

            return Command::SUCCESS;
        }

        // 2. Only updates sync
        if ($this->option('only-updates')) {
            $pages = max(1, (int) $this->option('pages'));
            $this->info("Buscando y actualizando aplicaciones existentes en TorrentMac ({$pages} páginas)...");
            $pending = $importer->getPendingUpdates($pages);

            if (empty($pending)) {
                $this->info('✓ Todos tus programas de TorrentMac están al día. Nada que actualizar.');

                return Command::SUCCESS;
            }

            $this->info('Actualizando '.count($pending).' programas...');
            $updated = 0;

            foreach ($pending as $item) {
                $this->line("  Actualizando {$item['clean_name']} a v{$item['version']}...");
                try {
                    $app = $importer->importAppByUrl($item['url'], $downloadImages);
                    if ($app) {
                        $updated++;
                        $this->info("  ✓ Actualizada: {$app->name} (v{$app->version})");
                    } else {
                        $this->warn("  ⚠ Error al actualizar: {$item['name']}");
                    }
                } catch (\Throwable $e) {
                    $this->error("  ✗ Error: {$e->getMessage()}");
                }
            }

            $this->info("\n✓ Actualizaciones completadas: {$updated}/".count($pending));

            return Command::SUCCESS;
        }

        // 3. Import Single App
        if ($appTarget = $this->option('app')) {
            $url = str_starts_with($appTarget, 'http')
                ? $appTarget
                : 'https://www.torrentmac.net/'.ltrim($appTarget, '/').'/';

            $this->info("Importando aplicación desde TorrentMac: {$url}...");
            $app = $importer->importAppByUrl($url, $downloadImages);

            if ($app) {
                $this->info("✓ Aplicación importada con éxito: {$app->name} (v{$app->version})");
                $this->line('  Categoría: '.($app->category?->name ?? 'N/A'));
                $this->line('  Torrent URL: '.($app->torrent_url ?: 'No disponible'));
                $this->line('  R2 / Storage Path: '.($app->torrent_file_path ?: 'No disponible'));
                $this->line('  Descarga Dual: '.($app->has_torrent && $app->download_url_external ? 'SÍ (DDL + Torrent)' : 'Sólo Torrent'));

                return Command::SUCCESS;
            }

            $this->error('Error al importar la aplicación desde TorrentMac.');

            return Command::FAILURE;
        }

        // 4. Sync Latest from Home / Category
        $pages = max(1, (int) $this->option('pages'));
        $category = (string) $this->option('category');

        $this->info("Iniciando escaneo de TorrentMac (Páginas: {$pages}, Categoría: {$category})...");
        $disk = $importer->getStorageDisk();
        $this->line("  [Storage Disk activo]: {$disk}");

        $totalFound = 0;
        $totalImported = 0;

        for ($p = 1; $p <= $pages; $p++) {
            $this->line("\n--- Escaneando página {$p}/{$pages} ---");
            $cards = $importer->getTorrentmacApps($p, false, $category);
            $totalFound += count($cards);

            $this->info('Encontradas '.count($cards).' aplicaciones en la página.');

            foreach ($cards as $card) {
                $this->line("  Procesando: {$card['clean_name']} ({$card['slug']})...");

                try {
                    $app = $importer->importAppByUrl($card['url'], $downloadImages);
                    if ($app) {
                        $totalImported++;
                        $dualTag = $app->has_torrent && $app->download_url_external ? ' [DUAL DDL+TORRENT]' : '';
                        $this->info("  ✓ Importada: {$app->name} v{$app->version}{$dualTag}");
                    } else {
                        $this->warn("  ⚠ Omitida o fallo de parseo: {$card['name']}");
                    }
                } catch (\Throwable $e) {
                    $this->error("  ✗ Error: {$e->getMessage()}");
                }
            }
        }

        $this->info("\n==========================================");
        $this->info('Sincronización completada.');
        $this->info("Total detectadas: {$totalFound} | Total importadas/actualizadas: {$totalImported}");
        $this->info('==========================================');

        return Command::SUCCESS;
    }
}
