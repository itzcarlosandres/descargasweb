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
Route::post('/app/{application:slug}/review', [HomeController::class, 'storeReview'])->name('app.review');
Route::post('/review/{review}/vote', [HomeController::class, 'voteReview'])->name('review.vote');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/popular', [HomeController::class, 'popular'])->name('popular');
Route::get('/new', [HomeController::class, 'new'])->name('new');
Route::get('/download/{application:slug}', [HomeController::class, 'downloadPage'])->name('download');
Route::get('/download/{application:slug}/file', [HomeController::class, 'downloadFile'])->name('download.file');
Route::get('/dl/{token}', [HomeController::class, 'resolveDownloadToken'])->name('download.token');
Route::post('/app/{application:slug}/favorite', [HomeController::class, 'toggleFavorite'])->name('app.favorite');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap', [HomeController::class, 'sitemap']);
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
        'email' => 'Las credenciales proporcionadas no son válidas.',
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
    Route::post('/applications/reset-downloads', [AdminController::class, 'resetDownloads'])->name('applications.reset-downloads');

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

    // DDL Scraper & Auto-Sync
    Route::get('/scraper', [ScraperController::class, 'index'])->name('scraper');
    Route::get('/scraper/latest', [ScraperController::class, 'getLatest'])->name('scraper.latest');
    Route::get('/scraper/pending-updates', [ScraperController::class, 'getPendingUpdates'])->name('scraper.pending-updates');
    Route::post('/scraper/search', [ScraperController::class, 'search'])->name('scraper.search');
    Route::post('/scraper/import-single', [ScraperController::class, 'importSingle'])->name('scraper.import-single');
    Route::post('/scraper/sync-categories', [ScraperController::class, 'syncCategories'])->name('scraper.sync-categories');
    Route::post('/scraper/sync-updates', [ScraperController::class, 'syncUpdates'])->name('scraper.sync-updates');
    Route::post('/scraper/cron-settings', [ScraperController::class, 'updateCronSettings'])->name('scraper.cron-settings');

    // TorrentMac Scraper & Cloud Sync
    Route::get('/scraper/torrentmac/latest', [ScraperController::class, 'getTorrentmacLatest'])->name('scraper.torrentmac.latest');
    Route::post('/scraper/torrentmac/search', [ScraperController::class, 'searchTorrentmac'])->name('scraper.torrentmac.search');
    Route::post('/scraper/torrentmac/import-single', [ScraperController::class, 'importTorrentmacSingle'])->name('scraper.torrentmac.import-single');
    Route::post('/scraper/torrentmac/sync-latest', [ScraperController::class, 'syncTorrentmacLatest'])->name('scraper.torrentmac.sync-latest');
    Route::post('/scraper/torrentmac/storage-settings', [ScraperController::class, 'updateTorrentStorageSettings'])->name('scraper.torrentmac.storage-settings');
});
