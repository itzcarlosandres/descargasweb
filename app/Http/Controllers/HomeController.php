<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Category;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('home_stats', 3600, function () {
            return [
                'total_apps' => Application::published()->count(),
                'total_downloads' => Application::published()->sum('downloads'),
                'updated_this_week' => Application::published()->where('updated_at', '>=', now()->subWeek())->count(),
            ];
        });

        $featured = Application::published()->featured()
            ->with('category')
            ->latest('updated_at')
            ->limit(4)
            ->get();

        $applications = Application::published()
            ->with('category')
            ->latest('updated_at')
            ->latest('id')
            ->paginate(12);

        $topCategories = Category::active()->ordered()->limit(7)->get();

        return view('pages.home', compact('stats', 'featured', 'applications', 'topCategories'));
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

        return view('pages.search', compact('results', 'query', 'categories', 'categoryId'));
    }

    public function popular()
    {
        $applications = Application::published()
            ->popular()
            ->with('category')
            ->orderBy('downloads', 'desc')
            ->paginate(12);

        return view('pages.popular', compact('applications'));
    }

    public function new()
    {
        $applications = Application::published()
            ->with('category')
            ->orderBy('released_at', 'desc')
            ->paginate(12);

        return view('pages.new', compact('applications'));
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

        // Specific Version Download (Older Version)
        if ($targetVersion) {
            if ($isTorrentRequest) {
                if ($targetVersion->torrent_url) {
                    return redirect()->away($targetVersion->torrent_url);
                }
                if ($targetVersion->torrent_file_path && Storage::disk('public')->exists($targetVersion->torrent_file_path)) {
                    return Storage::disk('public')->download($targetVersion->torrent_file_path);
                }
            }

            if ($targetVersion->download_url) {
                return redirect()->away($targetVersion->download_url);
            }
        }

        // Torrent Download
        if ($isTorrentRequest && $application->has_torrent) {
            if ($application->torrent_url) {
                return redirect()->away($application->torrent_url);
            }

            if ($application->torrent_file_path && Storage::disk('public')->exists($application->torrent_file_path)) {
                return Storage::disk('public')->download($application->torrent_file_path);
            }

            if ($application->magnet_link) {
                return redirect()->away($application->magnet_link);
            }
        }

        // Direct Download (DDL)
        if ($application->download_url_external) {
            return redirect()->away($application->download_url_external);
        }

        if ($application->download_url) {
            return redirect()->to($application->download_url);
        }

        // Fallback to torrent if no DDL is available
        if ($application->has_torrent && $application->torrent_url) {
            return redirect()->away($application->torrent_url);
        }

        return redirect()->back()->with('error', 'Download link not available.');
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

    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap_xml', 3600, function () {
            $categories = Category::active()->ordered()
                ->select(['id', 'name', 'slug', 'updated_at'])
                ->get();

            $applications = Application::published()
                ->select(['id', 'category_id', 'name', 'slug', 'updated_at', 'featured', 'icon', 'screenshot'])
                ->latest('updated_at')
                ->get();

            $latestAppDate = $applications->first()?->updated_at?->tz('UTC')->toAtomString() ?? now()->tz('UTC')->toAtomString();

            return view('sitemap', compact('categories', 'applications', 'latestAppDate'))->render();
        });

        // Ensure static physical sitemap.xml exists on disk for Nginx static serving
        $staticPath = public_path('sitemap.xml');
        if (! file_exists($staticPath)) {
            @file_put_contents($staticPath, $xml);
        }

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('X-Robots-Tag', 'index, follow')
            ->header('Cache-Control', 'public, max-age=86400');
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

        $results = Application::published()
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
}
