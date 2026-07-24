<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum\Controllers;

use Altum\Models\Domain;
use Altum\Response;

defined('ALTUMCODE') || die();

class Index extends Controller {

    public function index() {

        /* Custom index redirect if set */
        if(!empty(settings()->main->index_url)) {
            header('Location: ' . settings()->main->index_url); die();
        }

        /* Plans View */
        $view = new \Altum\View('partials/plans', (array) $this);
        $this->add_view_content('plans', $view->run());

        $landing_stats = $this->get_landing_stats();

        /* Check if the cache exists */
        $cache_instance = cache()->getItem('index_stats');

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            $total_users = database()->query("SELECT COUNT(`user_id`) AS `total` FROM `users`")->fetch_object()->total ?? 0;
            $total_links = database()->query("SELECT COUNT(`link_id`) AS `total` FROM `links`")->fetch_object()->total ?? 0;
            $total_qr_codes = database()->query("SELECT COUNT(`qr_code_id`) AS `total` FROM `qr_codes`")->fetch_object()->total ?? 0;
            $total_track_links = database()->query("
                SELECT
                    (
                        (SELECT COALESCE(SUM(`clicks`), 0) FROM `links`)
                        +
                        (SELECT COALESCE(SUM(`clicks`), 0) FROM `biolinks_blocks`)
                    ) AS `total`
            ")->fetch_object()->total ?? 0;
            if(\Altum\Plugin::is_active('aix')) {
                if(settings()->aix->documents_is_enabled) {
                    $total_documents = database()->query("SELECT COUNT(`document_id`) AS `total` FROM `documents`")->fetch_object()->total ?? 0;
                }

                if(settings()->aix->images_is_enabled && settings()->aix->images_display_latest_on_index) {
                    $total_images = database()->query("SELECT COUNT(`image_id`) AS `total` FROM `images`")->fetch_object()->total ?? 0;
                    $images = db()->orderBy('image_id', 'DESC')->get('images', 16);
                }
            }
            $stats = [
                'total_users' => $total_users,
                'total_links' => $total_links,
                'total_qr_codes' => $total_qr_codes,
                'total_track_links' => $total_track_links,
                'total_documents' => $total_documents ?? null,
                'total_images' => $total_images ?? null,
                'images' => $images ?? [],
            ];

            /* Save to cache */
            cache()->save($cache_instance->set($stats)->expiresAfter(3600));

        } else {

            /* Get cache */
            $stats = $cache_instance->get();
            extract($stats);

        }

        if(settings()->main->display_index_latest_blog_posts) {
            $language = \Altum\Language::$name;

            /* Blog posts query */
            $blog_posts_result_query = "
                SELECT * 
                FROM `blog_posts`
                WHERE (`language` = '{$language}' OR `language` IS NULL) AND `is_published` = 1 
                ORDER BY `blog_post_id` DESC
                LIMIT 3
            ";

            $blog_posts = \Altum\Cache::cache_function_result('blog_posts?hash=' . md5($blog_posts_result_query), 'blog_posts', function() use ($blog_posts_result_query) {
                $blog_posts_result = database()->query($blog_posts_result_query);

                /* Iterate over the blog posts */
                $blog_posts = [];

                while($row = $blog_posts_result->fetch_object()) {
                    /* Transform content if needed */
                    $row->content = json_decode($row->content) ? convert_editorjs_json_to_html($row->content) : nl2br($row->content);

                    $blog_posts[] = $row;
                }

                return $blog_posts;
            });
        }

        $tools_categories = require APP_PATH . 'includes/tools/categories.php';
        $enabled_tools = count(array_filter((array) settings()->tools->available_tools));

        /* Get the available domains to use */
        $domains = (new Domain())->get_available_additional_domains();

        /* Main View */
        $view = new \Altum\View('index/index', (array) $this);
        $this->add_view_content('content', $view->run([
            'total_users' => $total_users,
            'total_links' => $landing_stats['total_links'],
            'total_qr_codes' => $landing_stats['total_qr_codes'],
            'total_track_links' => $landing_stats['total_track_links'],
            'total_flipbooks' => $landing_stats['total_flipbooks'],
            'total_documents' => $total_documents ?? null,
            'total_images' => $total_images ?? null,
            'images' => $images ?? null,
            'blog_posts' => $blog_posts ?? [],
            'tools_categories' => $tools_categories,
            'enabled_tools' => $enabled_tools,
            'domains' => $domains,
        ]));

    }

    public function stats_ajax() {

        if(!empty($_POST)) {
            redirect();
        }

        Response::json('', 'success', $this->get_landing_stats(true));
    }

    private function get_landing_stats($refresh_flipbooks = false) {

        $total_links = database()->query("SELECT COUNT(`link_id`) AS `total` FROM `links`")->fetch_object()->total ?? 0;
        $total_qr_codes = database()->query("SELECT COUNT(`qr_code_id`) AS `total` FROM `qr_codes`")->fetch_object()->total ?? 0;
        $total_track_links = database()->query("
            SELECT
                (
                    (SELECT COALESCE(SUM(`clicks`), 0) FROM `links`)
                    +
                    (SELECT COALESCE(SUM(`clicks`), 0) FROM `biolinks_blocks`)
                ) AS `total`
        ")->fetch_object()->total ?? 0;

        return [
            'total_links' => (int) $total_links,
            'total_qr_codes' => (int) $total_qr_codes,
            'total_track_links' => (int) $total_track_links,
            'total_flipbooks' => $this->get_public_flipbooks_total($refresh_flipbooks),
        ];
    }

    private function get_public_flipbooks_total($refresh = false) {

        $cache_instance = cache()->getItem('index_flipbook_stats');
        $cached_count = $cache_instance->get();

        if(!$refresh && !is_null($cached_count)) {
            return (int) $cached_count;
        }

        $count = $this->request_public_flipbooks_total();

        if(!is_null($count)) {
            cache()->save($cache_instance->set((int) $count)->expiresAfter(60));

            return (int) $count;
        }

        return !is_null($cached_count) ? (int) $cached_count : 0;
    }

    private function request_public_flipbooks_total() {

        $endpoints = [
            'https://flipbook.smqr.link/api/stats/flipbooks',
            'https://flipbook.smqr.link/api/stats',
            'https://flipbook.smqr.link/api/flipbooks',
        ];

        foreach($endpoints as $endpoint) {
            $request = $this->perform_public_flipbook_request($endpoint);

            if($request['status'] === 'error' || $request['response_code'] >= 400) {
                continue;
            }

            $response = json_decode($request['response_body'], true);

            if(!is_array($response)) {
                continue;
            }

            $count = $this->normalize_public_flipbook_count($response);

            if(!is_null($count)) {
                return $count;
            }
        }

        return null;
    }

    private function perform_public_flipbook_request($endpoint) {

        $response_body = null;
        $response_code = 0;

        if(function_exists('curl_init')) {
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                ],
                CURLOPT_USERAGENT => 'SeamlessQRCode Landing Stats',
            ]);

            $response_body = curl_exec($ch);
            $response_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if($response_body === false) {
                return [
                    'status' => 'error',
                    'response_body' => null,
                    'response_code' => $response_code,
                ];
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Accept: application/json\r\nUser-Agent: SeamlessQRCode Landing Stats\r\n",
                    'timeout' => 5,
                    'ignore_errors' => true,
                ],
            ]);

            $response_body = @file_get_contents($endpoint, false, $context);

            if(isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
                $response_code = (int) $matches[1];
            }

            if($response_body === false) {
                return [
                    'status' => 'error',
                    'response_body' => null,
                    'response_code' => $response_code,
                ];
            }
        }

        return [
            'status' => 'success',
            'response_body' => $response_body,
            'response_code' => $response_code,
        ];
    }

    private function normalize_public_flipbook_count($response) {

        foreach(['flipbooks', 'total_flipbooks', 'total', 'count'] as $key) {
            if(isset($response[$key]) && is_numeric($response[$key])) {
                return max(0, (int) $response[$key]);
            }
        }

        if(isset($response['data']) && is_array($response['data'])) {
            foreach(['flipbooks', 'total_flipbooks', 'total', 'count'] as $key) {
                if(isset($response['data'][$key]) && is_numeric($response['data'][$key])) {
                    return max(0, (int) $response['data'][$key]);
                }
            }

            if(array_is_list($response['data'])) {
                return count($response['data']);
            }
        }

        if(isset($response['items']) && is_array($response['items'])) {
            return count($response['items']);
        }

        if(array_is_list($response)) {
            return count($response);
        }

        return null;
    }

}
