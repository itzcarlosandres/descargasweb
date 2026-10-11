<?php

namespace App\Services\Notification;

use App\Models\Application;
use App\Models\BrokenLinkReport;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationDispatcher
{
    /**
     * Dispatch notification for newly published or updated application
     */
    public function notifyAppRelease(Application $app, bool $isUpdate = false): void
    {
        $notifyOnNew = (bool) Setting::get('notify_on_new_app', true);
        $notifyOnUpdate = (bool) Setting::get('notify_on_update', true);

        if ($isUpdate && ! $notifyOnUpdate) {
            return;
        }

        if (! $isUpdate && ! $notifyOnNew) {
            return;
        }

        if ((bool) Setting::get('telegram_enabled', false)) {
            $this->notifyTelegram($app, $isUpdate);
        }

        if ((bool) Setting::get('discord_enabled', false)) {
            $this->notifyDiscord($app, $isUpdate);
        }
    }

    /**
     * Dispatch notification when a user reports a broken download link
     */
    public function notifyBrokenLink(BrokenLinkReport $report): void
    {
        if (! (bool) Setting::get('notify_on_broken_link', true)) {
            return;
        }

        $app = $report->application;
        $appName = $app?->name ?? 'Aplicación Desconocida';
        $appUrl = $app ? route('app', $app->slug) : config('app.url');
        $reasonLabels = [
            'ddl' => 'Enlace Directo (DDL) caído / no descarga',
            'torrent' => 'Archivo .torrent o Magnet sin semillas',
            'mirror' => 'Servidores espejo / mirrors caídos',
            'other' => 'Archivo corrupto o problema de versión',
        ];
        $reason = $reasonLabels[$report->type] ?? ucfirst($report->type);
        $notes = $report->notes ? trim($report->notes) : 'Sin comentarios adicionales';

        // 1. Telegram
        if ((bool) Setting::get('telegram_enabled', false)) {
            $botToken = Setting::get('telegram_bot_token');
            $chatId = Setting::get('telegram_channel_id');

            if (! empty($botToken) && ! empty($chatId)) {
                try {
                    $caption = "⚠️ *REPORTE DE ENLACE CAÍDO*\n\n"
                        ."📱 *Programa:* [{$appName}]({$appUrl})\n"
                        ."🚨 *Motivo:* `{$reason}`\n"
                        ."📝 *Notas:* {$notes}\n"
                        ."🌐 *IP:* `{$report->ip_address}`\n\n"
                        ."👉 [Ver Ficha]({$appUrl}) · [Panel Admin](".route('admin.applications.edit', $app ?? 1).')';

                    Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $caption,
                        'parse_mode' => 'Markdown',
                        'disable_web_page_preview' => false,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("Telegram broken link alert failed: {$e->getMessage()}");
                }
            }
        }

        // 2. Discord
        if ((bool) Setting::get('discord_enabled', false)) {
            $webhookUrl = Setting::get('discord_webhook_url');

            if (! empty($webhookUrl)) {
                try {
                    $payload = [
                        'embeds' => [
                            [
                                'title' => "⚠️ Reporte de Enlace Caído: {$appName}",
                                'url' => $appUrl,
                                'description' => 'Un usuario ha reportado un problema con los enlaces de descarga.',
                                'color' => 0xEF4444, // Red
                                'thumbnail' => [
                                    'url' => $app?->icon_url ?? '',
                                ],
                                'fields' => [
                                    [
                                        'name' => 'Motivo',
                                        'value' => $reason,
                                        'inline' => true,
                                    ],
                                    [
                                        'name' => 'IP',
                                        'value' => $report->ip_address ?: 'Desconocida',
                                        'inline' => true,
                                    ],
                                    [
                                        'name' => 'Detalles / Observaciones',
                                        'value' => $notes,
                                        'inline' => false,
                                    ],
                                ],
                                'footer' => [
                                    'text' => 'HackMac · Broken Link System',
                                ],
                                'timestamp' => now()->toIso8601String(),
                            ],
                        ],
                    ];

                    Http::timeout(10)->post($webhookUrl, $payload);
                } catch (\Throwable $e) {
                    Log::warning("Discord broken link alert failed: {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * Send notification to Telegram channel
     */
    public function notifyTelegram(Application $app, bool $isUpdate = false): void
    {
        $botToken = Setting::get('telegram_bot_token');
        $chatId = Setting::get('telegram_channel_id');

        if (empty($botToken) || empty($chatId)) {
            return;
        }

        try {
            $action = $isUpdate ? '🔄 *App Actualizada*' : '🚀 *Nuevo Lanzamiento*';
            $appUrl = route('app', $app->slug);
            $type = $app->has_torrent && $app->download_url_external ? 'Dual (Torrent + DDL)' : ($app->has_torrent ? 'Torrent P2P' : 'DDL Directo');

            $caption = "{$action}\n\n"
                ."📱 *{$app->name}* `v{$app->version}`\n"
                .'📂 *Categoría:* '.($app->category?->name ?? 'General')."\n"
                .'💾 *Tamaño:* '.($app->size ?: 'Universal')."\n"
                ."⚡ *Tipo:* {$type}\n\n"
                ."🔗 [Descargar en HackMac.cc]({$appUrl})";

            if (! empty($app->icon_url)) {
                Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                    'chat_id' => $chatId,
                    'photo' => $app->icon_url,
                    'caption' => $caption,
                    'parse_mode' => 'Markdown',
                ]);
            } else {
                Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $caption,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => false,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("Telegram notification failed for '{$app->name}': {$e->getMessage()}");
        }
    }

    /**
     * Send notification to Discord channel via Webhook
     */
    public function notifyDiscord(Application $app, bool $isUpdate = false): void
    {
        $webhookUrl = Setting::get('discord_webhook_url');

        if (empty($webhookUrl)) {
            return;
        }

        try {
            $action = $isUpdate ? '🔄 App Actualizada en HackMac' : '🚀 Nuevo Lanzamiento en HackMac';
            $appUrl = route('app', $app->slug);
            $type = $app->has_torrent && $app->download_url_external ? 'Dual (Torrent + DDL)' : ($app->has_torrent ? 'Torrent P2P' : 'DDL Directo');

            $payload = [
                'embeds' => [
                    [
                        'title' => "{$app->name} v{$app->version}",
                        'url' => $appUrl,
                        'description' => $app->short_description ?: 'Descarga verificada para macOS (Apple Silicon & Intel).',
                        'color' => $isUpdate ? 0x0071E3 : 0x30D158,
                        'thumbnail' => [
                            'url' => $app->icon_url,
                        ],
                        'fields' => [
                            [
                                'name' => 'Categoría',
                                'value' => $app->category?->name ?? 'General',
                                'inline' => true,
                            ],
                            [
                                'name' => 'Tamaño',
                                'value' => $app->size ?: 'Universal',
                                'inline' => true,
                            ],
                            [
                                'name' => 'Tipo',
                                'value' => $type,
                                'inline' => true,
                            ],
                        ],
                        'footer' => [
                            'text' => $action,
                        ],
                        'timestamp' => now()->toIso8601String(),
                    ],
                ],
            ];

            Http::timeout(10)->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            Log::warning("Discord webhook notification failed for '{$app->name}': {$e->getMessage()}");
        }
    }

    /**
     * Test Telegram connection with custom or stored credentials
     */
    public function testTelegram(?string $botToken = null, ?string $chatId = null): array
    {
        $botToken = $botToken ?: Setting::get('telegram_bot_token');
        $chatId = $chatId ?: Setting::get('telegram_channel_id');

        if (empty($botToken)) {
            return ['success' => false, 'message' => 'El Token del Bot de Telegram está vacío.'];
        }

        if (empty($chatId)) {
            return ['success' => false, 'message' => 'El Chat ID o @Canal de Telegram está vacío.'];
        }

        try {
            $text = "🤖 *¡Prueba de Notificación Exitosa!*\n\n"
                ."Tu canal o grupo está correctamente vinculado con *HackMac.cc*.\n"
                .'A partir de ahora recibirás alertas automáticas de lanzamientos, actualizaciones y reportes de enlaces.';

            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => '¡Mensaje de prueba enviado con éxito a Telegram!'];
            }

            $body = $response->json();
            $errorDesc = $body['description'] ?? 'Error desconocido';

            return ['success' => false, 'message' => "Telegram API respondió error: {$errorDesc}"];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => "Excepción al conectar con Telegram: {$e->getMessage()}"];
        }
    }

    /**
     * Test Discord Webhook connection
     */
    public function testDiscord(?string $webhookUrl = null): array
    {
        $webhookUrl = $webhookUrl ?: Setting::get('discord_webhook_url');

        if (empty($webhookUrl)) {
            return ['success' => false, 'message' => 'La URL del Webhook de Discord está vacía.'];
        }

        try {
            $payload = [
                'embeds' => [
                    [
                        'title' => '🤖 ¡Prueba de Notificación Exitosa en Discord!',
                        'description' => 'Tu servidor de Discord está correctamente vinculado con **HackMac.cc**. Las alertas automáticas llegarán a este canal.',
                        'color' => 0x5865F2, // Discord Blurple
                        'fields' => [
                            [
                                'name' => 'Estado',
                                'value' => 'Conexión Activa y Verificada',
                                'inline' => true,
                            ],
                            [
                                'name' => 'Fecha',
                                'value' => now()->format('Y-m-d H:i:s'),
                                'inline' => true,
                            ],
                        ],
                        'footer' => [
                            'text' => 'HackMac Notification System',
                        ],
                        'timestamp' => now()->toIso8601String(),
                    ],
                ],
            ];

            $response = Http::timeout(10)->post($webhookUrl, $payload);

            if ($response->successful()) {
                return ['success' => true, 'message' => '¡Mensaje de prueba enviado con éxito a Discord!'];
            }

            return ['success' => false, 'message' => "Discord Webhook devolvió HTTP {$response->status()}"];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => "Excepción al conectar con Discord: {$e->getMessage()}"];
        }
    }
}
