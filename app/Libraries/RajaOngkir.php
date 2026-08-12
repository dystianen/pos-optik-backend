<?php

namespace App\Libraries;

class RajaOngkir
{
    private static function getApiKey()
    {
        $key = env('RAJAONGKIR_API_KEY') ?: '';
        return trim($key, '"\' ');
    }

    private static function getOriginCityId()
    {
        return env('RAJAONGKIR_ORIGIN_CITY_ID'); // Default: Sleman district in Komerce
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
        log_message('error', 'Komerce RajaOngkir Connection Failure: ' . $e->getMessage());
        // Save offline status in cache for 5 minutes (300 seconds) to avoid hanging subsequent requests
        cache()->save('rajaongkir_offline', true, 300);
    }

    /**
     * Helper to lookup postal code for a Komerce city
     */
    private static function getCityPostalCode($cityId)
    {
        $cacheKey = 'rajaongkir_postal_code_city_' . $cityId;
        $cached = cache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Get districts of this city
            $districts = self::getDistricts($cityId);
            if (!empty($districts)) {
                $firstDistrictId = $districts[0]['subdistrict_id'];
                
                // Get sub-districts of this district
                $url = 'https://rajaongkir.komerce.id/api/v1/destination/sub-district/' . $firstDistrictId;
                $client = \Config\Services::curlrequest();
                $response = $client->request('GET', $url, [
                    'headers' => [
                        'key' => self::getApiKey()
                    ],
                    'timeout' => 5,
                    'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
                ]);

                $body = json_decode($response->getBody(), true);
                if (isset($body['meta']['code']) && $body['meta']['code'] === 200 && !empty($body['data'])) {
                    $postalCode = $body['data'][0]['zip_code'] ?? '';
                    cache()->save($cacheKey, $postalCode, 3600 * 24 * 30); // Cache for 30 days
                    return $postalCode;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch postal code for city ' . $cityId . ': ' . $e->getMessage());
        }

        cache()->save($cacheKey, '', 3600 * 2); // Cache empty result for 2 hours on failure
        return '';
    }

    /**
     * Get list of provinces
     */
    public static function getProvinces()
    {
        $cacheKey = 'rajaongkir_provinces';
        $cached = cache($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $client = \Config\Services::curlrequest();
            $url = 'https://rajaongkir.komerce.id/api/v1/destination/province';
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'key' => self::getApiKey()
                ],
                'timeout' => 10,
                'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
            ]);

            $body = json_decode($response->getBody(), true);
            if (isset($body['meta']['code']) && $body['meta']['code'] === 200) {
                $provinces = [];
                foreach ($body['data'] as $item) {
                    $provinces[] = [
                        'province_id' => (string) $item['id'],
                        'province'    => $item['name']
                    ];
                }
                cache()->save($cacheKey, $provinces, 3600 * 24); // Cache for 24 hours
                return $provinces;
            }
            
            log_message('error', 'Komerce Province API returned non-200: ' . $response->getBody());
        } catch (\Throwable $e) {
            log_message('error', 'Komerce RajaOngkir Province Fetch Failure: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get list of cities
     */
    public static function getCities($provinceId = null)
    {
        $cacheKey = 'rajaongkir_cities_' . ($provinceId ?: 'all');
        $cached = cache($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $client = \Config\Services::curlrequest();
            if ($provinceId) {
                $url = 'https://rajaongkir.komerce.id/api/v1/destination/city/' . $provinceId;
                $response = $client->request('GET', $url, [
                    'headers' => [
                        'key' => self::getApiKey()
                    ],
                    'timeout' => 10,
                    'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
                ]);

                $body = json_decode($response->getBody(), true);
                if (isset($body['meta']['code']) && $body['meta']['code'] === 200) {
                    $cities = [];
                    foreach ($body['data'] as $item) {
                        // For performance and to prevent 429 errors, check cache only and do not query APIs dynamically.
                        $postalCodeCacheKey = 'rajaongkir_postal_code_city_' . $item['id'];
                        $postalCode = cache($postalCodeCacheKey) ?: '';
                        
                        $cities[] = [
                            'city_id'     => (string) $item['id'],
                            'province_id' => (string) $provinceId,
                            'city_name'   => $item['name'],
                            'type'        => '',
                            'postal_code' => $postalCode
                        ];
                    }
                    cache()->save($cacheKey, $cities, 3600 * 24); // Cache for 24 hours
                    return $cities;
                }
                log_message('error', 'Komerce City API returned non-200 for province ' . $provinceId . ': ' . $response->getBody());
            } else {
                $provinces = self::getProvinces();
                $allCities = [];
                foreach ($provinces as $p) {
                    $pId = $p['province_id'];
                    $url = 'https://rajaongkir.komerce.id/api/v1/destination/city/' . $pId;
                    try {
                        $response = $client->request('GET', $url, [
                            'headers' => [
                                'key' => self::getApiKey()
                            ],
                            'timeout' => 10,
                            'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
                        ]);
                        $body = json_decode($response->getBody(), true);
                        if (isset($body['meta']['code']) && $body['meta']['code'] === 200) {
                            foreach ($body['data'] as $item) {
                                // For performance when fetching all cities, do NOT fetch postal codes dynamically via API.
                                // Instead, check if it's already cached. If not, leave it empty or fetch it later.
                                $postalCodeCacheKey = 'rajaongkir_postal_code_city_' . $item['id'];
                                $postalCode = cache($postalCodeCacheKey) ?: '';

                                $allCities[] = [
                                    'city_id'     => (string) $item['id'],
                                    'province_id' => (string) $pId,
                                    'city_name'   => $item['name'],
                                    'type'        => '',
                                    'postal_code' => $postalCode
                                ];
                            }
                        }
                    } catch (\Throwable $innerEx) {
                        log_message('error', 'Komerce City API failed for province ' . $pId . ': ' . $innerEx->getMessage());
                    }
                }
                cache()->save($cacheKey, $allCities, 3600 * 24); // Cache for 24 hours
                return $allCities;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Komerce RajaOngkir Cities Fetch Failure: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get list of districts (subdistrict) in a city
     */
    public static function getDistricts($cityId)
    {
        if (empty($cityId)) {
            return [];
        }

        $cacheKey = 'rajaongkir_districts_' . $cityId;
        $cached = cache($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $url = 'https://rajaongkir.komerce.id/api/v1/destination/district/' . $cityId;

            $client = \Config\Services::curlrequest();
            $response = $client->request('GET', $url, [
                'headers' => [
                    'key' => self::getApiKey()
                ],
                'timeout' => 10,
                'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
            ]);

            $body = json_decode($response->getBody(), true);
            if (isset($body['meta']['code']) && $body['meta']['code'] === 200) {
                $districts = [];
                foreach ($body['data'] as $item) {
                    $districts[] = [
                        'subdistrict_id'   => (string) $item['id'],
                        'city_id'          => (string) $cityId,
                        'subdistrict_name' => $item['name']
                    ];
                }
                cache()->save($cacheKey, $districts, 3600 * 24); // Cache for 24 hours
                return $districts;
            }

            log_message('error', 'Komerce district API returned non-200: ' . $response->getBody());
        } catch (\Throwable $e) {
            log_message('error', 'Komerce RajaOngkir District Fetch Failure for city ' . $cityId . ': ' . $e->getMessage());
        }

        return [];
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
            $url = 'https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost';

            $formParams = [
                'origin'      => self::getOriginCityId(),
                'destination' => $destinationId,
                'weight'      => $weightGrams,
                'courier'     => strtolower($courier),
                'price'       => 'lowest'
            ];

            $response = $client->request('POST', $url, [
                'headers' => [
                    'key' => self::getApiKey(),
                    'content-type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => $formParams,
                'timeout' => 10,
                'verify'  => env('CURL_VERIFY') !== false && env('CURL_VERIFY') !== 'false'
            ]);

            $body = json_decode($response->getBody(), true);
            if (isset($body['meta']['code']) && $body['meta']['code'] === 200 && isset($body['data'])) {
                // Map Komerce flat array to official RajaOngkir format
                $costs = [];
                $courierName = strtoupper($courier);
                foreach ($body['data'] as $item) {
                    if (strtolower($item['code']) === strtolower($courier)) {
                        $courierName = $item['name'];
                        
                        // Clean ETD
                        $etd = str_ireplace(' day', '', $item['etd'] ?? '');
                        
                        $costs[] = [
                            'service' => $item['service'],
                            'description' => $item['description'],
                            'cost' => [
                                [
                                    'value' => (int) $item['cost'],
                                    'etd' => $etd ?: '',
                                    'note' => ''
                                ]
                            ]
                        ];
                    }
                }
                
                return [
                    'code' => strtolower($courier),
                    'name' => $courierName,
                    'costs' => $costs
                ];
            }

            log_message('error', 'Komerce cost API returned non-200: ' . $response->getBody());
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
