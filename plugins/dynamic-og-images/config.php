<?php
defined('ALTUMCODE') || die();

return (object) [
    'plugin_id' => 'dynamic-og-images',
    'name' => 'Dynamic OG images',
    'description' => 'This plugin is used to generate dynamic OG images for a personalized image presentation on social media when sharing your links.',
    'version' => '1.0.0',
    'url' => 'https://seamlessqrcode.com/plugins/dynamic-og-images',
    'author' => 'Leoivard',
    'author_url' => 'https://seamlessqrcode.com/',
    'status' => 'inexistent',
    'actions'=> true,
    'settings_url' => url('admin/settings/dynamic_og_images'),
    'avatar_style' => 'background-color: #8EC5FC;background-image: linear-gradient(62deg, #8EC5FC 0%, #E0C3FC 100%);',
    'icon' => '🌠',
];

