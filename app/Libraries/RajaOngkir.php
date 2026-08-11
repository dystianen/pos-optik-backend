<?php

namespace App\Libraries;

class RajaOngkir
{
    private static function getApiKey()
    {
        return env('RAJAONGKIR_API_KEY') ?: '';
    }

    private static function getOriginCityId()
    {
        return env('RAJAONGKIR_ORIGIN_CITY_ID') ?: '419'; // Default: Sleman
    }

    private static function getAccountType()
    {
        return env('RAJAONGKIR_ACCOUNT_TYPE') ?: 'pro'; // Default to pro for district support
    }

    private static function getBaseUrl()
    {
        $type = self::getAccountType();
        if ($type === 'pro') {
            return 'https://pro.rajaongkir.com/api';
        }
        if ($type === 'basic') {
            return 'https://api.rajaongkir.com/basic';
        }
        return 'https://api.rajaongkir.com/starter';
    }

    private static function isMockEnabled()
    {
        // 1. Force mock if explicitly set in .env
        $forceMock = env('RAJAONGKIR_MOCK');
        if ($forceMock === true || $forceMock === 'true' || $forceMock === '1' || $forceMock === 1) {
            return true;
        }

        // 2. Fallback to mock if API has been detected as offline/timing out
        if (cache('rajaongkir_offline')) {
            return true;
        }

        // 3. Fallback to mock if API key is empty
        return empty(self::getApiKey());
    }

    private static function handleConnectionFailure(\Throwable $e)
    {
        log_message('error', 'RajaOngkir Connection Failure: ' . $e->getMessage());
        // Save offline status in cache for 5 minutes (300 seconds) to avoid hanging subsequent requests
        cache()->save('rajaongkir_offline', true, 300);
    }

    /**
     * Get list of provinces
     */
    public static function getProvinces()
    {
        if (self::isMockEnabled()) {
            return self::getMockProvinces();
        }

        $cacheKey = 'rajaongkir_provinces';
        $cached = cache($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $client = \Config\Services::curlrequest();
            $url = self::getBaseUrl() . '/province';
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'key' => self::getApiKey()
                ],
                'timeout' => 2, // Fast timeout (2 seconds)
                'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
            ]);

            $body = json_decode($response->getBody(), true);
            if (isset($body['rajaongkir']['status']['code']) && $body['rajaongkir']['status']['code'] === 200) {
                $provinces = $body['rajaongkir']['results'];
                cache()->save($cacheKey, $provinces, 3600 * 24); // Cache for 24 hours
                return $provinces;
            }
            
            log_message('error', 'RajaOngkir API returned non-200: ' . $response->getBody());
            cache()->save('rajaongkir_offline', true, 300);
        } catch (\Throwable $e) {
            self::handleConnectionFailure($e);
        }

        return self::getMockProvinces();
    }

    /**
     * Get list of cities
     */
    public static function getCities($provinceId = null)
    {
        if (self::isMockEnabled()) {
            return self::getMockCities($provinceId);
        }

        $cacheKey = 'rajaongkir_cities_' . ($provinceId ?: 'all');
        $cached = cache($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $url = self::getBaseUrl() . '/city';
            if ($provinceId) {
                $url .= '?province=' . $provinceId;
            }

            $client = \Config\Services::curlrequest();
            $response = $client->request('GET', $url, [
                'headers' => [
                    'key' => self::getApiKey()
                ],
                'timeout' => 2,
                'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
            ]);

            $body = json_decode($response->getBody(), true);
            if (isset($body['rajaongkir']['status']['code']) && $body['rajaongkir']['status']['code'] === 200) {
                $cities = $body['rajaongkir']['results'];
                cache()->save($cacheKey, $cities, 3600 * 24); // Cache for 24 hours
                return $cities;
            }

            log_message('error', 'RajaOngkir API returned non-200: ' . $response->getBody());
            cache()->save('rajaongkir_offline', true, 300);
        } catch (\Throwable $e) {
            self::handleConnectionFailure($e);
        }

        return self::getMockCities($provinceId);
    }

    /**
     * Get list of districts (subdistrict) in a city (Only for Pro account)
     */
    public static function getDistricts($cityId)
    {
        if (empty($cityId)) {
            return [];
        }

        if (self::isMockEnabled() || self::getAccountType() !== 'pro') {
            return self::getMockDistricts($cityId);
        }

        $cacheKey = 'rajaongkir_districts_' . $cityId;
        $cached = cache($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $url = self::getBaseUrl() . '/subdistrict?city=' . $cityId;

            $client = \Config\Services::curlrequest();
            $response = $client->request('GET', $url, [
                'headers' => [
                    'key' => self::getApiKey()
                ],
                'timeout' => 2,
                'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
            ]);

            $body = json_decode($response->getBody(), true);
            if (isset($body['rajaongkir']['status']['code']) && $body['rajaongkir']['status']['code'] === 200) {
                $districts = $body['rajaongkir']['results'];
                cache()->save($cacheKey, $districts, 3600 * 24); // Cache for 24 hours
                return $districts;
            }

            log_message('error', 'RajaOngkir subdistrict API returned non-200: ' . $response->getBody());
            cache()->save('rajaongkir_offline', true, 300);
        } catch (\Throwable $e) {
            self::handleConnectionFailure($e);
        }

        return self::getMockDistricts($cityId);
    }

    /**
     * Calculate cost
     */
    public static function calculateCost($destinationId, $weightGrams, $courier, $destinationType = 'subdistrict')
    {
        if (self::isMockEnabled()) {
            return self::getMockCost($destinationId, $weightGrams, $courier, $destinationType);
        }

        try {
            $client = \Config\Services::curlrequest();
            $url = self::getBaseUrl() . '/cost';

            $formParams = [
                'origin'          => self::getOriginCityId(),
                'originType'      => 'city',
                'destination'     => $destinationId,
                'destinationType' => $destinationType,
                'weight'          => $weightGrams,
                'courier'         => strtolower($courier)
            ];

            // If account type is starter, it does not support originType/destinationType
            if (self::getAccountType() === 'starter') {
                unset($formParams['originType']);
                unset($formParams['destinationType']);
            }

            $response = $client->request('POST', $url, [
                'headers' => [
                    'key' => self::getApiKey(),
                    'content-type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => $formParams,
                'timeout' => 2,
                'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
            ]);

            $body = json_decode($response->getBody(), true);
            if (isset($body['rajaongkir']['status']['code']) && $body['rajaongkir']['status']['code'] === 200) {
                return $body['rajaongkir']['results'][0] ?? null;
            }

            log_message('error', 'RajaOngkir cost API returned non-200: ' . $response->getBody());
            cache()->save('rajaongkir_offline', true, 300);
        } catch (\Throwable $e) {
            self::handleConnectionFailure($e);
        }

        return self::getMockCost($destinationId, $weightGrams, $courier, $destinationType);
    }

    // ==========================================
    // MOCK DATA FALLBACKS
    // ==========================================

    private static function getMockProvinces()
    {
        return [
            ['province_id' => '5', 'province' => 'DI Yogyakarta'],
            ['province_id' => '6', 'province' => 'DKI Jakarta'],
            ['province_id' => '9', 'province' => 'Jawa Barat'],
            ['province_id' => '10', 'province' => 'Jawa Tengah'],
            ['province_id' => '11', 'province' => 'Jawa Timur'],
            ['province_id' => '1', 'province' => 'Bali'],
            ['province_id' => '12', 'province' => 'Kalimantan Barat'],
            ['province_id' => '21', 'province' => 'Sulawesi Selatan'],
            ['province_id' => '33', 'province' => 'Sumatera Utara'],
            ['province_id' => '24', 'province' => 'Papua'],
        ];
    }

    private static function getMockCities($provinceId = null)
    {
        $allCities = [
            ['city_id' => '419', 'province_id' => '5', 'city_name' => 'Sleman', 'type' => 'Kabupaten', 'postal_code' => '55512'],
            ['city_id' => '501', 'province_id' => '5', 'city_name' => 'Yogyakarta', 'type' => 'Kota', 'postal_code' => '55111'],
            ['city_id' => '152', 'province_id' => '6', 'city_name' => 'Jakarta Barat', 'type' => 'Kota', 'postal_code' => '11220'],
            ['city_id' => '153', 'province_id' => '6', 'city_name' => 'Jakarta Selatan', 'type' => 'Kota', 'postal_code' => '12000'],
            ['city_id' => '151', 'province_id' => '6', 'city_name' => 'Jakarta Pusat', 'type' => 'Kota', 'postal_code' => '10000'],
            ['city_id' => '23', 'province_id' => '9', 'city_name' => 'Bandung', 'type' => 'Kota', 'postal_code' => '40111'],
            ['city_id' => '399', 'province_id' => '10', 'city_name' => 'Semarang', 'type' => 'Kota', 'postal_code' => '50135'],
            ['city_id' => '444', 'province_id' => '11', 'city_name' => 'Surabaya', 'type' => 'Kota', 'postal_code' => '60111'],
            ['city_id' => '114', 'province_id' => '1', 'city_name' => 'Denpasar', 'type' => 'Kota', 'postal_code' => '80111'],
            ['city_id' => '272', 'province_id' => '21', 'city_name' => 'Makassar', 'type' => 'Kota', 'postal_code' => '90111'],
            ['city_id' => '278', 'province_id' => '33', 'city_name' => 'Medan', 'type' => 'Kota', 'postal_code' => '20111'],
            ['city_id' => '155', 'province_id' => '24', 'city_name' => 'Jayapura', 'type' => 'Kota', 'postal_code' => '99111'],
        ];

        if (!$provinceId) {
            return $allCities;
        }

        return array_values(array_filter($allCities, function ($c) use ($provinceId) {
            return (string)$c['province_id'] === (string)$provinceId;
        }));
    }

    private static function getMockDistricts($cityId)
    {
        $allDistricts = [
            // Sleman (419)
            ['subdistrict_id' => '5740', 'city_id' => '419', 'subdistrict_name' => 'Depok'],
            ['subdistrict_id' => '5741', 'city_id' => '419', 'subdistrict_name' => 'Gamping'],
            ['subdistrict_id' => '5742', 'city_id' => '419', 'subdistrict_name' => 'Mlati'],
            ['subdistrict_id' => '5743', 'city_id' => '419', 'subdistrict_name' => 'Kalasan'],
            // Yogyakarta (501)
            ['subdistrict_id' => '6990', 'city_id' => '501', 'subdistrict_name' => 'Gondokusuman'],
            ['subdistrict_id' => '6991', 'city_id' => '501', 'subdistrict_name' => 'Umbulharjo'],
            ['subdistrict_id' => '6992', 'city_id' => '501', 'subdistrict_name' => 'Kotagede'],
            // Jakarta Selatan (153)
            ['subdistrict_id' => '21000', 'city_id' => '153', 'subdistrict_name' => 'Tebet'],
            ['subdistrict_id' => '21001', 'city_id' => '153', 'subdistrict_name' => 'Kebayoran Baru'],
            ['subdistrict_id' => '21002', 'city_id' => '153', 'subdistrict_name' => 'Cilandak'],
            // Surabaya (444)
            ['subdistrict_id' => '32000', 'city_id' => '444', 'subdistrict_name' => 'Wonokromo'],
            ['subdistrict_id' => '32001', 'city_id' => '444', 'subdistrict_name' => 'Tegalsari'],
            ['subdistrict_id' => '32002', 'city_id' => '444', 'subdistrict_name' => 'Gubeng'],
        ];

        $filtered = array_values(array_filter($allDistricts, function ($d) use ($cityId) {
            return (string)$d['city_id'] === (string)$cityId;
        }));

        if (!empty($filtered)) {
            return $filtered;
        }

        // Generic fallback district if city has no mock districts
        return [
            ['subdistrict_id' => $cityId . '01', 'city_id' => $cityId, 'subdistrict_name' => 'Kecamatan Satu'],
            ['subdistrict_id' => $cityId . '02', 'city_id' => $cityId, 'subdistrict_name' => 'Kecamatan Dua'],
        ];
    }

    private static function getMockCost($destinationId, $weightGrams, $courier, $destinationType)
    {
        $weightKg = ceil($weightGrams / 1000);
        if ($weightKg < 1) {
            $weightKg = 1;
        }

        $courier = strtolower($courier);
        $courierName = strtoupper($courier);

        // Standard mock rates based on destination type and subdistrict ID length
        $provinceId = 'unknown';

        if ($destinationType === 'subdistrict') {
            // Determine city from subdistrict ID
            $districtIdStr = (string)$destinationId;
            if (strpos($districtIdStr, '574') === 0 || strpos($districtIdStr, '419') === 0) {
                $provinceId = '5'; // Sleman
            } else if (strpos($districtIdStr, '699') === 0 || strpos($districtIdStr, '501') === 0) {
                $provinceId = '5'; // Yogyakarta
            } else if (strpos($districtIdStr, '2100') === 0 || strpos($districtIdStr, '153') === 0) {
                $provinceId = '6'; // Jakarta Selatan
            } else if (strpos($districtIdStr, '3200') === 0 || strpos($districtIdStr, '444') === 0) {
                $provinceId = '11'; // Surabaya
            }
        }

        $costs = [];

        if ($provinceId === '5') {
            // DI Yogyakarta (e.g. Sleman districts)
            $costs[] = [
                'service' => 'REG',
                'description' => 'Layanan Reguler',
                'cost' => [
                    ['value' => 8000 * $weightKg, 'etd' => '1-2', 'note' => '']
                ]
            ];
            if ($courier === 'jne') {
                $costs[] = [
                    'service' => 'YES',
                    'description' => 'Yakin Esok Sampai',
                    'cost' => [
                        ['value' => 14000 * $weightKg, 'etd' => '1-1', 'note' => '']
                    ]
                ];
            }
        } else if ($provinceId === '6') {
            // DKI Jakarta
            $costs[] = [
                'service' => 'REG',
                'description' => 'Layanan Reguler',
                'cost' => [
                    ['value' => 14000 * $weightKg, 'etd' => '2-3', 'note' => '']
                ]
            ];
            if ($courier === 'jne') {
                $costs[] = [
                    'service' => 'YES',
                    'description' => 'Yakin Esok Sampai',
                    'cost' => [
                        ['value' => 24000 * $weightKg, 'etd' => '1-1', 'note' => '']
                    ]
                ];
            }
        } else if ($provinceId === '11') {
            // Surabaya
            $costs[] = [
                'service' => 'REG',
                'description' => 'Layanan Reguler',
                'cost' => [
                    ['value' => 12000 * $weightKg, 'etd' => '2-3', 'note' => '']
                ]
            ];
        } else {
            // General Fallback
            $costs[] = [
                'service' => 'REG',
                'description' => 'Layanan Reguler',
                'cost' => [
                    ['value' => 22000 * $weightKg, 'etd' => '3-5', 'note' => '']
                ]
            ];
        }

        return [
            'code' => $courier,
            'name' => $courierName === 'JNE' ? 'Jalur Nugraha Ekakurir (JNE)' : ($courierName === 'POS' ? 'POS Indonesia' : 'TIKI'),
            'costs' => $costs
        ];
    }
}
