<?php

/**
 * Patches FilePond's compiled file-upload.js to include the Georgian (ka) locale.
 *
 * FilePond does not ship a Georgian locale. This script injects it into Filament's
 * pre-built dist file after every `composer update` via post-update-cmd.
 *
 * Source locale: resources/js/filepond-locale-ka.js
 */
$localeFile = __DIR__ . '/../resources/js/filepond-locale-ka.js';

$targets = [
    __DIR__ . '/../vendor/filament/forms/dist/components/file-upload.js',
    __DIR__ . '/../public/js/filament/forms/components/file-upload.js',
];

if (! file_exists($localeFile)) {
    echo "patch-filepond-ka: skipping (locale file not found)\n";
    exit(0);
}

// Parse the locale JS file (export default { key: 'value', ... })
$localeContent = file_get_contents($localeFile);
preg_match_all('/(\w+):\s+[\'`](.*?)[\'`],?\s*$/m', $localeContent, $matches, PREG_SET_ORDER);

if (empty($matches)) {
    echo "patch-filepond-ka: ERROR — could not parse locale file\n";
    exit(1);
}

$pairs = array_map(fn ($m) => $m[1] . ':"' . addslashes($m[2]) . '"', $matches);
$localeObj = 'var ka={' . implode(',', $pairs) . '};';

foreach ($targets as $target) {
    if (! file_exists($target)) {
        echo "patch-filepond-ka: skipping {$target} (not found)\n";

        continue;
    }

    $content = file_get_contents($target);

    if (str_contains($content, 'ka:ka}')) {
        echo "patch-filepond-ka: already patched — {$target}\n";

        continue;
    }

    if (! str_contains($content, 'var fr={')) {
        echo "patch-filepond-ka: ERROR — insertion point not found in {$target}\n";

        continue;
    }

    $patched = str_replace('var fr={', $localeObj . 'var fr={', $content);
    $patched = str_replace('zh_TW:gr}', 'zh_TW:gr,ka:ka}', $patched);

    file_put_contents($target, $patched);
    echo "patch-filepond-ka: Georgian locale injected — {$target}\n";
}
