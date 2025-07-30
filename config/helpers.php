<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * 	available options:
 *   geoplugin_request	"100.7.14.189"
 *   geoplugin_status	200
 *   geoplugin_delay	"1ms"
 *   geoplugin_credit	"Some of the returned data includes GeoLite data created by MaxMind, available from <a href='http://www.maxmind.com'>http://www.maxmind.com</a>."
 *   geoplugin_city	"Richmond"
 *   geoplugin_region	"Virginia"
 *   geoplugin_regionCode	"VA"
 *   geoplugin_regionName	"Virginia"
 *   geoplugin_areaCode	""
 *   geoplugin_dmaCode	"556"
 *   geoplugin_countryCode	"US"
 *   geoplugin_countryName	"United States"
 *   geoplugin_inEU	0
 *   geoplugin_euVATrate	false
 *   geoplugin_continentCode	"NA"
 *   geoplugin_continentName	"North America"
 *   geoplugin_latitude	"37.4365"
 *   geoplugin_longitude	"-77.4807"
 *   geoplugin_locationAccuracyRadius	"5"
 *   geoplugin_timezone	"America/New_York"
 *   geoplugin_currencyCode	"USD"
 *   geoplugin_currencySymbol	"$"
 *   geoplugin_currencySymbol_UTF8	"$"
 *   geoplugin_currencyConverter	0
 */

function get_client_ip()
{
    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? filter_var(($_SERVER['HTTP_CLIENT_IP']), FILTER_VALIDATE_IP) : '';
    $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP) : '';
    $ip = isset($_SERVER['REMOTE_ADDR']) ? filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) : '';
    return $ip;
}
function country_by_ip()
{
    $ip = get_client_ip();
    if ($ip != '') {
        $source = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));

        if ($source && $source->geoplugin_countryName != null) {
            $result = [
                'countryCode' => $source->geoplugin_countryCode
            ];
            $result = $result['countryCode'];
        } else {
            $result = 'Unknown';
        }
    } else {
        $result = 'Unknown';
    }
    return $result;
}


/**
 * 
 *   session.sid_length is the number of characters in the ID
 *   session.sid_bits_per_character controls the set of characters used. 
 *   From the manual: The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, '-', ','). 
 */

function isValidSessionId(string $sessionId): bool
{
    if (empty($sessionId)) {
        return false;
    }

    $sidLength = ini_get('session.sid_length');
    $bitsPerCharacter = ini_get('session.sid_bits_per_character');
    $characterClass = [
        6 => '0-9a-zA-z,-',
        5 => '0-9a-z',
        4 => '0-9a-f'
    ];

    if (array_key_exists($bitsPerCharacter, $characterClass)) {
        $pattern = '/^[' . $characterClass . ']{' . $sidLength . '}$/';
        return preg_match($pattern, $sessionId) === 1;
    }
    throw new \RuntimeException('Unknown value in session.sid_bits_per_character.');
}
