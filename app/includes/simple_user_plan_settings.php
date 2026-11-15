<?php
/*
 * Copyright (c) 2025 leoivard
 */

defined('ALTUMCODE') || die();

$features = [
    'custom_url',
    'deep_links',
    'removable_branding',
];

if(settings()->links->biolinks_is_enabled) {
    $features = array_merge($features, [
        'custom_branding',
        'dofollow_is_enabled',
		'branded_button_is_enabled',
        'leap_link',
        'seo',
        'fonts',
        'custom_css_is_enabled',
        'custom_js_is_enabled',
    ]);
}

$features = array_merge($features, [
    'statistics',
    'temporary_url_is_enabled',
    'cloaking_is_enabled',
    'app_linking_is_enabled',
    'targeting_is_enabled',
    'utm',
    'password',
    'sensitive_content',
    'no_ads',
]);

if(settings()->main->api_is_enabled) {
    $features = array_merge($features, [
        'api_is_enabled',
    ]);
}

if(settings()->main->white_labeling_is_enabled) {
    $features = array_merge($features, [
        'white_labeling_is_enabled',
    ]);
}

if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled) {
    $features = array_merge($features, [
        'custom_pwa_is_enabled',
    ]);
}

return $features;

