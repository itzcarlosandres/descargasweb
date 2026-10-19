<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Setting;
use App\Services\Storage\TorrentStorageService;
use Illuminate\Console\Command;

class LocalizeTorrentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'torrents:localize
                            {--limit=50 : Cantidad máxima de aplicaciones a procesar (0 para ilimitado)}
                            {--force : Forzar la re-descarga y re-almacenamiento de torrents ya existentes}
                            {--app= : Procesar únicamente una aplicación por su ID o slug}
                            {--disk= : Forzar destino de almacenamiento: "local" o "r2"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descarga y localiza archivos .torrent externos (ej. Torrentmac), los renombra con la marca HackMac.cc y los sube a R2 o almacenamiento local';

    /**
     * Execute the console command.
     */
    public function handle(TorrentStorageService $storageService): int
    {
        $limit = (int) $this->option('limit');
        $force = (bool) $this->option('force');
        $singleApp = $this->option('app');
        $explicitDisk = $this->option('disk');

        if ($explicitDisk) {
            Setting::set('torrentmac_storage_disk', $explicitDisk, 'scraper');
            $this->info("Destino de almacenamiento configurado temporalmente a: {$explicitDisk}");
        }

        $query = Application::query()->where('has_torrent', true);

        if ($singleApp) {
            $query->where(function ($q) use ($singleApp) {
                if (is_numeric($singleApp)) {
                    $q->where('id', (int) $singleApp);
                } else {
                    $q->where('slug', $singleApp);
                }
            });
        } elseif (! $force) {
            // Find apps with missing local file or pointing to torrentmac/external
            $query->where(function ($q) {
                $q->whereNull('torrent_file_path')
                    ->orWhere('torrent_url', 'like', '%torrentmac%')
                    ->orWhere('torrent_file_path', 'not like', '%HackMac.cc%');
            });
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $apps = $query->orderBy('id', 'desc')->get();
        $total = $apps->count();

        if ($total === 0) {
            $this->info('✓ No hay aplicaciones con torrents pendientes de localizar.');

            return Command::SUCCESS;
        }

        $this->info("Iniciando localización de torrents para {$total} aplicaciones...");
        $this->newLine();

        $successCount = 0;
        $failedCount = 0;
        $fallbackCount = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($apps as $app) {
            $result = $storageService->localizeApplicationTorrent($app, null, $force);

            if ($result['success']) {
                $successCount++;
                $driver = $result['storage_driver'] ?? 'almacenado';
                $this->newLine();
                $this->line("  <info>✓</info> [{$app->name}]: <comment>{$result['filename']}</comment> ({$driver})");
            } else {
                if (! empty($result['fallback_magnet'])) {
                    $fallbackCount++;
                    $this->newLine();
                    $this->line("  <comment>!</comment> [{$app->name}]: Enlace externo caído. Magnet link activo como respaldo.");
                } else {
                    $failedCount++;
                    $this->newLine();
                    $this->line("  <error>✗</error> [{$app->name}]: {$result['error']}");
                }
            }

            // Also check versions
            foreach ($app->versions as $version) {
                if (! empty($version->torrent_url) && (str_contains($version->torrent_url, 'torrentmac') || empty($version->torrent_file_path) || $force)) {
                    $storageService->localizeApplicationTorrent($app, $version, $force);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('=========================================');
        $this->info(' Resumen de Localización de Torrents:');
        $this->line("  • Localizados y renombrados: <info>{$successCount}</info>");
        if ($fallbackCount > 0) {
            $this->line("  • Enlaces externos caídos (con respaldo Magnet): <comment>{$fallbackCount}</comment>");
        }
        if ($failedCount > 0) {
            $this->line("  • Errores / Sin enlace disponible: <error>{$failedCount}</error>");
        }
        $this->info('=========================================');

        return Command::SUCCESS;
    }
}
