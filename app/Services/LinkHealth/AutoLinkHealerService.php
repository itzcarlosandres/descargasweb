<?php

namespace App\Services\LinkHealth;

use App\Models\Application;
use App\Models\BrokenLinkReport;
use App\Services\Scraper\HaxmacClient;
use App\Services\Scraper\HaxmacParser;
use App\Services\Scraper\TorrentmacClient;
use App\Services\Scraper\TorrentmacParser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoLinkHealerService
{
    public function __construct(
        protected TorrentmacClient $torrentmacClient,
        protected TorrentmacParser $torrentmacParser,
        protected HaxmacClient $haxmacClient,
        protected HaxmacParser $haxmacParser,
        protected LinkHealthCheckerService $healthChecker
    ) {}

    /**
     * Search both Torrentmac and Haxmac for live link candidates
     *
     * @return array<int, array{
     *     source: string,
     *     title: string,
     *     clean_name: string,
     *     version: ?string,
     *     url: string,
     *     torrent_url: ?string,
     *     magnet_link: ?string,
     *     download_url: ?string,
     *     mirrors: array,
     *     score: int,
     *     is_exact_version: bool
     * }>
     */
    public function searchCandidates(string $appName, ?string $targetVersion = null): array
    {
        $candidates = [];

        // Clean app name for optimal search query (e.g. remove "macOS", "for Mac")
        $cleanQuery = trim(preg_replace('/\b(macos|mac|apple silicon|m1|m2|m3|universal)\b/i', '', $appName));

        // 1. Search Torrentmac
        try {
            $tHtml = $this->torrentmacClient->searchHtml($cleanQuery);
            if (! empty($tHtml)) {
                $tCards = $this->torrentmacParser->parseAppCards($tHtml);

                // Check top 3 cards
                foreach (array_slice($tCards, 0, 3) as $card) {
                    $detailHtml = $this->torrentmacClient->fetchHtml($card['url']);
                    if (! empty($detailHtml)) {
                        $detail = $this->torrentmacParser->parseAppDetail($detailHtml, $card['url']);
                        if ($detail && (! empty($detail['torrent_url']) || ! empty($detail['magnet_link']))) {
                            $score = $this->calculateMatchScore($appName, $targetVersion, $card['clean_name'], $detail['version']);
                            $candidates[] = [
                                'source' => 'Torrentmac',
                                'title' => $card['name'],
                                'clean_name' => $card['clean_name'],
                                'version' => $detail['version'],
                                'url' => $card['url'],
                                'torrent_url' => $detail['torrent_url'],
                                'magnet_link' => $detail['magnet_link'],
                                'download_url' => null,
                                'mirrors' => [],
                                'score' => $score,
                                'is_exact_version' => $targetVersion && $detail['version'] && version_compare($targetVersion, $detail['version'], '=='),
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("AutoLinkHealer: Torrentmac search error for {$appName}: {$e->getMessage()}");
        }

        // 2. Search Haxmac
        try {
            $hHtml = $this->haxmacClient->searchHtml($cleanQuery);
            if (! empty($hHtml)) {
                $hCards = $this->haxmacParser->parseAppCards($hHtml);

                // Check top 3 cards
                foreach (array_slice($hCards, 0, 3) as $card) {
                    $detailHtml = $this->haxmacClient->fetchHtml($card['url']);
                    if (! empty($detailHtml)) {
                        $detail = $this->haxmacParser->parseAppDetail($detailHtml, $card['url']);
                        $mirrors = [];

                        if (! empty($detail['download_page_url'])) {
                            $dlHtml = $this->haxmacClient->fetchHtml($detail['download_page_url']);
                            if (! empty($dlHtml)) {
                                $parsedDl = $this->haxmacParser->parseDownloadMirrors($dlHtml);
                                foreach ($parsedDl['mirrors'] as $m) {
                                    $mirrors[] = [
                                        'name' => $m['host'],
                                        'url' => $m['url'],
                                    ];
                                }
                            }
                        }

                        if (! empty($mirrors)) {
                            $score = $this->calculateMatchScore($appName, $targetVersion, $card['name'], $detail['version']);
                            $candidates[] = [
                                'source' => 'Haxmac',
                                'title' => $card['name'],
                                'clean_name' => $card['name'],
                                'version' => $detail['version'],
                                'url' => $card['url'],
                                'torrent_url' => null,
                                'magnet_link' => null,
                                'download_url' => $mirrors[0]['url'] ?? null,
                                'mirrors' => $mirrors,
                                'score' => $score,
                                'is_exact_version' => $targetVersion && $detail['version'] && version_compare($targetVersion, $detail['version'], '=='),
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("AutoLinkHealer: Haxmac search error for {$appName}: {$e->getMessage()}");
        }

        // Sort candidates by score descending
        usort($candidates, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $candidates;
    }

    /**
     * Automatically attempt to heal an application's links
     */
    public function healApplication(Application $application, ?BrokenLinkReport $report = null): array
    {
        $candidates = $this->searchCandidates($application->name, $application->version);

        if (empty($candidates)) {
            return [
                'success' => false,
                'message' => 'No se encontraron resultados en Haxmac ni Torrentmac para «'.$application->name.'».',
                'candidates' => [],
            ];
        }

        // Pick best candidate with score >= 70
        $bestCandidate = null;
        foreach ($candidates as $cand) {
            if ($cand['score'] >= 70) {
                $bestCandidate = $cand;
                break;
            }
        }

        if (! $bestCandidate) {
            return [
                'success' => false,
                'message' => 'Se encontraron coincidencias pero con baja confianza. Se requiere selección manual.',
                'candidates' => $candidates,
            ];
        }

        $applied = $this->applyCandidate($application, $bestCandidate, $report);

        return [
            'success' => $applied,
            'message' => '¡Enlace reparado exitosamente desde '.$bestCandidate['source'].' (v'.($bestCandidate['version'] ?? 'N/A').')!',
            'candidate' => $bestCandidate,
            'all_candidates' => $candidates,
        ];
    }

    /**
     * Apply chosen candidate links to the application
     */
    public function applyCandidate(Application $application, array $candidate, ?BrokenLinkReport $report = null): bool
    {
        $updates = [];

        // 1. Torrent & Magnet
        if (! empty($candidate['magnet_link'])) {
            $updates['magnet_link'] = $candidate['magnet_link'];
            $updates['has_torrent'] = true;
        }

        if (! empty($candidate['torrent_url'])) {
            $updates['torrent_url'] = $candidate['torrent_url'];
            $updates['has_torrent'] = true;
        }

        // 2. Direct Mirrors & Download URL
        if (! empty($candidate['mirrors'])) {
            $updates['download_mirrors'] = $candidate['mirrors'];
            if (empty($application->download_url) && ! empty($candidate['mirrors'][0]['url'])) {
                $updates['download_url'] = $candidate['mirrors'][0]['url'];
            }
        } elseif (! empty($candidate['download_url'])) {
            $updates['download_url'] = $candidate['download_url'];
        }

        // 3. Update version if candidate is a newer version
        if (! empty($candidate['version'])) {
            $updates['version'] = $candidate['version'];
        }

        $application->update($updates);

        // Also sync current version record if available
        $currentVersion = $application->currentVersion;
        if ($currentVersion) {
            $currentVersion->update([
                'version' => $candidate['version'] ?? $currentVersion->version,
                'download_url' => $updates['download_url'] ?? $currentVersion->download_url,
                'download_mirrors' => $updates['download_mirrors'] ?? $currentVersion->download_mirrors,
            ]);
        }

        // Resolve BrokenLinkReport if supplied
        if ($report) {
            $sourceInfo = ($candidate['source'] ?? 'Auto-Healer').' (v'.($candidate['version'] ?? 'N/A').')';
            $report->update([
                'status' => 'resolved',
                'notes' => trim(($report->notes ? $report->notes."\n" : '').'✓ Auto-reparado el '.now()->format('d/m/Y H:i').' vía '.$sourceInfo),
            ]);
        }

        return true;
    }

    /**
     * Calculate similarity score between requested application and scraped candidate (0-100)
     */
    protected function calculateMatchScore(string $targetName, ?string $targetVersion, string $scrapedName, ?string $scrapedVersion): int
    {
        $normTarget = Str::slug($targetName);
        $normScraped = Str::slug($scrapedName);

        // 1. Text similarity
        similar_text($normTarget, $normScraped, $percent);
        $score = (int) $percent;

        // If one contains the other entirely, give high baseline
        if (str_contains($normScraped, $normTarget) || str_contains($normTarget, $normScraped)) {
            $score = max($score, 80);
        }

        // 2. Version comparison bonus/penalty
        if (! empty($targetVersion) && ! empty($scrapedVersion)) {
            $cleanTargetVer = preg_replace('/[^0-9.]/', '', $targetVersion);
            $cleanScrapedVer = preg_replace('/[^0-9.]/', '', $scrapedVersion);

            if ($cleanTargetVer === $cleanScrapedVer) {
                $score += 15;
            } elseif (version_compare($cleanScrapedVer, $cleanTargetVer, '>')) {
                // Scraped version is newer, which is great for software updates
                $score += 10;
            }
        }

        return min(100, max(0, $score));
    }
}
