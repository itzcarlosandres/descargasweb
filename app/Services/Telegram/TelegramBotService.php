<?php

namespace App\Services\Telegram;

use App\Models\Application;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TelegramBotService
{
    /**
     * Format application message using template with dynamic placeholders.
     */
    public function formatMessage(Application $app, ?string $template = null): string
    {
        $template = $template ?: Setting::get('telegram_template');
        if (empty($template)) {
            $template = "🚀 <b>NEW APP PUBLISHED!</b>\n\n📱 <b>Title:</b> {title}\n📂 <b>Category:</b> {category}\n🏷️ <b>Version:</b> {version}\n💾 <b>Size:</b> {size}\n⚡ <b>Type:</b> {type}\n\n🔗 <a href=\"{url}\">Download on HackMac.cc</a>";
        }

        $appUrl = route('app', $app->slug);
        $type = $app->has_torrent && $app->download_url_external ? 'Dual (Torrent + DDL)' : ($app->has_torrent ? 'Torrent P2P' : 'DDL Directo');
        $categoryName = $app->category?->name ?? 'General';
        $version = $app->version ?: 'Universal';
        $size = $app->formatted_size ?: 'Universal';
        $shortDesc = strip_tags($app->short_description ?: '');
        $coverUrl = route('app.og-image', $app->slug);

        $replacements = [
            '{title}' => htmlspecialchars($app->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{category}' => htmlspecialchars($categoryName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{version}' => htmlspecialchars($version, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{size}' => htmlspecialchars($size, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{type}' => htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{url}' => $appUrl,
            '{description}' => htmlspecialchars($shortDesc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{image}' => $coverUrl,
            '{cover}' => $coverUrl,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Resolve and prepare cover image for Telegram sendPhoto.
     * Handles local disk paths, formats (converts AVIF to JPEG in memory), and URLs.
     *
     * @return array{bytes?: string, filename?: string, mime?: string, url?: string}|null
     */
    public function resolveCoverImage(Application $app): ?array
    {
        $candidate = null;

        // 1. Direct screenshot property
        if (! empty($app->screenshot)) {
            $candidate = $app->screenshot;
        }

        // 2. First gallery image
        if (empty($candidate)) {
            $firstGallery = $app->images()->orderBy('sort_order')->first();
            if ($firstGallery && ! empty($firstGallery->path)) {
                $candidate = $firstGallery->path;
            }
        }

        // 3. Check disk for matching screenshot files (e.g. apps/{slug}-screen-*)
        if (empty($candidate)) {
            try {
                $diskFiles = Storage::disk('public')->files('apps');
                $matches = array_values(array_filter($diskFiles, fn ($f) => str_starts_with($f, 'apps/'.$app->slug.'-screen')));
                if (! empty($matches)) {
                    $candidate = $matches[0];
                    try {
                        $app->updateQuietly(['screenshot' => $candidate]);
                    } catch (\Throwable $e) {
                        // ignore DB write errors
                    }
                }
            } catch (\Throwable $e) {
                // ignore storage listing errors
            }
        }

        // 4. App icon as secondary fallback
        if (empty($candidate) && ! empty($app->icon)) {
            $candidate = $app->icon;
        }

        // 5. If no candidate found, use default site OG cover
        if (empty($candidate)) {
            $defaultOg = public_path('images/og-share.jpg');
            if (file_exists($defaultOg)) {
                return [
                    'bytes' => file_get_contents($defaultOg),
                    'filename' => 'cover.jpg',
                    'mime' => 'image/jpeg',
                ];
            }

            return null;
        }

        // Case A: Remote URL
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            try {
                $imgRes = Http::timeout(10)->get($candidate);
                if ($imgRes->successful()) {
                    $body = $imgRes->body();
                    $pathPart = parse_url($candidate, PHP_URL_PATH) ?? '';
                    if (str_ends_with(strtolower($pathPart), '.avif') || str_starts_with(substr($body, 4, 8), 'ftypavif')) {
                        $converted = $this->convertAvifToJpeg($body);
                        if ($converted) {
                            return [
                                'bytes' => $converted,
                                'filename' => 'cover.jpg',
                                'mime' => 'image/jpeg',
                            ];
                        }
                    }

                    return [
                        'bytes' => $body,
                        'filename' => basename($pathPart) ?: 'cover.jpg',
                        'mime' => $imgRes->header('Content-Type') ?: 'image/jpeg',
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("Could not download remote cover {$candidate}: ".$e->getMessage());
            }

            return [
                'url' => $candidate,
            ];
        }

        // Case B: Local storage or public file
        $localPath = Storage::disk('public')->path($candidate);
        if (! file_exists($localPath)) {
            $altPath = public_path('storage/'.$candidate);
            if (file_exists($altPath)) {
                $localPath = $altPath;
            } elseif (file_exists(public_path($candidate))) {
                $localPath = public_path($candidate);
            } else {
                $defaultOg = public_path('images/og-share.jpg');
                if (file_exists($defaultOg)) {
                    $localPath = $defaultOg;
                } else {
                    return null;
                }
            }
        }

        $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));

        // SVG is not supported by Telegram sendPhoto - use default banner
        if ($extension === 'svg') {
            $defaultOg = public_path('images/og-share.jpg');
            if (file_exists($defaultOg)) {
                return [
                    'bytes' => file_get_contents($defaultOg),
                    'filename' => 'cover.jpg',
                    'mime' => 'image/jpeg',
                ];
            }

            return null;
        }

        // Convert AVIF to JPEG in memory
        if ($extension === 'avif') {
            $converted = $this->convertAvifPathToJpeg($localPath);
            if ($converted) {
                return [
                    'bytes' => $converted,
                    'filename' => 'cover.jpg',
                    'mime' => 'image/jpeg',
                ];
            }

            $defaultOg = public_path('images/og-share.jpg');
            if (file_exists($defaultOg)) {
                return [
                    'bytes' => file_get_contents($defaultOg),
                    'filename' => 'cover.jpg',
                    'mime' => 'image/jpeg',
                ];
            }
        }

        // Standard JPEG, PNG, WEBP
        $bytes = @file_get_contents($localPath);
        if ($bytes !== false) {
            return [
                'bytes' => $bytes,
                'filename' => basename($localPath),
                'mime' => @mime_content_type($localPath) ?: 'image/jpeg',
            ];
        }

        return null;
    }

    /**
     * Convert an AVIF file on disk to JPEG binary bytes.
     */
    protected function convertAvifPathToJpeg(string $filePath): ?string
    {
        if (! function_exists('imagecreatefromavif')) {
            return null;
        }

        try {
            $img = @imagecreatefromavif($filePath);
            if (! $img) {
                return null;
            }

            ob_start();
            imagejpeg($img, null, 88);
            $jpgData = ob_get_clean();
            imagedestroy($img);

            return $jpgData ?: null;
        } catch (\Throwable $e) {
            Log::warning("AVIF to JPEG conversion failed for {$filePath}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Convert in-memory AVIF binary data to JPEG binary bytes.
     */
    protected function convertAvifToJpeg(string $binaryData): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        try {
            $img = @imagecreatefromstring($binaryData);
            if (! $img) {
                return null;
            }

            ob_start();
            imagejpeg($img, null, 88);
            $jpgData = ob_get_clean();
            imagedestroy($img);

            return $jpgData ?: null;
        } catch (\Throwable $e) {
            Log::warning('AVIF binary to JPEG conversion failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Send an application post to the Telegram channel.
     */
    public function sendApplication(Application $app, ?string $customTemplate = null): array
    {
        $botToken = Setting::get('telegram_bot_token');
        $chatId = Setting::get('telegram_channel_id') ?: Setting::get('telegram_channel');

        if (empty($botToken) || empty($chatId)) {
            return [
                'success' => false,
                'message' => 'Configura el Bot Token y el Canal de Telegram en el panel.',
            ];
        }

        $this->resetDailyCounterIfNeeded();

        $message = $this->formatMessage($app, $customTemplate);
        $cover = $this->resolveCoverImage($app);

        try {
            $sent = false;
            $response = null;

            // 1. Attempt to send as photo if cover image is available
            if ($cover) {
                // Telegram caption limit is 1024 characters
                $caption = mb_strlen($message) > 1020 ? mb_substr($message, 0, 1017).'...' : $message;

                if (! empty($cover['bytes'])) {
                    $response = Http::timeout(30)
                        ->asMultipart()
                        ->attach('photo', $cover['bytes'], $cover['filename'] ?? 'cover.jpg')
                        ->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                            'chat_id' => $chatId,
                            'caption' => $caption,
                            'parse_mode' => 'HTML',
                        ]);
                } elseif (! empty($cover['url'])) {
                    $response = Http::timeout(20)->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                        'chat_id' => $chatId,
                        'photo' => $cover['url'],
                        'caption' => $caption,
                        'parse_mode' => 'HTML',
                    ]);
                }

                if ($response && $response->successful()) {
                    $sent = true;
                } else {
                    $err = $response ? ($response->json('description') ?? $response->body()) : 'sin respuesta';
                    Log::warning("Telegram sendPhoto failed for {$app->name}: {$err}. Intentando fallback a sendMessage.");
                }
            }

            // 2. Fallback to text message if sendPhoto failed or no photo
            if (! $sent) {
                $response = Http::timeout(15)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => false,
                ]);

                if ($response && $response->successful()) {
                    $sent = true;
                }
            }

            if ($sent) {
                try {
                    if (Schema::hasColumn('applications', 'telegram_sent')) {
                        $app->updateQuietly([
                            'telegram_sent' => true,
                            'telegram_sent_at' => now(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo marcar la app como telegram_sent: '.$e->getMessage());
                }

                $todayCount = (int) Setting::get('telegram_sent_today_count', 0) + 1;
                $totalCount = (int) Setting::get('telegram_total_sent_count', 0) + 1;

                Setting::set('telegram_sent_today_count', (string) $todayCount, 'telegram');
                Setting::set('telegram_total_sent_count', (string) $totalCount, 'telegram');
                Setting::set('telegram_last_sent_date', now()->toDateString(), 'telegram');

                Log::info("Telegram auto-post successful for: {$app->name} (ID: {$app->id})");

                return [
                    'success' => true,
                    'message' => "✓ Publicado con éxito en Telegram: {$app->name}",
                    'app' => [
                        'id' => $app->id,
                        'name' => $app->name,
                        'sent_today' => $todayCount,
                        'total_sent' => $totalCount,
                    ],
                ];
            }

            $errorDetail = $response ? ($response->json('description') ?? $response->body()) : 'Error desconocido';
            Log::warning("Telegram post failed for {$app->name}: {$errorDetail}");

            return [
                'success' => false,
                'message' => "Error de Telegram: {$errorDetail}",
            ];
        } catch (\Throwable $e) {
            Log::error("Telegram exception for {$app->name}: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => 'Excepción al contactar Telegram: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Dispatch the next pending application in queue.
     */
    public function dispatchNext(): array
    {
        $this->resetDailyCounterIfNeeded();

        $dailyLimit = (int) Setting::get('telegram_daily_limit', 20);
        $todayCount = (int) Setting::get('telegram_sent_today_count', 0);

        if ($dailyLimit > 0 && $todayCount >= $dailyLimit) {
            return [
                'success' => false,
                'message' => "Se ha alcanzado el límite diario de {$dailyLimit} publicaciones.",
                'limit_reached' => true,
            ];
        }

        try {
            if (! Schema::hasColumn('applications', 'telegram_sent')) {
                return [
                    'success' => false,
                    'message' => 'Falta ejecutar las migraciones en la base de datos (php artisan migrate).',
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión a la base de datos: '.$e->getMessage(),
            ];
        }

        $app = Application::pendingTelegram()->first();

        if (! $app) {
            return [
                'success' => false,
                'message' => 'No hay aplicaciones pendientes por publicar en la cola.',
                'empty_queue' => true,
            ];
        }

        return $this->sendApplication($app);
    }

    /**
     * Cron runner: dispatches 1 app per minute if auto pilot is enabled.
     */
    public function dispatchCron(): array
    {
        $autoPilot = filter_var(Setting::get('telegram_auto_pilot', false), FILTER_VALIDATE_BOOLEAN);

        if (! $autoPilot) {
            return [
                'skipped' => true,
                'reason' => 'Piloto automático desactivado.',
            ];
        }

        return $this->dispatchNext();
    }

    /**
     * Test connection to Telegram bot & channel.
     */
    public function testConnection(?string $token = null, ?string $channelId = null): array
    {
        $token = $token ?: Setting::get('telegram_bot_token');
        $channelId = $channelId ?: Setting::get('telegram_channel_id');

        if (empty($token) || empty($channelId)) {
            return [
                'success' => false,
                'message' => 'Debes ingresar el Token del Bot y el ID/Username del Canal.',
            ];
        }

        try {
            // First check bot identity
            $meRes = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");
            if (! $meRes->successful()) {
                return [
                    'success' => false,
                    'message' => 'Token inválido: '.($meRes->json('description') ?? 'No se pudo autenticar el bot.'),
                ];
            }

            $botInfo = $meRes->json('result');
            $botUsername = $botInfo['username'] ?? 'Bot';

            // Send test ping to the channel
            $testMsg = "🤖 <b>Conexión Exitosa con HackMac</b>\n\n"
                ."El Bot @{$botUsername} está conectado correctamente a este canal y listo para publicar novedades.";

            $sendRes = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $channelId,
                'text' => $testMsg,
                'parse_mode' => 'HTML',
            ]);

            if ($sendRes->successful()) {
                return [
                    'success' => true,
                    'message' => "¡Conexión exitosa! Mensaje de prueba enviado al canal con @{$botUsername}.",
                    'bot' => $botInfo,
                ];
            }

            $desc = $sendRes->json('description') ?? 'No se pudo enviar el mensaje al canal.';

            return [
                'success' => false,
                'message' => "El bot @{$botUsername} no pudo publicar en el canal: {$desc}. Asegúrate de que el bot sea Administrador del canal con permisos para publicar.",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Reset today counter when the date changes.
     */
    public function resetDailyCounterIfNeeded(): void
    {
        $today = now()->toDateString();
        $lastSentDate = Setting::get('telegram_last_sent_date');

        if ($lastSentDate !== $today) {
            Setting::set('telegram_sent_today_count', '0', 'telegram');
            Setting::set('telegram_last_sent_date', $today, 'telegram');
        }
    }

    /**
     * Get queue statistics for the admin dashboard.
     */
    public function getQueueStats(): array
    {
        $this->resetDailyCounterIfNeeded();

        $pendingCount = 0;
        try {
            if (Schema::hasColumn('applications', 'telegram_sent')) {
                $pendingCount = Application::where('published', true)->where('telegram_sent', false)->count();
            }
        } catch (\Throwable $e) {
            $pendingCount = 0;
        }

        $sentToday = (int) Setting::get('telegram_sent_today_count', 0);
        $dailyLimit = (int) Setting::get('telegram_daily_limit', 20);
        $totalSent = (int) Setting::get('telegram_total_sent_count', 0);
        $autoPilot = filter_var(Setting::get('telegram_auto_pilot', false), FILTER_VALIDATE_BOOLEAN);
        $botToken = Setting::get('telegram_bot_token');
        $channelId = Setting::get('telegram_channel_id');

        return [
            'pending_count' => $pendingCount,
            'sent_today' => $sentToday,
            'daily_limit' => $dailyLimit,
            'total_sent' => $totalSent,
            'auto_pilot' => $autoPilot,
            'is_configured' => ! empty($botToken) && ! empty($channelId),
            'channel_url' => Setting::get('telegram_channel_url', 'https://t.me/hackmac'),
        ];
    }
}
