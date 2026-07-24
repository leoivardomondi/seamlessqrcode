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

defined('ALTUMCODE') || die();

ini_set('display_errors', 'Off');

/* Error handlers */
function altumcode_shutdown_handler() {
    $last_error = error_get_last();

    if($last_error && ($last_error['type'] === E_ERROR || $last_error['type'] === E_CORE_ERROR || $last_error['type'] === E_PARSE || $last_error['type'] === E_COMPILE_ERROR)) {
        $reference = 'fatal-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
        $logs_directory = ROOT_PATH . 'logs';

        if(!is_dir($logs_directory)) {
            @mkdir($logs_directory, 0755, true);
        }

        @file_put_contents($logs_directory . '/php-errors.log', json_encode([
            'reference' => $reference,
            'datetime' => date('c'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'error' => $last_error,
        ], JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);

        error_log('[Fatal] ' . $reference . ' ' . ($last_error['message'] ?? 'Unknown fatal error'));

        $is_ajax_request = (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && mb_strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            || str_contains($_SERVER['REQUEST_URI'] ?? '', 'link-ajax')
        );

        if($is_ajax_request) {
            if(!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
            }

            echo json_encode([
                'message' => ['We could not perform this action. Reference: ' . $reference],
                'status' => 'error',
                'details' => ['reference' => $reference],
            ]);
            die();
        }

        echo <<<ALTUM

<style>
    html {
        background: #161538;
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
        width: 100%;
        height: 100%;
        color: #eeeeee;
    }
    body {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    a {
        color: #c3beff;
        text-decoration: none;
    }
    .text-white {
        color: white;
    }
    .altumcode-logo {
        width: 3rem;
        height: auto;
    }
    .buttons {
        margin-top: 1.5rem;
    }
</style>

ALTUM;

        echo '<div>';
        echo '<h1 class="text-white">Internal server error</h1>';
        echo '<p>Our website is having some issues right now.</p>';
        echo '<p>We are actively working on fixing the issue.</p>';
        echo '</div>';
        die();

    }
}

/* Register error handlers */
register_shutdown_function('altumcode_shutdown_handler');
