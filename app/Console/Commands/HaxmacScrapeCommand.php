<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\Scraper\HaxmacImporter;
use Illuminate\Console\Command;

class HaxmacScrapeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'haxmac:scrape
                            {--categories : Sincronizar todas las categorías del menú de HaxMac}
                            {--search= : Buscar e importar aplicaciones por término}
                            {--app= : Importar una aplicación específica por URL o slug}
                            {--category= : Importar aplicaciones de una categoría específica por slug}
                            {--sync-latest : Sincronizar automáticamente novedades y actualizaciones desde la portada}
                            {--limit=0 : Límite de programas a procesar en la sincronización}
                            {--pages=1 : Número de páginas a recorrer por categoría}
                            {--no-images : No descargar las imágenes de iconos localmente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extrae aplicaciones, categorías y enlaces limpios desde HaxMac';

    /**
     * Execute the console command.
     */
    public function handle(HaxmacImporter $importer): int
    {
        $downloadImages = ! $this->option('no-images');

        // 1. Sync Categories
        if ($this->option('categories')) {
            $this->info('Sincronizando categorías desde el menú de HaxMac...');
            $result = $importer->syncCategories();
            $this->info("✓ Categorías creadas: {$result['created']}, actualizadas: {$result['updated']}");

            foreach ($result['categories'] as $cat) {
                $this->line("  • {$cat['name']} ({$cat['slug']})");
            }

            return Command::SUCCESS;
        }

        // 2. Import Single App
        if ($appTarget = $this->option('app')) {
            $this->info("Importando aplicación: {$appTarget}...");
            $app = $importer->importApp($appTarget, $downloadImages);

            if ($app) {
                $this->info("✓ Aplicación importada con éxito: {$app->name} (v{$app->version})");
                $this->line('  Categoría: '.($app->category?->name ?? 'N/A'));
                $this->line('  Enlace Descarga: '.($app->download_url_external ?: 'No detectado'));

                return Command::SUCCESS;
            }

            $this->error("✗ No se pudo importar la aplicación {$appTarget}");

            return Command::FAILURE;
        }

        // 3. Search and Import
        if ($searchQuery = $this->option('search')) {
            $this->info("Buscando en HaxMac: '{$searchQuery}'...");
            $results = $importer->searchHaxmac($searchQuery);

            if (empty($results)) {
                $this->warn("No se encontraron resultados en HaxMac para '{$searchQuery}'.");

                return Command::SUCCESS;
            }

            $this->info('Se encontraron '.count($results).' resultados:');
            foreach ($results as $item) {
                $status = $item['is_imported']
                    ? ($item['has_update'] ? '<comment>[Actualización Disponible]</comment>' : '<info>[Ya Importado]</info>')
                    : '<fg=gray>[Nuevo]</>';

                $this->line("  • {$item['name']} (v{$item['version']}) {$status}");
            }

            return Command::SUCCESS;
        }

        // 4. Import Category
        if ($categorySlug = $this->option('category')) {
            $pages = (int) $this->option('pages');
            $this->info("Importando categoría '{$categorySlug}' ({$pages} páginas)...");

            $result = $importer->importCategory($categorySlug, $pages, $downloadImages);

            $this->info('✓ Resumen de importación:');
            $this->line("  Nuevas: {$result['imported']}");
            $this->line("  Actualizadas: {$result['updated']}");
            $this->line("  Fallidas: {$result['failed']}");

            return Command::SUCCESS;
        }

        // 5. Smart Sync Latest Updates (Homepage scanner)
        if ($this->option('sync-latest')) {
            Setting::set('scraper_cron_enabled', 1, 'scraper');
            Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
            Setting::clearCache();

            $limit = (int) $this->option('limit');
            $this->info('Consultando portada de HaxMac para detectar novedades y actualizaciones...');
            $result = $importer->syncLatestUpdates($downloadImages, $limit);

            $this->info("✓ Revisión completada ({$result['total_checked']} programas analizados):");
            $this->line("  • Nuevos importados: {$result['new_imported']}");
            $this->line("  • Actualizados: {$result['updated']}");

            foreach ($result['items'] as $item) {
                $tag = $item['type'] === 'new' ? '<info>[NUEVO]</info>' : '<comment>[ACTUALIZADO]</comment>';
                $this->line("    {$tag} {$item['name']} (v{$item['version']})");
            }

            return Command::SUCCESS;
        }

        // If no option supplied, show help
        $this->info('Por favor especifica una opción:');
        $this->line('  php artisan haxmac:scrape --sync-latest');
        $this->line('  php artisan haxmac:scrape --categories');
        $this->line('  php artisan haxmac:scrape --search="Photoshop"');
        $this->line('  php artisan haxmac:scrape --app="cleanmymac"');
        $this->line('  php artisan haxmac:scrape --category="developer-tools" --pages=1');

        return Command::SUCCESS;
    }
}
