<?php

namespace App\Services\Telegram;

use App\Models\Application;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    /**
     * Format application message using template with dynamic placeholders.
     */
    public function formatMessage(Application $app, ?string $template = null): string
    {
        $template = $template ?: Setting::get('telegram_template');
        if (empty($template)) {
            $template = "🚀 <b>¡NUEVO PROGRAMA PUBLICADO!</b>\n\n📱 <b>Título:</b> {title}\n📂 <b>Categoría:</b> {category}\n🏷️ <b>Versión:</b> {version}\n💾 <b>Tamaño:</b> {size}\n⚡ <b>Tipo:</b> {type}\n\n🔗 <a href=\"{url}\">Descargar en HackMac.cc</a>";
        }

        $appUrl = route('app', $app->slug);
        $type = $app->has_torrent && $app->download_url_external ? 'Dual (Torrent + DDL)' : ($app->has_torrent ? 'Torrent P2P' : 'DDL Directo');
        $categoryName = $app->category?->name ?? 'General';
        $version = $app->version ?: 'Universal';
        $size = $app->formatted_size ?: 'Universal';
        $shortDesc = strip_tags($app->short_description ?: '');

        $replacements = [
            '{title}' => htmlspecialchars($app->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{category}' => htmlspecialchars($categoryName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{version}' => htmlspecialchars($version, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{size}' => htmlspecialchars($size, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{type}' => htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            '{url}' => $appUrl,
            '{description}' => htmlspecialchars($shortDesc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Send an application post to the Telegram channel.
     */
    public function sendApplication(Application $app, ?string $customTemplate = null): array
    {
        $botToken = Setting::get('telegram_bot_token');
        $chatId = Setting::get('telegram_channel_id');

        if (empty($botToken) || empty($chatId)) {
            return [
                'success' => false,
                'message' => 'Configura el Bot Token y el Canal de Telegram en el panel.',
            ];
        }

        $this->resetDailyCounterIfNeeded();

        $message = $this->formatMessage($app, $customTemplate);
        $imageUrl = ! empty($app->screenshot_url) ? $app->screenshot_url : $app->icon_url;

        try {
            $sent = false;
            $response = null;

            // Attempt to send as photo if image URL is available
            if (! empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Telegram caption limit is 1024 characters
                $caption = mb_strlen($message) > 1020 ? mb_substr($message, 0, 1017).'...' : $message;

                $response = Http::timeout(15)->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                    'chat_id' => $chatId,
                    'photo' => $imageUrl,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]);

                if ($response->successful()) {
                    $sent = true;
                }
            }

            // Fallback to text message if sendPhoto failed or no photo URL
            if (! $sent) {
                $response = Http::timeout(15)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => false,
                ]);

                if ($response->successful()) {
                    $sent = true;
                }
            }

            if ($sent) {
                $app->updateQuietly([
                    'telegram_sent' => true,
                    'telegram_sent_at' => now(),
                ]);

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

        $pendingCount = Application::where('published', true)->where('telegram_sent', false)->count();
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
