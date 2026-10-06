<?php

namespace App\Services\AI;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiService
{
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Get configured API key from settings.
     */
    public function getApiKey(): ?string
    {
        $key = Setting::get('gemini_api_key');

        return ! empty($key) ? trim($key) : null;
    }

    /**
     * Get configured model.
     */
    public function getModel(): string
    {
        return Setting::get('gemini_model', 'gemini-2.5-flash') ?: 'gemini-2.5-flash';
    }

    /**
     * Check if AI generation is enabled and configured.
     */
    public function isEnabled(): bool
    {
        $enabled = Setting::get('gemini_auto_generate', '1');

        return in_array($enabled, [true, 1, '1', 'true'], true) && ! empty($this->getApiKey());
    }

    /**
     * Test Gemini API connection with a minimal prompt.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(?string $apiKey = null, ?string $model = null): array
    {
        $key = $apiKey ? trim($apiKey) : $this->getApiKey();
        $selectedModel = $model ? trim($model) : $this->getModel();

        if (empty($key)) {
            return [
                'success' => false,
                'message' => 'API Key no proporcionada.',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->post("{$this->baseUrl}/{$selectedModel}:generateContent?key={$key}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Respond with the exact word "CONNECTED" if you can read this.'],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

                return [
                    'success' => true,
                    'message' => '¡Conexión exitosa con Gemini ('.htmlspecialchars($selectedModel).')! Respuesta: '.trim($text),
                ];
            }

            $errorMsg = $response->json('error.message') ?? 'HTTP '.$response->status().' '.$response->reason();

            return [
                'success' => false,
                'message' => 'Error de conexión: '.$errorMsg,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Excepción de red: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Generate concise English description (~4 lines / 50-60 words with internal SEO category link) and 7 features.
     *
     * @return array{description: string, features: string}|null
     */
    public function generateAppContent(
        string $name,
        ?string $category = null,
        ?string $version = null,
        ?string $shortDesc = null,
        ?string $scrapedDesc = null,
        ?string $categorySlug = null
    ): ?array {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            Log::warning("GeminiService: Cannot generate content for '{$name}', API key is missing.");

            return null;
        }

        $model = $this->getModel();
        $targetWords = (int) Setting::get('gemini_target_words', 60);
        if ($targetWords < 30 || $targetWords > 300) {
            $targetWords = 60;
        }

        $featuresCount = (int) Setting::get('gemini_features_count', 7);
        if ($featuresCount < 3 || $featuresCount > 15) {
            $featuresCount = 7;
        }

        $categoryName = $category ?: 'Utilities';
        $categorySlug = $categorySlug ?: Str::slug($categoryName);
        $categoryUrl = "/category/{$categorySlug}";

        // Clean scraped description from HTML tags for reference
        $cleanScraped = ! empty($scrapedDesc) ? trim(strip_tags($scrapedDesc)) : '';
        if (strlen($cleanScraped) > 1000) {
            $cleanScraped = substr($cleanScraped, 0, 1000).'...';
        }

        $systemPrompt = <<<'SYS'
You are an expert macOS software editor and copywriter.
Your goal is to write ultra-clean, concise, and engaging software summaries in professional English for Mac users.
Always output valid JSON without markdown fences, containing strictly two keys: "description" and "features".
SYS;

        $userPrompt = <<<PROMPT
Generate concise, SEO-optimized editorial content in English for this macOS software:
- Application Name: {$name}
- Category: {$categoryName}
- Category URL: {$categoryUrl}
- Version: {$version}
- Context: {$shortDesc}
- Scraped Reference: {$cleanScraped}

STRICT REQUIREMENTS:
1. "description":
   - Write a VERY CONCISE, IMPACTFUL summary that takes EXACTLY ~4 lines of text (between 45 and 65 words total).
   - Format: EXACTLY ONE single HTML paragraph (<p>...</p>). Never output multiple paragraphs.
   - INTERNAL SEO LINKING (MANDATORY): You MUST naturally embed an internal hyperlink linking to its category: <a href="{$categoryUrl}">macOS {$categoryName}</a> or similar natural anchor text (e.g. "...stands out as an essential tool in <a href="{$categoryUrl}">{$categoryName}</a> for macOS...").
   - Highlight what the software is, primary benefit, and Apple Silicon readiness in 2 to 3 fluid sentences. Avoid long filler sentences or generic history.

2. "features":
   - Provide an array of EXACTLY {$featuresCount} objects.
   - Each object must have two string keys:
     - "title": A concise feature name (3-5 words).
     - "description": A punchy sentence describing the capability (12-20 words).

JSON OUTPUT FORMAT:
{
  "description": "<p>A concise ~4 lines paragraph with an internal link to <a href=\"{$categoryUrl}\">macOS {$categoryName}</a>...</p>",
  "features": [
    {"title": "Title 1", "description": "Description 1"},
    ... {$featuresCount} items total
  ]
}
PROMPT;

        try {
            $response = Http::withoutVerifying()
                ->timeout(45)
                ->retry(2, 600)
                ->post("{$this->baseUrl}/{$model}:generateContent?key={$apiKey}", [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemPrompt],
                        ],
                    ],
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $userPrompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json',
                        'temperature' => 0.6,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error("GeminiService API error for '{$name}': HTTP {$response->status()} - {$response->body()}");

                return null;
            }

            $result = $response->json();
            $rawJson = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (empty($rawJson)) {
                Log::warning("GeminiService: Empty content received for '{$name}'.");

                return null;
            }

            // Remove any accidental markdown backticks if present
            $rawJson = trim($rawJson);
            if (str_starts_with($rawJson, '```json')) {
                $rawJson = substr($rawJson, 7);
            }
            if (str_starts_with($rawJson, '```')) {
                $rawJson = substr($rawJson, 3);
            }
            if (str_ends_with($rawJson, '```')) {
                $rawJson = substr($rawJson, 0, -3);
            }
            $rawJson = trim($rawJson);

            $parsed = json_decode($rawJson, true);
            if (! is_array($parsed) || empty($parsed['description'])) {
                Log::warning("GeminiService: Failed to parse JSON response for '{$name}'. Raw: {$rawJson}");

                return null;
            }

            // Format description: Keep strictly 1 paragraph (~4 lines)
            $descriptionHtml = trim($parsed['description']);

            // If Gemini returned multiple paragraphs, take only the first one to guarantee ~4 lines
            if (preg_match('/<p>(.*?)<\/p>/is', $descriptionHtml, $pMatch)) {
                $descriptionHtml = '<p>'.trim($pMatch[1]).'</p>';
            } elseif (! str_contains($descriptionHtml, '<p>')) {
                $firstPart = explode("\n\n", $descriptionHtml)[0];
                $descriptionHtml = '<p>'.trim($firstPart).'</p>';
            }

            // Guarantee internal SEO category link
            if (! str_contains($descriptionHtml, 'href="/category/') && ! str_contains($descriptionHtml, "href='{$categoryUrl}'") && ! str_contains($descriptionHtml, "href=\"{$categoryUrl}\"")) {
                // Try to find the category name in text to wrap it as a link
                $pattern = '/\b('.preg_quote($categoryName, '/').')\b/i';
                if (preg_match($pattern, $descriptionHtml)) {
                    $descriptionHtml = preg_replace($pattern, '<a href="'.$categoryUrl.'">$1</a>', $descriptionHtml, 1);
                } else {
                    // Append natural category anchor before closing </p>
                    $descriptionHtml = str_replace('</p>', ' Discover more top-tier options in <a href="'.$categoryUrl.'">macOS '.$categoryName.'</a>.</p>', $descriptionHtml);
                }
            }

            // Format features list HTML (strictly 7 items with glowing squircle bullet support)
            $featuresListHtml = '';
            if (! empty($parsed['features']) && is_array($parsed['features'])) {
                $featuresListHtml = '<ul>';
                $count = 0;
                foreach ($parsed['features'] as $item) {
                    if ($count >= $featuresCount) {
                        break;
                    }
                    if (is_array($item) && ! empty($item['title'])) {
                        $title = htmlspecialchars(trim($item['title']));
                        $desc = htmlspecialchars(trim($item['description'] ?? ''));
                        $featuresListHtml .= "<li><strong>{$title}:</strong> {$desc}</li>";
                        $count++;
                    } elseif (is_string($item)) {
                        $featuresListHtml .= '<li>'.htmlspecialchars(trim($item)).'</li>';
                        $count++;
                    }
                }
                $featuresListHtml .= '</ul>';
            }

            Log::info("GeminiService: Successfully generated concise 4-line description with category SEO link and {$featuresCount} features for '{$name}'.");

            return [
                'description' => $descriptionHtml,
                'features' => $featuresListHtml,
            ];
        } catch (\Throwable $e) {
            Log::error("GeminiService Exception for '{$name}': {$e->getMessage()}");

            return null;
        }
    }
}
