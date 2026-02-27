<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }} - Insights Redesign</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="antialiased bg-dark text-white font-sans selection:bg-primary selection:text-white overflow-x-hidden min-h-screen flex flex-col pt-24">
    <!-- Blur Decorations -->
    <div
        class="fixed top-[-10%] inset-is-[-10%] size-[40%] bg-primary opacity-20 blur-[100px] rounded-full -z-10 animate-pulse">
    </div>
    <div class="fixed bottom-[-10%] inset-ie-[-10%] size-[30%] bg-secondary opacity-20 blur-[100px] rounded-full -z-10">
    </div>
    <div class="fixed top-[40%] inset-ie-[-5%] size-[20%] bg-purple-500 opacity-10 blur-[80px] rounded-full -z-10">
    </div>

    <header
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 border-b border-white/5 bg-dark/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 group">
                <img src="{{ asset('images/logo-light.avif') }}" alt="{{ config('app.name') }}" class="h-10 w-auto">
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-white/60">
            </nav>

            <div class="flex items-center gap-4">
                <div
                    class="flex items-center gap-2 me-4 bg-white/5 p-1 rounded-full group cursor-pointer transition-all hover:bg-white/10">
                    @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <a rel="alternate" hreflang="{{ $localeCode }}"
                            href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                            class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all {{ App::getLocale() === $localeCode ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-white/40 hover:text-white' }}">
                            {{ $localeCode }}
                        </a>
                    @endforeach
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ filament()->getPanel('admin')->getUrl() }}"
                        class="px-6 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-full font-semibold transition-all shadow-lg shadow-primary/20">
                        {{ __('welcome.account') }}
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center">
        <div class="max-w-7xl mx-auto px-6 py-20 w-full">
            <div class="grid lg:grid-cols-2 lg:gap-12 items-center">
                <!-- Text Content -->
                <div class="space-y-8 animate-slide-in-from-left">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/20 text-primary text-xs font-bold uppercase tracking-widest">
                        <span class="relative flex size-2">
                            <span
                                class="animate-ping absolute inline-flex size-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full size-2 bg-primary"></span>
                        </span>
                        {{ __('welcome.insights') }}
                    </div>

                    <h1 class="text-6xl md:text-7xl lg:text-8xl font-black leading-[1.1] tracking-tight text-balance">
                        {{ __('welcome.hero_title_1') }} <br>
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-rose-400">{{ __('welcome.hero_title_2') }}</span>
                        {{ __('welcome.hero_title_3') }}
                    </h1>

                    <p class="text-lg md:text-xl text-white/50 max-w-lg font-medium leading-relaxed text-pretty">
                        {{ __('welcome.hero_description') }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <a href="{{ filament()->getPanel('admin')->getUrl() }}"
                            class="group relative px-8 py-4 bg-primary hover:bg-primary-hover rounded-2xl font-bold text-lg transition-all shadow-xl shadow-primary/30 flex items-center justify-center gap-2 overflow-hidden">
                            <span class="relative z-10 text-white">{{ __('welcome.account') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 group-hover:translate-x-1 transition-transform relative z-10 text-white"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-[100%] group-hover:translate-x-[100%] transition-transform duration-1000">
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Visual Section -->
                <div class="hidden lg:block relative animate-zoom-in">
                    <div
                        class="relative z-10 bg-dark-card/50 backdrop-blur-xl border border-white/10 rounded-4xl p-8 shadow-2xl">
                        <div
                            class="aspect-square bg-gradient-to-tr from-primary/20 to-secondary/20 rounded-3xl overflow-hidden flex items-center justify-center relative">
                            <div
                                class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10">
                            </div>
                            <!-- Symbolic 12 Illustration -->
                            <div class="text-[12rem] font-black text-white/10 select-none">12</div>
                            <!-- Floating UI Elements -->
                            <div
                                class="absolute top-10 inset-ie-10 p-4 bg-dark-card rounded-2xl border border-white/10 shadow-xl animate-bounce duration-[3000ms]">
                                <div class="flex gap-2">
                                    <div class="size-3 rounded-full bg-red-500"></div>
                                    <div class="size-3 rounded-full bg-yellow-500"></div>
                                    <div class="size-3 rounded-full bg-green-500"></div>
                                </div>
                            </div>
                            <div
                                class="absolute bottom-12 inset-is-12 p-6 bg-white rounded-3xl shadow-2xl shadow-primary/20 rotate-[-6deg] animate-pulse">
                                <div class="h-4 w-24 bg-primary/20 rounded-full mb-3"></div>
                                <div class="h-4 w-16 bg-primary/10 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Background Glow behind visual -->
                    <div class="absolute -inset-10 bg-primary opacity-30 blur-[100px] -z-10 rounded-full"></div>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-12 border-t border-white/5 bg-dark">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex items-center gap-4">
                <a href="/" class="opacity-50 hover:opacity-100 transition-opacity">
                    <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}" class="size-8">
                </a>
                <span
                    class="text-sm font-semibold text-white/30 uppercase tracking-widest">{{ __('welcome.all_rights_reserved') }}</span>
            </div>
        </div>
    </footer>
</body>

</html>
