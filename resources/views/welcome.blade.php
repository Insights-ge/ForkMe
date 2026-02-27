@extends('layouts.app')

@section('title', __('welcome.seo.title'))
@section('meta_description', __('welcome.seo.description'))
@section('meta_keywords', __('welcome.seo.keywords'))

@section('content')
    <div class="pt-24 min-h-[calc(100vh-80px)] flex flex-col">
        <!-- Blur Decorations -->
        <div
            class="fixed top-[-10%] inset-is-[-10%] size-[40%] bg-primary opacity-20 blur-[100px] rounded-full -z-10 animate-pulse">
        </div>
        <div class="fixed bottom-[-10%] inset-ie-[-10%] size-[30%] bg-secondary opacity-20 blur-[100px] rounded-full -z-10">
        </div>
        <div class="fixed top-[40%] inset-ie-[-5%] size-[20%] bg-purple-500 opacity-10 blur-[80px] rounded-full -z-10"></div>

        <div class="flex-grow flex items-center justify-center">
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
        </div>
    </div>
@endsection
