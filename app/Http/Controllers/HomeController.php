<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\BrokenLinkReport;
use App\Models\Category;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\SearchLog;
use App\Services\Notification\NotificationDispatcher;
use App\Services\Storage\TorrentStorageService;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __construct(
        protected TorrentStorageService $torrentStorage
    ) {}

    public function index()
    {
        $stats = Cache::remember('home_stats', 3600, function () {
            return [
                'total_apps' => Application::published()->count(),
                'total_downloads' => Application::published()->sum('downloads'),
                'updated_this_week' => Application::published()->where('updated_at', '>=', now()->subWeek())->count(),
            ];
        });

        // Consultas directas e instantáneas (1-2 ms) sin serializar modelos en caché
        $featured = Application::published()
            ->featured()
            ->with('category')
            ->latest('updated_at')
            ->limit(4)
            ->get();

        if ($featured->isEmpty()) {
            $featured = Application::published()
                ->with('category')
                ->latest('updated_at')
                ->limit(4)
                ->get();
        }

        $applications = Application::published()
            ->with('category')
            ->recentlyAdded()
            ->paginate(12);

        $recentlyUpdated = Application::published()
            ->recentlyUpdated()
            ->with('category')
            ->limit(6)
            ->get();

        $topCategories = Cache::remember('home_top_categories', 1800, function () {
            return Category::active()
                ->ordered()
                ->limit(7)
                ->get();
        });

        if (! ($topCategories instanceof Collection)) {
            Cache::forget('home_top_categories');
            $topCategories = Category::active()
                ->ordered()
                ->limit(7)
                ->get();
        }

        return view('pages.home', compact('stats', 'featured', 'applications', 'recentlyUpdated', 'topCategories'));
    }

    public function categories()
    {
        $categories = Category::active()->ordered()->withCount('publishedApplications')->get();

        return view('pages.categories', compact('categories'));
    }

    public function category(Category $category)
    {
        $applications = Application::published()
            ->where('category_id', $category->id)
            ->orderBy('downloads', 'desc')
            ->paginate(12);

        return view('pages.category', compact('category', 'applications'));
    }

    public function app(Application $application)
    {
        if (! $application->published) {
            abort(404);
        }

        $sortComments = request('sort_comments', 'top');

        $application->load([
            'category',
            'tags',
            'versions',
            'images',
            'approvedReviews' => function ($q) {
                $q->latest();
            },
            'rootApprovedReviews' => function ($q) use ($sortComments) {
                if ($sortComments === 'new') {
                    $q->latest();
                } elseif ($sortComments === 'trending') {
                    $q->orderByRaw('(upvotes - downvotes) DESC')->latest();
                } else {
                    $q->orderBy('upvotes', 'desc')->latest();
                }
                $q->with(['approvedReplies' => function ($r) {
                    $r->oldest();
                }]);
            },
        ]);

        $relatedApps = Application::published()
            ->where('category_id', $application->category_id)
            ->where('id', '!=', $application->id)
            ->orderBy('downloads', 'desc')
            ->limit(6)
            ->get();

        $moreApps = $relatedApps->count() >= 4
            ? $relatedApps
            : Application::published()
                ->where('id', '!=', $application->id)
                ->orderBy('downloads', 'desc')
                ->limit(6)
                ->get();

        $topPosts = Application::published()
            ->with('category')
            ->orderBy('downloads', 'desc')
            ->limit(10)
            ->get();

        $recentPosts = Application::published()
            ->with('category')
            ->orderBy('released_at', 'desc')
            ->limit(8)
            ->get();

        $userRating = auth()->check()
            ? ($application->approvedReviews()->where('user_id', auth()->id())->value('rating') ?? (int) session('rated_app_'.$application->id, 0))
            : (int) session('rated_app_'.$application->id, 0);

        return view('pages.app-detail', compact('application', 'relatedApps', 'moreApps', 'topPosts', 'recentPosts', 'userRating'));
    }

    public function search()
    {
        $query = request('q', request('s', ''));
        $sort = request('sort', 'relevance');
        $categoryId = request('category');

        $results = Application::published()
            ->with('category')
            ->search($query);

        if ($categoryId) {
            $results->where('category_id', $categoryId);
        }

        match ($sort) {
            'newest' => $results->orderedByNewest(),
            'downloads' => $results->orderedByDownloads(),
            'rating' => $results->orderedByRating(),
            default => $results->orderedByDownloads(),
        };

        $results = $results->paginate(12)->appends(request()->query());
        $categories = Category::active()->ordered()->get();

        if (! empty($query) && strlen(trim($query)) >= 2 && request()->query('page', 1) == 1) {
            try {
                SearchLog::create([
                    'query' => Str::limit(trim($query), 150),
                    'results_count' => $results->total(),
                    'ip_address' => request()->ip(),
                ]);
            } catch (\Throwable $e) {
            }
        }

        return view('pages.search', compact('results', 'query', 'categories', 'categoryId'));
    }

    public function reportBrokenLink(Request $request, Application $application)
    {
        $validated = $request->validate([
            'type' => 'nullable|string|in:ddl,torrent,mirror,other',
            'notes' => 'nullable|string|max:500',
        ]);

        $report = BrokenLinkReport::create([
            'application_id' => $application->id,
            'type' => $validated['type'] ?? 'ddl',
            'notes' => $validated['notes'] ?? null,
            'ip_address' => $request->ip(),
            'status' => 'pending',
        ]);

        try {
            app(NotificationDispatcher::class)->notifyBrokenLink($report);
        } catch (\Throwable $e) {
            Log::warning("Could not dispatch broken link notification: {$e->getMessage()}");
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => '¡Reporte recibido con éxito! Nuestro equipo revisará el enlace de inmediato.']);
        }

        return back()->with('success', '¡Gracias! El reporte ha sido recibido y el enlace será verificado.');
    }

    public function popular()
    {
        $topApplications = Cache::remember('popular_top_applications', 600, function () {
            return Application::published()
                ->with('category')
                ->orderBy('downloads', 'desc')
                ->limit(25)
                ->get();
        });

        if (! ($topApplications instanceof Collection)) {
            Cache::forget('popular_top_applications');
            $topApplications = Application::published()
                ->with('category')
                ->orderBy('downloads', 'desc')
                ->limit(25)
                ->get();
        }

        $topThree = $topApplications->take(3);
        $remainingApps = $topApplications->slice(3);

        return view('pages.popular', compact('topApplications', 'topThree', 'remainingApps'));
    }

    public function new()
    {
        $applications = Application::published()
            ->with('category')
            ->recentlyAdded()
            ->paginate(12);

        return view('pages.new', compact('applications'));
    }

    public function updated()
    {
        $applications = Application::published()
            ->with('category')
            ->recentlyUpdated()
            ->paginate(12);

        return view('pages.updated', compact('applications'));
    }

    public function downloadPage(Application $application)
    {
        if (! $application->published) {
            abort(404);
        }

        $type = request()->query('type');
        $versionId = request()->query('version');
        $targetVersion = null;

        if ($versionId) {
            $targetVersion = $application->versions()->where('id', $versionId)->first();
        }

        $isTorrent = ($type === 'torrent') || (
            $targetVersion
                ? (! $targetVersion->download_url && ($targetVersion->torrent_url || $targetVersion->torrent_file_path))
                : (! $application->download_url_external && ! $application->download_url && $application->has_torrent)
        );

        // Generate encrypted temporary token (expires in 60 minutes)
        $payload = [
            'app_id' => $application->id,
            'version_id' => $targetVersion?->id,
            'type' => $type,
            'exp' => now()->addMinutes(60)->timestamp,
        ];
        $encrypted = Crypt::encryptString(json_encode($payload));
        $downloadToken = rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
        $obfuscatedToken = base64_encode($downloadToken);

        $fileDownloadUrl = route('download.token', ['token' => $downloadToken]);

        $relatedApps = Application::published()
            ->where('category_id', $application->category_id)
            ->where('id', '!=', $application->id)
            ->limit(4)
            ->get();

        return view('pages.download', compact('application', 'targetVersion', 'type', 'isTorrent', 'downloadToken', 'obfuscatedToken', 'fileDownloadUrl', 'relatedApps'));
    }

    public function resolveDownloadToken(string $token)
    {
        try {
            $base64 = strtr($token, '-_', '+/');
            $padLength = 4 - (strlen($base64) % 4);
            if ($padLength < 4) {
                $base64 .= str_repeat('=', $padLength);
            }
            $decryptedJson = Crypt::decryptString(base64_decode($base64));
            $data = json_decode($decryptedJson, true);
        } catch (\Throwable $e) {
            abort(404, 'Invalid download link.');
        }

        if (empty($data['app_id']) || empty($data['exp']) || $data['exp'] < now()->timestamp) {
            return redirect()->route('home')->with('error', 'The download link has expired. Please request a new one.');
        }

        $application = Application::findOrFail($data['app_id']);
        if (! $application->published) {
            abort(404);
        }

        return $this->processDownload($application, $data['type'] ?? null, $data['version_id'] ?? null);
    }

    public function downloadFile(Application $application)
    {
        if (! $application->published) {
            abort(404);
        }

        return $this->processDownload($application, request()->query('type'), request()->query('version'));
    }

    protected function processDownload(Application $application, ?string $type = null, mixed $versionId = null)
    {
        $targetVersion = null;
        if ($versionId) {
            $targetVersion = $application->versions()->where('id', $versionId)->first();
        }

        $isTorrentRequest = ($type === 'torrent') || (
            $targetVersion
                ? (! $targetVersion->download_url && ($targetVersion->torrent_url || $targetVersion->torrent_file_path))
                : (! $application->download_url_external && ! $application->download_url && $application->has_torrent)
        );

        $application->incrementDownloads();
        Cache::forget('home_stats');

        Download::create([
            'application_id' => $application->id,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'downloaded_at' => now(),
        ]);

        // Specific Version Torrent Download
        if ($targetVersion && $isTorrentRequest) {
            return $this->handleTorrentDownload($application, $targetVersion);
        }

        // Specific Version Direct Download
        if ($targetVersion && $targetVersion->download_url) {
            return redirect()->away($targetVersion->download_url);
        }

        // Main Application Torrent Download
        if ($isTorrentRequest && $application->has_torrent) {
            return $this->handleTorrentDownload($application);
        }

        // Direct Download (DDL)
        if ($application->download_url_external) {
            return redirect()->away($application->download_url_external);
        }

        if ($application->download_url) {
            return redirect()->to($application->download_url);
        }

        // Fallback to torrent if no DDL is available
        if ($application->has_torrent && ($application->torrent_url || $application->torrent_file_path || $application->magnet_link)) {
            return $this->handleTorrentDownload($application);
        }

        return redirect()->back()->with('error', 'Download link not available.');
    }

    /**
     * Handle torrent downloads with auto-localization, HackMac.cc rebranding, and magnet fallback
     */
    protected function handleTorrentDownload(Application $application, ?ApplicationVersion $targetVersion = null)
    {
        $target = $targetVersion ?? $application;

        // 1. Attempt to localize/brand the torrent via TorrentStorageService
        try {
            $localized = $this->torrentStorage->localizeApplicationTorrent($application, $targetVersion);

            if (! empty($localized['success'])) {
                // If stored in Cloudflare R2 and public URL is accessible
                if (($localized['storage_driver'] ?? null) === 'r2' && ! empty($localized['public_url'])) {
                    return redirect()->away($localized['public_url']);
                }

                // If stored locally on public disk, download with the exact HackMac branded filename
                if (! empty($localized['file_path']) && Storage::disk('public')->exists($localized['file_path'])) {
                    return Storage::disk('public')->download($localized['file_path'], $localized['filename']);
                }
            }
        } catch (\Throwable $e) {
            Log::error("HomeController: Error localizing torrent for {$application->slug}: {$e->getMessage()}");
        }

        // 2. Check if a pre-existing local file is present on disk
        if ($target->torrent_file_path && Storage::disk('public')->exists($target->torrent_file_path)) {
            $filename = $this->torrentStorage->generateBrandedFilename(
                $application->name,
                $targetVersion?->version ?: $application->version,
                $target->torrent_file_path
            );

            return Storage::disk('public')->download($target->torrent_file_path, $filename);
        }

        // 3. Fallback to Magnet Link if external source is dead or unavailable
        if (! empty($target->magnet_link)) {
            return redirect()->away($target->magnet_link);
        }

        // 4. Fallback to DDL if available
        if ($application->download_url_external) {
            return redirect()->away($application->download_url_external);
        }

        if ($application->download_url) {
            return redirect()->to($application->download_url);
        }

        return redirect()->back()->with('error', 'The requested torrent or download is temporarily unavailable.');
    }

    public function storeReview(Request $request, Application $application)
    {
        if (! $application->published) {
            abort(404);
        }

        $validated = $request->validate([
            'author_name' => 'required|string|max:100',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1500',
            'parent_id' => 'nullable|exists:reviews,id',
        ]);

        $validated['application_id'] = $application->id;
        $validated['user_id'] = auth()->id();
        $validated['rating'] = $validated['rating'] ?? 5;
        $validated['approved'] = true;

        Review::create($validated);

        $application->recalculateRating();

        session()->put('rated_app_'.$application->id, (int) $validated['rating']);

        return back()->with('review_success', 'Thank you for your review! It has been published.');
    }

    public function voteReview(Request $request, Review $review)
    {
        $type = $request->validate([
            'type' => 'required|in:up,down',
        ])['type'];

        $sessionKey = 'voted_review_'.$review->id;
        $currentVote = session()->get($sessionKey);

        if ($currentVote === $type) {
            // Cancel vote
            if ($type === 'up' && $review->upvotes > 0) {
                $review->decrement('upvotes');
            } elseif ($type === 'down' && $review->downvotes > 0) {
                $review->decrement('downvotes');
            }
            session()->forget($sessionKey);
            $userVote = null;
        } else {
            // Reverse previous vote
            if ($currentVote === 'up' && $review->upvotes > 0) {
                $review->decrement('upvotes');
            } elseif ($currentVote === 'down' && $review->downvotes > 0) {
                $review->decrement('downvotes');
            }

            if ($type === 'up') {
                $review->increment('upvotes');
            } else {
                $review->increment('downvotes');
            }
            session()->put($sessionKey, $type);
            $userVote = $type;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'upvotes' => $review->fresh()->upvotes,
                'downvotes' => $review->fresh()->downvotes,
                'user_vote' => $userVote,
            ]);
        }

        return back();
    }

    protected function prepareSitemapUrl(): void
    {
        $appUrl = config('app.url');
        if (empty($appUrl) || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $appUrl = 'https://hackmac.cc';
        }
        URL::forceRootUrl($appUrl);
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        URL::forceScheme($scheme);
    }

    public function sitemap(): Response
    {
        return $this->sitemapIndex();
    }

    public function sitemapIndex(): Response
    {
        $this->prepareSitemapUrl();

        $xml = Cache::remember('sitemap_index_xml', 3600, function () {
            $latestAppDate = Application::published()->latest('updated_at')->value('updated_at')?->tz('UTC')->toAtomString() ?? now()->tz('UTC')->toAtomString();
            $latestCategoryDate = Category::active()->latest('updated_at')->value('updated_at')?->tz('UTC')->toAtomString() ?? now()->tz('UTC')->toAtomString();
            $latestPagesDate = $latestAppDate;

            return view('sitemap.index', compact('latestAppDate', 'latestCategoryDate', 'latestPagesDate'))->render();
        });

        $staticPath = public_path('sitemap.xml');
        @file_put_contents($staticPath, $xml);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('X-Robots-Tag', 'index, follow')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function sitemapApps(): Response
    {
        $this->prepareSitemapUrl();

        $xml = Cache::remember('sitemap_apps_xml', 3600, function () {
            $applications = Application::published()
                ->select(['id', 'category_id', 'name', 'slug', 'updated_at', 'featured', 'icon', 'screenshot'])
                ->latest('updated_at')
                ->get();

            return view('sitemap.apps', compact('applications'))->render();
        });

        $staticPath = public_path('sitemap-apps.xml');
        @file_put_contents($staticPath, $xml);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('X-Robots-Tag', 'index, follow')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function sitemapCategories(): Response
    {
        $this->prepareSitemapUrl();

        $xml = Cache::remember('sitemap_categories_xml', 3600, function () {
            $categories = Category::active()->ordered()
                ->select(['id', 'name', 'slug', 'updated_at'])
                ->get();

            return view('sitemap.categories', compact('categories'))->render();
        });

        $staticPath = public_path('sitemap-categories.xml');
        @file_put_contents($staticPath, $xml);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('X-Robots-Tag', 'index, follow')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function sitemapPages(): Response
    {
        $this->prepareSitemapUrl();

        $xml = Cache::remember('sitemap_pages_xml', 3600, function () {
            $latestAppDate = Application::published()->latest('updated_at')->value('updated_at')?->tz('UTC')->toAtomString() ?? now()->tz('UTC')->toAtomString();

            return view('sitemap.pages', compact('latestAppDate'))->render();
        });

        $staticPath = public_path('sitemap-pages.xml');
        @file_put_contents($staticPath, $xml);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('X-Robots-Tag', 'index, follow')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function robots(): Response
    {
        $sitemapUrl = url('sitemap.xml');

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /api/',
            'Disallow: /download/',
            'Disallow: /login',
            'Disallow: /logout',
            'Disallow: /*?*search*',
            '',
            '# Search Engine Indexing',
            "Sitemap: {$sitemapUrl}",
            '',
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('X-Robots-Tag', 'index, follow')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function disableSip()
    {
        return view('pages.guides.disable-sip');
    }

    public function fixDamagedApps()
    {
        return view('pages.guides.fix-damaged-apps');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:3000',
        ]);

        return back()->with('contact_success', 'Thank you for contacting us! Your message has been sent successfully.');
    }

    public function dmca()
    {
        return view('pages.dmca');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function about()
    {
        $stats = [
            'total_apps' => Application::published()->count(),
            'total_downloads' => Application::published()->sum('downloads'),
            'total_categories' => Category::count(),
        ];

        return view('pages.about', compact('stats'));
    }

    public function liveSearch(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'live_search_'.md5(mb_strtolower($q));

        $results = Cache::remember($cacheKey, 180, function () use ($q) {
            return Application::published()
                ->with('category')
                ->search($q)
                ->limit(8)
                ->get()
                ->map(function ($app) {
                    return [
                        'id' => $app->id,
                        'name' => $app->name,
                        'slug' => $app->slug,
                        'url' => route('app', $app->slug),
                        'version' => $app->version,
                        'category' => $app->category?->name ?? 'Application',
                        'icon_url' => $app->icon_url,
                        'size' => $app->formatted_size,
                        'downloads' => $app->formatted_downloads,
                    ];
                })
                ->all();
        });

        return response()->json($results);
    }

    public function toggleFavorite(Application $application)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $exists = Favorite::where('user_id', $user->id)
                ->where('application_id', $application->id)
                ->first();

            if ($exists) {
                $exists->delete();
                $isFav = false;
            } else {
                Favorite::create([
                    'user_id' => $user->id,
                    'application_id' => $application->id,
                ]);
                $isFav = true;
            }
        } else {
            $favs = session()->get('favorites', []);
            if (in_array($application->id, $favs)) {
                $favs = array_diff($favs, [$application->id]);
                $isFav = false;
            } else {
                $favs[] = $application->id;
                $isFav = true;
            }
            session()->put('favorites', $favs);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'is_favorite' => $isFav]);
        }

        return back();
    }

    /**
     * Serve a real JPEG Open Graph image for the application.
     * Ensures Telegram, WhatsApp, Twitter and Facebook always display the app's featured image.
     */
    public function ogImage(Application $application)
    {
        $cacheFilename = "og-cache/{$application->slug}-{$application->id}.jpg";
        if (Storage::disk('public')->exists($cacheFilename)) {
            $path = Storage::disk('public')->path($cacheFilename);

            return response()->file($path, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'public, max-age=604800, immutable',
            ]);
        }

        $telegramService = app(TelegramBotService::class);
        $cover = $telegramService->resolveCoverImage($application);

        if ($cover && ! empty($cover['bytes'])) {
            try {
                Storage::disk('public')->put($cacheFilename, $cover['bytes']);
            } catch (\Throwable $e) {
                // ignore cache write error
            }

            return response($cover['bytes'], 200, [
                'Content-Type' => $cover['mime'] ?? 'image/jpeg',
                'Cache-Control' => 'public, max-age=604800, immutable',
            ]);
        }

        $generated = $telegramService->generateFeaturedCard($application);
        if ($generated) {
            try {
                Storage::disk('public')->put($cacheFilename, $generated);
            } catch (\Throwable $e) {
            }

            return response($generated, 200, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'public, max-age=604800, immutable',
            ]);
        }

        abort(404);
    }
}
