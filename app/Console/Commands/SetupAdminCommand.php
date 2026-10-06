<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:setup {--clear-posts : Borrar todos los posts de aplicaciones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurar credenciales de administrador y opcionalmente limpiar posts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = 'itzcarlosandres@gmail.com';
        $password = 'Carlos1995*';

        $user = User::where('email', $email)->first() ?? User::first();

        if ($user) {
            $user->update([
                'name' => 'Carlos',
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $this->info("Usuario existente actualizado: {$email}");
        } else {
            User::create([
                'name' => 'Carlos',
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $this->info("Nuevo usuario administrador creado: {$email}");
        }

        if ($this->option('clear-posts')) {
            $count = Application::count();
            Application::query()->delete();
            $this->warn("Se eliminaron {$count} publicaciones de aplicaciones.");
        }

        cache()->flush();
        $this->info('Caché del sistema vaciada exitosamente.');
        $this->newLine();
        $this->info('=== CREDENCIALES CONFIGURADAS ===');
        $this->line("Email:    <fg=cyan>{$email}</>");
        $this->line("Password: <fg=cyan>{$password}</>");

        return self::SUCCESS;
    }
}
