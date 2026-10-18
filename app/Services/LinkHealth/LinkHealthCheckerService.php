<?php

namespace App\Services\LinkHealth;

use App\Models\Application;
use Illuminate\Support\Facades\Http;

class LinkHealthCheckerService
{
    /**
     * Check if a single download URL or magnet link is alive and reachable
     *
     * @return array{alive: bool, status: ?int, reason: string, url: string}
     */
    public function checkUrl(string $url): array
    {
        $url = trim($url);

        if (empty($url)) {
            return [
                'alive' => false,
                'status' => null,
                'reason' => 'Enlace vacío',
                'url' => $url,
            ];
        }

        // Magnet link verification
        if (str_starts_with(strtolower($url), 'magnet:?')) {
            if (preg_match('/xt=urn:btih:[a-f0-9]{32,40}/i', $url) || preg_match('/xt=urn:btmh:/i', $url)) {
                return [
                    'alive' => true,
                    'status' => 200,
                    'reason' => 'Enlace Magnet BTIH válido',
                    'url' => $url,
                ];
            }

            return [
                'alive' => false,
                'status' => 400,
                'reason' => 'Magnet link sin hash BTIH válido',
                'url' => $url,
            ];
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'alive' => false,
                'status' => null,
                'reason' => 'Formato de URL inválido',
                'url' => $url,
            ];
        }

        try {
            // First attempt: lightweight HEAD request
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                    'Accept' => '*/*',
                ])
                ->timeout(6)
                ->head($url);

            $status = $response->status();

            // If HEAD was rejected (e.g. 405 Method Not Allowed or 403 by WAF), try Range GET
            if (in_array($status, [403, 405, 501])) {
                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                        'Range' => 'bytes=0-100',
                        'Accept' => '*/*',
                    ])
                    ->timeout(6)
                    ->get($url);

                $status = $response->status();
            }

            // Inspect redirects or destination signatures for common dead file pages
            $effectiveUrl = (string) $response->effectiveUri();
            if ($this->isKnownDeadHostRedirect($effectiveUrl, $response->body())) {
                return [
                    'alive' => false,
                    'status' => $status,
                    'reason' => 'Archivo eliminado o expirado en el servidor',
                    'url' => $url,
                ];
            }

            if ($response->successful() || in_array($status, [200, 206, 301, 302, 307, 308])) {
                return [
                    'alive' => true,
                    'status' => $status,
                    'reason' => 'Servidor activo (HTTP '.$status.')',
                    'url' => $url,
                ];
            }

            if (in_array($status, [404, 410])) {
                return [
                    'alive' => false,
                    'status' => $status,
                    'reason' => 'Archivo no encontrado (HTTP '.$status.')',
                    'url' => $url,
                ];
            }

            if ($status >= 500) {
                return [
                    'alive' => false,
                    'status' => $status,
                    'reason' => 'Servidor de descarga caído (HTTP '.$status.')',
                    'url' => $url,
                ];
            }

            return [
                'alive' => false,
                'status' => $status,
                'reason' => 'Respuesta no válida del servidor (HTTP '.$status.')',
                'url' => $url,
            ];
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $reason = 'Tiempo de espera agotado o conexión rechazada';

            if (str_contains(strtolower($msg), 'timed out')) {
                $reason = 'Timeout: el servidor no responde';
            } elseif (str_contains(strtolower($msg), 'could not resolve host')) {
                $reason = 'DNS caído o dominio no existe';
            }

            return [
                'alive' => false,
                'status' => null,
                'reason' => $reason,
                'url' => $url,
            ];
        }
    }

    /**
     * Check all download links and mirrors configured on an Application
     *
     * @return array{
     *     application_id: int,
     *     application_name: string,
     *     has_working_link: bool,
     *     checked_count: int,
     *     dead_count: int,
     *     results: array<int, array{type: string, name: string, url: string, alive: bool, status: ?int, reason: string}>
     * }
     */
    public function checkApplication(Application $application): array
    {
        $results = [];

        // 1. Primary Download URL
        if (! empty($application->download_url)) {
            $check = $this->checkUrl($application->download_url);
            $results[] = array_merge($check, [
                'type' => 'primary',
                'name' => 'Enlace Principal',
            ]);
        }

        // 2. External Download URL
        if (! empty($application->download_url_external)) {
            $check = $this->checkUrl($application->download_url_external);
            $results[] = array_merge($check, [
                'type' => 'external',
                'name' => 'Enlace Externo',
            ]);
        }

        // 3. Custom Download Mirrors
        if (! empty($application->download_mirrors) && is_array($application->download_mirrors)) {
            foreach ($application->download_mirrors as $idx => $mirror) {
                $mUrl = $mirror['url'] ?? '';
                $mName = $mirror['name'] ?? ('Servidor Alternativo #'.($idx + 1));
                if (! empty($mUrl)) {
                    $check = $this->checkUrl($mUrl);
                    $results[] = array_merge($check, [
                        'type' => 'mirror',
                        'name' => $mName,
                    ]);
                }
            }
        }

        // 4. Torrent File URL
        if (! empty($application->torrent_url)) {
            $check = $this->checkUrl($application->torrent_url);
            $results[] = array_merge($check, [
                'type' => 'torrent',
                'name' => 'Archivo Torrent',
            ]);
        }

        // 5. Magnet Link
        if (! empty($application->magnet_link)) {
            $check = $this->checkUrl($application->magnet_link);
            $results[] = array_merge($check, [
                'type' => 'magnet',
                'name' => 'Magnet Link',
            ]);
        }

        $deadCount = count(array_filter($results, fn ($r) => ! $r['alive']));
        $hasWorkingLink = count(array_filter($results, fn ($r) => $r['alive'])) > 0;

        return [
            'application_id' => $application->id,
            'application_name' => $application->name,
            'has_working_link' => $hasWorkingLink,
            'checked_count' => count($results),
            'dead_count' => $deadCount,
            'results' => $results,
        ];
    }

    /**
     * Check if redirected URL or response body matches known dead file host patterns
     */
    protected function isKnownDeadHostRedirect(string $url, string $body = ''): bool
    {
        $urlLower = strtolower($url);

        // Mediafire error
        if (str_contains($urlLower, 'error.php?errno=320') || str_contains($urlLower, 'mediafire.com/error')) {
            return true;
        }

        // Mega.nz dead patterns
        if (str_contains($urlLower, 'mega.nz/#!') && str_contains($urlLower, 'expired')) {
            return true;
        }

        // Qiwi / Usersdrive / Uploading removed page text
        if (! empty($body)) {
            $bodyLower = strtolower(substr($body, 0, 1500));
            if (
                str_contains($bodyLower, 'file has been deleted') ||
                str_contains($bodyLower, 'file was deleted') ||
                str_contains($bodyLower, 'file not found') ||
                str_contains($bodyLower, 'the file you were looking for could not be found')
            ) {
                return true;
            }
        }

        return false;
    }
}
