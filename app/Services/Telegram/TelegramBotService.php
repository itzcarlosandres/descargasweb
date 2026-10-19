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
     * Resolve and prepare the featured cover image ("Imagen Destacada") for Telegram sendPhoto.
     * Guaranteed priority:
     *   1. Real application screenshot (from database or disk, converting AVIF and WEBP to JPEG).
     *   2. First gallery image.
     *   3. Dynamic custom 1200x630 Featured Card generated for this specific app (using its icon, name, version, category).
     *   4. High-resolution app icon converted to JPEG.
     * Note: Generic default banners (og-share.jpg) are completely eliminated.
     *
     * @return array{bytes: string, filename: string, mime: string}|null
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

        // If a screenshot candidate exists, resolve and convert it
        if (! empty($candidate)) {
            $resolved = $this->prepareImageBytes($candidate);
            if ($resolved) {
                return $resolved;
            }
        }

        // 4. No screenshot found: Generate a custom, high-resolution 1200x630 Featured Card ("Imagen Destacada")
        $generatedBytes = $this->generateFeaturedCard($app);
        if ($generatedBytes) {
            try {
                $coverPath = "apps/{$app->slug}-cover.jpg";
                Storage::disk('public')->put($coverPath, $generatedBytes);
                if (empty($app->screenshot)) {
                    $app->updateQuietly(['screenshot' => $coverPath]);
                }
            } catch (\Throwable $e) {
                // ignore write errors
            }

            return [
                'bytes' => $generatedBytes,
                'filename' => 'cover.jpg',
                'mime' => 'image/jpeg',
            ];
        }

        // 5. Fallback: app icon converted to JPEG
        if (! empty($app->icon)) {
            $iconResolved = $this->prepareImageBytes($app->icon);
            if ($iconResolved) {
                return $iconResolved;
            }
        }

        return null;
    }

    /**
     * Prepare image bytes from local disk, public storage, or remote URL.
     * Automatically converts AVIF and WEBP formats to JPEG so Telegram sendPhoto always succeeds.
     *
     * @return array{bytes: string, filename: string, mime: string}|null
     */
    public function prepareImageBytes(string $candidate): ?array
    {
        $candidate = trim($candidate);
        if (empty($candidate)) {
            return null;
        }

        $rawBytes = null;
        $extension = '';
        $filename = 'cover.jpg';

        // Case A: Remote URL
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            try {
                $imgRes = Http::timeout(12)->get($candidate);
                if ($imgRes->successful()) {
                    $rawBytes = $imgRes->body();
                    $pathPart = parse_url($candidate, PHP_URL_PATH) ?? '';
                    $extension = strtolower(pathinfo($pathPart, PATHINFO_EXTENSION));
                    $filename = basename($pathPart) ?: 'cover.jpg';
                }
            } catch (\Throwable $e) {
                Log::warning("Could not download remote image {$candidate}: ".$e->getMessage());
            }
        } else {
            // Case B: Local storage or public file
            $localPath = Storage::disk('public')->path($candidate);
            if (! file_exists($localPath)) {
                $altPath = public_path('storage/'.$candidate);
                if (file_exists($altPath)) {
                    $localPath = $altPath;
                } elseif (file_exists(public_path($candidate))) {
                    $localPath = public_path($candidate);
                } else {
                    return null;
                }
            }

            $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
            $filename = basename($localPath);
            $rawBytes = @file_get_contents($localPath);
        }

        if (empty($rawBytes)) {
            return null;
        }

        // SVG is not supported as a photo in Telegram
        if ($extension === 'svg') {
            return null;
        }

        // Convert AVIF to JPEG
        $isAvif = $extension === 'avif' || str_starts_with(substr($rawBytes, 4, 8), 'ftypavif');
        if ($isAvif) {
            $converted = $this->convertAvifToJpeg($rawBytes);
            if ($converted) {
                return [
                    'bytes' => $converted,
                    'filename' => 'cover.jpg',
                    'mime' => 'image/jpeg',
                ];
            }

            return null;
        }

        // Convert WEBP to JPEG (Telegram sendPhoto does NOT accept WEBP format)
        $isWebp = $extension === 'webp' || (str_starts_with($rawBytes, 'RIFF') && str_contains(substr($rawBytes, 8, 8), 'WEBP'));
        if ($isWebp) {
            $converted = $this->convertWebpToJpeg($rawBytes);
            if ($converted) {
                return [
                    'bytes' => $converted,
                    'filename' => 'cover.jpg',
                    'mime' => 'image/jpeg',
                ];
            }

            return null;
        }

        return [
            'bytes' => $rawBytes,
            'filename' => $filename,
            'mime' => str_ends_with($filename, '.png') ? 'image/png' : 'image/jpeg',
        ];
    }

    /**
     * Convert in-memory WEBP binary data to JPEG binary bytes.
     */
    protected function convertWebpToJpeg(string $binaryData): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        try {
            $img = @imagecreatefromstring($binaryData);
            if (! $img) {
                return null;
            }

            $w = imagesx($img);
            $h = imagesy($img);
            $bg = imagecreatetruecolor($w, $h);
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefilledrectangle($bg, 0, 0, $w, $h, $white);
            imagecopy($bg, $img, 0, 0, 0, 0, $w, $h);

            ob_start();
            imagejpeg($bg, null, 90);
            $jpgData = ob_get_clean();
            imagedestroy($img);
            imagedestroy($bg);

            return $jpgData ?: null;
        } catch (\Throwable $e) {
            Log::warning('WEBP binary to JPEG conversion failed: '.$e->getMessage());

            return null;
        }
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
     * Generate a premium 1200x630 macOS featured banner card for the application.
     */
    public function generateFeaturedCard(Application $app): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        try {
            $width = 1200;
            $height = 630;
            $im = imagecreatetruecolor($width, $height);
            imagealphablending($im, true);
            imagesavealpha($im, true);

            // 1. Dark sleek gradient background (#0A0E17 to #151A26)
            for ($y = 0; $y < $height; $y++) {
                $ratio = $y / $height;
                $r = (int) (10 + (21 - 10) * $ratio);
                $g = (int) (14 + (26 - 14) * $ratio);
                $b = (int) (23 + (38 - 23) * $ratio);
                $color = imagecolorallocate($im, $r, $g, $b);
                imageline($im, 0, $y, $width, $y, $color);
            }

            // 2. Subtle ambient glow behind the icon
            for ($radius = 180; $radius > 0; $radius -= 15) {
                $alpha = (int) (127 - (180 - $radius) * 0.15);
                $glowColor = imagecolorallocatealpha($im, 245, 158, 11, max(115, min(127, $alpha)));
                imagefilledellipse($im, 220, 315, $radius * 2, $radius * 2, $glowColor);
            }

            // 3. Traffic light dots (macOS style top-left)
            $red = imagecolorallocate($im, 255, 95, 86);
            $yellow = imagecolorallocate($im, 255, 189, 46);
            $green = imagecolorallocate($im, 39, 201, 63);
            imagefilledellipse($im, 70, 55, 16, 16, $red);
            imagefilledellipse($im, 96, 55, 16, 16, $yellow);
            imagefilledellipse($im, 122, 55, 16, 16, $green);

            // 4. Header Brand
            $brandColor = imagecolorallocate($im, 156, 163, 175);
            $gold = imagecolorallocate($im, 245, 158, 11);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagestring($im, 5, 160, 48, 'HACKMAC.CC', $gold);
            imagestring($im, 4, 280, 49, '|   PREMIUM MACOS RELEASES', $brandColor);

            // 5. Draw Icon on the left (box at x=100, y=195, w=240, h=240)
            $iconDrawn = false;
            $iconCandidate = $app->icon;
            if ($iconCandidate) {
                $iconLocalPath = Storage::disk('public')->path($iconCandidate);
                if (! file_exists($iconLocalPath)) {
                    $alt = public_path('storage/'.$iconCandidate);
                    if (file_exists($alt)) {
                        $iconLocalPath = $alt;
                    }
                }

                if (file_exists($iconLocalPath)) {
                    $raw = @file_get_contents($iconLocalPath);
                    $srcImg = $raw ? @imagecreatefromstring($raw) : null;

                    if ($srcImg) {
                        $cardBg = imagecolorallocate($im, 22, 27, 34);
                        $cardBorder = imagecolorallocate($im, 48, 54, 61);
                        imagefilledrectangle($im, 95, 190, 345, 440, $cardBg);
                        imagerectangle($im, 95, 190, 345, 440, $cardBorder);
                        imagecopyresampled($im, $srcImg, 100, 195, 0, 0, 240, 240, imagesx($srcImg), imagesy($srcImg));
                        imagedestroy($srcImg);
                        $iconDrawn = true;
                    }
                }
            }

            if (! $iconDrawn) {
                // Fallback Monogram Icon
                $badgeBg = imagecolorallocate($im, 30, 41, 59);
                $badgeBorder = imagecolorallocate($im, 245, 158, 11);
                imagefilledrectangle($im, 100, 195, 340, 435, $badgeBg);
                imagerectangle($im, 100, 195, 340, 435, $badgeBorder);
                $initial = strtoupper(substr($app->name, 0, 1));
                imagestring($im, 5, 210, 300, $initial, $white);
            }

            // 6. Right Side Content
            $name = mb_strimwidth($app->name, 0, 36, '...');
            imagestring($im, 5, 400, 205, strtoupper($name), $white);

            $catName = $app->category?->name ?? 'macOS Application';
            $versionStr = $app->version ? "v{$app->version}" : 'Latest Release';
            $sizeStr = $app->size ?: 'Universal';

            $pillBg = imagecolorallocate($im, 30, 41, 59);
            $pillBorder = imagecolorallocate($im, 51, 65, 85);
            imagefilledrectangle($im, 400, 255, 620, 290, $pillBg);
            imagerectangle($im, 400, 255, 620, 290, $pillBorder);
            imagestring($im, 4, 415, 265, $versionStr, $gold);

            imagefilledrectangle($im, 635, 255, 880, 290, $pillBg);
            imagerectangle($im, 635, 255, 880, 290, $pillBorder);
            imagestring($im, 4, 650, 265, mb_strimwidth($catName, 0, 24, ''), $white);

            $textMuted = imagecolorallocate($im, 148, 163, 184);
            imagestring($im, 4, 400, 320, 'Architecture:  Universal (Apple Silicon & Intel 64-bit)', $textMuted);
            imagestring($im, 4, 400, 355, "File Size:     {$sizeStr}", $textMuted);
            imagestring($im, 4, 400, 390, 'Security:      Verified & Scanned Clean  -  Direct High Speed', $gold);

            imagefilledrectangle($im, 0, 624, $width, 630, $gold);

            ob_start();
            imagejpeg($im, null, 92);
            $bytes = ob_get_clean();
            imagedestroy($im);

            return $bytes ?: null;
        } catch (\Throwable $e) {
            Log::warning('Featured card generation failed: '.$e->getMessage());

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

        $buttonText = Setting::get('telegram_post_button_text') ?: '📥 DESCARGAR AHORA';
        $replyMarkup = json_encode([
            'inline_keyboard' => [
                [
                    [
                        'text' => $buttonText,
                        'url' => route('app', $app->slug),
                    ],
                ],
            ],
        ]);

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
                            'reply_markup' => $replyMarkup,
                        ]);
                } elseif (! empty($cover['url'])) {
                    $response = Http::timeout(20)->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                        'chat_id' => $chatId,
                        'photo' => $cover['url'],
                        'caption' => $caption,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $replyMarkup,
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
                    'disable_web_page_preview' => true,
                    'reply_markup' => $replyMarkup,
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
