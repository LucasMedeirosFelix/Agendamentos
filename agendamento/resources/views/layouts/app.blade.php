<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#eef4ef">
    <title>@yield('title', 'Agenda') · Hora Clara</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/agenda.css') }}">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('booking') }}" aria-label="Hora Clara, página inicial">
            <span class="brand-mark" aria-hidden="true">h</span>
            <span class="brand-name">hora<span>clara</span></span>
        </a>
        <nav class="main-nav" aria-label="Navegação principal">
            <a class="{{ request()->routeIs('booking') ? 'is-current' : '' }}" href="{{ route('booking') }}">Agendar</a>
            <a class="{{ request()->routeIs('dashboard') ? 'is-current' : '' }}" href="{{ route('dashboard') }}">Agenda</a>
            <a class="{{ request()->routeIs('services.*') ? 'is-current' : '' }}" href="{{ route('services.index') }}">Serviços</a>
        </nav>
        <a class="topbar-action" href="{{ route('dashboard') }}"><span class="online-dot"></span> Painel</a>
    </header>

    @if (session('success'))
        <div class="flash-message" role="status">{{ session('success') }}</div>
    @endif

    <main class="page-content">
        @yield('content')
    </main>

    <footer class="site-footer">
        <span>Hora Clara</span>
        <span>Atendimento organizado, no seu tempo.</span>
    </footer>

    @stack('scripts')
</body>
</html>