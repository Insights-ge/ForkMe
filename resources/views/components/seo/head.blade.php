@php
    $supportedConfig = config('laravellocalization.supportedLocales') ?? [];
    $supportedLocales = array_keys($supportedConfig) ?: [app()->getLocale()];

    $currentLocale = app()->getLocale();
    $currentRegionalBcp47 = str_replace('_', '-', $supportedConfig[$currentLocale]['regional'] ?? $currentLocale);

    $ogLocaleOf = function ($loc) use ($supportedConfig) {
        return str_replace('-', '_', $supportedConfig[$loc]['regional'] ?? $loc);
    };

    $hreflangOf = fn($loc) => strtolower($loc);

    // Default params logic (can be extended)
    $params = $params ?? [];

    $canonical = LaravelLocalization::getLocalizedURL($currentLocale, null, $params, true);
    $defaultLocale = LaravelLocalization::getDefaultLocale() ?? (config('app.fallback_locale') ?? 'en');
    $xDefaultHref = LaravelLocalization::getLocalizedURL($defaultLocale, null, $params, true);

    $title = $title ?? trim($__env->yieldContent('title', config('seo.site_name')));
    $description = $description ?? trim($__env->yieldContent('meta_description', __('landing.description')));
    $keywords = $keywords ?? trim($__env->yieldContent('meta_keywords', __('landing.keywords')));

    $ogImage = $ogImage ?? asset(config('seo.og.image'));
    $nowIso = \Illuminate\Support\Carbon::now()->toIso8601String();

    $base = url('/');
    $orgId = $base . '#org';
    $siteId = $base . '#website';

    $langsForSchema = [];
    foreach ($supportedLocales as $loc) {
        $langsForSchema[] = str_replace('_', '-', $supportedConfig[$loc]['regional'] ?? $loc);
    }

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $orgId,
                'name' => config('seo.site_name'),
                'url' => $base,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset(config('seo.branding.logo_main')),
                ],
                // Add more organization details from config if needed
            ],
            [
                '@type' => 'WebSite',
                '@id' => $siteId,
                'url' => $base,
                'name' => config('seo.site_name'),
                'inLanguage' => $langsForSchema,
                'publisher' => ['@id' => $orgId],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $canonical . '#webpage',
                'url' => $canonical,
                'name' => $title,
                'inLanguage' => $currentRegionalBcp47,
                'description' => $description,
                'isPartOf' => ['@id' => $siteId],
                'publisher' => ['@id' => $orgId],
                'dateModified' => $nowIso,
            ],
        ],
    ];
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
@if ($keywords)
    <meta name="keywords" content="{{ $keywords }}">
@endif

<meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
<meta name="googlebot" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
<meta name="referrer" content="strict-origin-when-cross-origin">
<meta name="format-detection" content="telephone=no">

<link rel="canonical" href="{{ $canonical }}">

<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:type" content="{{ $ogType ?? config('seo.og.type', 'website') }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="{{ config('seo.og.image_width') }}">
<meta property="og:image:height" content="{{ config('seo.og.image_height') }}">
<meta property="og:locale" content="{{ $ogLocaleOf($currentLocale) }}">

@foreach ($supportedLocales as $alt)
    @if ($alt !== $currentLocale)
        <meta property="og:locale:alternate" content="{{ $ogLocaleOf($alt) }}">
    @endif
@endforeach

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">
@if (config('seo.social.twitter'))
    <meta name="twitter:site" content="{{ config('seo.social.twitter') }}">
@endif

<link rel="alternate" hreflang="x-default" href="{{ $xDefaultHref }}">
@foreach ($supportedLocales as $loc)
    @php
        $altUrl = LaravelLocalization::getLocalizedURL($loc, null, $params, true);
    @endphp
    @if ($altUrl)
        <link rel="alternate" hreflang="{{ $hreflangOf($loc) }}" href="{{ $altUrl }}">
    @endif
@endforeach

<script type="application/ld+json">
    {!! json_encode($graph, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>

<x-seo.gtm-head />
