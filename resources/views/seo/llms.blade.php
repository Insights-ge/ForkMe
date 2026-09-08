{{--
    Source template for public/llms.txt, rendered by `php artisan sitemap:generate`.

    The heading, summary and page links come from GeneralSettings; everything else
    is plain Markdown you can edit freely, except the tech stack, which is read from
    composer.json and package.json so it cannot drift from the manifests.

    Blank-line runs are collapsed on render, so the directives below do not need to
    hug their surrounding text.
--}}
# {!! $siteName !!}

@if ($tagline !== '')
> {!! $tagline !!}

@endif
@if ($about !== '')
{!! $about !!}
@else
{!! $siteName !!} is an opinionated Laravel boilerplate built by the Insights team. It provides a production-ready foundation for new web projects, with the most common packages pre-installed and configured so teams can fork and start building immediately.
@endif

## Purpose

- Eliminate repetitive setup across projects
- Enforce consistent tooling and conventions
- Ship with admin panel, permissions, media, i18n, and SEO out of the box

## Tech Stack

@foreach ($techStack as $section => $items)
### {!! $section !!}

@foreach ($items as $item)
- {!! $item !!}
@endforeach

@endforeach
## Setup

```bash
composer run setup
```

This runs: `composer install` → copy `.env` → `key:generate` → `migrate` → `npm install` → `npm run build`

## Key Conventions

- Filament resources use Shield for authorization
- All models with translatable content use Spatie Translatable
- Roles and permissions managed through Filament Shield UI
- Locale-prefixed routes (`/{locale}`) resolve the default locale from settings
- Pest used for all automated tests

## Pages

@foreach ($pages as $page)
- [{!! $page['label'] !!}]({!! $page['url'] !!})@if ($page['notes'] !== ''): {!! $page['notes'] !!}@endif

@endforeach
