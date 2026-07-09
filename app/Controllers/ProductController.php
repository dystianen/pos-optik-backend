<?php

namespace App\Controllers;

use App\Libraries\R2Storage;
use App\Models\CustomerModel;
use App\Models\ProductAttributeModel;
use App\Models\ProductAttributeValueModel;
use App\Models\ProductCategoryModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;
use App\Models\ProductVariantAttributeModel;
use App\Models\ProductVariantImageModel;
use App\Models\ProductVariantModel;
use App\Models\ProductVariantValueModel;

class ProductController extends BaseController
{
    protected $productModel;
    protected $productImageModel;
    protected $attributeModel;
    protected $pavModel;
    protected $variantModel;
    protected $pvValueModel;
    protected $categoryModel;
    protected $variantImageModel;
    protected $productVariantAttributeModel;
    protected $r2;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productImageModel = new ProductImageModel();
        $this->categoryModel = new ProductCategoryModel();
        $this->attributeModel = new ProductAttributeModel();
        $this->pavModel = new ProductAttributeValueModel();
        $this->variantModel = new ProductVariantModel();
        $this->variantImageModel = new ProductVariantImageModel();
        $this->pvValueModel = new ProductVariantValueModel();
        $this->productVariantAttributeModel = new ProductVariantAttributeModel();
        $this->r2 = new R2Storage();
        helper(['slug', 'sku']);
    }

    public function webIndex()
    {
        $attributes = $this->attributeModel->findAll();

        $currentPage = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $search     = $this->request->getGet('search');
        $categoryId = $this->request->getGet('category_id');
        $brand      = $this->request->getGet('brand');

        $builder = $this->productModel
            ->select('
                products.*,
                product_categories.category_name,
                COUNT(product_variants.variant_id) AS total_variants
            ')
            ->join('product_categories', 'product_categories.category_id = products.category_id')
            ->join(
                'product_variants',
                'product_variants.product_id = products.product_id AND product_variants.deleted_at IS NULL',
                'left'
            )
            ->groupBy('products.product_id')
            ->orderBy('products.created_at', 'DESC');

        // Tambahkan filter pencarian jika ada keyword
        if (!empty($search)) {
            $builder->groupStart()
                ->like('products.product_name', $search)
                ->orLike('products.product_sku', $search)
                ->orLike('products.product_brand', $search)
                ->orLike('product_categories.category_name', $search)
                ->groupEnd();
        }

        if (!empty($categoryId)) {
            $builder->where('products.category_id', $categoryId);
        }

        if (!empty($brand)) {
            $builder->where('products.product_brand', $brand);
        }

        // Clone builder untuk count
        $countBuilder = clone $builder;

        $products = $builder->findAll($totalLimit, $offset);
        $totalRows = $countBuilder->countAllResults(false);
        $totalPages = (int) ceil($totalRows / $totalLimit);

        $categories = $this->categoryModel->findAll();
        $distinctBrands = $this->productModel
            ->select('product_brand')
            ->distinct()
            ->where('product_brand !=', '')
            ->where('deleted_at IS NULL')
            ->findAll();

        $data = [
            "attributes" => $attributes,
            "products" => $products,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
            "search" => $search, // lempar ke view agar input tetap terisi
            "categories" => $categories,
            "brands" => array_column($distinctBrands, 'product_brand'),
            "selectedCategoryId" => $categoryId,
            "selectedBrand" => $brand,
        ];

        return view('products/v_index', $data);
    }

    public function form()
    {
        $data = [];

        $id = $this->request->getGet('id');

        // --- STATIC DATA ---
        $data['categories'] = $this->categoryModel->findAll();

        if (empty($id)) {
            log_message('debug', 'Mode: CREATE (no ID)');

            $data['product'] = null;
            $data['product_images'] = [];
            $data['pav_values'] = [];
            $data['selected_attributes'] = [];
            $data['selected_attribute_values'] = [];
            $data['variants'] = [];
            $data['attributes'] = []; // Empty on create since no category is selected yet

            return view('products/v_form', $data);
        }


        // ------------------------------------------------------------------
        // 1. PRODUCT
        // ------------------------------------------------------------------
        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('failed', 'Product not found.');
        }
        $data['product'] = $product;

        // ✅ GET ATTRIBUTES FOR THIS PRODUCT'S CATEGORY
        $attributes = $this->attributeModel
            ->where('category_id', $product['category_id'])
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        foreach ($attributes as &$attr) {
            // Load master values untuk setiap attribute
            $attr['values'] = $this->db->table('product_attribute_master_values')
                ->where('attribute_id', $attr['attribute_id'])
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();
        }
        $data['attributes'] = $attributes;

        // ------------------------------------------------------------------
        // 2. PRODUCT IMAGES
        // ------------------------------------------------------------------
        $images = $this->productImageModel
            ->where('product_id', $id)
            ->where('type', 'gallery')
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        $data['product_images'] = $images;

        // ------------------------------------------------------------------
        // 3. PRODUCT ATTRIBUTE VALUES (PAV)
        // ------------------------------------------------------------------
        $pav = $this->pavModel
            ->where('product_id', $id)
            ->where('deleted_at', null)
            ->findAll();

        // Format: pav_values[attribute_id] = [pav_id, value, value2, ...]
        $pavValues = [];
        foreach ($pav as $row) {
            $attrId = $row['attribute_id'];

            if (!isset($pavValues[$attrId])) {
                $pavValues[$attrId] = [
                    'pav_ids' => [],
                    'values' => []
                ];
            }

            $pavValues[$attrId]['pav_ids'][] = $row['pav_id'];
            $pavValues[$attrId]['values'][] = $row['value'];
        }
        $data['pav_values'] = $pavValues;

        // ------------------------------------------------------------------
        // (NEW) — SELECTED ATTRIBUTE IDs
        // ------------------------------------------------------------------
        $variantAttrs = $this->productVariantAttributeModel
            ->where('product_id', $id)
            ->findAll();

        $selectedAttributes = array_column($variantAttrs, 'attribute_id');
        $data['selected_attributes'] = $selectedAttributes;

        // ------------------------------------------------------------------
        // (NEW) — SELECTED ATTRIBUTE VALUES (flat array)
        // ------------------------------------------------------------------
        $selectedAttributeValues = [];

        foreach ($pav as $row) {
            $attrId = $row['attribute_id'];

            if (!isset($selectedAttributeValues[$attrId])) {
                $selectedAttributeValues[$attrId] = [];
            }

            $selectedAttributeValues[$attrId][] = $row['value'];
        }

        $data['selected_attribute_values'] = $selectedAttributeValues;


        // ------------------------------------------------------------------
        // 4. PRODUCT VARIANTS
        // ------------------------------------------------------------------
        $variants = $this->variantModel
            ->where('product_id', $id)
            ->where('deleted_at', null)
            ->findAll();

        foreach ($variants as &$v) {
            $variantId = $v['variant_id'];

            // Mapping ke PAV
            $pvValues = $this->pvValueModel
                ->select('product_attribute_values.attribute_id, product_attribute_values.value, product_attributes.attribute_name')
                ->join('product_attribute_values', 'product_attribute_values.pav_id = product_variant_values.pav_id')
                ->join('product_attributes', 'product_attributes.attribute_id = product_attribute_values.attribute_id')
                ->where('product_variant_values.variant_id', $variantId)
                ->where('product_variant_values.deleted_at', null)
                ->findAll();

            $v['pav_mapping'] = $pvValues;

            // If signature is empty in DB, compute it on the fly
            if (empty($v['variant_signature'])) {
                $v['variant_signature'] = $this->generateSignature($pvValues);
            }

            // Variant Image
            $variantImage = $this->variantImageModel
                ->select('product_images.*')
                ->join('product_images', 'product_images.product_image_id = product_variant_images.product_image_id')
                ->where('product_variant_images.variant_id', $variantId)
                ->where('product_images.deleted_at', null)
                ->first();

            $v['variant_image'] = $variantImage;
        }

        $data['variants'] = $variants;

        return view('products/v_form', $data);
    }

    public function save()
    {
        $db = \Config\Database::connect();
        $request = $this->request;
        $post = $request->getPost();
        $id = $post['id'] ?? null;

        log_message('debug', '========== SAVE PRODUCT START ==========');
        log_message('debug', 'POST: ' . json_encode($post));

        // -------------------------------------------------
        // VALIDATION
        // -------------------------------------------------
        $rules = [
            'product_name'  => 'required|min_length[3]',
            'product_price' => 'required|numeric',
            'category_id'   => 'required',
        ];

        $redirectUrl = 'products/form' . ($id ? '?id=' . $id : '');

        if (!$this->validate($rules)) {
            return redirect()->to(site_url($redirectUrl))->withInput()->with('failed', implode('<br>', $this->validator->getErrors()));
        }

        $oldProduct = null;
        if ($id) {
            $oldProduct = $this->productModel->find($id);
        }

        $productData = [
            'category_id'   => $post['category_id'],
            'product_name'  => $post['product_name'],
            'product_price' => $post['product_price'],
            'product_brand' => !empty($post['product_brand']) ? strtoupper($post['product_brand']) : null,
            'description'   => $post['description'] ?? null,
        ];

        if (!$id || empty($oldProduct['product_sku']) || $oldProduct['category_id'] !== $post['category_id']) {
            $productData['product_sku'] = generate_unique_product_sku($post['category_id'], $id);
        }

        try {

            // =================================================
            // START TRANSACTION
            // =================================================
            $db->transBegin();

            // -------------------------------------------------
            // SAVE PRODUCT
            // -------------------------------------------------
            if ($id) {
                if ($this->productModel->update($id, $productData) === false) {
                    $errors = implode('<br>', $this->productModel->errors());
                    throw new \Exception('Failed to update product: ' . $errors);
                }
                $productId = $id;
            } else {
                if ($this->productModel->insert($productData) === false) {
                    $errors = implode('<br>', $this->productModel->errors());
                    throw new \Exception('Failed to insert product: ' . $errors);
                }
                $productId = $this->productModel->getInsertID();
            }

            $productSku = $productData['product_sku'] ?? $oldProduct['product_sku'] ?? '';

            log_message('debug', "Product ID: $productId");

            // -------------------------------------------------
            // PRODUCT IMAGES (PRIMARY)
            // -------------------------------------------------
            $files = $request->getFiles();

            if (!empty($files['images'])) {

                // ✅ Cek dulu apakah ada file yang valid
                $hasValidImages = false;
                foreach ($files['images'] as $img) {
                    if ($img->isValid() && !$img->hasMoved()) {
                        $hasValidImages = true;
                        break;
                    }
                }

                // ✅ Hanya reset primary jika memang ada gambar baru
                if ($hasValidImages) {
                    $this->productImageModel
                        ->where('product_id', $productId)
                        ->where('type', 'gallery')
                        ->set(['is_primary' => 0])
                        ->update();
                }

                $isPrimarySet = false;

                foreach ($files['images'] as $img) {
                    if (!$img->isValid() || $img->hasMoved()) continue;

                    $objectUrl = $this->r2->uploadFile(
                        $img->getTempName(),
                        $img->getRandomName()
                    );

                    if (!$objectUrl) {
                        throw new \Exception('Failed upload product image');
                    }

                    $this->productImageModel->insert([
                        'product_id' => $productId,
                        'variant_id' => null,
                        'type'       => 'gallery',
                        'url'        => $objectUrl,
                        'alt_text'   => $post['product_name'],
                        'mime_type'  => $img->getClientMimeType(),
                        'size_bytes' => $img->getSize(),
                        'is_primary' => $isPrimarySet ? 0 : 1,
                    ]);

                    $isPrimarySet = true;
                }
            }

            // Fetch category attributes for signature generation
            $categoryAttributes = $this->attributeModel
                ->where('category_id', $post['category_id'])
                ->where('deleted_at', null)
                ->findAll();
            $categoryAttributesMap = [];
            foreach ($categoryAttributes as $attr) {
                $categoryAttributesMap[$attr['attribute_id']] = $attr['attribute_name'];
            }

            // -------------------------------------------------
            // VARIANT ATTRIBUTE TOGGLE (ON / OFF)
            // -------------------------------------------------
            $variantAttributes = $post['variant_attributes'] ?? [];

            // reset toggle lama
            $this->productVariantAttributeModel
                ->where('product_id', $productId)
                ->delete();

            // simpan toggle baru
            foreach ($variantAttributes as $attrId) {
                $this->productVariantAttributeModel->insert([
                    'product_id'   => $productId,
                    'attribute_id' => $attrId,
                ]);
            }


            // -------------------------------------------------
            // PRODUCT ATTRIBUTE VALUES
            // -------------------------------------------------
            if (!empty($post['attributes'])) {
                foreach ($post['attributes'] as $attrId => $value) {

                    // ✅ HANDLE ARRAY VALUES (untuk checkbox/multi-select)
                    if (is_array($value)) {
                        // Hapus PAV lama untuk attribute ini
                        $this->pavModel
                            ->where('product_id', $productId)
                            ->where('attribute_id', $attrId)
                            ->delete();

                        // Insert setiap value sebagai PAV baru
                        foreach ($value as $singleValue) {
                            $this->pavModel->insert([
                                'product_id'   => $productId,
                                'attribute_id' => $attrId,
                                'value'        => $singleValue
                            ]);
                        }
                    } else {
                        // Single value (text/number/single select)
                        $exists = $this->pavModel
                            ->where([
                                'product_id'   => $productId,
                                'attribute_id' => $attrId
                            ])
                            ->first();

                        if ($exists) {
                            $this->pavModel->update($exists['pav_id'], ['value' => $value]);
                        } else {
                            $this->pavModel->insert([
                                'product_id'   => $productId,
                                'attribute_id' => $attrId,
                                'value'        => $value
                            ]);
                        }
                    }
                }
            }

            // -------------------------------------------------
            // VARIANTS
            // -------------------------------------------------
            $existingVariants = $this->variantModel
                ->where('product_id', $productId)
                ->findAll();

            $existingIds = array_column($existingVariants, 'variant_id');
            $receivedIds = [];

            if (!empty($post['variants'])) {

                foreach ($post['variants'] as $index => $v) {

                    if (!is_array($v)) continue;

                    $variantId   = $v['variant_id'] ?? null;
                    $variantName = $v['label'] ?? 'Variant';
                    $price       = $v['price'] ?? null;

                    // Compute signature on the backend
                    $computedSignature = '';
                    if (!empty($v['mapping'])) {
                        $mapping = is_string($v['mapping'])
                            ? json_decode($v['mapping'], true)
                            : $v['mapping'];
                        if (is_array($mapping)) {
                            $pvValuesForSignature = [];
                            foreach ($mapping as $item) {
                                if (is_array($item)) {
                                    $attrId = $item['attribute_id'] ?? null;
                                    $val = $item['value'] ?? null;
                                    $attrName = $categoryAttributesMap[$attrId] ?? null;
                                    if ($attrId && $val && $attrName) {
                                        $pvValuesForSignature[] = [
                                            'attribute_id' => $attrId,
                                            'attribute_name' => $attrName,
                                            'value' => $val
                                        ];
                                    }
                                }
                            }
                            $computedSignature = $this->generateSignature($pvValuesForSignature);
                        }
                    }

                    $variantSku = generate_variant_sku($productSku, $pvValuesForSignature ?? []);

                    // ----------------------------
                    // INSERT / UPDATE VARIANT
                    // ----------------------------
                    if ($variantId) {
                        $this->variantModel->update($variantId, [
                            'variant_name' => $variantName,
                            'price'        => $price,
                            'variant_signature' => $computedSignature,
                            'variant_sku' => $variantSku
                        ]);
                    } else {
                        $insert = $this->variantModel->insert([
                            'variant_id'   => $variantId,
                            'product_id'   => $productId,
                            'variant_name' => $variantName,
                            'price'        => $price,
                            'variant_signature' => $computedSignature,
                            'variant_sku' => $variantSku
                        ]);

                        $variantId = $this->variantModel->getInsertID();

                        if (!$insert) {
                            throw new \Exception('Failed insert variant');
                        }
                    }

                    // HARD CHECK (FK SAFETY)
                    $variantExists = $this->variantModel
                        ->where('variant_id', $variantId)
                        ->first();

                    if (!$variantExists) {
                        throw new \Exception("Variant not exists after save: $variantId");
                    }

                    $receivedIds[] = $variantId;

                    // ----------------------------
                    // VARIANT IMAGE
                    // ----------------------------
                    $file = $request->getFile("variants.$index.image");

                    if ($file && $file->isValid() && !$file->hasMoved()) {

                        // Delete old image
                        $old = $this->variantImageModel
                            ->where('variant_id', $variantId)
                            ->first();

                        if ($old) {
                            $oldImg = $this->productImageModel
                                ->find($old['product_image_id']);

                            if ($oldImg) {
                                $this->r2->deleteFile($oldImg['url']);
                                $this->variantImageModel->delete($old['pv_image_id']);
                                $this->productImageModel->delete($oldImg['product_image_id']);
                            }
                        }

                        $url = $this->r2->uploadFile(
                            $file->getTempName(),
                            $file->getRandomName()
                        );

                        if (!$url) {
                            throw new \Exception('Failed upload variant image');
                        }

                        $this->productImageModel->insert([
                            'product_id'       => $productId,
                            'url'              => $url,
                            'alt_text'         => $variantName,
                            'mime_type'        => $file->getClientMimeType(),
                            'size_bytes'       => $file->getSize(),
                            'type'             => 'variant',
                            'is_primary'       => 0
                        ]);

                        $productImageId = $this->productImageModel->getInsertID();

                        $this->variantImageModel->insert([
                            'variant_id'       => $variantId,
                            'product_image_id' => $productImageId
                        ]);
                    }

                    // ----------------------------
                    // VARIANT ATTRIBUTE MAPPING
                    // ----------------------------
                    if (!empty($v['mapping'])) {

                        $mapping = is_string($v['mapping'])
                            ? json_decode($v['mapping'], true)
                            : $v['mapping'];

                        if (is_array($mapping)) {

                            // Hapus mapping lama
                            $this->pvValueModel
                                ->where('variant_id', $variantId)
                                ->delete();

                            foreach ($mapping as $item) {

                                // ✅ CEK FORMAT: apakah old format (string ID) atau new format (object)?
                                if (is_string($item)) {
                                    // OLD FORMAT: langsung pav_id
                                    $pavId = $item;
                                } else {
                                    // NEW FORMAT: {attribute_id: "...", value: "..."}
                                    $attributeId = $item['attribute_id'] ?? null;
                                    $value = $item['value'] ?? null;

                                    if (!$attributeId || !$value) {
                                        log_message('error', 'Invalid mapping item: ' . json_encode($item));
                                        continue;
                                    }

                                    // ✅ LOOKUP PAV_ID dari attribute_id + value
                                    $pav = $this->pavModel
                                        ->where('product_id', $productId)
                                        ->where('attribute_id', $attributeId)
                                        ->where('value', $value)
                                        ->first();

                                    if (!$pav) {
                                        throw new \Exception("PAV not found for attribute_id=$attributeId, value=$value");
                                    }

                                    $pavId = $pav['pav_id'];
                                }

                                // HARD SAFETY CHECK
                                if (!$this->pavModel->find($pavId)) {
                                    throw new \Exception("PAV not found: $pavId");
                                }

                                $this->pvValueModel->insert([
                                    'variant_id' => $variantId,
                                    'pav_id'     => $pavId
                                ]);
                            }
                        }
                    }
                }
            }

            // -------------------------------------------------
            // DELETE REMOVED VARIANTS
            // -------------------------------------------------
            foreach ($existingIds as $oldId) {

                if (!in_array($oldId, $receivedIds)) {

                    $this->pvValueModel->where('variant_id', $oldId)->delete();

                    $img = $this->variantImageModel
                        ->where('variant_id', $oldId)
                        ->first();

                    if ($img) {
                        $pimg = $this->productImageModel
                            ->find($img['product_image_id']);

                        if ($pimg) {
                            $this->r2->deleteFile($pimg['url']);
                            $this->productImageModel->delete($pimg['product_image_id']);
                        }

                        $this->variantImageModel->delete($img['pv_image_id']);
                    }

                    $this->variantModel->delete($oldId);
                }
            }

            // =================================================
            // COMMIT
            // =================================================
            $db->transCommit();
            session()->setFlashdata('success', 'Product saved successfully');
            log_message('debug', '========== SAVE PRODUCT END ==========');
            return redirect()->to('/products');
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message('error', $e->getMessage());
            log_message('error', $e->getTraceAsString());

            log_message('debug', '========== SAVE PRODUCT END ==========');
            return redirect()
                ->to(site_url($redirectUrl))
                ->withInput()
                ->with('failed', $e->getMessage());
        }
    }

    public function deleteImage()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        $json = $this->request->getJSON(true);
        $imageId = $json['image_id'] ?? null;
        $productId = $json['product_id'] ?? null;

        if (!$imageId || !$productId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid parameters'
            ])->setStatusCode(400);
        }

        try {
            // 🔍 Ambil data image
            $image = $this->productImageModel
                ->where('product_image_id', $imageId)
                ->where('product_id', $productId)
                ->first();

            if (!$image) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Image not found'
                ])->setStatusCode(404);
            }

            // 🔍 Cek apakah dipakai variant
            $variantImage = $this->variantImageModel
                ->where('product_image_id', $imageId)
                ->first();

            if ($variantImage) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cannot delete image. This image is used by a product variant.'
                ])->setStatusCode(400);
            }

            $r2Key = $image['url'];

            try {
                $this->r2->deleteFile($r2Key);
                log_message('info', 'R2 deletion success: ' . $r2Key);
            } catch (\Throwable $e) {
                log_message('error', 'R2 deletion FAILED: ' . $e->getMessage());
            }

            // 🗑 3. Hapus record DB
            $this->productImageModel
                ->where('product_image_id', $imageId)
                ->delete();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Throwable $e) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function webDelete($id)
    {
        $this->productModel->delete($id);
        return redirect()->to('/products')->with('success', 'Product deleted successfully');
    }

    public function getAttributesPartial()
    {
        $categoryId = $this->request->getGet('category_id');
        $productId = $this->request->getGet('product_id');

        if (empty($categoryId)) {
            return '';
        }

        // Fetch attributes filtered by category_id, ordered by sort_order
        $attributes = $this->attributeModel
            ->where('category_id', $categoryId)
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        foreach ($attributes as &$attr) {
            $attr['values'] = $this->db->table('product_attribute_master_values')
                ->where('attribute_id', $attr['attribute_id'])
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();
        }

        // Prefilled values if editing
        $pavValues = [];
        $selectedAttributeValues = [];
        $selectedAttributes = [];

        if (!empty($productId)) {
            $pav = $this->pavModel
                ->where('product_id', $productId)
                ->where('deleted_at', null)
                ->findAll();

            foreach ($pav as $row) {
                $attrId = $row['attribute_id'];
                if (!isset($pavValues[$attrId])) {
                    $pavValues[$attrId] = [
                        'pav_ids' => [],
                        'values' => []
                    ];
                }
                $pavValues[$attrId]['pav_ids'][] = $row['pav_id'];
                $pavValues[$attrId]['values'][] = $row['value'];

                if (!isset($selectedAttributeValues[$attrId])) {
                    $selectedAttributeValues[$attrId] = [];
                }
                $selectedAttributeValues[$attrId][] = $row['value'];
            }

            $variantAttrs = $this->productVariantAttributeModel
                ->where('product_id', $productId)
                ->findAll();
            $selectedAttributes = array_column($variantAttrs, 'attribute_id');
        }

        return view('products/partials/v_attributes_partial', [
            'attributes' => $attributes,
            'pav_values' => $pavValues,
            'selected_attribute_values' => $selectedAttributeValues,
            'selected_attributes' => $selectedAttributes,
        ]);
    }

    private function toSlug($str)
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9\s_-]/', '', $str);
        $str = preg_replace('/\s+/', '-', $str);
        $str = preg_replace('/-+/', '-', $str);
        return $str;
    }

    private function generateSignature(array $pvValues)
    {
        // Sort by attribute_id ASC (since UUIDs are strings, we sort alphabetically)
        usort($pvValues, function ($a, $b) {
            return strcmp($a['attribute_id'], $b['attribute_id']);
        });

        $parts = [];
        foreach ($pvValues as $item) {
            $attrSlug = $this->toSlug($item['attribute_name']);
            $valSlug = $this->toSlug($item['value']);
            $parts[] = "{$attrSlug}:{$valSlug}";
        }

        return implode('|', $parts);
    }
}
