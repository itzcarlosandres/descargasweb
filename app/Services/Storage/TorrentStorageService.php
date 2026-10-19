<?php

namespace App\Services\Storage;

use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TorrentStorageService
{
    public function __construct(
        protected CloudflareR2Service $r2Service
    ) {}

    /**
     * Generate a clean, branded filename eliminating third-party domains (e.g. torrentmac.net)
     * and replacing them with HackMac.cc
     */
    public function generateBrandedFilename(string $appName, ?string $version = null, ?string $originalUrl = null): string
    {
        $brand = 'HackMac.cc';

        // 1. If original URL exists, attempt to sanitize its basename
        if ($originalUrl) {
            $parsedPath = parse_url($originalUrl, PHP_URL_PATH);
            if ($parsedPath) {
                $basename = urldecode(basename($parsedPath));

                // Strip .torrent extension
                $clean = preg_replace('/\.torrent$/i', '', $basename);

                // Strip trailing .zip if present (e.g. .zip.torrent)
                $clean = preg_replace('/\.zip$/i', '', $clean);

                // Strip third party branding keywords and enclosing brackets
                $clean = preg_replace('/\[[^\]]*(torrentmac|macbb|cmacapps)[^\]]*\]/i', '', $clean);
                $clean = preg_replace('/\([^\)]*(torrentmac|macbb|cmacapps)[^\)]*\)/i', '', $clean);
                $clean = preg_replace('/[_\.\-\s]*(torrentmac(\.net)?|macbb(\.org)?|cmacapps(\.com)?)[_\.\-\s]*/i', '_', $clean);

                // Strip empty brackets/parentheses
                $clean = preg_replace('/[\[\]\(\)\{\}]+/', '', $clean);

                // Strip any existing hackmac branding to re-apply cleanly
                $clean = preg_replace('/[_\.\-\s]*hackmac(\.cc)?[_\.\-\s]*/i', '_', $clean);

                // Strip any trailing _zip or .zip
                $clean = preg_replace('/[_\.\-\s]zip$/i', '', $clean);

                // Remove repeated delimiters
                $clean = trim(preg_replace('/[_\s]+/', '_', $clean), '_');

                if (! empty($clean) && strlen($clean) > 3) {
                    // Ensure macOS is mentioned if not present
                    if (! preg_match('/macos/i', $clean)) {
                        $clean .= '_macOS';
                    }

                    return "{$clean}_{$brand}.torrent";
                }
            }
        }

        // 2. Fallback: Clean generation from app name & version
        $cleanName = preg_replace('/[^\w\.\-]+/u', '_', trim($appName));
        $cleanName = trim(preg_replace('/_+/', '_', $cleanName), '_');

        $cleanVersion = $version ? trim(preg_replace('/[^\w\.\-]+/u', '_', $version), '_') : null;

        $parts = [$cleanName];
        if ($cleanVersion && ! str_contains($cleanName, $cleanVersion)) {
            $parts[] = $cleanVersion;
        }

        if (! preg_match('/macos/i', $cleanName)) {
            $parts[] = 'macOS';
        }

        $parts[] = $brand;

        return implode('_', $parts).'.torrent';
    }

    /**
     * Download binary content from a remote URL with realistic browser headers
     */
    public function downloadRemoteBinary(string $url): ?string
    {
        if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => '*/*',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer' => parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).'/',
            ])
                ->timeout(30)
                ->get($url);

            if ($response->successful()) {
                $body = $response->body();
                if (strlen($body) > 10) {
                    return $body;
                }
            } else {
                Log::warning("TorrentStorageService: Failed to download remote torrent [HTTP {$response->status()}]: {$url}");
            }
        } catch (\Throwable $e) {
            Log::error("TorrentStorageService: Exception downloading torrent from {$url}: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Store torrent binary to Cloudflare R2 (if configured/selected) or local public disk
     *
     * @return array{storage_driver: string, file_path: string, public_url: string, filename: string, magnet_link: ?string}
     */
    public function storeTorrentBinary(string $binary, string $filename, string $slug, ?string $displayName = null): array
    {
        $relativePath = "torrents/{$slug}/{$filename}";
        $magnetLink = $this->extractMagnetFromTorrent($binary, $displayName ?: $slug);

        $preferredDisk = Setting::get('torrentmac_storage_disk', 'local');
        $useR2 = ($preferredDisk === 'r2' || $this->r2Service->isConfigured());

        if ($useR2) {
            $r2PublicUrl = $this->r2Service->putObject($relativePath, $binary, 'application/x-bittorrent');
            if ($r2PublicUrl) {
                return [
                    'storage_driver' => 'r2',
                    'file_path' => $relativePath,
                    'public_url' => $r2PublicUrl,
                    'filename' => $filename,
                    'magnet_link' => $magnetLink,
                ];
            }
            Log::warning("TorrentStorageService: R2 upload failed for {$relativePath}, falling back to local public disk.");
        }

        // Store to public disk
        Storage::disk('public')->put($relativePath, $binary, 'public');
        $publicUrl = Storage::disk('public')->url($relativePath);

        return [
            'storage_driver' => 'public',
            'file_path' => $relativePath,
            'public_url' => $publicUrl,
            'filename' => $filename,
            'magnet_link' => $magnetLink,
        ];
    }

    /**
     * Localize and brand a torrent for an application (or specific version)
     *
     * @return array{success: bool, error?: string, file_path?: string, public_url?: string, filename?: string, magnet_link?: ?string, binary?: string}
     */
    public function localizeApplicationTorrent(Application $application, ?ApplicationVersion $version = null, bool $force = false): array
    {
        $target = $version ?? $application;
        $appName = $application->name;
        $verString = $version ? $version->version : $application->version;
        $remoteUrl = $target->torrent_url;
        $existingPath = $target->torrent_file_path;

        $brandedFilename = $this->generateBrandedFilename($appName, $verString, $remoteUrl);
        $expectedRelativePath = "torrents/{$application->slug}/{$brandedFilename}";

        // If file already exists and not forced, verify if it already has HackMac branding
        if (! $force && ! empty($existingPath)) {
            $alreadyBranded = str_contains($existingPath, 'HackMac.cc');

            if ($alreadyBranded && Storage::disk('public')->exists($existingPath)) {
                return [
                    'success' => true,
                    'file_path' => $existingPath,
                    'public_url' => Storage::disk('public')->url($existingPath),
                    'filename' => basename($existingPath),
                    'magnet_link' => $target->magnet_link,
                ];
            }

            if ($alreadyBranded && $this->r2Service->isConfigured() && $this->r2Service->objectExists($existingPath)) {
                $creds = $this->r2Service->getCredentials();

                return [
                    'success' => true,
                    'file_path' => $existingPath,
                    'public_url' => $this->r2Service->buildPublicUrl($existingPath, $creds),
                    'filename' => basename($existingPath),
                    'magnet_link' => $target->magnet_link,
                ];
            }

            // If existing file is present locally but without HackMac branding, rebrand and move it
            if (Storage::disk('public')->exists($existingPath)) {
                $binary = Storage::disk('public')->get($existingPath);
                if (! empty($binary)) {
                    $stored = $this->storeTorrentBinary($binary, $brandedFilename, $application->slug, "{$appName} {$verString}");
                    $target->update([
                        'torrent_file_path' => $stored['file_path'],
                        'torrent_url' => $stored['public_url'],
                    ]);

                    return array_merge(['success' => true, 'binary' => $binary], $stored);
                }
            }

            // If existing file is on R2 without HackMac branding, rebrand and copy it
            if ($this->r2Service->isConfigured() && $this->r2Service->objectExists($existingPath)) {
                $binary = $this->r2Service->getObject($existingPath);
                if (! empty($binary)) {
                    $stored = $this->storeTorrentBinary($binary, $brandedFilename, $application->slug, "{$appName} {$verString}");
                    $target->update([
                        'torrent_file_path' => $stored['file_path'],
                        'torrent_url' => $stored['public_url'],
                    ]);

                    return array_merge(['success' => true, 'binary' => $binary], $stored);
                }
            }
        }

        // If remote URL is missing or already points to our own localized storage
        if (empty($remoteUrl) || str_contains($remoteUrl, 'HackMac.cc') || ! filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
            // Check if existing file is in public storage under expected relative path
            if (Storage::disk('public')->exists($expectedRelativePath)) {
                $target->update([
                    'torrent_file_path' => $expectedRelativePath,
                    'torrent_url' => Storage::disk('public')->url($expectedRelativePath),
                ]);

                return [
                    'success' => true,
                    'file_path' => $expectedRelativePath,
                    'public_url' => Storage::disk('public')->url($expectedRelativePath),
                    'filename' => $brandedFilename,
                    'magnet_link' => $target->magnet_link,
                ];
            }

            return [
                'success' => false,
                'error' => 'No valid external torrent URL found to localize.',
                'fallback_magnet' => $target->magnet_link,
            ];
        }

        // Download the remote .torrent binary
        $binary = $this->downloadRemoteBinary($remoteUrl);
        if (! $binary) {
            return [
                'success' => false,
                'error' => "Remote torrent file could not be fetched from {$remoteUrl}.",
                'fallback_magnet' => $target->magnet_link,
            ];
        }

        // Store to R2 or Local public storage
        $stored = $this->storeTorrentBinary($binary, $brandedFilename, $application->slug, "{$appName} {$verString}");

        // Update database record
        $updatePayload = [
            'torrent_file_path' => $stored['file_path'],
            'torrent_url' => $stored['public_url'],
        ];

        if (! empty($stored['magnet_link']) && empty($target->magnet_link)) {
            $updatePayload['magnet_link'] = $stored['magnet_link'];
        }

        $target->update($updatePayload);

        return array_merge(['success' => true, 'binary' => $binary], $stored);
    }

    /**
     * Extract or calculate BitTorrent Magnet Link from raw .torrent Bencoded binary
     */
    public function extractMagnetFromTorrent(string $torrentContent, ?string $displayName = null): ?string
    {
        $infoPos = strpos($torrentContent, '4:info');
        if ($infoPos === false) {
            return null;
        }

        $dictStart = $infoPos + 6;
        if (! isset($torrentContent[$dictStart]) || $torrentContent[$dictStart] !== 'd') {
            return null;
        }

        $len = strlen($torrentContent);
        $pos = $dictStart;
        $depth = 0;

        while ($pos < $len) {
            $char = $torrentContent[$pos];

            if ($char === 'd' || $char === 'l') {
                $depth++;
                $pos++;
            } elseif ($char === 'e') {
                $depth--;
                $pos++;
                if ($depth === 0) {
                    $infoDict = substr($torrentContent, $dictStart, $pos - $dictStart);
                    $infoHash = sha1($infoDict);

                    $trackers = [
                        'udp://tracker.opentrackr.org:1337/announce',
                        'udp://open.stealth.si:80/announce',
                        'udp://tracker.torrent.eu.org:451/announce',
                        'udp://tracker.bittor.pw:1337/announce',
                    ];

                    $magnet = "magnet:?xt=urn:btih:{$infoHash}";
                    if ($displayName) {
                        $magnet .= '&dn='.rawurlencode($displayName);
                    }
                    foreach ($trackers as $tr) {
                        $magnet .= '&tr='.rawurlencode($tr);
                    }

                    return $magnet;
                }
            } elseif ($char === 'i') {
                $pos++;
                $ePos = strpos($torrentContent, 'e', $pos);
                if ($ePos === false) {
                    return null;
                }
                $pos = $ePos + 1;
            } elseif (ctype_digit($char)) {
                $colonPos = strpos($torrentContent, ':', $pos);
                if ($colonPos === false) {
                    return null;
                }
                $strLen = (int) substr($torrentContent, $pos, $colonPos - $pos);
                $pos = $colonPos + 1 + $strLen;
            } else {
                return null;
            }
        }

        return null;
    }
}
