<?php

/* Load all the related plugin files */
require_once \Altum\Plugin::get('pwa')->path . 'Pwa.php';

/* Functions */
if(!function_exists('pwa_get_relative_manifest_url')) {
    function pwa_get_relative_manifest_url($url) {
        if(!$url) {
            return $url;
        }

        $site_url = parse_url(SITE_URL);
        $parsed_url = parse_url($url);

        if($parsed_url === false) {
            return $url;
        }

        if(isset($parsed_url['host'])) {
            if(!isset($site_url['host']) || mb_strtolower($parsed_url['host']) != mb_strtolower($site_url['host'])) {
                return $url;
            }
        } else if(!isset($url[0]) || $url[0] != '/') {
            return $url;
        }

        $site_path = $site_url['path'] ?? '/';
        $manifest_directory = '/' . trim($site_path, '/') . '/' . UPLOADS_URL_PATH . \Altum\Uploads::get_path('pwa');
        $manifest_directory = preg_replace('#/+#', '/', $manifest_directory);
        $target_path = preg_replace('#/+#', '/', $parsed_url['path'] ?? '/');

        $from_segments = array_values(array_filter(explode('/', trim($manifest_directory, '/')), 'strlen'));
        $to_segments = array_values(array_filter(explode('/', trim($target_path, '/')), 'strlen'));

        while(count($from_segments) && count($to_segments) && $from_segments[0] == $to_segments[0]) {
            array_shift($from_segments);
            array_shift($to_segments);
        }

        $relative_url = str_repeat('../', count($from_segments)) . implode('/', $to_segments);
        $relative_url = $relative_url ?: './';

        if(isset($parsed_url['query'])) {
            $relative_url .= '?' . $parsed_url['query'];
        }

        if(isset($parsed_url['fragment'])) {
            $relative_url .= '#' . $parsed_url['fragment'];
        }

        return $relative_url;
    }
}

if(!function_exists('pwa_generate_manifest')) {
    function pwa_generate_manifest($options = []) {
        $manifest = [
            'id' => md5(SITE_URL),
            'start_url' => pwa_get_relative_manifest_url($options['start_url'] ?? SITE_URL),
            'scope' => pwa_get_relative_manifest_url(SITE_URL),
            'name' => $options['name'],
            'short_name' => $options['short_name'],
            'description' => $options['description'],
            'theme_color' => $options['theme_color'],
            'background_color' => $options['theme_color'],
            'display' => 'standalone',
            'orientation' => 'any',
            'icons' => [],
            'screenshots' => [],
            'categories' => ['utilities'],
            'dir' => 'auto',
            'lang' => \Altum\Language::$default_code,
        ];

        if($options['app_icon_url']) {
            $manifest['icons'][] = [
                'src' => pwa_get_relative_manifest_url($options['app_icon_url']),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any'
            ];
        }

        if($options['app_icon_maskable_url']) {
            $manifest['icons'][] = [
                'src' => pwa_get_relative_manifest_url($options['app_icon_maskable_url']),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ];
        }

        if(count($options['mobile_screenshots'])) {
            foreach($options['mobile_screenshots'] as $screenshot_url) {
                $info = getimagesize($screenshot_url);

                $manifest['screenshots'][] = [
                    'src' => pwa_get_relative_manifest_url($screenshot_url),
                    'sizes' => $info[0] . 'x' . $info[1],
                    'type' => $info['mime'],
                    'form_factor' => 'narrow'
                ];
            }
        }

        if(count($options['desktop_screenshots'])) {
            foreach($options['desktop_screenshots'] as $screenshot_url) {
                $info = getimagesize($screenshot_url);

                $manifest['screenshots'][] = [
                    'src' => pwa_get_relative_manifest_url($screenshot_url),
                    'sizes' => $info[0] . 'x' . $info[1],
                    'type' => $info['mime'],
                    'form_factor' => 'wide'
                ];
            }
        }

        if(count($options['shortcuts'])) {
            foreach($options['shortcuts'] as $shortcut) {
                if(!empty($shortcut['name'])) {
                    $manifest['shortcuts'][] = [
                        'name' => $shortcut['name'],
                        'description' => $shortcut['description'],
                        'url' => pwa_get_relative_manifest_url($shortcut['url']),
                        'icons' => [[
                            'src' => pwa_get_relative_manifest_url($shortcut['icon_url']),
                            'sizes' => '192x192',
                        ]]
                    ];
                }
            }
        }

        return json_encode($manifest, JSON_UNESCAPED_SLASHES);
    }
}

if(!function_exists('pwa_save_manifest')) {
    function pwa_save_manifest($manifest_content, $file_name = 'manifest') {
        $file_name = preg_replace('/[^A-Za-z0-9_-]/', '', $file_name) ?: 'manifest';

        file_put_contents(\Altum\Uploads::get_full_path('pwa') . $file_name . '.json', $manifest_content);
    }
}
