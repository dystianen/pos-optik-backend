<?php

namespace App\Controllers\Api;

use App\Libraries\R2Storage;
use App\Models\OrderStatusModel;
use App\Models\ProductAttributeModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use CodeIgniter\HTTP\ResponseInterface;
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

        $currentPage = $this->productModel->pager->getCurrentPage('products');

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

    // GET /api/products/recommendations
    public function apiProductRecommendations($productId)
    {
        $limit     = (int) ($this->request->getVar('limit') ?? 10);
        $search    = $this->request->getVar('search');

        if (!$productId) {
            return $this->errorResponse('productId is required');
        }

        /**
         * ==========================
         * 1. BASE PRODUCT ATTRIBUTES
         * ==========================
         */
        $baseAttributes = $this->db->table('product_attribute_values pav')
            ->select('pav.attribute_id, pav.value')
            ->where('pav.product_id', $productId)
            ->where('pav.deleted_at', null)
            ->get()
            ->getResultArray();

        if (empty($baseAttributes)) {
            return $this->successResponse();
        }

        $baseAttrMap = [];
        foreach ($baseAttributes as $row) {
            $baseAttrMap[$row['attribute_id']][] = strtolower(trim($row['value']));
        }

        /**
         * ==========================
         * 2. CANDIDATE PRODUCTS
         * ==========================
         */
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

        // 🔍 SEARCH FILTER (TETAP ADA)
        if (!empty($search)) {
            $builder->groupStart()
                ->like('p.product_name', $search)
                ->orLike('p.product_brand', $search)
                ->groupEnd();
        }

        // Ambil lebih banyak untuk scoring
        $products = $builder
            ->limit($limit * 3)
            ->get()
            ->getResultArray();

        if (empty($products)) {
            return $this->successResponse();
        }

        /**
         * ==========================
         * 3. ATTRIBUTES FOR CANDIDATES
         * ==========================
         */
        $productIds = array_column($products, 'product_id');

        $allAttributes = $this->db->table('product_attribute_values pav')
            ->select('pav.product_id, pav.attribute_id, pav.value')
            ->whereIn('pav.product_id', $productIds)
            ->where('pav.deleted_at', null)
            ->get()
            ->getResultArray();

        $attrByProduct = [];
        foreach ($allAttributes as $row) {
            $attrByProduct[$row['product_id']][] = [
                'attribute_id' => $row['attribute_id'],
                'value'        => strtolower(trim($row['value']))
            ];
        }

        /**
         * ==========================
         * 4. CONTENT-BASED SCORING
         * ==========================
         */
        $recommendations = [];

        foreach ($products as $product) {
            $pid   = $product['product_id'];
            $score = 0;

            if (!isset($attrByProduct[$pid])) {
                continue;
            }

            foreach ($attrByProduct[$pid] as $attr) {
                $attrId = $attr['attribute_id'];
                $value  = $attr['value'];

                if (!isset($baseAttrMap[$attrId])) {
                    continue;
                }

                // Same attribute
                $score += 1;

                // Exact value match
                if (in_array($value, $baseAttrMap[$attrId], true)) {
                    $score += 2;
                }
            }

            if ($score > 0) {
                $product['score'] = $score;
                $recommendations[] = $product;
            }
        }

        /**
         * ==========================
         * 5. SORT & LIMIT
         * ==========================
         */
        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);

        $recommendations = array_slice($recommendations, 0, $limit);

        return $this->successResponse($recommendations);
    }

    // GET /api/products/{id}
    public function apiProductDetail($id)
    {
        /**
         * ======================
         * PRODUCT
         * ======================
         */
        $product = $this->productModel->find($id);

        if (!$product) {
            return $this->errorResponse('Product not found');
        }

        /**
         * ======================
         * GALLERY IMAGES
         * ======================
         */
        $galleryImages = $this->productImageModel
            ->select('product_image_id, url, alt_text, is_primary')
            ->where('product_id', $id)
            ->where('type', 'gallery')
            ->orderBy('is_primary', 'DESC')
            ->findAll();

        /**
         * ======================
         * VARIANTS
         * ======================
         */
        $variants = $this->variantModel
            ->select('variant_id, variant_name, price, stock')
            ->where('product_id', $id)
            ->findAll();

        /**
         * ======================
         * VARIANT IMAGE (1 VARIANT = 1 IMAGE)
         * ======================
         */
        $variantImageMap = [];
        $variantIds = array_column($variants, 'variant_id');

        if (!empty($variantIds)) {
            $variantImages = $this->db->table('product_variant_images pvi')
                ->select('
                pvi.variant_id,
                pi.product_image_id,
                pi.url,
                pi.alt_text
            ')
                ->join(
                    'product_images pi',
                    'pi.product_image_id = pvi.product_image_id'
                )
                ->whereIn('pvi.variant_id', $variantIds)
                ->get()
                ->getResultArray();

            // Mapping SINGLE image → variant
            foreach ($variantImages as $img) {
                $variantImageMap[$img['variant_id']] = [
                    'product_image_id' => $img['product_image_id'],
                    'url'              => $img['url'],
                    'alt_text'         => $img['alt_text'],
                ];
            }
        }

        // Inject image ke variant (bukan array)
        foreach ($variants as &$variant) {
            $variant['image'] = $variantImageMap[$variant['variant_id']] ?? null;
        }
        unset($variant);

        /**
         * ======================
         * RESPONSE
         * ======================
         */
        $product['gallery']  = $galleryImages;
        $product['variants'] = $variants;

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
