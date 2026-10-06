<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ResetDownloadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apps:reset-downloads {--real : Sync downloads count to real recorded downloads in downloads table instead of zero}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all application download counters to zero or sync with real portal downloads';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $syncReal = $this->option('real');

        if ($syncReal) {
            $this->info('Sincronizando contadores con las descargas reales...');
            $apps = Application::all();
            $bar = $this->output->createProgressBar($apps->count());
            $bar->start();

            foreach ($apps as $app) {
                $realCount = $app->downloads()->count();
                $app->update(['downloads' => $realCount]);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('¡Contadores sincronizados con la tabla de descargas reales!');
        } else {
            $count = Application::count();
            Application::query()->update(['downloads' => 0]);
            $this->info("¡Se han reiniciado los contadores de {$count} aplicaciones a 0 para producción!");
        }

        Cache::forget('home_stats');

        return self::SUCCESS;
    }
}
