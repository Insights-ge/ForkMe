{{--
    Source template for public/humans.txt, rendered by `php artisan sitemap:generate`.

    The TEAM block credits whoever built the site and is free text. The SITE block
    pulls its name, URL and languages from GeneralSettings.
--}}
/* TEAM */
  Name: Insights Team
  Site: https://insights.ge
  Contact: https://discord.gg/
  Location: Global

/* SITE */
  Name: {!! $siteName !!}
  Site: {!! $siteUrl !!}
  Contact: {!! $contactEmail !!}
  Last update: {!! $lastUpdate !!}
  Language: {!! $languages !!}
  Standards: HTML5, CSS3, Laravel, TailwindCSS
  Components: Livewire, Alpine.js
  Doctype: HTML5
