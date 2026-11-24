<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cardino')</title>
    <style>
        :root {
            --bg: #f4f5f7; --surface: #ffffff; --ink: #14181f; --muted: #5c6573;
            --line: #dde1e7; --brand: #0f2a44; --accent: #f2b705; --accent-ink: #3a2c00;
            --danger: #b42318; --danger-bg: #fdecea;
            --font: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); font: 15px/1.5 var(--font); }
        a { color: var(--brand); }
        .wordmark { font-weight: 800; letter-spacing: -0.02em; color: var(--brand); }
        .wordmark span { color: var(--accent); }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; border: 0; border-radius: 8px;
               padding: .7rem 1.1rem; font: 600 15px var(--font); cursor: pointer; background: var(--brand); color: #fff; }
        .btn:hover { filter: brightness(1.15); }
        .btn:focus-visible, input:focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
        .btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--line); }
        label { display: block; font-weight: 600; margin-bottom: .35rem; }
        input[type=text], input[type=password] { width: 100%; padding: .7rem .8rem; border: 1px solid var(--line); border-radius: 8px;
               font: 16px var(--font); background: #fff; color: var(--ink); }
        .error { color: var(--danger); background: var(--danger-bg); border-radius: 8px; padding: .6rem .8rem; font-size: 14px; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
    @stack('head')
</head>
<body>
@yield('body')
</body>
</html>
