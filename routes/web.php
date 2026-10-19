<?php

use App\Http\Controllers\Admin\ScraperController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/categories', [HomeController::class, 'categories'])->name('categories');
Route::get('/category/{category:slug}', [HomeController::class, 'category'])->name('category');
Route::get('/app/{application:slug}', [HomeController::class, 'app'])->name('app');
Route::get('/app/{application:slug}/og-image.jpg', [HomeController::class, 'ogImage'])->name('app.og-image');
Route::post('/app/{application:slug}/review', [HomeController::class, 'storeReview'])->name('app.review');
Route::post('/review/{review}/vote', [HomeController::class, 'voteReview'])->name('review.vote');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/popular', [HomeController::class, 'popular'])->name('popular');
Route::get('/new', [HomeController::class, 'new'])->name('new');
Route::get('/updated', [HomeController::class, 'updated'])->name('updated');
Route::get('/download/{application:slug}', [HomeController::class, 'downloadPage'])->name('download');
Route::get('/download/{application:slug}/file', [HomeController::class, 'downloadFile'])->name('download.file');
Route::get('/dl/{token}', [HomeController::class, 'resolveDownloadToken'])->name('download.token');
Route::post('/app/{application:slug}/favorite', [HomeController::class, 'toggleFavorite'])->name('app.favorite');
Route::post('/app/{application:slug}/report', [HomeController::class, 'reportBrokenLink'])->name('app.report');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap', [HomeController::class, 'sitemap']);
Route::get('/sitemap-apps.xml', [HomeController::class, 'sitemapApps'])->name('sitemap.apps');
Route::get('/sitemap-categories.xml', [HomeController::class, 'sitemapCategories'])->name('sitemap.categories');
Route::get('/sitemap-pages.xml', [HomeController::class, 'sitemapPages'])->name('sitemap.pages');
Route::get('/robots.txt', [HomeController::class, 'robots'])->name('robots');

// macOS Guides
Route::get('/disable-sip', [HomeController::class, 'disableSip'])->name('guide.sip');
Route::get('/fix-damaged-apps', [HomeController::class, 'fixDamagedApps'])->name('guide.fix');

// Institutional & Legal Pages
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->name('contact.submit');
Route::get('/dmca', [HomeController::class, 'dmca'])->name('dmca');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/about', [HomeController::class, 'about'])->name('about');

// Live Search API
Route::get('/api/search/live', [HomeController::class, 'liveSearch'])->name('api.search.live');

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $remember = $request->boolean('remember');

    if (auth()->attempt($credentials, $remember)) {
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->middleware('throttle:5,1')->name('login.post');

Route::post('/logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications', [AdminController::class, 'applications'])->name('applications');
    Route::get('/applications/create', [AdminController::class, 'createApplication'])->name('applications.create');
    Route::post('/applications', [AdminController::class, 'storeApplication'])->name('applications.store');
    Route::get('/applications/{application}/edit', [AdminController::class, 'editApplication'])->name('applications.edit');
    Route::put('/applications/{application}', [AdminController::class, 'updateApplication'])->name('applications.update');
    Route::delete('/applications/{application}', [AdminController::class, 'destroyApplication'])->name('applications.destroy');
    Route::post('/applications/{application}/toggle-featured', [AdminController::class, 'toggleFeatured'])->name('applications.toggle-featured');
    Route::post('/applications/{application}/publish-telegram', [AdminController::class, 'publishToTelegram'])->name('applications.publish-telegram');
    Route::post('/applications/reset-downloads', [AdminController::class, 'resetDownloads'])->name('applications.reset-downloads');
    Route::post('/applications/bulk-action', [AdminController::class, 'bulkAction'])->name('applications.bulk-action');

    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/{report}/resolve', [AdminController::class, 'resolveReport'])->name('reports.resolve');
    Route::delete('/reports/{report}', [AdminController::class, 'destroyReport'])->name('reports.destroy');
    Route::post('/reports/{report}/search-replacement', [AdminController::class, 'searchReportReplacement'])->name('reports.search-replacement');
    Route::post('/reports/{report}/auto-repair', [AdminController::class, 'autoRepairReport'])->name('reports.auto-repair');
    Route::post('/reports/{report}/apply-replacement', [AdminController::class, 'applyReportReplacement'])->name('reports.apply-replacement');
    Route::post('/reports/{report}/check-health', [AdminController::class, 'checkReportHealth'])->name('reports.check-health');
    Route::post('/reports/scan-batch', [AdminController::class, 'scanBatchHealth'])->name('reports.scan-batch');
    Route::post('/reports/heal-app/{application}', [AdminController::class, 'autoRepairApp'])->name('reports.heal-app');

    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('/categories/create', [AdminController::class, 'createCategory'])->name('categories.create');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

    Route::post('/applications/{application}/generate-ai', [AdminController::class, 'generateAiContent'])->name('applications.generate-ai');

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/test-gemini', [AdminController::class, 'testGemini'])->name('settings.test-gemini');
    Route::post('/settings/test-r2', [AdminController::class, 'testR2'])->name('settings.test-r2');
    Route::get('/settings/r2/stats', [AdminController::class, 'r2Stats'])->name('settings.r2.stats');
    Route::post('/settings/r2/toggle', [AdminController::class, 'toggleR2'])->name('settings.r2.toggle');
    Route::post('/settings/r2/migrate-batch', [AdminController::class, 'migrateR2Batch'])->name('settings.r2.migrate-batch');
    Route::post('/settings/test-telegram', [AdminController::class, 'testTelegram'])->name('settings.test-telegram');
    Route::post('/settings/telegram/dispatch-one', [AdminController::class, 'dispatchTelegramOne'])->name('settings.telegram.dispatch-one');
    Route::post('/settings/telegram/toggle-auto-pilot', [AdminController::class, 'toggleTelegramAutoPilot'])->name('settings.telegram.toggle-auto-pilot');
    Route::get('/settings/telegram/stats', [AdminController::class, 'telegramStats'])->name('settings.telegram.stats');
    Route::post('/settings/test-discord', [AdminController::class, 'testDiscord'])->name('settings.test-discord');

    // DDL Scraper & Auto-Sync
    Route::get('/scraper', [ScraperController::class, 'index'])->name('scraper');
    Route::get('/scraper/latest', [ScraperController::class, 'getLatest'])->name('scraper.latest');
    Route::get('/scraper/pending-updates', [ScraperController::class, 'getPendingUpdates'])->name('scraper.pending-updates');
    Route::post('/scraper/search', [ScraperController::class, 'search'])->name('scraper.search');
    Route::post('/scraper/import-single', [ScraperController::class, 'importSingle'])->name('scraper.import-single');
    Route::post('/scraper/sync-categories', [ScraperController::class, 'syncCategories'])->name('scraper.sync-categories');
    Route::post('/scraper/sync-updates', [ScraperController::class, 'syncUpdates'])->name('scraper.sync-updates');
    Route::post('/scraper/cron-settings', [ScraperController::class, 'updateCronSettings'])->name('scraper.cron-settings');
    Route::post('/scraper/toggle-cron', [ScraperController::class, 'toggleCron'])->name('scraper.toggle-cron');
    Route::post('/scraper/toggle-drip', [ScraperController::class, 'toggleDrip'])->name('scraper.toggle-drip');
    Route::post('/scraper/drip/release-now', [ScraperController::class, 'releaseDripBatch'])->name('scraper.drip.release-now');
    Route::post('/scraper/drip/fill-drafts', [ScraperController::class, 'fillDraftBatch'])->name('scraper.drip.fill-drafts');

    // TorrentMac Scraper & Cloud Sync
    Route::get('/scraper/torrentmac/latest', [ScraperController::class, 'getTorrentmacLatest'])->name('scraper.torrentmac.latest');
    Route::get('/scraper/torrentmac/pending-updates', [ScraperController::class, 'getTorrentmacPendingUpdates'])->name('scraper.torrentmac.pending-updates');
    Route::post('/scraper/torrentmac/search', [ScraperController::class, 'searchTorrentmac'])->name('scraper.torrentmac.search');
    Route::post('/scraper/torrentmac/import-single', [ScraperController::class, 'importTorrentmacSingle'])->name('scraper.torrentmac.import-single');
    Route::post('/scraper/torrentmac/sync-latest', [ScraperController::class, 'syncTorrentmacLatest'])->name('scraper.torrentmac.sync-latest');
    Route::post('/scraper/torrentmac/storage-settings', [ScraperController::class, 'updateTorrentStorageSettings'])->name('scraper.torrentmac.storage-settings');
});
