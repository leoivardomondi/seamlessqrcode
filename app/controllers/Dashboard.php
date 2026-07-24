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

class Dashboard extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type'], ['url', 'location_url'], ['link_id', 'last_datetime', 'datetime', 'clicks', 'url']));
        $filters->set_default_order_by($this->user->preferences->links_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = \Altum\Cache::cache_function_result('links_total?user_id=' . $this->user->user_id, null, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('links', 'count(*)');
        });
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('links?' . $filters->get_get() . '&page=%d')));

        /* Get domains */
        $domains = (new Domain())->get_available_domains_by_user($this->user);

        /* Get the links list for the project */
        $links_result = database()->query("
            SELECT 
                *
            FROM 
                `links`
            WHERE 
                `user_id` = {$this->user->user_id}
            {$filters->get_sql_order_by()}
            {$paginator->get_sql_limit()}
        ");

        /* Iterate over the links */
        $links = [];

        while($row = $links_result->fetch_object()) {
            $row->full_url = $row->domain_id && isset($domains[$row->domain_id]) ? $domains[$row->domain_id]->scheme . $domains[$row->domain_id]->host . '/' . ($domains[$row->domain_id]->link_id == $row->link_id ? null : $row->url) : SITE_URL . $row->url;

            /* Static links need the / for proper asset pathing */
            if($row->type == 'static') {
                $row->full_url .= '/';
            }

            $row->settings = json_decode($row->settings);

            $links[] = $row;
        }

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get statistics */
        if(count($links)) {
            $links_chart = [];
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');
            $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d');

            $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

            $track_links_result_query = "
                SELECT
                    COUNT(`id`) AS `pageviews`,
                    SUM(`is_unique`) AS `visitors`,
                    DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
                FROM
                    `track_links`
                WHERE   
                    `user_id` = {$this->user->user_id} 
                    AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`
                ORDER BY
                    `formatted_date`
            ";

            $links_chart = \Altum\Cache::cache_function_result('track_links?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() use ($track_links_result_query) {
                $links_chart = [];

                $track_links_result = database()->query($track_links_result_query);

                /* Generate the raw chart data and save logs for later usage */
                while($row = $track_links_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);

                    $links_chart[$label] = [
                        'pageviews' => $row->pageviews,
                        'visitors' => $row->visitors
                    ];
                }

                return $links_chart;
            }, 60 * 60 * settings()->main->chart_cache ?? 12);

            $links_chart = get_chart_data($links_chart);
        }

        /* Some statistics for the widgets */
                /* START of new code block */
        if(settings()->codes->qr_codes_is_enabled) {
            $qr_codes_total = \Altum\Cache::cache_function_result('qr_codes_total?user_id=' . $this->user->user_id, null, function() {
                return db()->where('user_id', $this->user->user_id)->getValue('qr_codes', 'count(*)');
            });
        }
        /* END of new code block */
        
        if(settings()->links->shortener_is_enabled) {
            $link_links_total = \Altum\Cache::cache_function_result('link_links_total?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() {
                return db()->where('user_id', $this->user->user_id)->where('type', 'link')->getValue('links', 'count(*)');
            });
        }

        if(settings()->links->files_is_enabled) {
            $file_links_total = \Altum\Cache::cache_function_result('file_links_total?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() {
                return db()->where('user_id', $this->user->user_id)->where('type', 'file')->getValue('links', 'count(*)');
            });
        }

        if(settings()->links->vcards_is_enabled) {
            $vcard_links_total = \Altum\Cache::cache_function_result('vcard_links_total?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() {
                return db()->where('user_id', $this->user->user_id)->where('type', 'vcard')->getValue('links', 'count(*)');
            });
        }

        if(settings()->links->biolinks_is_enabled) {
            $biolink_links_total = \Altum\Cache::cache_function_result('biolink_links_total?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() {
                return db()->where('user_id', $this->user->user_id)->where('type', 'biolink')->getValue('links', 'count(*)');
            });
        }

        if(settings()->links->events_is_enabled) {
            $event_links_total = \Altum\Cache::cache_function_result('event_links_total?user_id=' . $this->user->user_id, null, function() {
                return db()->where('user_id', $this->user->user_id)->where('type', 'event')->getValue('links', 'count(*)');
            });
        }

        if(settings()->links->static_is_enabled) {
            $static_links_total = \Altum\Cache::cache_function_result('static_links_total?user_id=' . $this->user->user_id, null, function() {
                return db()->where('user_id', $this->user->user_id)->where('type', 'static')->getValue('links', 'count(*)');
            });
        }

        /* Delete Modal */
        $view = new \Altum\View('links/link_delete_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Create Link Modal */
        $domains = (new Domain())->get_available_domains_by_user($this->user);
        $data = [
            'domains' => $domains
        ];

        $view = new \Altum\View('links/create_link_modals', (array) $this);
        \Altum\Event::add_content($view->run($data), 'modals');

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Prepare the Links View */
        $data = [
            'links'             => $links,
            'pagination'        => $pagination,
            'filters'           => $filters,
            'projects'          => $projects,
            'links_types'       => require APP_PATH . 'includes/links_types.php',
        ];
        $view = new \Altum\View('links/links_content', (array) $this);
        $this->add_view_content('links_content', $view->run($data));

        /* Prepare the view */
        $data = [
            'links_chart'               => $links_chart ?? null,

            /* Widgets stats */
            'event_links_total'         => $event_links_total ?? null,
            'static_links_total'        => $static_links_total ?? null,
            'vcard_links_total'         => $vcard_links_total ?? null,
            'link_links_total'          => $link_links_total ?? null,
            'file_links_total'          => $file_links_total ?? null,
            'biolink_links_total'       => $biolink_links_total ?? null,
            /* START of new code block */
            'qr_codes_total'            => $qr_codes_total ?? null,
            /* END of new code block */
        ];

        $view = new \Altum\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_flipbook_stats_ajax() {

        \Altum\Authentication::guard();

        if(!empty($_POST)) {
            redirect('dashboard');
        }

        $result = $this->get_flipbook_stats_data();

        if($result['status'] === 'success') {
            Response::json('', 'success', $result['data']);
        }

        Response::json($result['message'], 'error');
    }

    private function get_flipbook_stats_data() {
        $cache_instance = cache()->getItem('flipbook_stats?user_id=' . $this->user->user_id);
        $cached_result = $cache_instance->get();

        if(!is_null($cached_result)) {
            return $cached_result;
        }

        $result = $this->request_flipbook_stats();

        if($result['status'] === 'success') {
            cache()->save(
                $cache_instance
                    ->set($result)
                    ->expiresAfter(60)
                    ->addTag('user_id=' . $this->user->user_id)
            );
        }

        return $result;
    }

    private function request_flipbook_stats() {
        $endpoints = [];

        if(!empty($this->user->email)) {
            $endpoints[] = 'https://flipbook.smqr.link/api/stats/user-flipbooks?' . http_build_query([
                'email' => $this->user->email,
            ]);
        }

        $endpoints[] = 'https://flipbook.smqr.link/api/flipbooks?' . http_build_query([
            'userId' => $this->user->user_id,
        ]);

        foreach($endpoints as $endpoint) {
            $request = $this->perform_flipbook_request($endpoint);

            if($request['status'] === 'error' || $request['response_code'] === 404) {
                continue;
            }

            $response = json_decode($request['response_body'], true);

            if(!is_array($response)) {
                continue;
            }

            if($request['response_code'] >= 400) {
                continue;
            }

            return [
                'status' => 'success',
                'data' => $this->normalize_flipbook_stats($response),
            ];
        }

        return [
            'status' => 'success',
            'data' => $this->normalize_flipbook_stats([]),
        ];
    }

    private function perform_flipbook_request($endpoint) {
        $response_body = null;
        $response_code = 0;

        if(function_exists('curl_init')) {
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                ],
                CURLOPT_USERAGENT => 'SeamlessQRCode Dashboard Flipbook Card',
            ]);

            $response_body = curl_exec($ch);
            $response_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);

            curl_close($ch);

            if($response_body === false) {
                return [
                    'status' => 'error',
                    'response_body' => null,
                    'response_code' => $response_code,
                    'message' => $curl_error ?: 'Unable to load flipbook statistics.',
                ];
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Accept: application/json\r\nUser-Agent: SeamlessQRCode Dashboard Flipbook Card\r\n",
                    'timeout' => 12,
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
                    'message' => 'Unable to load flipbook statistics.',
                ];
            }
        }

        return [
            'status' => 'success',
            'response_body' => $response_body,
            'response_code' => $response_code,
            'message' => null,
        ];
    }

    private function normalize_flipbook_stats($response) {
        $recent_flipbooks = [];

        if(isset($response['flipbooks']) && is_numeric($response['flipbooks'])) {
            $flipbooks = max(0, (int) $response['flipbooks']);
        } else if(isset($response['data']) && is_array($response['data'])) {
            $flipbooks = count($response['data']);
        } else if(isset($response['items']) && is_array($response['items'])) {
            $flipbooks = count($response['items']);
        } else if(array_is_list($response)) {
            $flipbooks = count($response);
        } else {
            $flipbooks = 0;
        }

        foreach(array_slice($response['recent_flipbooks'] ?? [], 0, 3) as $recent_flipbook) {
            if(!is_array($recent_flipbook)) {
                continue;
            }

            $recent_flipbooks[] = [
                'id' => $recent_flipbook['id'] ?? null,
                'name' => trim($recent_flipbook['name'] ?? '') ?: 'Untitled flipbook',
                'view_count' => (int) ($recent_flipbook['view_count'] ?? 0),
                'created_at' => $recent_flipbook['created_at'] ?? null,
            ];
        }

        return [
            'email' => $response['email'] ?? $this->user->email,
            'flipbooks' => $flipbooks,
            'total_views' => max(0, (int) ($response['total_views'] ?? 0)),
            'recent_flipbooks' => $recent_flipbooks,
        ];
    }

}
