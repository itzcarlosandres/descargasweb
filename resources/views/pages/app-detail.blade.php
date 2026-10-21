<x-app-layout>
    @php
        $appTitle = "Descargar {$application->name} {$application->version} - Uptodown";
        $appDesc = Str::limit(strip_tags($application->short_description ?: $application->description), 160) ?: "Descarga {$application->name} {$application->version} gratis y sin virus en Uptodown.";
        $shareImage = route('app.og-image', $application->slug);
    @endphp

    @section('title', $appTitle)
    @section('description', $appDesc)
    @section('og_image', $shareImage)
    @section('og_type', 'article')
    @section('canonical', route('app', $application->slug))

    @section('schema')
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "{{ addslashes($application->name) }}",
        "operatingSystem": "{{ addslashes($application->platform ?? 'Android / Windows / PC') }}",
        "applicationCategory": "{{ addslashes($application->category->name ?? 'General') }}",
        "softwareVersion": "{{ addslashes($application->version ?? '1.0') }}",
        "fileSize": "{{ addslashes($application->formatted_size ?? '50 MB') }}",
        "description": "{{ addslashes($appDesc) }}",
        "image": "{{ $shareImage }}",
        "url": "{{ route('app', $application->slug) }}",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
    }
    </script>
    @endsection

    <style>
        /* Exact Uptodown Dark Theme Design Architecture */
        :root {
            --utd-bg: #09191d;
            --utd-content: #0f292f;
            --utd-card: #13353d;
            --utd-cyan: #00aad5;
            --utd-cyan-hover: #00beed;
            --utd-text: #ffffff;
            --utd-muted: #cddce0;
            --utd-subtext: #7f949a;
            --utd-border: #163e47;
        }

        body {
            background-color: #09191d !important;
            color: #ffffff !important;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }

        .utd-page-wrapper {
            max-width: 1366px;
            margin: 0 auto;
            background-color: #0f292f;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.7);
        }

        /* Top Cyan Ribbon */
        .utd-ribbon {
            background-color: #00aad5;
            color: #ffffff;
            padding: 6px 14px;
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .utd-ribbon a {
            text-decoration: underline;
            color: #ffffff;
            font-weight: 900;
        }

        /* Breadcrumbs */
        .utd-breadcrumbs {
            padding: 12px 20px 6px;
            font-size: 11.5px;
            color: #7f949a;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .utd-breadcrumbs a { color: #7f949a; transition: color 0.15s; }
        .utd-breadcrumbs a:hover { color: #00aad5; }
        .utd-breadcrumbs span.sep { color: #163e47; font-weight: 900; }
        .utd-breadcrumbs span.current { color: #cddce0; font-weight: 700; }

        /* Two-Column Detail Layout */
        .utd-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 32px;
            padding: 16px 20px 40px;
        }
        @media (max-width: 1080px) {
            .utd-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        /* App Header Block */
        .utd-app-header {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 18px;
        }
        .utd-app-icon-lg {
            width: 88px;
            height: 88px;
            border-radius: 20px;
            object-fit: cover;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
            flex-shrink: 0;
            background: #13353d;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .utd-app-title-area {
            flex: 1;
        }
        .utd-app-main-title {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.15;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .utd-app-version-sub {
            font-size: 12.5px;
            color: #7f949a;
            margin-top: 3px;
        }
        .utd-app-author-link {
            color: #00aad5;
            font-weight: 700;
            font-size: 12.5px;
            display: inline-block;
            margin-top: 3px;
        }
        .utd-app-author-link:hover { text-decoration: underline; }

        /* Key Metrics Badges */
        .utd-metrics-bar {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 12px 0 16px;
            border-bottom: 1px solid #163e47;
            margin-bottom: 18px;
        }
        .utd-metric-item {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .utd-metric-val {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
        }
        .utd-metric-val.star { color: #fbbf24; }
        .utd-metric-val.shield { color: #10b981; }
        .utd-metric-label { font-size: 11px; color: #7f949a; }
        .utd-metric-info-icon { font-size: 10px; color: #7f949a; cursor: pointer; }

        /* App pitch tagline */
        .utd-app-pitch {
            font-size: 14.5px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        /* Action Buttons Row */
        .utd-download-action-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }
        .utd-btn-main-download {
            flex: 1;
            min-width: 280px;
            max-width: 440px;
            background-color: #00aad5;
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 170, 213, 0.35);
            transition: transform 0.15s ease, background-color 0.15s ease;
            text-decoration: none;
        }
        .utd-btn-main-download:hover {
            background-color: #00beed;
            transform: translateY(-2px);
            color: #ffffff;
        }
        .utd-btn-main-title {
            font-size: 15px;
            font-weight: 800;
            line-height: 1.2;
        }
        .utd-btn-main-sub {
            font-size: 11px;
            opacity: 0.9;
            font-weight: 500;
            margin-top: 2px;
        }
        .utd-btn-main-icon {
            width: 34px;
            height: 34px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .utd-btn-other-versions {
            background: #13353d;
            border: 1px solid #163e47;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .utd-btn-other-versions:hover {
            background: #163e47;
            border-color: #00aad5;
            color: #00aad5;
        }

        /* Screenshots Carousel */
        .utd-screenshots-block {
            margin-bottom: 30px;
        }
        .utd-screenshots-track {
            display: flex !important;
            flex-direction: row !important;
            gap: 12px;
            overflow-x: auto !important;
            scrollbar-width: none;
            padding-bottom: 12px;
        }
        .utd-screenshots-track::-webkit-scrollbar { display: none; }
        .utd-shot-img {
            width: 156px;
            height: 310px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #163e47;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            flex-shrink: 0;
            cursor: pointer;
            transition: transform 0.15s ease;
        }
        .utd-shot-img:hover {
            transform: scale(1.02);
            border-color: #00aad5;
        }
        .utd-scroll-indicator {
            width: 100%;
            height: 3px;
            background: #13353d;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 4px;
        }
        .utd-scroll-progress {
            width: 35%;
            height: 100%;
            background: #00aad5;
            border-radius: 2px;
        }

        /* Editorial Review */
        .utd-editorial-block {
            margin-bottom: 32px;
        }
        .utd-author-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }
        .utd-author-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #00aad5;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
        }
        .utd-author-name {
            font-size: 12px;
            color: #7f949a;
        }
        .utd-author-name strong { color: #ffffff; }
        .utd-review-text {
            font-size: 13.5px;
            color: #cddce0;
            line-height: 1.7;
        }
        .utd-review-text p { margin-bottom: 12px; }

        .utd-requirements-box {
            margin-top: 20px;
            padding-top: 14px;
            border-top: 1px solid #163e47;
        }
        .utd-req-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 4px;
        }
        .utd-req-desc {
            font-size: 12.5px;
            color: #7f949a;
        }

        /* Technical Specs Section */
        .utd-specs-section {
            margin-bottom: 36px;
        }
        .utd-section-heading {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .utd-section-heading a {
            font-size: 15px;
            color: #00aad5;
            font-weight: 800;
            text-decoration: none;
        }
        .utd-spec-group {
            margin-bottom: 22px;
        }
        .utd-spec-group-title {
            font-size: 12.5px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 10px;
        }
        .utd-spec-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        @media (max-width: 768px) {
            .utd-spec-grid {
                grid-template-columns: 1fr;
            }
        }
        .utd-spec-cell {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 12px;
        }
        .utd-spec-cell-icon {
            width: 22px;
            height: 22px;
            color: #00aad5;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .utd-spec-label {
            color: #7f949a;
            display: block;
            margin-bottom: 2px;
        }
        .utd-spec-value {
            color: #ffffff;
            font-weight: 600;
            word-break: break-all;
        }
        .utd-spec-value.link {
            color: #00aad5;
            text-decoration: underline;
            cursor: pointer;
        }

        /* Other Versions Grid */
        .utd-versions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 34px;
        }
        @media (max-width: 640px) {
            .utd-versions-grid {
                grid-template-columns: 1fr;
            }
        }
        .utd-version-card {
            background: #13353d;
            border: 1px solid #163e47;
            border-radius: 8px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .utd-version-card:hover {
            border-color: #00aad5;
            background: #163e47;
        }
        .utd-ver-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .utd-ver-pill {
            background: #0f292f;
            color: #00aad5;
            font-size: 9.5px;
            font-weight: 800;
            padding: 3px 6px;
            border-radius: 4px;
            border: 1px solid #163e47;
        }
        .utd-ver-num {
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
        }
        .utd-ver-date {
            font-size: 11px;
            color: #7f949a;
            display: block;
        }
        .utd-ver-os {
            font-size: 11.5px;
            color: #7f949a;
            font-weight: 600;
        }

        /* Rate App & Reviews */
        .utd-rate-box {
            border-top: 1px solid #163e47;
            padding-top: 24px;
            margin-bottom: 28px;
        }
        .utd-rate-stars-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .utd-rate-mini-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
        }
        .utd-stars-interactive {
            display: flex;
            gap: 6px;
            color: #fbbf24;
            font-size: 24px;
            cursor: pointer;
        }
        .utd-rate-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .utd-btn-comment {
            background: #00aad5;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 800;
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .utd-btn-comment:hover { background: #00beed; }
        .utd-btn-wishlist, .utd-btn-favorite {
            background: #13353d;
            border: 1px solid #163e47;
            color: #cddce0;
            font-size: 11.5px;
            font-weight: 700;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .utd-btn-wishlist:hover, .utd-btn-favorite:hover {
            color: #00aad5;
            border-color: #00aad5;
        }

        .utd-score-row {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
        }
        .utd-score-giant {
            font-size: 56px;
            font-weight: 900;
            color: #ffffff;
            line-height: 0.9;
            letter-spacing: -2px;
        }
        .utd-score-bars {
            flex: 1;
            max-width: 220px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .utd-bar-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            color: #7f949a;
        }
        .utd-bar-fill-track {
            flex: 1;
            height: 6px;
            background: #13353d;
            border-radius: 3px;
            overflow: hidden;
        }
        .utd-bar-fill-actual {
            height: 100%;
            background: #00aad5;
            border-radius: 3px;
        }

        .utd-comments-card {
            background: #13353d;
            border: 1px solid #163e47;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            margin-bottom: 30px;
        }
        .utd-comments-card p {
            font-size: 13px;
            color: #cddce0;
            margin-bottom: 14px;
        }

        /* Sidebar Modules */
        .utd-sidebar {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .utd-sidebar-section-title {
            font-size: 14px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            cursor: pointer;
        }
        .utd-sidebar-section-title span.arrow {
            color: #00aad5;
            font-size: 16px;
            font-weight: 900;
        }
        .utd-sidebar-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .utd-sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: transform 0.15s ease;
            text-decoration: none;
        }
        .utd-sidebar-item:hover {
            transform: translateX(3px);
        }
        .utd-sidebar-thumb {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: #13353d;
        }
        .utd-sidebar-info {
            flex: 1;
            min-width: 0;
        }
        .utd-sidebar-name {
            font-size: 12.5px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .utd-sidebar-desc {
            font-size: 10.5px;
            color: #7f949a;
            line-height: 1.25;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .utd-floating-dl-widget {
            position: sticky;
            bottom: 20px;
            background: #00aad5;
            color: #ffffff;
            padding: 10px 14px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
            cursor: pointer;
            z-index: 50;
            transition: transform 0.15s ease;
            text-decoration: none;
        }
        .utd-floating-dl-widget:hover {
            transform: translateY(-2px);
            background: #00beed;
            color: #ffffff;
        }
    </style>

    <div class="utd-page-wrapper">

        <!-- Cyan Notification Ribbon (Exact from Uptodown) -->
        <div class="utd-ribbon">
            <span>¡Uptodown App Store ya está disponible! <a href="{{ route('download', $application->slug) }}">Descárgala ya</a> y mantén tus apps al día.</span>
        </div>

        <!-- Breadcrumbs -->
        <nav class="utd-breadcrumbs">
            <a href="{{ route('home') }}">Inicio</a>
            <span class="sep">&rsaquo;</span>
            <a href="{{ route('categories') }}">Programas</a>
            <span class="sep">&rsaquo;</span>
            <a href="{{ route('category', $application->category?->slug ?? 'apps') }}">{{ $application->category?->name ?? 'General' }}</a>
            <span class="sep">&rsaquo;</span>
            <span class="current">{{ $application->name }}</span>
        </nav>

        <!-- Two-Column Page Layout -->
        <div class="utd-detail-grid">

            <!-- LEFT COLUMN: Main App Details -->
            <div class="utd-main-column">

                <!-- App Header (Icon + Title + Author) -->
                <div class="utd-app-header">
                    <img src="{{ $application->icon_url }}" 
                         onerror="this.src='https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=200&auto=format&fit=crop'"
                         class="utd-app-icon-lg" 
                         alt="{{ $application->name }} Icon">
                    <div class="utd-app-title-area">
                        <h1 class="utd-app-main-title">{{ $application->name }}</h1>
                        <div class="utd-app-version-sub">{{ $application->version ?? '5.5.4' }}</div>
                        <a href="{{ route('category', $application->category?->slug ?? 'apps') }}" class="utd-app-author-link">
                            {{ $application->developer ?? ($application->category?->name ?? 'Uptodown Verified') }}
                        </a>
                    </div>
                </div>

                <!-- Metrics Row -->
                <div class="utd-metrics-bar">
                    <div class="utd-metric-item">
                        <span class="utd-metric-val star">&#9733; {{ number_format($application->rating > 0 ? $application->rating : 5.0, 1) }}</span>
                        <span class="utd-metric-label">{{ max($application->reviews_count, 1) }} valoraciones</span>
                        <span class="utd-metric-info-icon" title="Información">ⓘ</span>
                    </div>
                    <div class="utd-metric-item">
                        <span class="utd-metric-val">{{ $application->formatted_downloads ?? '26.6 k' }}</span>
                        <span class="utd-metric-label">descargas</span>
                        <span class="utd-metric-info-icon" title="Información">ⓘ</span>
                    </div>
                    <div class="utd-metric-item">
                        <span class="utd-metric-val shield">&#128737; 0</span>
                        <span class="utd-metric-label">Seguridad</span>
                        <span class="utd-metric-info-icon" title="VirusTotal 0/68">ⓘ</span>
                    </div>
                </div>

                <!-- Pitch sentence -->
                <p class="utd-app-pitch">
                    {{ $application->short_description ?: 'Aprende y gestiona tus contenidos favoritos con este instalador oficial y seguro.' }}
                </p>

                <!-- Download Action Buttons -->
                <div class="utd-download-action-row">
                    <a href="{{ route('download', $application->slug) }}" class="utd-btn-main-download">
                        <div>
                            <div class="utd-btn-main-title">Consigue la última versión</div>
                            <div class="utd-btn-main-sub">
                                {{ $application->version ?? '5.5.4' }} &bull; {{ $application->updated_at?->format('d M Y') ?? '21 dic 2024' }} &bull; {{ $application->formatted_size ?? '111.39 MB' }}
                            </div>
                        </div>
                        <div class="utd-btn-main-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                        </div>
                    </a>

                    <a href="#otras-versiones" class="utd-btn-other-versions">OTRAS VERSIONES</a>
                </div>

                <!-- Screenshots Carousel -->
                <section class="utd-screenshots-block">
                    <div class="utd-screenshots-track">
                        @if($application->images && $application->images->count() > 0)
                            @foreach($application->images as $img)
                                <img src="{{ $img->image_url }}" class="utd-shot-img" alt="Screenshot {{ $loop->iteration }}">
                            @endforeach
                        @else
                            <img src="https://images.unsplash.com/photo-1616469829941-c7200edec809?q=80&w=400&auto=format&fit=crop" class="utd-shot-img" alt="Screen 1">
                            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=400&auto=format&fit=crop" class="utd-shot-img" alt="Screen 2">
                            <img src="https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=400&auto=format&fit=crop" class="utd-shot-img" alt="Screen 3">
                            <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=400&auto=format&fit=crop" class="utd-shot-img" alt="Screen 4">
                            <img src="https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?q=80&w=400&auto=format&fit=crop" class="utd-shot-img" alt="Screen 5">
                        @endif
                    </div>
                    <div class="utd-scroll-indicator">
                        <div class="utd-scroll-progress"></div>
                    </div>
                </section>

                <!-- Editorial Review -->
                <article class="utd-editorial-block">
                    <div class="utd-author-badge">
                        <div class="utd-author-avatar">U</div>
                        <span class="utd-author-name">Autor: <strong>Uptodown Editorial Team</strong></span>
                    </div>

                    <div class="utd-review-text">
                        {!! $application->description ? nl2br(e($application->description)) : '
                        <p>El sistema de aprendizaje y distribución de esta aplicación está diseñado para facilitar a los usuarios un acceso rápido, seguro y optimizado a todas sus características principales.</p>
                        <p>Cada versión publicada en nuestra plataforma cuenta con el análisis de seguridad exhaustivo de Uptodown, garantizando paquetes limpios, libres de virus y con instaladores verificados.</p>' !!}
                    </div>

                    <div class="utd-requirements-box">
                        <div class="utd-req-title">Requisitos (Última versión)</div>
                        <div class="utd-req-desc">
                            Requiere {{ $application->platform ?? 'Android 7.0 o Windows 10/11' }} o superior
                        </div>
                    </div>
                </article>

                <!-- Technical Information Table -->
                <section class="utd-specs-section">
                    <div class="utd-section-heading">
                        <span>Información sobre {{ $application->name }} {{ $application->version ?? '5.5.4' }}</span>
                    </div>

                    <!-- Group 1: Información básica -->
                    <div class="utd-spec-group">
                        <div class="utd-spec-group-title">Información básica</div>
                        <div class="utd-spec-grid">
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                                <div>
                                    <span class="utd-spec-label">Desarrollador</span>
                                    <span class="utd-spec-value link">{{ $application->developer ?? ($application->category?->name ?? 'Goodtox') }}</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                <div>
                                    <span class="utd-spec-label">Categoría</span>
                                    <span class="utd-spec-value">{{ $application->category?->name ?? 'Educación' }}</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8M8 12h8"></path></svg>
                                <div>
                                    <span class="utd-spec-label">Clasificación</span>
                                    <span class="utd-spec-value">+3</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line></svg>
                                <div>
                                    <span class="utd-spec-label">Idiomas</span>
                                    <span class="utd-spec-value">Español y 11 más</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Group 2: Modelo de distribución -->
                    <div class="utd-spec-group">
                        <div class="utd-spec-group-title">Modelo de distribución</div>
                        <div class="utd-spec-grid">
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="6" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="18"></line></svg>
                                <div>
                                    <span class="utd-spec-label">Precio</span>
                                    <span class="utd-spec-value">Gratis</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <div>
                                    <span class="utd-spec-label">Licencia</span>
                                    <span class="utd-spec-value">{{ strtoupper($application->license ?? 'Propietario') }}</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M14.83 14.83a4 4 0 1 1 0-5.66"></path></svg>
                                <div>
                                    <span class="utd-spec-label">Copyright</span>
                                    <span class="utd-spec-value">&copy; {{ date('Y') }} {{ $application->developer ?? 'Goodtox' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Group 3: Seguridad y privacidad -->
                    <div class="utd-spec-group">
                        <div class="utd-spec-group-title">Seguridad y privacidad</div>
                        <div class="utd-spec-grid">
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <div>
                                    <span class="utd-spec-label">Permisos solicitados</span>
                                    <span class="utd-spec-value link">Ver 27 permisos</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <div>
                                    <span class="utd-spec-label">Firma del contenido</span>
                                    <span class="utd-spec-value">a3a8f6d653110c7c34efec1a3ba8421c</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                <div>
                                    <span class="utd-spec-label">Seguridad verificada</span>
                                    <span class="utd-spec-value link">Ver informe de seguridad y antivirus</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Group 4: Información de descarga -->
                    <div class="utd-spec-group">
                        <div class="utd-spec-group-title">Información de descarga</div>
                        <div class="utd-spec-grid">
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                <div>
                                    <span class="utd-spec-label">Descargas</span>
                                    <span class="utd-spec-value">{{ $application->formatted_downloads ?? '26,672' }}</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <div>
                                    <span class="utd-spec-label">Fecha</span>
                                    <span class="utd-spec-value">{{ $application->updated_at?->format('d M Y') ?? '21 dic. 2024' }}</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                <div>
                                    <span class="utd-spec-label">Tipo de archivo</span>
                                    <span class="utd-spec-value">{{ $application->has_torrent ? 'Torrent / Repack' : 'XAPK / APK' }}</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>
                                <div>
                                    <span class="utd-spec-label">Tamaño</span>
                                    <span class="utd-spec-value">{{ $application->formatted_size ?? '111.39 MB' }}</span>
                                </div>
                            </div>
                            <div class="utd-spec-cell" style="grid-column: span 2;">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 2l-2 2m-1.41 1.41L13 10m-3 3l-2 2m-1.41 1.41L2 21"></path><circle cx="15.5" cy="8.5" r="3.5"></circle></svg>
                                <div>
                                    <span class="utd-spec-label">SHA256</span>
                                    <span class="utd-spec-value" style="font-size:10.5px;">49b294025c88b030b6ea55331f24d266e7f949a62efd4889c2</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Group 5: Detalles técnicos -->
                    <div class="utd-spec-group">
                        <div class="utd-spec-group-title">Detalles técnicos</div>
                        <div class="utd-spec-grid">
                            <div class="utd-spec-cell">
                                <svg class="utd-spec-cell-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                <div>
                                    <span class="utd-spec-label">Nombre de paquete</span>
                                    <span class="utd-spec-value">{{ $application->bundle_id ?? ('ru.sleng.' . $application->slug) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Other Versions Grid -->
                <section id="otras-versiones" class="utd-specs-section">
                    <div class="utd-section-heading">
                        <span>Otras versiones</span>
                        <a href="#">&rsaquo;</a>
                    </div>

                    <div class="utd-versions-grid">
                        @if($application->versions && $application->versions->count() > 0)
                            @foreach($application->versions as $ver)
                                <div class="utd-version-card">
                                    <div class="utd-ver-left">
                                        <span class="utd-ver-pill">XAPK</span>
                                        <div>
                                            <span class="utd-ver-num">{{ $ver->version }}</span>
                                            <span class="utd-ver-date">{{ $ver->created_at?->format('d M Y') ?? '18 oct. 2024' }}</span>
                                        </div>
                                    </div>
                                    <span class="utd-ver-os">Android + 7.0</span>
                                </div>
                            @endforeach
                        @else
                            <div class="utd-version-card">
                                <div class="utd-ver-left">
                                    <span class="utd-ver-pill">XAPK</span>
                                    <div>
                                        <span class="utd-ver-num">5.5.3</span>
                                        <span class="utd-ver-date">18 oct. 2024</span>
                                    </div>
                                </div>
                                <span class="utd-ver-os">Android + 7.0</span>
                            </div>
                            <div class="utd-version-card">
                                <div class="utd-ver-left">
                                    <span class="utd-ver-pill">XAPK</span>
                                    <div>
                                        <span class="utd-ver-num">5.5.1</span>
                                        <span class="utd-ver-date">20 sep. 2024</span>
                                    </div>
                                </div>
                                <span class="utd-ver-os">Android + 7.0</span>
                            </div>
                            <div class="utd-version-card">
                                <div class="utd-ver-left">
                                    <span class="utd-ver-pill">XAPK</span>
                                    <div>
                                        <span class="utd-ver-num">5.3.32</span>
                                        <span class="utd-ver-date">25 ago. 2024</span>
                                    </div>
                                </div>
                                <span class="utd-ver-os">Android + 7.0</span>
                            </div>
                            <div class="utd-version-card">
                                <div class="utd-ver-left">
                                    <span class="utd-ver-pill">XAPK</span>
                                    <div>
                                        <span class="utd-ver-num">5.3.31</span>
                                        <span class="utd-ver-date">4 jul. 2024</span>
                                    </div>
                                </div>
                                <span class="utd-ver-os">Android + 7.0</span>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- Rate this App Block -->
                <section class="utd-rate-box">
                    <div class="utd-section-heading">
                        <span>Valora esta app</span>
                        <a href="#">&rsaquo;</a>
                    </div>

                    <div class="utd-rate-stars-row">
                        <img src="{{ $application->icon_url }}" 
                             onerror="this.src='https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=100&auto=format&fit=crop'"
                             class="utd-rate-mini-icon" 
                             alt="{{ $application->name }}">
                        <div class="utd-stars-interactive">
                            <span>&#9733;</span>
                            <span>&#9733;</span>
                            <span>&#9733;</span>
                            <span>&#9733;</span>
                            <span>&#9733;</span>
                        </div>
                    </div>

                    <div class="utd-rate-actions">
                        <button class="utd-btn-comment">ESCRIBIR UN COMENTARIO</button>
                        <button class="utd-btn-wishlist">&#9825; AÑADIR A LA LISTA DE DESEOS</button>
                        <button class="utd-btn-favorite">&#9734; AÑADIR A MIS FAVORITAS</button>
                    </div>

                    <!-- Score Breakdown -->
                    <div class="utd-score-row">
                        <div class="utd-score-giant">5.0</div>
                        <div class="utd-score-bars">
                            <div class="utd-bar-row">
                                <span>5</span>
                                <div class="utd-bar-fill-track"><div class="utd-bar-fill-actual" style="width: 100%;"></div></div>
                            </div>
                            <div class="utd-bar-row">
                                <span>4</span>
                                <div class="utd-bar-fill-track"><div class="utd-bar-fill-actual" style="width: 0%;"></div></div>
                            </div>
                            <div class="utd-bar-row">
                                <span>3</span>
                                <div class="utd-bar-fill-track"><div class="utd-bar-fill-actual" style="width: 0%;"></div></div>
                            </div>
                            <div class="utd-bar-row">
                                <span>2</span>
                                <div class="utd-bar-fill-track"><div class="utd-bar-fill-actual" style="width: 0%;"></div></div>
                            </div>
                            <div class="utd-bar-row">
                                <span>1</span>
                                <div class="utd-bar-fill-track"><div class="utd-bar-fill-actual" style="width: 0%;"></div></div>
                            </div>
                        </div>
                        <div style="font-size: 11px; color: #7f949a; font-weight: 800; text-transform: uppercase;">
                            {{ max($application->reviews_count, 1) }} VALORACIONES
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="utd-section-heading">
                        <span>Comentarios</span>
                        <a href="#">&rsaquo;</a>
                    </div>

                    <div class="utd-comments-card">
                        <p>Todavía no hay opiniones sobre {{ $application->name }}. ¡Sé el primero!</p>
                        <button class="utd-btn-comment">COMENTAR</button>
                    </div>
                </section>

            </div>

            <!-- RIGHT COLUMN: Sidebar Modules (Exact from Screenshot) -->
            <aside class="utd-sidebar">

                <!-- Module 1: Similar a [App] -->
                <div>
                    <div class="utd-sidebar-section-title">
                        <span>Similar a {{ $application->name }}</span>
                        <span class="arrow">&rsaquo;</span>
                    </div>

                    <div class="utd-sidebar-list">
                        @if($relatedApps && $relatedApps->count() > 0)
                            @foreach($relatedApps as $rel)
                                <a href="{{ route('app', $rel->slug) }}" class="utd-sidebar-item">
                                    <img src="{{ $rel->icon_url }}" 
                                         onerror="this.src='https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=100&auto=format&fit=crop'"
                                         class="utd-sidebar-thumb" 
                                         alt="{{ $rel->name }}">
                                    <div class="utd-sidebar-info">
                                        <div class="utd-sidebar-name">{{ $rel->name }}</div>
                                        <div class="utd-sidebar-desc">{{ Str::limit(strip_tags($rel->short_description ?: $rel->description), 50) }}</div>
                                    </div>
                                </a>
                            @endforeach
                        @else
                            <div class="utd-sidebar-item">
                                <img src="https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=100&auto=format&fit=crop" class="utd-sidebar-thumb" alt="Duolingo">
                                <div class="utd-sidebar-info">
                                    <div class="utd-sidebar-name">Duolingo</div>
                                    <div class="utd-sidebar-desc">Una forma sencilla de aprender idiomas</div>
                                </div>
                            </div>
                            <div class="utd-sidebar-item">
                                <img src="https://images.unsplash.com/photo-1579373903781-fd5c0c30c4cd?q=80&w=100&auto=format&fit=crop" class="utd-sidebar-thumb" alt="Scratch">
                                <div class="utd-sidebar-info">
                                    <div class="utd-sidebar-name">Scratch</div>
                                    <div class="utd-sidebar-desc">Crea tus propias historias mientras aprendes programación</div>
                                </div>
                            </div>
                            <div class="utd-sidebar-item">
                                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=100&auto=format&fit=crop" class="utd-sidebar-thumb" alt="Ted">
                                <div class="utd-sidebar-info">
                                    <div class="utd-sidebar-name">Ted</div>
                                    <div class="utd-sidebar-desc">Discursos de referencia, guías y herramientas</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Module 2: Descubre apps de Productividad -->
                <div>
                    <div class="utd-sidebar-section-title">
                        <span>Descubre apps de {{ $application->category?->name ?? 'Productividad' }}</span>
                        <span class="arrow">&rsaquo;</span>
                    </div>

                    <div class="utd-sidebar-list">
                        <div class="utd-sidebar-item">
                            <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=100&auto=format&fit=crop" class="utd-sidebar-thumb" alt="Word">
                            <div class="utd-sidebar-info">
                                <div class="utd-sidebar-name">Microsoft Word</div>
                                <div class="utd-sidebar-desc">Aplicación oficial de Microsoft Word para Android y PC</div>
                            </div>
                        </div>
                        <div class="utd-sidebar-item">
                            <img src="https://images.unsplash.com/photo-1620712943543-bcc4688e7485?q=80&w=100&auto=format&fit=crop" class="utd-sidebar-thumb" alt="ChatGPT">
                            <div class="utd-sidebar-info">
                                <div class="utd-sidebar-name">ChatGPT</div>
                                <div class="utd-sidebar-desc">La app oficial de ChatGPT desarrollada por OpenAI</div>
                            </div>
                        </div>
                        <div class="utd-sidebar-item">
                            <img src="https://images.unsplash.com/photo-1558494949-ef010cbdcc31?q=80&w=100&auto=format&fit=crop" class="utd-sidebar-thumb" alt="OPPO">
                            <div class="utd-sidebar-info">
                                <div class="utd-sidebar-name">OPPO Clone Phone</div>
                                <div class="utd-sidebar-desc">Transfiere los datos entre dispositivos Oppo</div>
                            </div>
                        </div>
                        <div class="utd-sidebar-item">
                            <img src="https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=100&auto=format&fit=crop" class="utd-sidebar-thumb" alt="File Manager">
                            <div class="utd-sidebar-info">
                                <div class="utd-sidebar-name">File Manager +</div>
                                <div class="utd-sidebar-desc">Increíble administrador con opciones avanzadas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating mini download card -->
                <a href="{{ route('download', $application->slug) }}" class="utd-floating-dl-widget">
                    <div>
                        <div style="font-size:11.5px; font-weight:800;">Consigue la última versión</div>
                        <div style="font-size:9.5px; opacity:0.9;">{{ $application->version ?? '5.5.4' }} &bull; {{ $application->formatted_size ?? '111.39 MB' }}</div>
                    </div>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </a>

            </aside>

        </div>

    </div>
</x-app-layout>
