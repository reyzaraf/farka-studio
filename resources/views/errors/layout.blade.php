<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>@yield('title', 'Error') | Farka Studio</title>
    <link rel="icon" href="{{ asset('farkalogo.svg') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Noto+Sans+JP:wght@400;500;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --ink: #111111;
            --ink-55: rgba(17,17,17,.55);
            --ink-35: rgba(17,17,17,.35);
            --font-title: 'Montserrat', system-ui, -apple-system, Segoe UI, sans-serif;
            --font-head: 'Source Sans 3', system-ui, -apple-system, Segoe UI, sans-serif;
            --font-body: 'Noto Sans JP', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        }
        html, body { height: 100%; }
        body {
            font-family: var(--font-body); color: var(--ink); background: #fff;
            -webkit-font-smoothing: antialiased;
            display: flex; flex-direction: column; min-height: 100vh;
        }
        a { color: inherit; }
        .topbar { padding: 26px 24px; }
        .brand { display: inline-block; text-decoration: none; }
        .logo { height: 34px; width: auto; display: block; }
        .wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px; text-align: center; }
        .inner { max-width: 560px; }
        .rule { width: 44px; height: 3px; background: var(--ink); margin: 0 auto 18px; }
        .code {
            font-family: var(--font-title); font-weight: 900;
            font-size: clamp(5rem, 22vw, 11rem); line-height: .88; letter-spacing: -.04em;
        }
        .title {
            font-family: var(--font-title); font-weight: 800;
            font-size: clamp(1.3rem, 4.5vw, 2.1rem); letter-spacing: -.01em; margin-top: 10px;
        }
        .msg { font-size: clamp(1rem, 2.4vw, 1.12rem); color: var(--ink-55); margin-top: 14px; line-height: 1.6; }
        .btn {
            display: inline-flex; align-items: center; gap: 10px; margin-top: 34px;
            background: var(--ink); color: #fff; text-decoration: none;
            font-family: var(--font-head); font-weight: 700; font-size: .95rem;
            padding: 14px 28px; border-radius: 12px; transition: background .15s;
        }
        .btn:hover { background: #000; }
        .foot {
            padding: 24px; text-align: center; font-size: 11px; letter-spacing: .2em;
            text-transform: uppercase; color: var(--ink-35);
        }
    </style>
</head>
<body>
    <div class="topbar"><a href="{{ url('/') }}" class="brand" aria-label="Farka Studio — kembali ke beranda"><img src="{{ asset('farkalogo.svg') }}" alt="Farka Studio" class="logo"></a></div>

    <main class="wrap">
        <div class="inner">
            <div class="rule"></div>
            <div class="code">@yield('code', 'Oops')</div>
            <h1 class="title">@yield('title', 'Something went wrong')</h1>
            <p class="msg">@yield('message', 'The page you are looking for is unavailable.')</p>
            <a href="{{ url('/') }}" class="btn">&larr; Kembali ke Beranda</a>
        </div>
    </main>

    <div class="foot">Farka Studio &middot; Architecture &amp; Interior Design</div>
</body>
</html>
