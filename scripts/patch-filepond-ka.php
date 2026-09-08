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

$localeContent = file_get_contents($localeFile);

if ($localeContent === false) {
    echo "patch-filepond-ka: ERROR — could not read locale file\n";
    exit(1);
}

preg_match_all(
    '/^\s*([A-Za-z_$][A-Za-z0-9_$]*)\s*:\s*([\'`])(.*?)\2,?\s*$/m',
    $localeContent,
    $matches,
    PREG_SET_ORDER,
);

if (empty($matches)) {
    echo "patch-filepond-ka: ERROR — could not parse locale file\n";
    exit(1);
}

$pairs = array_map(
    static fn (array $match): string => $match[1] . ':' . json_encode(
        $match[3],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ),
    $matches,
);

$localeVariable = '__filePondLocaleKa';
$localeObject = "var {$localeVariable}={" . implode(',', $pairs) . '};';
$localeObjectPattern = '/var (?:ka|' . preg_quote($localeVariable, '/') . ')=\{labelIdle:[^;]*?imageValidateSizeLabelExpectedMaxResolution:[^;]*?\};/u';
$defaultExportPattern = 'export\{[A-Za-z_$][A-Za-z0-9_$]* as default\};';
$patchedLocaleMapPattern = '/,ka:(?:ka|' . preg_quote($localeVariable, '/') . ')(?=\};' . $defaultExportPattern . ')/';
$localeMapPattern = '/var ([A-Za-z_$][A-Za-z0-9_$]*)=\{(am:[^{}]+,zh_TW:[A-Za-z_$][A-Za-z0-9_$]*)\}(?=;' . $defaultExportPattern . ')/';
$hasErrors = false;

foreach ($targets as $target) {
    if (! file_exists($target)) {
        echo "patch-filepond-ka: skipping {$target} (not found)\n";

        continue;
    }

    $content = file_get_contents($target);

    if ($content === false) {
        echo "patch-filepond-ka: ERROR — could not read {$target}\n";
        $hasErrors = true;

        continue;
    }

    $cleaned = preg_replace($localeObjectPattern, '', $content);
    $cleaned = preg_replace($patchedLocaleMapPattern, '', $cleaned ?? '');

    if ($cleaned === null || preg_match_all($localeMapPattern, $cleaned) !== 1) {
        echo "patch-filepond-ka: ERROR — locale map not found in {$target}\n";
        $hasErrors = true;

        continue;
    }

    $patched = preg_replace_callback(
        $localeMapPattern,
        static fn (array $match): string => $localeObject
            . 'var ' . $match[1] . '={' . $match[2] . ',ka:' . $localeVariable . '}',
        $cleaned,
        1,
        $replacementCount,
    );

    if ($patched === null || $replacementCount !== 1) {
        echo "patch-filepond-ka: ERROR — could not patch locale map in {$target}\n";
        $hasErrors = true;

        continue;
    }

    if ($patched === $content) {
        echo "patch-filepond-ka: already patched — {$target}\n";

        continue;
    }

    if (file_put_contents($target, $patched) === false) {
        echo "patch-filepond-ka: ERROR — could not write {$target}\n";
        $hasErrors = true;

        continue;
    }

    echo "patch-filepond-ka: Georgian locale injected — {$target}\n";
}

exit($hasErrors ? 1 : 0);
