<?php

namespace App\Services\Scraper\Traits;

use App\Models\Application;
use Illuminate\Support\Str;

trait AppResolverTrait
{
    /**
     * Resolve existing application using a 4-tier smart deduplication engine:
     * 1. Exact slug match
     * 2. Clean slug match (stripping version numbers, e.g. final-cut-pro-10-8 -> final-cut-pro)
     * 3. Normalized lowercase name match
     * 4. Close root name match (stripping trailing versions, tags and edition markers)
     */
    public function resolveExistingApplication(array $appData): ?Application
    {
        // 1. Direct Slug match
        if (! empty($appData['slug'])) {
            $existing = Application::with('images', 'versions')->where('slug', $appData['slug'])->first();
            if ($existing) {
                return $existing;
            }
        }

        // 2. Clean name slug match
        $cleanName = ! empty($appData['clean_name']) ? $appData['clean_name'] : ($appData['name'] ?? null);
        if ($cleanName) {
            $cleanSlug = Str::slug($cleanName);
            if ($cleanSlug) {
                $existing = Application::with('images', 'versions')->where('slug', $cleanSlug)->first();
                if ($existing) {
                    return $existing;
                }
            }

            // 3. Normalized Name match (case-insensitive)
            $existing = Application::with('images', 'versions')
                ->whereRaw('LOWER(name) = ?', [strtolower(trim($cleanName))])
                ->first();
            if ($existing) {
                return $existing;
            }

            // 4. Raw name without version numbers
            if (! empty($appData['name'])) {
                $rawClean = preg_replace('/\b(?:v|ver\.?|version)?\s*\d+(\.\d+)*(?:\s*(?:b|beta|rc|build)\d*)?\b/i', '', $appData['name']);
                $rawClean = trim(preg_replace('/\s+/', ' ', $rawClean));
                if ($rawClean && $rawClean !== $cleanName) {
                    $existing = Application::with('images', 'versions')
                        ->where('slug', Str::slug($rawClean))
                        ->orWhereRaw('LOWER(name) = ?', [strtolower($rawClean)])
                        ->first();
                    if ($existing) {
                        return $existing;
                    }
                }
            }
        }

        return null;
    }
}
