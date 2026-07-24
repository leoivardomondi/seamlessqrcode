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

function get_custom_image_if_any($image_key) {
    $image_key_id = str_replace('.', '_', get_slug($image_key));

    if(!empty(settings()->custom_images->{$image_key_id})) {
        return \Altum\Uploads::get_full_url('custom_images') . settings()->custom_images->{$image_key_id};
    } else {
        return ASSETS_FULL_URL . 'images/' . $image_key;
    }
}

function output_alert($type, $message, $icon = true, $dismissable = true) {

    switch($type) {
        case 'error':
            $alert_type = 'danger';
            $icon = $icon ? '<i class="fas fa-fw fa-times-circle text-' . $alert_type . ' mr-2"></i>' : null;
            break;

        case 'success':
            $alert_type = 'success';
            $icon = $icon ? '<i class="fas fa-fw fa-check-circle text-' . $alert_type . ' mr-2"></i>' : null;
            break;

        case 'info':
            $alert_type = 'info';
            $icon = $icon ? '<i class="fas fa-fw fa-info-circle text-' . $alert_type . ' mr-2"></i>' : null;
            break;

        case 'warning':
            $alert_type = 'warning';
            $icon = $icon ? '<i class="fas fa-fw fa-triangle-exclamation text-' . $alert_type . ' mr-2"></i>' : null;
            break;
    }

    $dismiss_button = $dismissable ? '<button type="button" class="close ml-2" data-dismiss="alert"><i class="fas fa-fw fa-sm fa-times text-' . $alert_type . '"></i></button>' : null;

    return '
        <div class="alert alert-' . $alert_type . ' altum-animate altum-animate-fill-both altum-animate-fade-in">
            ' . $icon . '
            ' . $dismiss_button . '
            ' . $message . '
        </div>
    ';
}

/* Aws functions */
function get_aws_s3_config() {
    $aws_s3_config = [
        'region' => settings()->offload->region ?: 'us-east-1',
        'version' => 'latest',
        'credentials' => [
            'key' => settings()->offload->access_key,
            'secret' => settings()->offload->secret_access_key,
        ],
        'http' => [
            'defaultOptions' => [
                'verify' => true,
                'curl' => [
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2
                ]
            ]
        ],
    ];

    if(settings()->offload->provider != 'aws-s3') {
        $aws_s3_config['endpoint'] = settings()->offload->endpoint_url;
    }

    return $aws_s3_config;
}

/* Generate chart data for based on the date key and each of keys inside */
function get_chart_data(Array $main_array) {

    $results = [];

    foreach($main_array as $date_label => $data) {

        foreach($data as $label_key => $label_value) {

            if(!isset($results[$label_key])) {
                $results[$label_key] = [];
            }

            $results[$label_key][] = $label_value;

        }

    }

    foreach($results as $key => $value) {
        $results[$key] = '["' . implode('", "', $value) . '"]';
    }

    $results['labels'] = '["' . implode('", "', array_keys($main_array)) . '"]';

    $results['is_empty'] = count($results) > 1 ? false : true;

    return $results;
}

function get_user_avatar($avatar, $email) {
    return $avatar ? \Altum\Uploads::get_full_url('users') . $avatar : get_gravatar($email);
}

function get_gravatar($email, $size = 80, $d = 'identicon', $rating = 'g') {
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5(mb_strtolower(trim($email ?? '')));
    $url .= "?s=$size&d=$d&r=$rating";

    return $url;
}

function get_favicon_url_from_domain($domain) {
    return sprintf('https://external-content.duckduckgo.com/ip3/%s.ico', $domain);
}

/* Helper to output proper and nice numbers */
function nr($number, $decimals = 0, $display_decimals_if_zero = true, $extra = false) {

    if($extra) {
        $formatted_number = $number;
        $touched = false;

        if(!$touched && (!is_array($extra) || (is_array($extra) && in_array('B', $extra)))) {

            if($number > 999999999) {
                $formatted_number = number_format($number / 1000000000, $decimals, l('global.number.decimal_point'), l('global.number.thousands_separator')) . 'B';

                $touched = true;
            }

        }

        if(!$touched && (!is_array($extra) || (is_array($extra) && in_array('M', $extra)))) {

            if($number > 999999) {
                $formatted_number = number_format($number / 1000000, $decimals, l('global.number.decimal_point'), l('global.number.thousands_separator')) . 'M';

                $touched = true;
            }

        }

        if(!$touched && (!is_array($extra) || (is_array($extra) && in_array('K', $extra)))) {

            if($number > 999) {
                $formatted_number = number_format($number / 1000, $decimals, l('global.number.decimal_point'), l('global.number.thousands_separator')) . 'K';

                $touched = true;
            }

        }

        if($decimals > 0) {
            $dotzero = '.' . str_repeat('0', $decimals);
            $formatted_number = str_replace($dotzero, '', $formatted_number);
        }

        return $formatted_number;
    }

    if($number == 0) {
        return 0;
    }

    if(!$display_decimals_if_zero && $decimals > 0) {
        $decimals = floor($number) != $number ? $decimals : 0;
    }

    return number_format($number, $decimals, l('global.number.decimal_point'), l('global.number.thousands_separator'));
}

function get_maxmind_reader_country() {
    static $cached = null;

    if($cached !== null) {
        return $cached;
    }

    return $cached = (new \MaxMind\Db\Reader(APP_PATH . 'includes/GeoLite2-Country.mmdb'));
}

function get_maxmind_reader_city() {
    static $cached = null;

    if($cached !== null) {
        return $cached;
    }

    return $cached = (new \MaxMind\Db\Reader(APP_PATH . 'includes/GeoLite2-City.mmdb'));
}

function get_ip() {
    static $cached_ip_address = null;

    /* return cached IP address if already determined */
    if($cached_ip_address !== null) {
        return $cached_ip_address;
    }

    /* list of server keys to check for IP */
    $ip_sources = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];

    foreach ($ip_sources as $server_key) {
        if(!empty($_SERVER[$server_key])) {
            $ip_value = $_SERVER[$server_key];

            /* handle multiple IPs from X_FORWARDED_FOR */
            if($server_key === 'HTTP_X_FORWARDED_FOR') {
                $ip_parts = explode(',', $ip_value);
                $ip_value = trim(reset($ip_parts));
            }

            /* validate and assign */
            if(filter_var($ip_value, FILTER_VALIDATE_IP)) {
                $cached_ip_address = $ip_value;
                return $cached_ip_address;
            }
        }
    }

    /* fallback if no valid IP found */
    return null;
}

function get_this_device_type() {
    static $cached_device_type = null;

    /* return cached IP address if already determined */
    if($cached_device_type !== null) {
        return $cached_device_type;
    }

    return $cached_device_type = get_device_type($_SERVER['HTTP_USER_AGENT']);
}

function get_device_type($user_agent) {
    /* normalize user agent */
    $normalized_user_agent = strtolower(trim($user_agent));

    /* regular expressions */
    $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot).*mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
    $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?!.*mobile))/i';

    /* detect device type */
    if (preg_match($mobile_regex, $normalized_user_agent)) {
        return 'mobile';
    }

    if (preg_match($tablet_regex, $normalized_user_agent)) {
        return 'tablet';
    }

    return 'desktop';
}

function process_export_json($array_of_objects, $type = '', $type_array = [], $file_name = 'data') {

    if(isset($_GET['export']) && $_GET['export'] == 'json') {
        //ALTUMCODE:DEMO if(DEMO) exit('This command is blocked on the demo.');

        if(\Altum\Title::get()) $file_name = \Altum\Title::get();
        header('Content-Disposition: attachment; filename="' . $file_name . '.json";');
        header('Content-Type: application/json; charset=UTF-8');

        $json = json_exporter($array_of_objects, $type, $type_array);

        die($json);
    }

}

function json_exporter($array_of_objects, $type = 'basic', $type_array = []) {

    foreach($array_of_objects as $object) {

        foreach($object as $key => $value) {

            if(($type == 'exclude' && in_array($key, $type_array)) || ($type == 'include' && !in_array($key, $type_array))) {
                unset($object->{$key});
            }

        }

    }

    return json_encode($array_of_objects);
}

function process_export_csv($array, $type = '', $type_array = [], $file_name = 'data') {

    if(isset($_GET['export']) && $_GET['export'] == 'csv') {
        //ALTUMCODE:DEMO if(DEMO) exit('This command is blocked on the demo.');

        if(\Altum\Title::get()) $file_name = \Altum\Title::get();
        header('Content-Disposition: attachment; filename="' . $file_name . '.csv";');
        header('Content-Type: application/csv; charset=UTF-8');

        $csv = csv_exporter($array, $type, $type_array);

        die($csv);
    }

}

function csv_exporter($array, $type = 'basic', $type_array = []) {

    $result = '';

    /* Export the header */
    $headers = [];
    foreach(array_keys((array) reset($array)) as $value) {
        /* Check if not excluded */
        if(($type == 'exclude' && !in_array($value, $type_array)) || ($type == 'include' && in_array($value, $type_array)) || $type == 'basic') {
            $headers[] = '"' . $value . '"';
        }
    }

    $result .= implode(',', $headers);

    /* Data */
    foreach($array as $row) {
        $result .= "\n";

        $row_array = [];

        foreach($row as $key => $value) {
            /* Check if not excluded */
            if(($type == 'exclude' && !in_array($key, $type_array)) || ($type == 'include' && in_array($key, $type_array)) || $type == 'basic') {
                $row_array[] = '"' . addslashes($value ?? '') . '"';
            }
        }

        $result .= implode(',', $row_array);
    }

    return $result;
}

function csv_link_exporter($csv) {
    return 'data:application/csv;charset=utf-8,' . urlencode($csv);
}

function get_zero_decimal_currencies_array() {
    return [
        'JPY',
        'KRW',
        'VND',
        'VUV',
        'KMF',
        'DJF',
        'XOF',
        'XPF',
        'GNF',
        'RWF',
        'UGX',
        'CLP',
        'PYG',
        'MGA',
        'BIF',
        'XAF',
    ];
}
function get_continents_array() {
    return [
        'AF' => '🌍 Africa',
        'AN' => '🧊 Antarctica',
        'AS' => '🌏 Asia',
        'EU' => '🌍 Europe',
        'NA' => '🌎 North America',
        'OC' => '🌊 Oceania',
        'SA' => '🌎 South America',
    ];
}

function get_continents_no_emoji_array() {
    return [
        'AF' => 'Africa',
        'AN' => 'Antarctica',
        'AS' => 'Asia',
        'EU' => 'Europe',
        'NA' => 'North America',
        'OC' => 'Oceania',
        'SA' => 'South America',
    ];
}

function get_continent_from_continent_code($code) {
    return get_continents_array()[mb_strtoupper($code ?? '')] ?? $code;
}

function get_countries_no_emoji_array() {
    return [
        'AF' => 'Afghanistan',
        'AX' => 'Åland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua & Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BQ' => 'Caribbean NL',  // Caribbean Netherlands (Bonaire, Sint Eustatius & Saba)
        'BA' => 'Bosnia & Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean',
        'BN' => 'Brunei',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Rep.',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo',
        'CD' => 'DR Congo',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => "Côte d'Ivoire",
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CW' => 'Curaçao',
        'CY' => 'Cyprus',
        'CZ' => 'Czechia',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Rep.',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French S. Terr.',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard & McDonald Is.',
        'VA' => 'Holy See',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'North Korea',
        'KR' => 'South Korea',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Laos',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macau',
        'MK' => 'North Macedonia',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'N. Mariana Is.',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestine',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RU' => 'Russia',
        'RW' => 'Rwanda',
        'BL' => 'St. Barth',
        'SH' => 'St. Helena, Asc. & Tristan da Cunha',
        'KN' => 'St. Kitts & Nevis',
        'LC' => 'St. Lucia',
        'MF' => 'St. Martin (FR)',
        'PM' => 'St. Pierre & Miquelon',
        'VC' => 'St. Vincent & Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome & Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SX' => 'Sint Maarten (NL)',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'S. Georgia & S. Sandwich Is.',
        'SS' => 'South Sudan',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard & Jan Mayen',
        'SZ' => 'Eswatini',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syria',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad & Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks & Caicos',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'UAE',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UM' => 'US Outlying Is.',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'VG' => 'British Virgin Is.',
        'VI' => 'US Virgin Is.',
        'WF' => 'Wallis & Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
    ];
}

function get_countries_array() {
    return [
        'AF' => '🇦🇫 Afghanistan',
        'AX' => '🇦🇽 Åland Islands',
        'AL' => '🇦🇱 Albania',
        'DZ' => '🇩🇿 Algeria',
        'AS' => '🇦🇸 American Samoa',
        'AD' => '🇦🇩 Andorra',
        'AO' => '🇦🇴 Angola',
        'AI' => '🇦🇮 Anguilla',
        'AQ' => '🇦🇶 Antarctica',
        'AG' => '🇦🇬 Antigua & Barbuda',
        'AR' => '🇦🇷 Argentina',
        'AM' => '🇦🇲 Armenia',
        'AW' => '🇦🇼 Aruba',
        'AU' => '🇦🇺 Australia',
        'AT' => '🇦🇹 Austria',
        'AZ' => '🇦🇿 Azerbaijan',
        'BS' => '🇧🇸 Bahamas',
        'BH' => '🇧🇭 Bahrain',
        'BD' => '🇧🇩 Bangladesh',
        'BB' => '🇧🇧 Barbados',
        'BY' => '🇧🇾 Belarus',
        'BE' => '🇧🇪 Belgium',
        'BZ' => '🇧🇿 Belize',
        'BJ' => '🇧🇯 Benin',
        'BM' => '🇧🇲 Bermuda',
        'BT' => '🇧🇹 Bhutan',
        'BO' => '🇧🇴 Bolivia',
        'BQ' => '🇧🇶 Caribbean NL',
        'BA' => '🇧🇦 Bosnia & Herzegovina',
        'BW' => '🇧🇼 Botswana',
        'BV' => '🇧🇻 Bouvet Island',
        'BR' => '🇧🇷 Brazil',
        'IO' => '🇮🇴 British Indian Ocean',
        'BN' => '🇧🇳 Brunei',
        'BG' => '🇧🇬 Bulgaria',
        'BF' => '🇧🇫 Burkina Faso',
        'BI' => '🇧🇮 Burundi',
        'KH' => '🇰🇭 Cambodia',
        'CM' => '🇨🇲 Cameroon',
        'CA' => '🇨🇦 Canada',
        'CV' => '🇨🇻 Cape Verde',
        'KY' => '🇰🇾 Cayman Islands',
        'CF' => '🇨🇫 Central African Rep.',
        'TD' => '🇹🇩 Chad',
        'CL' => '🇨🇱 Chile',
        'CN' => '🇨🇳 China',
        'CX' => '🇨🇽 Christmas Island',
        'CC' => '🇨🇨 Cocos Islands',
        'CO' => '🇨🇴 Colombia',
        'KM' => '🇰🇲 Comoros',
        'CG' => '🇨🇬 Congo',
        'CD' => '🇨🇩 DR Congo',
        'CK' => '🇨🇰 Cook Islands',
        'CR' => '🇨🇷 Costa Rica',
        'CI' => '🇨🇮 Côte d\'Ivoire',
        'HR' => '🇭🇷 Croatia',
        'CU' => '🇨🇺 Cuba',
        'CW' => '🇨🇼 Curaçao',
        'CY' => '🇨🇾 Cyprus',
        'CZ' => '🇨🇿 Czechia',
        'DK' => '🇩🇰 Denmark',
        'DJ' => '🇩🇯 Djibouti',
        'DM' => '🇩🇲 Dominica',
        'DO' => '🇩🇴 Dominican Rep.',
        'EC' => '🇪🇨 Ecuador',
        'EG' => '🇪🇬 Egypt',
        'SV' => '🇸🇻 El Salvador',
        'GQ' => '🇬🇶 Equatorial Guinea',
        'ER' => '🇪🇷 Eritrea',
        'EE' => '🇪🇪 Estonia',
        'ET' => '🇪🇹 Ethiopia',
        'FK' => '🇫🇰 Falkland Islands',
        'FO' => '🇫🇴 Faroe Islands',
        'FJ' => '🇫🇯 Fiji',
        'FI' => '🇫🇮 Finland',
        'FR' => '🇫🇷 France',
        'GF' => '🇬🇫 French Guiana',
        'PF' => '🇵🇫 French Polynesia',
        'TF' => '🇹🇫 French S. Terr.',
        'GA' => '🇬🇦 Gabon',
        'GM' => '🇬🇲 Gambia',
        'GE' => '🇬🇪 Georgia',
        'DE' => '🇩🇪 Germany',
        'GH' => '🇬🇭 Ghana',
        'GI' => '🇬🇮 Gibraltar',
        'GR' => '🇬🇷 Greece',
        'GL' => '🇬🇱 Greenland',
        'GD' => '🇬🇩 Grenada',
        'GP' => '🇬🇵 Guadeloupe',
        'GU' => '🇬🇺 Guam',
        'GT' => '🇬🇹 Guatemala',
        'GG' => '🇬🇬 Guernsey',
        'GN' => '🇬🇳 Guinea',
        'GW' => '🇬🇼 Guinea-Bissau',
        'GY' => '🇬🇾 Guyana',
        'HT' => '🇭🇹 Haiti',
        'HM' => '🇭🇲 Heard & McDonald Is.',
        'VA' => '🇻🇦 Holy See',
        'HN' => '🇭🇳 Honduras',
        'HK' => '🇭🇰 Hong Kong',
        'HU' => '🇭🇺 Hungary',
        'IS' => '🇮🇸 Iceland',
        'IN' => '🇮🇳 India',
        'ID' => '🇮🇩 Indonesia',
        'IR' => '🇮🇷 Iran',
        'IQ' => '🇮🇶 Iraq',
        'IE' => '🇮🇪 Ireland',
        'IM' => '🇮🇲 Isle of Man',
        'IL' => '🇮🇱 Israel',
        'IT' => '🇮🇹 Italy',
        'JM' => '🇯🇲 Jamaica',
        'JP' => '🇯🇵 Japan',
        'JE' => '🇯🇪 Jersey',
        'JO' => '🇯🇴 Jordan',
        'KZ' => '🇰🇿 Kazakhstan',
        'KE' => '🇰🇪 Kenya',
        'KI' => '🇰🇮 Kiribati',
        'KP' => '🇰🇵 North Korea',
        'KR' => '🇰🇷 South Korea',
        'KW' => '🇰🇼 Kuwait',
        'KG' => '🇰🇬 Kyrgyzstan',
        'LA' => '🇱🇦 Laos',
        'LV' => '🇱🇻 Latvia',
        'LB' => '🇱🇧 Lebanon',
        'LS' => '🇱🇸 Lesotho',
        'LR' => '🇱🇷 Liberia',
        'LY' => '🇱🇾 Libya',
        'LI' => '🇱🇮 Liechtenstein',
        'LT' => '🇱🇹 Lithuania',
        'LU' => '🇱🇺 Luxembourg',
        'MO' => '🇲🇴 Macau',
        'MK' => '🇲🇰 North Macedonia',
        'MG' => '🇲🇬 Madagascar',
        'MW' => '🇲🇼 Malawi',
        'MY' => '🇲🇾 Malaysia',
        'MV' => '🇲🇻 Maldives',
        'ML' => '🇲🇱 Mali',
        'MT' => '🇲🇹 Malta',
        'MH' => '🇲🇭 Marshall Islands',
        'MQ' => '🇲🇶 Martinique',
        'MR' => '🇲🇷 Mauritania',
        'MU' => '🇲🇺 Mauritius',
        'YT' => '🇾🇹 Mayotte',
        'MX' => '🇲🇽 Mexico',
        'FM' => '🇫🇲 Micronesia',
        'MD' => '🇲🇩 Moldova',
        'MC' => '🇲🇨 Monaco',
        'MN' => '🇲🇳 Mongolia',
        'ME' => '🇲🇪 Montenegro',
        'MS' => '🇲🇸 Montserrat',
        'MA' => '🇲🇦 Morocco',
        'MZ' => '🇲🇿 Mozambique',
        'MM' => '🇲🇲 Myanmar',
        'NA' => '🇳🇦 Namibia',
        'NR' => '🇳🇷 Nauru',
        'NP' => '🇳🇵 Nepal',
        'NL' => '🇳🇱 Netherlands',
        'NC' => '🇳🇨 New Caledonia',
        'NZ' => '🇳🇿 New Zealand',
        'NI' => '🇳🇮 Nicaragua',
        'NE' => '🇳🇪 Niger',
        'NG' => '🇳🇬 Nigeria',
        'NU' => '🇳🇺 Niue',
        'NF' => '🇳🇫 Norfolk Island',
        'MP' => '🇲🇵 N. Mariana Is.',
        'NO' => '🇳🇴 Norway',
        'OM' => '🇴🇲 Oman',
        'PK' => '🇵🇰 Pakistan',
        'PW' => '🇵🇼 Palau',
        'PS' => '🇵🇸 Palestine',
        'PA' => '🇵🇦 Panama',
        'PG' => '🇵🇬 Papua New Guinea',
        'PY' => '🇵🇾 Paraguay',
        'PE' => '🇵🇪 Peru',
        'PH' => '🇵🇭 Philippines',
        'PN' => '🇵🇳 Pitcairn',
        'PL' => '🇵🇱 Poland',
        'PT' => '🇵🇹 Portugal',
        'PR' => '🇵🇷 Puerto Rico',
        'QA' => '🇶🇦 Qatar',
        'RE' => '🇷🇪 Réunion',
        'RO' => '🇷🇴 Romania',
        'RU' => '🇷🇺 Russia',
        'RW' => '🇷🇼 Rwanda',
        'BL' => '🇧🇱 St. Barth',
        'SH' => '🇸🇭 St. Helena, Asc. & Tristan da Cunha',
        'KN' => '🇰🇳 St. Kitts & Nevis',
        'LC' => '🇱🇨 St. Lucia',
        'MF' => '🇲🇫 St. Martin (FR)',
        'PM' => '🇵🇲 St. Pierre & Miquelon',
        'VC' => '🇻🇨 St. Vincent & Grenadines',
        'WS' => '🇼🇸 Samoa',
        'SM' => '🇸🇲 San Marino',
        'ST' => '🇸🇹 Sao Tome & Principe',
        'SA' => '🇸🇦 Saudi Arabia',
        'SN' => '🇸🇳 Senegal',
        'RS' => '🇷🇸 Serbia',
        'SC' => '🇸🇨 Seychelles',
        'SL' => '🇸🇱 Sierra Leone',
        'SG' => '🇸🇬 Singapore',
        'SX' => '🇸🇽 Sint Maarten (NL)',
        'SK' => '🇸🇰 Slovakia',
        'SI' => '🇸🇮 Slovenia',
        'SB' => '🇸🇧 Solomon Islands',
        'SO' => '🇸🇴 Somalia',
        'ZA' => '🇿🇦 South Africa',
        'GS' => '🇬🇸 S. Georgia & S. Sandwich Is.',
        'SS' => '🇸🇸 South Sudan',
        'ES' => '🇪🇸 Spain',
        'LK' => '🇱🇰 Sri Lanka',
        'SD' => '🇸🇩 Sudan',
        'SR' => '🇸🇷 Suriname',
        'SJ' => '🇸🇯 Svalbard & Jan Mayen',
        'SZ' => '🇸🇿 Eswatini',
        'SE' => '🇸🇪 Sweden',
        'CH' => '🇨🇭 Switzerland',
        'SY' => '🇸🇾 Syria',
        'TW' => '🇹🇼 Taiwan',
        'TJ' => '🇹🇯 Tajikistan',
        'TZ' => '🇹🇿 Tanzania',
        'TH' => '🇹🇭 Thailand',
        'TL' => '🇹🇱 Timor-Leste',
        'TG' => '🇹🇬 Togo',
        'TK' => '🇹🇰 Tokelau',
        'TO' => '🇹🇴 Tonga',
        'TT' => '🇹🇹 Trinidad & Tobago',
        'TN' => '🇹🇳 Tunisia',
        'TR' => '🇹🇷 Turkey',
        'TM' => '🇹🇲 Turkmenistan',
        'TC' => '🇹🇨 Turks & Caicos',
        'TV' => '🇹🇻 Tuvalu',
        'UG' => '🇺🇬 Uganda',
        'UA' => '🇺🇦 Ukraine',
        'AE' => '🇦🇪 UAE',
        'GB' => '🇬🇧 United Kingdom',
        'US' => '🇺🇸 United States',
        'UM' => '🇺🇲 US Outlying Is.',
        'UY' => '🇺🇾 Uruguay',
        'UZ' => '🇺🇿 Uzbekistan',
        'VU' => '🇻🇺 Vanuatu',
        'VE' => '🇻🇪 Venezuela',
        'VN' => '🇻🇳 Vietnam',
        'VG' => '🇻🇬 British Virgin Is.',
        'VI' => '🇻🇮 US Virgin Is.',
        'WF' => '🇼🇫 Wallis & Futuna',
        'EH' => '🇪🇭 Western Sahara',
        'YE' => '🇾🇪 Yemen',
        'ZM' => '🇿🇲 Zambia',
        'ZW' => '🇿🇼 Zimbabwe'
    ];
}

function get_country_from_country_code($code) {
    $code = mb_strtoupper($code ?? '');
    return get_countries_no_emoji_array()[$code] ?? $code;
}

function get_locale_languages_array() {
    return [
        'ab' => 'Abkhazian',
        'aa' => 'Afar',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'sq' => 'Albanian',
        'am' => 'Amharic',
        'ar' => 'Arabic',
        'an' => 'Aragonese',
        'hy' => 'Armenian',
        'as' => 'Assamese',
        'av' => 'Avaric',
        'ae' => 'Avestan',
        'ay' => 'Aymara',
        'az' => 'Azerbaijani',
        'bm' => 'Bambara',
        'ba' => 'Bashkir',
        'eu' => 'Basque',
        'be' => 'Belarusian',
        'bn' => 'Bengali',
        'bi' => 'Bislama',
        'bs' => 'Bosnian',
        'br' => 'Breton',
        'bg' => 'Bulgarian',
        'my' => 'Burmese',
        'ca' => 'Catalan, Valencian',
        'km' => 'Central Khmer',
        'ch' => 'Chamorro',
        'ce' => 'Chechen',
        'ny' => 'Chichewa, Chewa, Nyanja',
        'zh' => 'Chinese',
        'cu' => 'Church Slavonic, Old Bulgarian, Old Church Slavonic',
        'cv' => 'Chuvash',
        'kw' => 'Cornish',
        'co' => 'Corsican',
        'cr' => 'Cree',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'da' => 'Danish',
        'dv' => 'Divehi, Dhivehi, Maldivian',
        'nl' => 'Dutch, Flemish',
        'dz' => 'Dzongkha',
        'en' => 'English',
        'eo' => 'Esperanto',
        'et' => 'Estonian',
        'ee' => 'Ewe',
        'fo' => 'Faroese',
        'fj' => 'Fijian',
        'fi' => 'Finnish',
        'fr' => 'French',
        'ff' => 'Fulah',
        'gd' => 'Gaelic, Scottish Gaelic',
        'gl' => 'Galician',
        'lg' => 'Ganda',
        'ka' => 'Georgian',
        'de' => 'German',
        'ki' => 'Gikuyu, Kikuyu',
        'el' => 'Greek (Modern)',
        'kl' => 'Greenlandic, Kalaallisut',
        'gn' => 'Guarani',
        'gu' => 'Gujarati',
        'ht' => 'Haitian, Haitian Creole',
        'ha' => 'Hausa',
        'he' => 'Hebrew',
        'hz' => 'Herero',
        'hi' => 'Hindi',
        'ho' => 'Hiri Motu',
        'hu' => 'Hungarian',
        'is' => 'Icelandic',
        'io' => 'Ido',
        'ig' => 'Igbo',
        'id' => 'Indonesian',
        'ia' => 'Interlingua (International Auxiliary Language Association)',
        'ie' => 'Interlingue',
        'iu' => 'Inuktitut',
        'ik' => 'Inupiaq',
        'ga' => 'Irish',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'jv' => 'Javanese',
        'kn' => 'Kannada',
        'kr' => 'Kanuri',
        'ks' => 'Kashmiri',
        'kk' => 'Kazakh',
        'rw' => 'Kinyarwanda',
        'kv' => 'Komi',
        'kg' => 'Kongo',
        'ko' => 'Korean',
        'kj' => 'Kwanyama, Kuanyama',
        'ku' => 'Kurdish',
        'ky' => 'Kyrgyz',
        'lo' => 'Lao',
        'la' => 'Latin',
        'lv' => 'Latvian',
        'lb' => 'Letzeburgesch, Luxembourgish',
        'li' => 'Limburgish, Limburgan, Limburger',
        'ln' => 'Lingala',
        'lt' => 'Lithuanian',
        'lu' => 'Luba-Katanga',
        'mk' => 'Macedonian',
        'mg' => 'Malagasy',
        'ms' => 'Malay',
        'ml' => 'Malayalam',
        'mt' => 'Maltese',
        'gv' => 'Manx',
        'mi' => 'Maori',
        'mr' => 'Marathi',
        'mh' => 'Marshallese',
        'ro' => 'Romanian',
        'mn' => 'Mongolian',
        'na' => 'Nauru',
        'nv' => 'Navajo, Navaho',
        'nd' => 'Northern Ndebele',
        'ng' => 'Ndonga',
        'ne' => 'Nepali',
        'se' => 'Northern Sami',
        'no' => 'Norwegian',
        'nb' => 'Norwegian Bokmål',
        'nn' => 'Norwegian Nynorsk',
        'ii' => 'Nuosu, Sichuan Yi',
        'oc' => 'Occitan (post 1500)',
        'oj' => 'Ojibwa',
        'or' => 'Oriya',
        'om' => 'Oromo',
        'os' => 'Ossetian, Ossetic',
        'pi' => 'Pali',
        'pa' => 'Panjabi, Punjabi',
        'ps' => 'Pashto, Pushto',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'qu' => 'Quechua',
        'rm' => 'Romansh',
        'rn' => 'Rundi',
        'ru' => 'Russian',
        'sm' => 'Samoan',
        'sg' => 'Sango',
        'sa' => 'Sanskrit',
        'sc' => 'Sardinian',
        'sr' => 'Serbian',
        'sn' => 'Shona',
        'sd' => 'Sindhi',
        'si' => 'Sinhala, Sinhalese',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'so' => 'Somali',
        'st' => 'Sotho, Southern',
        'nr' => 'South Ndebele',
        'es' => 'Spanish, Castilian',
        'su' => 'Sundanese',
        'sw' => 'Swahili',
        'ss' => 'Swati',
        'sv' => 'Swedish',
        'tl' => 'Tagalog',
        'ty' => 'Tahitian',
        'tg' => 'Tajik',
        'ta' => 'Tamil',
        'tt' => 'Tatar',
        'te' => 'Telugu',
        'th' => 'Thai',
        'bo' => 'Tibetan',
        'ti' => 'Tigrinya',
        'to' => 'Tonga (Tonga Islands)',
        'ts' => 'Tsonga',
        'tn' => 'Tswana',
        'tr' => 'Turkish',
        'tk' => 'Turkmen',
        'tw' => 'Twi',
        'ug' => 'Uighur, Uyghur',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        've' => 'Venda',
        'vi' => 'Vietnamese',
        'vo' => 'Volapuk',
        'wa' => 'Walloon',
        'cy' => 'Welsh',
        'fy' => 'Western Frisian',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'za' => 'Zhuang, Chuang',
        'zu' => 'Zulu'
    ];
}

function get_language_from_locale($locale) {
    $languages = get_locale_languages_array();

    if(!isset($languages[$locale])) {
        return $locale;
    } else {
        return $languages[$locale];
    }
}

/* Dump & die */
function dd($string = null) {
    var_dump($string);
    die();
}

/* Output in debug.log file */
function dil($string = null) {
    ob_start();

    print_r($string);

    $content = ob_get_clean();

    error_log($content);
}

/* Quick include with parameters */
function include_view($view_path, $data = []) {

    $data = (object) $data;

    ob_start();

    require $view_path;

    return ob_get_clean();
}

function get_max_upload() {
    return min(convert_php_size_to_mb(ini_get('upload_max_filesize')), convert_php_size_to_mb(ini_get('post_max_size')));
}

function convert_php_size_to_mb($string) {
    $suffix = mb_strtoupper(mb_substr($string, -1));

    if(!in_array($suffix, ['P','T','G','M','K'])){
        return (int) $string;
    }

    $value = mb_substr($string, 0, -1);

    switch($suffix) {
        case 'P':
            $value *= 1000 * 1000 * 100;
            break;
        case 'T':
            $value *= 1000 * 1000;
            break;
        case 'G':
            $value *= 1000;
            break;
        case 'M':
            /* :) */
            break;
        case 'K':
            $value = $value / 1000;
            break;
    }

    return (float) $value;
}

function get_formatted_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1000));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1000, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function get_percentage_between_two_numbers($number, $total) {
    if($total == 0) {
        return 0;
    }

    return ($number / $total) * 100;
}

function get_percentage_change($first_number, $second_number) {
    $first_number = (int) $first_number;
    $second_number = (int) $second_number;

    if($first_number == $second_number) {
        return 0;
    }

    if($first_number < 1) {
        $first_number = 0;
    }

    if($first_number == 0) {
        return 100;
    }

    $difference = (($second_number - $first_number) / $first_number) * 100;

    return $difference;
}

function hex_to_rgb($hex) {
    /* match 3, 6, or 8 digit hex codes, optionally with # */
    preg_match("/^#?([0-9a-f]{1,8})$/i", $hex, $match);
    if (!isset($match[1])) {
        return false;
    }

    $hex_value = $match[1];
    $length = mb_strlen($hex_value);

    /* convert based on length */
    if ($length === 8) {
        /* full RGBA */
        list($r, $g, $b, $a) = [
            $hex_value[0].$hex_value[1],
            $hex_value[2].$hex_value[3],
            $hex_value[4].$hex_value[5],
            $hex_value[6].$hex_value[7]
        ];
    }
    elseif ($length === 6) {
        /* full RGB */
        list($r, $g, $b) = [
            $hex_value[0].$hex_value[1],
            $hex_value[2].$hex_value[3],
            $hex_value[4].$hex_value[5]
        ];
        $a = null;
    }
    elseif ($length === 3) {
        /* shorthand RGB */
        list($r, $g, $b) = [
            $hex_value[0].$hex_value[0],
            $hex_value[1].$hex_value[1],
            $hex_value[2].$hex_value[2]
        ];
        $a = null;
    }
    elseif ($length === 2) {
        /* grayscale */
        $r = $g = $b = $hex_value[0].$hex_value[1];
        $a = null;
    }
    elseif ($length === 1) {
        /* 1 digit grayscale */
        $r = $g = $b = $hex_value.$hex_value;
        $a = null;
    }
    else {
        return false;
    }

    $color = [];
    $color['r'] = hexdec($r);
    $color['g'] = hexdec($g);
    $color['b'] = hexdec($b);

    /* convert alpha to 0–100 percentage (fully opaque = 100) */
    if ($a !== null) {
        $alpha_decimal = hexdec($a);
        $color['a'] = round(($alpha_decimal / 255) * 100);
    } else {
        $color['a'] = 100;
    }

    return $color;
}

function process_and_get_redirect_params() {
    $redirect = null;

    if(isset($_GET['redirect'])) {
        /* Clean the redirect input */
        $redirect = query_clean($_GET['redirect']);

        /* Only allow alphanumeric, slashes, dashes, underscores, question marks and equal signs */
        if(!preg_match('/^[a-zA-Z0-9\/\-\_\?\=&]+$/', $redirect)) {
            $redirect = null;
        }

        if($redirect !== null) {
            $_SESSION['redirect'] = $redirect;
        }
    }

    return $redirect ?? $_SESSION['redirect'] ?? null;
}

function os_name_to_os_key($os_name) {
    $os = [
        'Windows' => 'windows',
        'Android' => 'android',
        'iOS' => 'ios',
        'OS X' => 'apple',
        'Linux' => 'linux',
        'Ubuntu' => 'ubuntu',
        'Chrome OS' => 'chromeos',
        'KaiOS' => 'kaios',
    ];

    return $os[$os_name] ?? 'unknown';
}

function display_response_time($number) {
    if($number > 1000) {
        return nr($number / 1000, 2) . ' ' . l('global.date.short_seconds');
    } else {
        return nr($number, 3) . ' ' . l('global.date.short_milliseconds');
    }
}

function browser_name_to_browser_key($browser_name) {
    $browsers = [
        'Chrome' => 'chrome',
        'Firefox' => 'firefox',
        'Firefox Mobile' => 'firefox',
        'Edge' => 'edge',
        'Brave' => 'brave',
        'Safari' => 'safari',
        'Opera' => 'opera',
        'Opera Mini' => 'opera',
        'Opera Mobile' => 'opera',
        'Opera Touch' => 'operatouch',
        'Yandex Browser' => 'yandex',
    ];

    return $browsers[$browser_name] ?? 'unknown';
}

function get_random_line_from_text($text) {
    $array = preg_split('/\r\n|\r|\n/', $text ?? '');
    return $array[array_rand($array)];
}

function get_plan_feature_limit_info($used, $total, $should_display = true) {
    if(!$should_display) return null;

    $percentage_used = $total == -1 || $total == 0 ? 0 : ($used / $total * 100);
    $percentage_remaining = $total == -1 ? l('global.unlimited') : nr(100-$percentage_used) . '%';

    return sprintf(l('global.info_message.plan_feature_limit_info'), '<strong>' . nr($used) . '</strong>', '<strong>' . ($total == -1 ? l('global.unlimited') : nr($total)) . '</strong>', '<strong>' . $percentage_remaining . '</strong>');
}

function get_plan_feature_disabled_info($has_upgrade_link = true) {
    $tooltip_title = l('global.info_message.plan_feature_no_access');
    $onclick_html = null;

    if($has_upgrade_link) {
        $tooltip_title .= '<br /><strong>' . l('global.info_message.plan_upgrade') . '</strong>';
        $onclick_html = '
            onclick="window.location.href=\'' . url('plan') . '\';"
            class="cursor-pointer"    
        ';
    }

    return <<<ALTUM
        data-toggle="tooltip"
        data-html="true"
        title="{$tooltip_title}"
        {$onclick_html}
    ALTUM;
}

function replace_space_with_plus($string) {
    return str_replace(' ', '+', $string);
}

function convert_editorjs_json_to_html($json) {
    $object = json_decode($json);

    $html = '';
    foreach($object->blocks as $block) {
        switch ($block->type) {
            case 'button':
                $html .= '<a href="' . $block->data->url . '" target="' . $block->data->target . '" class="btn btn-block btn-primary">' . $block->data->label . '</a>';
                break;

            case 'quote':
                $cite = !empty($block->data->caption) ? '<br /><cite class="font-size-small font-weight-bold">-' . $block->data->caption . '</cite>' : null;
                $html .= '<blockquote>"' . $block->data->text . '"' . $cite . '</blockquote>';
                break;

            case 'paragraph':
                $html .= '<p>' . $block->data->text . '</p>';
                break;

            case 'header':
                $html .= '<h' . $block->data->level . '>' . $block->data->text . '</h' . $block->data->level . '>';
                break;

            case 'raw':
                $html .= $block->data->html;
                break;

            case 'list':
                $lsType = ($block->data->style == 'ordered') ? 'ol' : 'ul';
                $html .= '<' . $lsType . '>';
                foreach($block->data->items as $item) {
                    $html .= '<li>' . $item . '</li>';
                }
                $html .= '</' . $lsType . '>';
                break;

            case 'code':
                $html .= '<div class="ql-code-block">' . e($block->data->code) . '</div>';
                break;

            case 'image':
                $html .= '<div><img class="img-fluid" src="' . $block->data->url . '" alt="' . $block->data->caption . '" /></div>';
                break;

            case 'delimiter':
                $html .= '<hr class="border-gray-100 my-4" />';
                break;

            case 'embed':
                $html .= '
                <div class="embed-responsive embed-responsive-16by9 link-iframe-round">
                    <iframe class="embed-responsive-item" scrolling="no" frameborder="no" src="' . $block->data->embed . '"></iframe>
                </div>
                ';

                if(!empty($block->data->caption)) {
                    $html .= '<p class="small text-muted">' . $block->data->caption . '</p>';
                }
                break;

            default:
                break;
        }
    }

    return $html;
}

function remove_directory_and_contents($dir) {
    if(is_dir($dir)) {
        $objects = scandir($dir);
        foreach($objects as $object) {
            if($object != "." && $object != "..") {
                if(filetype($dir."/".$object) == "dir")
                    remove_directory_and_contents($dir . "/" . $object);
                else unlink   ($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

function get_convert_tz_sql($column, $new_timezone, $old_timezone = null) {
    $tz_difference = \Altum\Date::get_timezone_difference($old_timezone ?? \Altum\Date::$default_timezone, $new_timezone);

    return $tz_difference != '+00:00' ? "CONVERT_TZ({$column}, '+00:00', '{$tz_difference}')" : $column;
}

function fire_and_forget(
    $method,
    $url,
    $params = [],
    $content_type = 'form',
    $custom_headers = [],
    $wait_for_response = false /* when true, wait for and return raw response */
) {
    $method = strtoupper($method);

    $parsed_url = parse_url($url);
    if(!$parsed_url || !isset($parsed_url['host'])) {
        /* invalid or incomplete URL */
        return null;
    }

    $host   = $parsed_url['host'];
    $scheme = $parsed_url['scheme'] ?? 'http';
    $port   = $parsed_url['port']   ?? ($scheme === 'https' ? 443 : 80);
    $path   = $parsed_url['path']   ?? '/';

    /* keep any existing query from $url */
    $existing_query = $parsed_url['query'] ?? '';
    $is_json        = strtolower($content_type) === 'json';

    /* if GET + form, append $params to existing query */
    if($method === 'GET' && !$is_json && !empty($params)) {
        $query_parts = [];
        foreach ($params as $key => $value) {
            $query_parts[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $params_query = implode('&', $query_parts);
        if($params_query) {
            $existing_query = $existing_query
                ? $existing_query . '&' . $params_query
                : $params_query;
        }
    }

    if($existing_query) {
        $path .= '?' . $existing_query;
    }

    /* build body for non-GET methods */
    $body_data = '';
    if($method !== 'GET') {
        $body_data = $is_json
            ? json_encode($params)
            : http_build_query($params);
    }

    /* base headers */
    $headers_assoc = ['Host' => $host];

    if($method !== 'GET') {
        if(!isset($custom_headers['Content-Type'])) {
            $headers_assoc['Content-Type'] = $is_json
                ? 'application/json'
                : 'application/x-www-form-urlencoded';
        }
        $headers_assoc['Content-Length']   = mb_strlen($body_data);
        $headers_assoc['Accept-Encoding']  = 'deflate, gzip, br, zstd';
        $headers_assoc['User-Agent']       = 'AltumCode.Com/1.0';
    }

    /* merge custom headers (overwrites defaults) */
    $headers_assoc = array_merge($headers_assoc, $custom_headers);

    /* finalize request headers */
    $headers_lines = ["$method $path HTTP/1.1"];
    foreach ($headers_assoc as $header_key => $header_value) {
        $headers_lines[] = "$header_key: $header_value";
    }

    $request  = implode("\r\n", $headers_lines) . "\r\n\r\n";
    $request .= $method !== 'GET' ? $body_data : '';

    /* handle TLS/SSL for https */
    $host_with_scheme = ($scheme === 'https' ? 'ssl://' : '') . $host;

    $socket = @fsockopen($host_with_scheme, $port, $errno, $errstr, 5);
    if(!$socket) {
        return null;
    }

    fwrite($socket, $request);

    if($wait_for_response) {
        /* read limited response with timeout to avoid hang */
        stream_set_timeout($socket, 3);
        $response      = '';
        $max_bytes     = 1024 * 64; /* 64KB max */
        $bytes_read    = 0;

        while(!feof($socket) && $bytes_read < $max_bytes) {
            $chunk       = fread($socket, 8192);
            if($chunk === false) {
                break;
            }
            $response   .= $chunk;
            $bytes_read += strlen($chunk);
        }

        fclose($socket);
        return $response;
    }

    /* default fire-and-forget */
    stream_set_timeout($socket, 0, 100000);
    fgets($socket, 128);
    fclose($socket);
    return null;
}

/* quilljs to bootstrap4 */
function quilljs_to_bootstra($html_content) {
    $quill_replacements = [
        /* Alignment */
        'ql-align-right'   => 'text-right',
        'ql-align-left'    => 'text-left',
        'ql-align-center'  => 'text-center',
        'ql-align-justify' => 'text-justify',

        /* Sizes */
        'ql-size-small' => 'small',
        'ql-size-large' => 'h4',
        'ql-size-huge'  => 'h3',
    ];

    /* Add Bootstrap classes next to existing Quill classes */
    foreach ($quill_replacements as $quill_class => $bootstrap_class) {
        $html_content = preg_replace(
            '/class="([^"]*?)\b' . preg_quote($quill_class, '/') . '\b([^"]*?)"/',
            'class="$1' . $quill_class . ' ' . $bootstrap_class . '$2"',
            $html_content
        );
    }

    /* Replace direction classes with dir attribute */
    $html_content = preg_replace(
        '/class="([^"]*?)\bql-direction-rtl\b([^"]*?)"/',
        'dir="rtl" class="$1$2"',
        $html_content
    );
    $html_content = preg_replace(
        '/class="([^"]*?)\bql-direction-ltr\b([^"]*?)"/',
        'dir="ltr" class="$1$2"',
        $html_content
    );

    return $html_content;
}
