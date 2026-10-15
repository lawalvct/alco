<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#062849">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') · @yield('title') | ALCO Associate Solutions</title>
    <link rel="icon" href="/brand/alco-favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/css/error-pages.css">
</head>
<body>
    <div class="error-shell">
        <header class="error-header">
            <a class="brand" href="/" aria-label="ALCO Associate Solutions homepage">
                <img src="/brand/alco-mark-white.svg" width="45" height="43" alt="">
                <span><strong>ALCO</strong><small>ASSOCIATE SOLUTIONS</small></span>
            </a>
            <a class="header-home" href="/">Home <span aria-hidden="true">↗</span></a>
        </header>

        <main class="error-main" id="main-content">
            <div class="error-copy">
                <p class="eyebrow"><span class="eyebrow-line" aria-hidden="true"></span> A pause, not a dead end</p>
                <p class="status-label">Error @yield('code')</p>
                <h1>@yield('heading')</h1>
                <p class="lead">@yield('message')</p>
                <div class="error-actions">
                    <a class="button-home" href="/">Return to homepage <span aria-hidden="true">→</span></a>
                    <a class="button-contact" href="mailto:info@alcosolutions.ng">Contact ALCO</a>
                </div>
                <p class="small-note">Professional accounting services in Lagos, Nigeria.</p>
            </div>

            <div class="error-art" aria-hidden="true">
                <span class="orbit orbit-one"></span>
                <span class="orbit orbit-two"></span>
                <div class="art-card">
                    <span class="art-label">ALCO / @yield('code')</span>
                    <span class="art-number">@yield('code')</span>
                    <span class="art-line"></span>
                    <span class="art-caption">@yield('artCaption')</span>
                </div>
            </div>
        </main>

        <footer class="error-footer">
            <span>© {{ date('Y') }} ALCO Associate Solutions</span>
            <span>Clarity today. Confidence for what comes next.</span>
        </footer>
    </div>
</body>
</html>
