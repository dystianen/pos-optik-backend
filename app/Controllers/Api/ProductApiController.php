<?php

namespace App\Controllers\Api;

use App\Libraries\R2Storage;
use App\Models\OrderStatusModel;
use App\Models\ProductAttributeModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use Config\OrderStatus;

class ProductApiController extends BaseApiController
{
    protected $productModel;
    protected $productImageModel;
    protected $attributeModel;
    protected $variantModel;
    protected $customerModel;
    protected $statusModel;
    protected $r2;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productImageModel = new ProductImageModel();
        $this->attributeModel = new ProductAttributeModel();
        $this->variantModel = new ProductVariantModel();
        $this->statusModel = new OrderStatusModel();
        $this->r2 = new R2Storage();
    }

    // GET /api/products/search
    public function apiSearchProduct()
    {
        $keyword = $this->request->getVar('q');

        if (empty($keyword)) {
            return $this->successResponse(
                [],
                'Empty search'
            );
        }

        $builder = $this->db->table('products p');

        $builder->select('
            p.product_id,
            p.product_name,
            p.product_price,
            p.product_brand,
            p.category_id,
            c.category_name,
            pi.url AS product_image_url
        ');

        $builder->join('product_categories c', 'c.category_id = p.category_id');
        $builder->join(
            'product_images pi',
            'pi.product_id = p.product_id AND pi.is_primary = 1',
            'left'
        );

        $builder->where('p.deleted_at', null);
        $builder->like('p.product_name', $keyword);
        $builder->orLike('p.product_brand', $keyword);
        $builder->orderBy('p.product_name', 'ASC');
        $builder->limit(20);

        $rows = $builder->get()->getResultArray();

        // Grouping hasil per category
        $grouped = [];

        foreach ($rows as $row) {
            $categoryId = $row['category_id'];

            if (!isset($grouped[$categoryId])) {
                $grouped[$categoryId] = [
                    'category_id'   => $categoryId,
                    'category_name' => $row['category_name'],
                    'products'      => []
                ];
            }

            $grouped[$categoryId]['products'][] = [
                'product_id'        => $row['product_id'],
                'product_name'      => $row['product_name'],
                'product_price'     => $row['product_price'],
                'product_brand'     => $row['product_brand'],
                'product_image_url' => $row['product_image_url']
            ];
        }

        return $this->successResponse(array_values($grouped));
    }

    // GET /api/products
    public function apiProduct()
    {
        $category = $this->request->getVar('category');
        $search   = $this->request->getVar('search');
        $page     = $this->request->getVar('page') ?? 1;
        $limit    = $this->request->getVar('limit') ?? 10;

        $jwtUser    = getJWTUser(false);
        $customerId = $jwtUser->user_id ?? null;

        // pakai builder dari model
        $builder = $this->productModel
            ->select('
                products.*,
                product_images.url AS product_image_url,
                IF(w.wishlist_id IS NULL, 0, 1) AS is_wishlist
            ')
            ->join(
                'product_images',
                'product_images.product_id = products.product_id 
                    AND product_images.is_primary = 1',
                'left'
            )
            ->where('products.deleted_at', null);

        if ($customerId) {
            $escapedCustomerId = $this->db->escape($customerId);
            $builder->join(
                'wishlists w',
                "w.product_id = products.product_id 
                AND w.customer_id = {$escapedCustomerId} 
                AND w.deleted_at IS NULL",
                'left'
            );
        } else {
            $builder->join(
                'wishlists w',
                '1 = 0',
                'left'
            );
        }

        if ($category) {
            $builder->where('products.category_id', $category);
        }

        if ($search) {
            $builder->groupStart()
                ->like('products.product_name', $search)
                ->orLike('products.product_brand', $search)
                ->groupEnd();
        }

        $products = $builder
            ->orderBy('products.created_at', 'DESC')
            ->paginate($limit, 'products', $page);

        if (empty($products)) {
            return $this->errorResponse('No products found');
        }

        // Ambil ID produk yang ditarik untuk query aggregate total_sold yang lebih efisien
        $productIds = array_column($products, 'product_id');

        $soldQuery = $this->db->table('order_items oi')
            ->select('oi.product_id, SUM(oi.quantity) AS total_sold')
            ->join('orders o', 'o.order_id = oi.order_id')
            ->whereIn('oi.product_id', $productIds)
            ->where('o.status_id', $this->statusModel->getIdByCode(OrderStatus::COMPLETED))
            ->groupBy('oi.product_id')
            ->get()
            ->getResultArray();

        $soldMap = [];
        foreach ($soldQuery as $row) {
            $soldMap[$row['product_id']] = (int) $row['total_sold'];
        }

        foreach ($products as &$product) {
            $product['total_sold'] = $soldMap[$product['product_id']] ?? 0;
        }
        unset($product);

        return $this->successResponse($products);
    }

    // GET /api/products/new-eyewear
    public function apiListNewEyewear()
    {
        $jwtUser    = getJWTUser(false);
        $customerId = $jwtUser->user_id ?? null;

        $search = $this->request->getVar('search');

        $builder = $this->db->table('products p');

        $builder->select('
            p.product_id,
            p.product_name,
            p.product_price,
            p.product_stock,
            p.product_brand,
            pi.url AS product_image_url,
            IF(w.wishlist_id IS NULL, 0, 1) AS is_wishlist
        ');

        $builder->join('product_categories pc', 'pc.category_id = p.category_id');
        $builder->join(
            'product_images pi',
            'pi.product_id = p.product_id AND pi.is_primary = 1',
            'left'
        );

        if ($customerId) {
            $escapedCustomerId = $this->db->escape($customerId);
            $builder->join(
                'wishlists w',
                "w.product_id = p.product_id 
                    AND w.customer_id = {$escapedCustomerId} 
                    AND w.deleted_at IS NULL",
                'left'
            );
        } else {
            $builder->join(
                'wishlists w',
                '1 = 0',
                'left'
            );
        }

        if (!empty($search)) {
            $builder->like('p.product_name', $search);
        }

        $builder->where('p.deleted_at', null);
        $builder->where('pc.category_name', 'Sunglasses');
        $builder->orderBy('p.created_at', 'DESC');
        $builder->limit(10);

        $products = $builder->get()->getResultArray();

        if (empty($products)) {
            return $this->successResponse($products);
        }

        // Ambil ID produk yang ditarik untuk query aggregate total_sold yang lebih efisien
        $productIds = array_column($products, 'product_id');

        $soldQuery = $this->db->table('order_items oi')
            ->select('oi.product_id, SUM(oi.quantity) AS total_sold')
            ->join('orders o', 'o.order_id = oi.order_id')
            ->whereIn('oi.product_id', $productIds)
            ->where('o.status_id', $this->statusModel->getIdByCode(OrderStatus::COMPLETED))
            ->groupBy('oi.product_id')
            ->get()
            ->getResultArray();

        $soldMap = [];
        foreach ($soldQuery as $row) {
            $soldMap[$row['product_id']] = (int) $row['total_sold'];
        }

        foreach ($products as &$product) {
            $product['total_sold'] = $soldMap[$product['product_id']] ?? 0;
        }
        unset($product);

        return $this->successResponse($products);
    }

    // GET /api/products/best-seller
    public function apiListBestSeller()
    {
        $jwtUser    = getJWTUser(false);
        $customerId = $jwtUser->user_id ?? null;

        $search = $this->request->getVar('search');

        // Pertama, cari 10 product ID dengan total penjualan terbanyak
        $bestSellerBuilder = $this->db->table('order_items oi');
        $bestSellerBuilder->select('oi.product_id, SUM(oi.quantity) AS total_sold')
            ->join('orders o', 'o.order_id = oi.order_id')
            ->join('products p', 'p.product_id = oi.product_id')
            ->where('o.status_id', $this->statusModel->getIdByCode(OrderStatus::COMPLETED))
            ->where('p.deleted_at', null);

        if (!empty($search)) {
            $bestSellerBuilder->like('p.product_name', $search);
        }

        $bestSellersRaw = $bestSellerBuilder
            ->groupBy('oi.product_id')
            ->orderBy('total_sold', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        if (empty($bestSellersRaw)) {
            return $this->successResponse([]);
        }

        $productIds = array_column($bestSellersRaw, 'product_id');

        $totalsMap = [];
        foreach ($bestSellersRaw as $bs) {
            $totalsMap[$bs['product_id']] = (int) $bs['total_sold'];
        }

        // Kedua, ambil detail relasinya secara paralel hanya untuk 10 ID tersebut
        $pBuilder = $this->db->table('products p');
        $pBuilder->select('
            p.product_id,
            p.product_name,
            p.product_price,
            p.product_stock,
            p.product_brand,
            pi.url AS product_image_url,
            IF(w.wishlist_id IS NULL, 0, 1) AS is_wishlist
        ');
        $pBuilder->join(
            'product_images pi',
            'pi.product_id = p.product_id AND pi.is_primary = 1',
            'left'
        );

        if ($customerId) {
            $escapedCustomerId = $this->db->escape($customerId);
            $pBuilder->join(
                'wishlists w',
                "w.product_id = p.product_id 
                AND w.customer_id = {$escapedCustomerId} 
                AND w.deleted_at IS NULL",
                'left'
            );
        } else {
            $pBuilder->join(
                'wishlists w',
                '1 = 0',
                'left'
            );
        }

        $pBuilder->whereIn('p.product_id', $productIds);
        $productsData = $pBuilder->get()->getResultArray();

        // Index the fetched products by product_id
        $productsById = [];
        foreach ($productsData as $pd) {
            $productsById[$pd['product_id']] = $pd;
        }

        // Map data keeping the correct DESC numerical sort order
        $bestSeller = [];
        foreach ($bestSellersRaw as $bs) {
            $pid = $bs['product_id'];
            if (isset($productsById[$pid])) {
                $item = $productsById[$pid];
                $item['total_sold'] = $totalsMap[$pid];
                $bestSeller[] = $item;
            }
        }

        return $this->successResponse($bestSeller);
    }

    // ===================================================================
    // GET /api/products/recommendations/{productId}
    // Product-based CBF: rekomendasi mirip dengan produk tertentu
    // ===================================================================
    public function apiProductRecommendations($productId)
    {
        $limit  = (int) ($this->request->getVar('limit') ?? 10);
        $search = $this->request->getVar('search');

        if (!$productId) {
            return $this->errorResponse('productId is required');
        }

        // ──────────────────────────────────────────────
        // STEP 1 · Input data pengguna (opsional)
        // Baca JWT customer untuk personalisasi
        // ──────────────────────────────────────────────
        $jwtUser    = getJWTUser(false);
        $customerId = $jwtUser->user_id ?? null;

        // ──────────────────────────────────────────────
        // STEP 2 · Ekstraksi fitur produk dasar
        // ──────────────────────────────────────────────
        $baseAttrRows = $this->db->table('product_attribute_values pav')
            ->select('pav.attribute_id, pav.value')
            ->where('pav.product_id', $productId)
            ->where('pav.deleted_at', null)
            ->get()
            ->getResultArray();

        if (empty($baseAttrRows)) {
            return $this->successResponse();
        }

        // Bangun one-hot vector untuk produk dasar
        $baseVector = [];
        foreach ($baseAttrRows as $row) {
            $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
            $baseVector[$key] = 1;
        }

        // ──────────────────────────────────────────────
        // STEP 3 · Profil pengguna dari histori POS
        // ──────────────────────────────────────────────
        $purchasedIds   = [];
        $userVector     = [];
        $hasPosData     = false;
        $posVector      = [];

        if ($customerId) {
            $completedStatusId = $this->statusModel->getIdByCode(\Config\OrderStatus::COMPLETED);

            // Ambil semua order completed customer
            $purchaseRows = $this->db->table('order_items oi')
                ->select('oi.product_id, o.order_type')
                ->join('orders o', 'o.order_id = oi.order_id')
                ->where('o.customer_id', $customerId)
                ->where('o.status_id', $completedStatusId)
                ->where('o.deleted_at', null)
                ->get()
                ->getResultArray();

            $purchasedIds = array_unique(array_column($purchaseRows, 'product_id'));

            // Cek apakah ada transaksi offline (data POS)
            $hasPosData = !empty(array_filter($purchaseRows, fn($r) => $r['order_type'] === 'offline'));

            // Ambil atribut semua produk yang pernah dibeli → bangun user profile vector
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
                    // User vector: frekuensi (TF sederhana)
                    $userVector[$key] = ($userVector[$key] ?? 0) + 1;

                    // POS vector: hanya dari transaksi offline
                    if ($row['order_type'] === 'offline') {
                        $posVector[$key] = ($posVector[$key] ?? 0) + 1;
                    }
                }
            }
        }

        // ──────────────────────────────────────────────
        // STEP 4 · Kandidat produk
        // ──────────────────────────────────────────────
        $builder = $this->db->table('products p')
            ->select('
                p.product_id,
                p.product_name,
                p.product_brand,
                p.product_price,
                p.product_stock,
                pi.url AS product_image_url
            ')
            ->join(
                'product_images pi',
                'pi.product_id = p.product_id
                    AND pi.type = "gallery"
                    AND pi.is_primary = 1',
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

        $products = $builder->limit($limit * 5)->get()->getResultArray();

        if (empty($products)) {
            return $this->successResponse();
        }

        // ──────────────────────────────────────────────
        // STEP 5 · Atribut semua kandidat
        // ──────────────────────────────────────────────
        $candidateIds = array_column($products, 'product_id');

        $allAttrRows = $this->db->table('product_attribute_values pav')
            ->select('pav.product_id, pav.attribute_id, pav.value')
            ->whereIn('pav.product_id', $candidateIds)
            ->where('pav.deleted_at', null)
            ->get()
            ->getResultArray();

        // Bangun vector per kandidat produk (one-hot)
        $vectorByProduct = [];
        foreach ($allAttrRows as $row) {
            $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
            $vectorByProduct[$row['product_id']][$key] = 1;
        }

        // ──────────────────────────────────────────────
        // STEP 6 · Representasi Vektor + Cosine Similarity
        // sim(u, p) = (u · p) / (|u| × |p|)
        // ──────────────────────────────────────────────
        $recommendations = [];

        // Bobot: 40% produk-dasar CBF + 40% user profile + 20% POS (jika ada)
        $wBase = 0.40;
        $wUser = $hasPosData ? 0.40 : 0.60;
        $wPos  = $hasPosData ? 0.20 : 0.00;

        foreach ($products as $product) {
            $pid = $product['product_id'];

            if (!isset($vectorByProduct[$pid])) {
                continue;
            }

            $candidateVec = $vectorByProduct[$pid];

            // Cosine similarity: produk dasar vs kandidat
            $cbfScore = $this->cosineSimilarity($baseVector, $candidateVec);

            if ($cbfScore <= 0 && empty($userVector)) {
                continue;
            }

            // Cosine similarity: user profile vs kandidat
            $userScore = empty($userVector) ? 0.0 : $this->cosineSimilarity($userVector, $candidateVec);

            // STEP 6a · Ada data POS? → Bobot POS (Gabung Skor)
            $posScore = $hasPosData ? $this->cosineSimilarity($posVector, $candidateVec) : 0.0;

            $finalScore = ($wBase * $cbfScore) + ($wUser * $userScore) + ($wPos * $posScore);

            if ($finalScore > 0) {
                $product['score']     = round($finalScore, 6);
                $product['cbf_score'] = round($cbfScore, 6);
                $recommendations[]    = $product;
            }
        }

        // ──────────────────────────────────────────────
        // STEP 7 · Urutkan Skor Similarity
        // ──────────────────────────────────────────────
        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);

        // ──────────────────────────────────────────────
        // STEP 8 · Filter produk yang sudah dibeli
        // ──────────────────────────────────────────────
        if (!empty($purchasedIds)) {
            $recommendations = array_values(array_filter(
                $recommendations,
                fn($p) => !in_array($p['product_id'], $purchasedIds)
            ));
        }

        // ──────────────────────────────────────────────
        // STEP 9 · Ambil Top-N Rekomendasi
        // ──────────────────────────────────────────────
        $recommendations = array_slice($recommendations, 0, $limit);

        return $this->successResponse($recommendations);
    }

    // ===================================================================
    // GET /api/products/my-recommendations  (auth required)
    // User-centric CBF: rekomendasi berdasar histori beli customer
    // ===================================================================
    public function apiMyRecommendations()
    {
        $jwtUser = getJWTUser();
        if (!$jwtUser) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $customerId = $jwtUser->user_id;
        $limit      = (int) ($this->request->getVar('limit') ?? 10);

        $completedStatusId = $this->statusModel->getIdByCode(\Config\OrderStatus::COMPLETED);

        // ──────────────────────────────────────────────
        // STEP 1 · Input: riwayat beli + POS data
        // ──────────────────────────────────────────────
        $purchaseRows = $this->db->table('order_items oi')
            ->select('oi.product_id, o.order_type')
            ->join('orders o', 'o.order_id = oi.order_id')
            ->where('o.customer_id', $customerId)
            ->where('o.status_id', $completedStatusId)
            ->where('o.deleted_at', null)
            ->get()
            ->getResultArray();

        $purchasedIds = array_unique(array_column($purchaseRows, 'product_id'));

        // Cold start: belum ada histori → fallback best seller (tanpa filter)
        if (empty($purchasedIds)) {
            return $this->apiBestSellerFallback($limit);
        }

        $hasPosData = !empty(array_filter($purchaseRows, fn($r) => $r['order_type'] === 'offline'));

        // ──────────────────────────────────────────────
        // STEP 2 · Ekstraksi fitur dari produk yang dibeli
        // ──────────────────────────────────────────────
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

        // ──────────────────────────────────────────────
        // STEP 3 · Pembuatan Profil Pengguna (vektor TF)
        // ──────────────────────────────────────────────
        $userVector = [];
        $posVector  = [];

        foreach ($userAttrRows as $row) {
            $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
            $userVector[$key] = ($userVector[$key] ?? 0) + 1;
            if ($row['order_type'] === 'offline') {
                $posVector[$key] = ($posVector[$key] ?? 0) + 1;
            }
        }

        if (empty($userVector)) {
            return $this->apiBestSellerFallback($limit);
        }

        // ──────────────────────────────────────────────
        // STEP 4 · Kandidat produk (exclude yang sudah dibeli)
        // ──────────────────────────────────────────────
        $builder = $this->db->table('products p')
            ->select('
                p.product_id,
                p.product_name,
                p.product_brand,
                p.product_price,
                p.product_stock,
                pi.url AS product_image_url
            ')
            ->join(
                'product_images pi',
                'pi.product_id = p.product_id
                    AND pi.type = "gallery"
                    AND pi.is_primary = 1',
                'left'
            )
            ->where('p.deleted_at', null)
            ->whereNotIn('p.product_id', $purchasedIds)
            ->limit($limit * 5);

        $products = $builder->get()->getResultArray();

        if (empty($products)) {
            return $this->successResponse([]);
        }

        // ──────────────────────────────────────────────
        // STEP 5 · Atribut kandidat → vektor
        // ──────────────────────────────────────────────
        $candidateIds = array_column($products, 'product_id');

        $allAttrRows = $this->db->table('product_attribute_values pav')
            ->select('pav.product_id, pav.attribute_id, pav.value')
            ->whereIn('pav.product_id', $candidateIds)
            ->where('pav.deleted_at', null)
            ->get()
            ->getResultArray();

        $vectorByProduct = [];
        foreach ($allAttrRows as $row) {
            $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
            $vectorByProduct[$row['product_id']][$key] = 1;
        }

        // ──────────────────────────────────────────────
        // STEP 6 · Cosine Similarity + POS Weighting
        // ──────────────────────────────────────────────
        $wUser = $hasPosData ? 0.60 : 1.00;
        $wPos  = $hasPosData ? 0.40 : 0.00;

        $recommendations = [];

        foreach ($products as $product) {
            $pid = $product['product_id'];

            if (!isset($vectorByProduct[$pid])) {
                continue;
            }

            $candidateVec = $vectorByProduct[$pid];
            $userScore    = $this->cosineSimilarity($userVector, $candidateVec);
            $posScore     = $hasPosData ? $this->cosineSimilarity($posVector, $candidateVec) : 0.0;
            $finalScore   = ($wUser * $userScore) + ($wPos * $posScore);

            if ($finalScore > 0) {
                $product['score'] = round($finalScore, 6);
                $recommendations[] = $product;
            }
        }

        // ──────────────────────────────────────────────
        // STEP 7 · Urutkan + Ambil Top-N
        // ──────────────────────────────────────────────
        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);
        $recommendations = array_slice($recommendations, 0, $limit);

        return $this->successResponse($recommendations);
    }

    // ===================================================================
    // HELPER: Cosine Similarity antara dua vektor asosiatif
    // sim(u, p) = (u · p) / (|u| × |p|)
    // ===================================================================
    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dot  = 0.0;
        $magA = 0.0;
        $magB = 0.0;

        foreach ($vecA as $key => $val) {
            $dot  += $val * ($vecB[$key] ?? 0);
            $magA += $val * $val;
        }
        foreach ($vecB as $val) {
            $magB += $val * $val;
        }

        if ($magA == 0.0 || $magB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($magA) * sqrt($magB));
    }

    // ===================================================================
    // HELPER: Cold-start fallback → kembalikan best seller
    // ===================================================================
    private function apiBestSellerFallback(int $limit): \CodeIgniter\HTTP\ResponseInterface
    {
        $completedStatusId = $this->statusModel->getIdByCode(\Config\OrderStatus::COMPLETED);

        $bsRows = $this->db->table('order_items oi')
            ->select('oi.product_id, SUM(oi.quantity) AS total_sold')
            ->join('orders o', 'o.order_id = oi.order_id')
            ->join('products p', 'p.product_id = oi.product_id')
            ->where('o.status_id', $completedStatusId)
            ->where('p.deleted_at', null)
            ->groupBy('oi.product_id')
            ->orderBy('total_sold', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        if (empty($bsRows)) {
            return $this->successResponse([]);
        }

        $bsIds    = array_column($bsRows, 'product_id');
        $bsTotals = array_column($bsRows, 'total_sold', 'product_id');

        $products = $this->db->table('products p')
            ->select('
                p.product_id,
                p.product_name,
                p.product_brand,
                p.product_price,
                p.product_stock,
                pi.url AS product_image_url
            ')
            ->join(
                'product_images pi',
                'pi.product_id = p.product_id AND pi.type = "gallery" AND pi.is_primary = 1',
                'left'
            )
            ->whereIn('p.product_id', $bsIds)
            ->get()
            ->getResultArray();

        $byId = [];
        foreach ($products as $p) {
            $byId[$p['product_id']] = $p;
        }

        $result = [];
        foreach ($bsRows as $bs) {
            $pid = $bs['product_id'];
            if (isset($byId[$pid])) {
                $item               = $byId[$pid];
                $item['total_sold'] = (int) $bsTotals[$pid];
                $item['score']      = 0;
                $result[]           = $item;
            }
        }

        return $this->successResponse($result);
    }

    // GET /api/products/{id}
    public function apiProductDetail($id)
    {
        /**
         * ======================
         * PRODUCT
         * ======================
         */
        $product = $this->productModel
            ->select('
                products.product_id,
                products.category_id,
                products.product_name,
                products.description,
                products.product_price,
                products.product_stock,
                pc.is_prescription_supported,
                pc.category_name
            ')
            ->join('product_categories pc', 'pc.category_id = products.category_id', 'left')
            ->where('products.product_id', $id)
            ->first();

        if (!$product) {
            return $this->errorResponse('Product not found');
        }

        /**
         * ======================
         * GALLERY IMAGES
         * ======================
         */
        $galleryImages = $this->productImageModel
            ->select('product_image_id, product_id, url, alt_text, is_primary')
            ->where([
                'product_id' => $id,
                'type'       => 'gallery'
            ])
            ->orderBy('is_primary', 'DESC')
            ->findAll();

        /**
         * ======================
         * VARIANTS + IMAGE
         * ======================
         */
        $variants = $this->variantModel
            ->select('
            product_variants.variant_id,
            product_variants.product_id,
            product_variants.variant_name,
            product_variants.price,
            product_variants.stock,

            pi.product_image_id,
            pi.url,
            pi.alt_text
        ')
            ->join(
                'product_variant_images pvi',
                'pvi.variant_id = product_variants.variant_id',
                'left'
            )
            ->join(
                'product_images pi',
                'pi.product_image_id = pvi.product_image_id',
                'left'
            )
            ->where('product_variants.product_id', $id)
            ->findAll();

        /**
         * ======================
         * FORMAT IMAGE
         * ======================
         */
        foreach ($variants as &$variant) {

            $variant['image'] = $variant['product_image_id']
                ? [
                    'product_image_id' => $variant['product_image_id'],
                    'url'              => $variant['url'],
                    'alt_text'         => $variant['alt_text'],
                ]
                : null;

            unset(
                $variant['product_image_id'],
                $variant['url'],
                $variant['alt_text']
            );
        }

        $product['gallery']  = $galleryImages;
        $product['variants'] = $variants;
        $product['is_prescription_supported'] =
            (bool) $product['is_prescription_supported'];

        return $this->successResponse($product);
    }

    // GET /api/products/{id}/attributes
    public function apiProductAttributes($productId)
    {
        // Ambil PAV + Attribute
        $rows = $this->db->table('product_attribute_values pav')
            ->select('
                a.attribute_id,
                a.attribute_name,
                pav.value
            ')
            ->join('product_attributes a', 'a.attribute_id = pav.attribute_id')
            ->where('pav.product_id', $productId)
            ->where('pav.deleted_at', null)
            ->where('a.deleted_at', null)
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return $this->successResponse();
        }

        // Grouping by attribute
        $attributes = [];

        foreach ($rows as $row) {
            $attrId = $row['attribute_id'];

            if (!isset($attributes[$attrId])) {
                $attributes[$attrId] = [
                    'attribute_id'   => $attrId,
                    'attribute_name' => $row['attribute_name'],
                    'values'         => []
                ];
            }

            $attributes[$attrId]['values'][] = $row['value'];
        }

        return $this->successResponse(array_values($attributes));
    }
}
