<?php

namespace App\Services;

use Config\Database;
use Config\OrderStatus;

class RecommendationService
{
    protected $db;
    protected $statusModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->statusModel = new \App\Models\OrderStatusModel();
    }

    /**
     * Get product recommendations with optional detailed debugging.
     */
    public function getRecommendations($productId, $customerId = null, $limit = 10, $search = null, $debug = false)
    {
        $startTime = microtime(true);
        $completedStatusId = $this->statusModel->getIdByCode(OrderStatus::COMPLETED);

        // Load all attributes to map attribute UUID to human-readable name
        $attributesList = $this->db->table('product_attributes')
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();
            
        $attrNameMap = [];
        foreach ($attributesList as $attr) {
            $attrNameMap[$attr['attribute_id']] = $attr['attribute_name'];
        }

        // STEP 1: Customer Information
        $customerInfo = null;
        $purchasedIds = [];
        $hasPosData = false;
        $userVector = [];
        $posVector = [];
        $userVectorReadable = [];
        $posVectorReadable = [];

        if ($customerId) {
            $customer = $this->db->table('customers')
                ->where('customer_id', $customerId)
                ->get()
                ->getRowArray();

            if ($customer) {
                // Count orders
                $totalOrders = $this->db->table('orders')
                    ->where('customer_id', $customerId)
                    ->where('deleted_at', null)
                    ->countAllResults();

                $completedOrders = $this->db->table('orders')
                    ->where('customer_id', $customerId)
                    ->where('status_id', $completedStatusId)
                    ->where('deleted_at', null)
                    ->countAllResults();

                $onlineOrders = $this->db->table('orders')
                    ->where('customer_id', $customerId)
                    ->where('order_type', 'online')
                    ->where('status_id', $completedStatusId)
                    ->where('deleted_at', null)
                    ->countAllResults();

                $posOrders = $this->db->table('orders')
                    ->where('customer_id', $customerId)
                    ->where('order_type', 'offline')
                    ->where('status_id', $completedStatusId)
                    ->where('deleted_at', null)
                    ->countAllResults();

                // Get purchase history items (completed status only)
                $purchaseHistory = $this->db->table('order_items oi')
                    ->select('o.created_at, o.order_id, p.product_name, o.order_type')
                    ->join('orders o', 'o.order_id = oi.order_id')
                    ->join('products p', 'p.product_id = oi.product_id')
                    ->where('o.customer_id', $customerId)
                    ->where('o.status_id', $completedStatusId)
                    ->where('o.deleted_at', null)
                    ->orderBy('o.created_at', 'DESC')
                    ->get()
                    ->getResultArray();

                $purchasedProductsList = [];
                foreach ($purchaseHistory as $ph) {
                    $purchasedProductsList[] = $ph['product_name'];
                }
                $purchasedProductsList = array_values(array_unique($purchasedProductsList));

                // Get purchased product IDs (unique)
                $purchaseRows = $this->db->table('order_items oi')
                    ->select('oi.product_id, o.order_type')
                    ->join('orders o', 'o.order_id = oi.order_id')
                    ->where('o.customer_id', $customerId)
                    ->where('o.status_id', $completedStatusId)
                    ->where('o.deleted_at', null)
                    ->get()
                    ->getResultArray();

                $purchasedIds = array_unique(array_column($purchaseRows, 'product_id'));
                $hasPosData = !empty(array_filter($purchaseRows, fn($r) => $r['order_type'] === 'offline'));

                // Get attributes of purchased products to build user profile vector
                if (!empty($purchasedIds)) {
                    $userAttrRows = $this->db->table('product_attribute_values pav')
                        ->select('pav.attribute_id, pav.value, o.order_type')
                        ->join('order_items oi', 'oi.product_id = pav.product_id')
                        ->join('orders o', 'o.order_id = oi.order_id')
                        ->whereIn('pav.product_id', $purchasedIds)
                        ->where('o.customer_id', $customerId)
                        ->where('o.status_id', $completedStatusId)
                        ->where('pav.deleted_at', null)
                        ->get()
                        ->getResultArray();

                    foreach ($userAttrRows as $row) {
                        $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
                        $userVector[$key] = ($userVector[$key] ?? 0) + 1;

                        $attrName = $attrNameMap[$row['attribute_id']] ?? $row['attribute_id'];
                        $readableKey = strtolower($attrName) . '::' . strtolower(trim($row['value']));
                        $userVectorReadable[$readableKey] = ($userVectorReadable[$readableKey] ?? 0) + 1;

                        if ($row['order_type'] === 'offline') {
                            $posVector[$key] = ($posVector[$key] ?? 0) + 1;
                            $posVectorReadable[$readableKey] = ($posVectorReadable[$readableKey] ?? 0) + 1;
                        }
                    }
                }

                // Sort user vectors by frequency descending
                arsort($userVectorReadable);
                arsort($posVectorReadable);

                $customerInfo = [
                    'customer_id' => $customerId,
                    'customer_name' => $customer['customer_name'],
                    'is_logged_in' => 'Ya',
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'marketplace_orders' => $onlineOrders,
                    'pos_orders' => $posOrders,
                    'purchased_products' => $purchasedProductsList,
                    'purchase_history' => $purchaseHistory
                ];
            }
        }

        if (!$customerInfo) {
            $customerInfo = [
                'customer_id' => null,
                'customer_name' => 'Guest',
                'is_logged_in' => 'Tidak',
                'total_orders' => 0,
                'completed_orders' => 0,
                'marketplace_orders' => 0,
                'pos_orders' => 0,
                'purchased_products' => [],
                'purchase_history' => []
            ];
        }

        // STEP 2: Base Product Details
        $baseProduct = $this->db->table('products p')
            ->select('p.product_id, p.product_name, p.product_brand, p.product_price, pc.category_name, p.category_id')
            ->join('product_categories pc', 'pc.category_id = p.category_id', 'left')
            ->where('p.product_id', $productId)
            ->get()
            ->getRowArray();

        if (!$baseProduct) {
            return $debug ? ['error' => 'Product not found'] : [];
        }

        $baseAttrRows = $this->db->table('product_attribute_values pav')
            ->select('pav.attribute_id, pav.value, pa.attribute_name')
            ->join('product_attributes pa', 'pa.attribute_id = pav.attribute_id')
            ->where('pav.product_id', $productId)
            ->where('pav.deleted_at', null)
            ->get()
            ->getResultArray();

        $baseAttributes = [];
        $baseVector = [];
        $baseVectorReadable = [];
        foreach ($baseAttrRows as $row) {
            $baseAttributes[] = [
                'attribute_name' => $row['attribute_name'],
                'value' => $row['value']
            ];
            $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
            $baseVector[$key] = 1;

            $readableKey = strtolower($row['attribute_name']) . '::' . strtolower(trim($row['value']));
            $baseVectorReadable[$readableKey] = 1;
        }

        $baseProductInfo = [
            'product_id' => $baseProduct['product_id'],
            'product_name' => $baseProduct['product_name'],
            'product_brand' => $baseProduct['product_brand'],
            'product_price' => (float)$baseProduct['product_price'],
            'category_name' => $baseProduct['category_name'] ?? '-',
            'category_id' => $baseProduct['category_id'],
            'attributes' => $baseAttributes,
            'base_vector' => $baseVectorReadable
        ];

        // STEP 4: Candidate Products
        $builder = $this->db->table('products p')
            ->select('
                p.product_id,
                p.product_name,
                p.product_brand,
                p.product_price,
                p.product_stock,
                pc.category_name,
                p.category_id,
                pi.url AS product_image_url
            ')
            ->join('product_categories pc', 'pc.category_id = p.category_id', 'left')
            ->join(
                'product_images pi',
                'pi.product_id = p.product_id AND pi.type = "gallery" AND pi.is_primary = 1',
                'left'
            )
            ->where('p.deleted_at', null)
            ->where('p.product_id !=', $productId);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('p.product_name', $search)
                ->orLike('p.product_brand', $search)
                ->groupEnd();
        }

        // Fetch products
        $candidates = $builder->limit($limit * 5)->get()->getResultArray();

        $candidatesData = [];
        if (!empty($candidates)) {
            $candidateIds = array_column($candidates, 'product_id');

            $allAttrRows = $this->db->table('product_attribute_values pav')
                ->select('pav.product_id, pav.attribute_id, pav.value, pa.attribute_name')
                ->join('product_attributes pa', 'pa.attribute_id = pav.attribute_id')
                ->whereIn('pav.product_id', $candidateIds)
                ->where('pav.deleted_at', null)
                ->get()
                ->getResultArray();

            $vectorByProduct = [];
            $vectorByProductReadable = [];
            $attributesByProduct = [];

            foreach ($allAttrRows as $row) {
                $pid = $row['product_id'];
                $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
                $readableKey = strtolower($row['attribute_name']) . '::' . strtolower(trim($row['value']));

                $vectorByProduct[$pid][$key] = 1;
                $vectorByProductReadable[$pid][$readableKey] = 1;

                $attributesByProduct[$pid][] = [
                    'attribute_name' => $row['attribute_name'],
                    'value' => $row['value']
                ];
            }

            // Weights
            $wBase = 0.40;
            $wUser = $hasPosData ? 0.40 : 0.60;
            $wPos  = $hasPosData ? 0.20 : 0.00;

            $scoredCandidates = [];

            foreach ($candidates as $product) {
                $pid = $product['product_id'];
                $candidateVec = $vectorByProduct[$pid] ?? [];

                if (empty($candidateVec)) {
                    continue;
                }

                // Compute Similarity
                $cbfResult = $this->calculateSimilarityDetails($baseVector, $candidateVec, 'Base', 'Candidate', $attrNameMap);
                $cbfScore = $cbfResult['similarity'];

                $userResult = ['similarity' => 0.0, 'dot_product' => 0, 'magnitude_a' => 0, 'magnitude_b' => 0, 'calculation' => 'User Vector is empty'];
                if (!empty($userVector)) {
                    $userResult = $this->calculateSimilarityDetails($userVector, $candidateVec, 'User', 'Candidate', $attrNameMap);
                }
                $userScore = $userResult['similarity'];

                $posResult = ['similarity' => 0.0, 'dot_product' => 0, 'magnitude_a' => 0, 'magnitude_b' => 0, 'calculation' => 'POS Vector is empty'];
                if ($hasPosData && !empty($posVector)) {
                    $posResult = $this->calculateSimilarityDetails($posVector, $candidateVec, 'POS', 'Candidate', $attrNameMap);
                }
                $posScore = $posResult['similarity'];

                // Calculate final score
                $finalScore = ($wBase * $cbfScore) + ($wUser * $userScore) + ($wPos * $posScore);

                // Check match attributes count
                $matchCount = 0;
                $totalBaseAttrs = count($baseVector);
                $matchingAttrs = [];
                $nonMatchingAttrs = [];
                foreach ($baseVector as $key => $val) {
                    if (isset($candidateVec[$key])) {
                        $matchCount++;
                        $matchingAttrs[] = $this->toReadableKey($key, $attrNameMap);
                    } else {
                        $nonMatchingAttrs[] = $this->toReadableKey($key, $attrNameMap);
                    }
                }

                $isPurchased = in_array($pid, $purchasedIds);

                $productData = [
                    'product_id' => $pid,
                    'product_name' => $product['product_name'],
                    'product_brand' => $product['product_brand'],
                    'product_price' => (float)$product['product_price'],
                    'product_stock' => (int)$product['product_stock'],
                    'category_name' => $product['category_name'] ?? '-',
                    'category_id' => $product['category_id'],
                    'product_image_url' => $product['product_image_url'],
                    'attributes' => $attributesByProduct[$pid] ?? [],
                    'product_vector' => $vectorByProductReadable[$pid] ?? [],
                    'cbf_details' => $cbfResult,
                    'user_details' => $userResult,
                    'pos_details' => $posResult,
                    'cbf_score' => $cbfScore,
                    'user_score' => $userScore,
                    'pos_score' => $posScore,
                    'final_score' => $finalScore,
                    'match_count' => $matchCount,
                    'total_base_attrs' => $totalBaseAttrs,
                    'is_purchased' => $isPurchased,
                    'matching_attrs' => $matchingAttrs,
                    'non_matching_attrs' => $nonMatchingAttrs
                ];

                $scoredCandidates[] = $productData;
            }

            // Sort by final score descending
            usort($scoredCandidates, fn($a, $b) => $b['final_score'] <=> $a['final_score']);

            // Assign status & rank
            $rankBefore = 1;
            $rankAfter = 1;
            $passedList = [];

            foreach ($scoredCandidates as &$candidate) {
                $pid = $candidate['product_id'];
                $candidate['rank_before'] = $rankBefore++;

                if ($candidate['is_purchased']) {
                    $candidate['status'] = 'Filtered';
                    $candidate['reason'] = 'Filtered karena sudah pernah dibeli.';
                    $candidate['rank_after'] = '-';
                } elseif ($candidate['final_score'] <= 0) {
                    $candidate['status'] = 'No Similarity';
                    $candidate['reason'] = 'Tidak direkomendasikan karena: ✗ tidak memiliki atribut yang sama (cosine similarity = 0)';
                    $candidate['rank_after'] = '-';
                } else {
                    $passedList[] = &$candidate;
                }
            }
            unset($candidate);

            // Assign rank_after
            foreach ($passedList as &$candidate) {
                $candidate['rank_after'] = $rankAfter++;
            }
            unset($candidate);

            // Slice recommendations (only passed ones, up to limit)
            $topRecs = array_slice($passedList, 0, $limit);
            $topRecIds = array_column($topRecs, 'product_id');

            // Set final statuses and reasons
            foreach ($passedList as &$candidate) {
                if (!in_array($candidate['product_id'], $topRecIds)) {
                    $candidate['status'] = 'Low Score';
                    $candidate['reason'] = 'Tidak direkomendasikan karena: skor akhir rendah (tidak masuk dalam top-N rekomendasi)';
                } else {
                    $candidate['status'] = 'Recommended';
                    
                    $reasons = [];
                    if ($baseProduct['category_id'] === $candidate['category_id']) {
                        $reasons[] = '✓ kategori sama';
                    }
                    foreach ($candidate['matching_attrs'] as $attr) {
                        $reasons[] = "✓ $attr sama";
                    }
                    
                    if ($customerId) {
                        $i = 0;
                        foreach ($userVectorReadable as $prefKey => $count) {
                            if ($i >= 3) break;
                            if (isset($candidate['product_vector'][$prefKey])) {
                                $parts = explode('::', $prefKey, 2);
                                $prefName = $parts[0] ?? '';
                                $prefVal = $parts[1] ?? '';
                                $reasons[] = "✓ customer sering membeli $prefName $prefVal";
                            }
                            $i++;
                        }
                    }

                    $candidate['reason'] = 'Recommended karena memiliki atribut: ' . implode(', ', $reasons);
                }
            }
            unset($candidate);

            $candidatesData = $scoredCandidates;
        }

        // Gather statistics
        $totalCandidates = count($candidatesData);
        $simGreaterThanZero = 0;
        $simEqualToZero = 0;
        $filteredCount = 0;
        $passedCount = 0;

        $sumCbf = 0.0;
        $sumUser = 0.0;
        $sumPos = 0.0;
        $sumFinal = 0.0;

        foreach ($candidatesData as $c) {
            if ($c['final_score'] > 0) {
                $simGreaterThanZero++;
            } else {
                $simEqualToZero++;
            }

            if ($c['status'] === 'Filtered') {
                $filteredCount++;
            } elseif ($c['status'] === 'Recommended' || $c['status'] === 'Low Score') {
                $passedCount++;
            }

            $sumCbf += $c['cbf_score'];
            $sumUser += $c['user_score'];
            $sumPos += $c['pos_score'];
            $sumFinal += $c['final_score'];
        }

        $avgCbf = $totalCandidates > 0 ? $sumCbf / $totalCandidates : 0;
        $avgUser = $totalCandidates > 0 ? $sumUser / $totalCandidates : 0;
        $avgPos = $totalCandidates > 0 ? $sumPos / $totalCandidates : 0;
        $avgFinal = $totalCandidates > 0 ? $sumFinal / $totalCandidates : 0;

        $stats = [
            'total_candidates' => $totalCandidates,
            'similarity_greater_than_zero' => $simGreaterThanZero,
            'similarity_equal_to_zero' => $simEqualToZero,
            'filtered_count' => $filteredCount,
            'passed_count' => $passedCount,
            'avg_cbf_score' => round($avgCbf, 6),
            'avg_user_score' => round($avgUser, 6),
            'avg_pos_score' => round($avgPos, 6),
            'avg_final_score' => round($avgFinal, 6),
        ];

        // Format final recommendations list (standard non-debug format)
        $recommendations = [];
        foreach ($candidatesData as $c) {
            if ($c['status'] === 'Recommended') {
                $recommendations[] = [
                    'product_id' => $c['product_id'],
                    'product_name' => $c['product_name'],
                    'product_brand' => $c['product_brand'],
                    'product_price' => $c['product_price'],
                    'product_stock' => $c['product_stock'],
                    'product_image_url' => $c['product_image_url'],
                    'score' => round($c['final_score'], 6),
                    'cbf_score' => round($c['cbf_score'], 6),
                ];
            }
        }

        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB';

        // Write log
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'customer_id' => $customerId,
            'product_id' => $productId,
            'candidate_count' => $totalCandidates,
            'all_scores' => array_column($candidatesData, 'final_score', 'product_id'),
            'chosen_products' => array_column($recommendations, 'product_id'),
            'filtering_reasons' => array_column($candidatesData, 'reason', 'product_id'),
            'execution_time_ms' => $executionTime,
            'peak_memory_usage' => $peakMemory
        ];
        log_message('info', 'Recommendation Debug Log: ' . json_encode($logData));

        if ($debug) {
            return [
                'customer_info' => $customerInfo,
                'base_product' => $baseProductInfo,
                'user_profile' => [
                    'user_vector' => $userVectorReadable,
                    'pos_vector' => $posVectorReadable,
                    'has_pos_data' => $hasPosData
                ],
                'weights' => [
                    'cbf_weight' => $wBase,
                    'user_weight' => $wUser,
                    'pos_weight' => $wPos
                ],
                'statistics' => $stats,
                'candidates' => $candidatesData,
                'recommendations' => $recommendations,
                'execution_time_ms' => $executionTime,
                'peak_memory_usage' => $peakMemory
            ];
        }

        return $recommendations;
    }

    /**
     * Compute cosine similarity step-by-step.
     */
    private function calculateSimilarityDetails(array $vecA, array $vecB, string $nameA, string $nameB, array $attrNameMap = []): array
    {
        $dot  = 0.0;
        $sumA = 0.0;
        $sumB = 0.0;

        foreach ($vecA as $key => $val) {
            $valB = $vecB[$key] ?? 0;
            $dot  += $val * $valB;
            $sumA += $val * $val;
        }
        foreach ($vecB as $val) {
            $sumB += $val * $val;
        }

        $magA = sqrt($sumA);
        $magB = sqrt($sumB);

        $similarity = 0.0;
        if ($magA > 0.0 && $magB > 0.0) {
            $similarity = $dot / ($magA * $magB);
        }

        $calculation = sprintf(
            "Dot Product = %g\nMagnitude %s = %s\nMagnitude %s = %s\nSimilarity\n%g / (%s × %s)\n= %s",
            $dot,
            $nameA,
            round($magA, 4),
            $nameB,
            round($magB, 4),
            $dot,
            round($magA, 4),
            round($magB, 4),
            round($similarity, 4)
        );

        return [
            'similarity' => round($similarity, 6),
            'dot_product' => $dot,
            'magnitude_a' => round($magA, 6),
            'magnitude_b' => round($magB, 6),
            'calculation' => $calculation
        ];
    }

    /**
     * Format vector keys into human readable names.
     */
    private function toReadableKey(string $key, array $attrNameMap): string
    {
        if (strpos($key, '::') !== false) {
            list($uuid, $val) = explode('::', $key, 2);
            $attrName = $attrNameMap[$uuid] ?? $uuid;
            return strtolower($attrName) . '::' . $val;
        }
        return $key;
    }

    /**
     * Compare personalization scores for a single product across two customers.
     */
    public function compareCustomers($productId, $customerAId, $customerBId)
    {
        $resultA = $this->getRecommendations($productId, $customerAId, 100, null, true);
        $resultB = $this->getRecommendations($productId, $customerBId, 100, null, true);

        if (isset($resultA['error']) || isset($resultB['error'])) {
            return ['error' => 'Product or customer not found'];
        }

        $baseProduct = $resultA['base_product'];

        // Align candidates and scores
        $comparisonList = [];
        $candidatesA = [];
        foreach ($resultA['candidates'] as $c) {
            $candidatesA[$c['product_id']] = $c;
        }

        foreach ($resultB['candidates'] as $c) {
            $pid = $c['product_id'];
            if (isset($candidatesA[$pid])) {
                $candA = $candidatesA[$pid];
                $comparisonList[] = [
                    'product_id' => $pid,
                    'product_name' => $c['product_name'],
                    'product_brand' => $c['product_brand'],
                    'product_price' => $c['product_price'],
                    'customer_a' => [
                        'cbf' => $candA['cbf_score'],
                        'user' => $candA['user_score'],
                        'pos' => $candA['pos_score'],
                        'final' => $candA['final_score'],
                        'status' => $candA['status'],
                        'reason' => $candA['reason']
                    ],
                    'customer_b' => [
                        'cbf' => $c['cbf_score'],
                        'user' => $c['user_score'],
                        'pos' => $c['pos_score'],
                        'final' => $c['final_score'],
                        'status' => $c['status'],
                        'reason' => $c['reason']
                    ]
                ];
            }
        }

        // Sort by final score diff
        $differences = [];
        foreach ($comparisonList as $comp) {
            $diff = abs($comp['customer_a']['final'] - $comp['customer_b']['final']);
            $differences[] = [
                'product' => $comp,
                'diff' => $diff
            ];
        }
        usort($differences, fn($a, $b) => $b['diff'] <=> $a['diff']);

        $customerAName = $resultA['customer_info']['customer_name'];
        $customerBName = $resultB['customer_info']['customer_name'];

        // Dynamic HTML explanation
        $explanation = "";

        if (!empty($differences) && $differences[0]['diff'] > 0.01) {
            $topDiff = $differences[0]['product'];
            $name = $topDiff['product_name'];
            $brand = $topDiff['product_brand'];
            $price = number_format($topDiff['product_price'], 0, ',', '.');
            $scoreA = number_format($topDiff['customer_a']['final'], 4);
            $scoreB = number_format($topDiff['customer_b']['final'], 4);

            // Get top user preferences list
            $prefAStr = [];
            $i = 0;
            foreach ($resultA['user_profile']['user_vector'] as $k => $v) {
                if ($i >= 3) break;
                $prefAStr[] = "<span class='badge bg-primary text-xxs text-white mb-1 me-1'>$k ($v x)</span>";
                $i++;
            }

            $prefBStr = [];
            $i = 0;
            foreach ($resultB['user_profile']['user_vector'] as $k => $v) {
                if ($i >= 3) break;
                $prefBStr[] = "<span class='badge bg-warning text-xxs text-dark mb-1 me-1'>$k ($v x)</span>";
                $i++;
            }

            $profileAHTML = "";
            if (!empty($prefAStr)) {
                $profileAHTML = "<p class='text-xs text-secondary mb-1'>Histori pembelian didominasi oleh:</p><div class='d-flex flex-wrap'>" . implode('', $prefAStr) . "</div>";
            } else {
                $profileAHTML = "<span class='badge bg-light text-danger text-xxs mb-1'><i class='fa-solid fa-snowflake me-1'></i>Cold Start</span><p class='text-xs text-secondary mt-1 mb-0'>Customer belum memiliki riwayat pembelian selesai, sehingga mendapat skor personalisasi default.</p>";
            }

            $profileBHTML = "";
            if (!empty($prefBStr)) {
                $profileBHTML = "<p class='text-xs text-secondary mb-1'>Histori pembelian didominasi oleh:</p><div class='d-flex flex-wrap'>" . implode('', $prefBStr) . "</div>";
            } else {
                $profileBHTML = "<span class='badge bg-light text-danger text-xxs mb-1'><i class='fa-solid fa-snowflake me-1'></i>Cold Start</span><p class='text-xs text-secondary mt-1 mb-0'>Customer belum memiliki riwayat pembelian selesai, sehingga mendapat skor personalisasi default.</p>";
            }

            $explanation = "
<div class='row align-items-center mb-4 border-bottom pb-3'>
  <div class='col-lg-5 text-center text-lg-start border-end'>
    <h6 class='text-uppercase text-secondary text-xxs font-weight-bold mb-1'>Kandidat Produk dengan Perbedaan Skor Terbesar</h6>
    <h4 class='font-weight-bolder text-primary mb-1'>$name</h4>
    <p class='text-xs text-muted mb-0'>$brand · Rp $price</p>
  </div>
  <div class='col-lg-7 mt-3 mt-lg-0'>
    <div class='row text-center'>
      <div class='col-6 border-end'>
        <h6 class='text-xs text-secondary font-weight-bold mb-1'>Skor Akhir $customerAName</h6>
        <span class='badge bg-gradient-primary text-md px-3 py-2 mt-1'>$scoreA</span>
      </div>
      <div class='col-6'>
        <h6 class='text-xs text-secondary font-weight-bold mb-1'>Skor Akhir $customerBName</h6>
        <span class='badge bg-gradient-warning text-md px-3 py-2 text-dark mt-1'>$scoreB</span>
      </div>
    </div>
  </div>
</div>

<div class='row mb-3'>
  <div class='col-md-6 mb-3 mb-md-0'>
    <div class='p-3 border border-radius-md h-100' style='background-color: rgba(94, 114, 228, 0.05);'>
      <h6 class='text-sm font-weight-bold text-primary mb-2'><i class='fa-solid fa-circle-user me-2'></i>Profil $customerAName</h6>
      $profileAHTML
    </div>
  </div>
  <div class='col-md-6'>
    <div class='p-3 border border-radius-md h-100' style='background-color: rgba(251, 99, 64, 0.05);'>
      <h6 class='text-sm font-weight-bold text-warning mb-2'><i class='fa-solid fa-circle-user me-2'></i>Profil $customerBName</h6>
      $profileBHTML
    </div>
  </div>
</div>

<div class='p-3 bg-white border-radius-md border border-light mt-3 shadow-xs'>
  <p class='text-xs text-secondary mb-0'>
    <i class='fa-solid fa-calculator text-info me-2'></i>
    <strong>Analisis Personalisasi:</strong> Nilai <code>cbfScore</code> kedua customer bernilai sama karena membandingkan kandidat produk yang sama dengan produk dasar. Perbedaan mencolok pada <code>finalScore</code> murni disebabkan oleh kecocokan atribut personalisasi (<code>userScore</code> dan <code>posScore</code>) yang disesuaikan dengan riwayat belanja masing-masing customer.
  </p>
</div>
";
        } else {
            $explanation = "
<div class='alert alert-info text-white text-sm border-0' role='alert'>
  <i class='fa-solid fa-circle-info me-2'></i>
  <strong>Kesamaan Karakteristik:</strong> Kedua customer memiliki riwayat pembelian yang sangat mirip atau keduanya berada dalam kondisi Cold Start. Oleh karena itu, skor rekomendasi dan urutan kandidat produk bernilai sama.
</div>
";
        }

        return [
            'base_product' => $baseProduct,
            'customer_a' => [
                'customer_id' => $customerAId,
                'customer_name' => $customerAName,
                'completed_orders' => $resultA['customer_info']['completed_orders'],
                'pos_orders' => $resultA['customer_info']['pos_orders'],
                'online_orders' => $resultA['customer_info']['marketplace_orders'],
                'preferences' => array_slice($resultA['user_profile']['user_vector'], 0, 5)
            ],
            'customer_b' => [
                'customer_id' => $customerBId,
                'customer_name' => $customerBName,
                'completed_orders' => $resultB['customer_info']['completed_orders'],
                'pos_orders' => $resultB['customer_info']['pos_orders'],
                'online_orders' => $resultB['customer_info']['marketplace_orders'],
                'preferences' => array_slice($resultB['user_profile']['user_vector'], 0, 5)
            ],
            'comparison' => $comparisonList,
            'explanation' => $explanation
        ];
    }
}
