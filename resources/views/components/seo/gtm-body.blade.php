@php
    $gtmId = app(\App\Settings\GeneralSettings::class)->gtm_id;
@endphp

@if ($gtmId)
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
    </noscript>
@endif
