<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProductController extends BaseController
{
    protected $productModel, $categoryModel, $customerModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new ProductCategoryModel();
        $this->customerModel = new CustomerModel();
    }

    // =======================
    // API FUNCTIONS
    // =======================

    // GET /api/products
    public function apiList()
    {
        $products = $this->productModel->findAll();
        return $this->response->setJSON($products);
    }

    // GET /api/product/recommendations
    public function apiProductRecommendations($customerId)
    {
        $customer = $this->customerModel->find($customerId);
        $products = $this->productModel;

        $eyeHistoryData = json_decode($customer['customer_eye_history'], true);
        $preferencesData = json_decode($customer['customer_preferences'], true);

        $recommendations = [];
        foreach ($products as $product) {
            $score = 0;

            // Power range matching
            if (!empty($product['power_range']) && is_array($eyeHistoryData)) {
                if (isset($eyeHistoryData['left_eye']['spere']) && isset($eyeHistoryData['right_eye']['sphere'])) {
                    $range = explode('-', $product['power_range']);
                    if (count($range) === 2) {
                        $min = floatval($range[0]);
                        $max = floatval($range[1]);
                        if (
                            ($eyeHistoryData['left_eye']['spere'] >= $min && $eyeHistoryData['left_eye']['spere'] <= $max) ||
                            ($eyeHistoryData['right_eye']['sphere'] >= $min && $eyeHistoryData['right_eye']['sphere'] <= $max)
                        ) {
                            $score += 2;
                        }
                    }
                }
            }

            // UV protection matching
            if (!empty($product['uv_protection']) && is_array($preferencesData)) {
                if (in_array(strtolower($product['uv_protection']), array_map('strtolower', $preferencesData))) {
                    $score += 1;
                }
            }

            // Color matching
            if (!empty($product['color']) && is_array($preferencesData)) {
                if (in_array(strtolower($product['color']), array_map('strtolower', $preferencesData))) {
                    $score += 1;
                }
            }

            // Coating matching
            if (!empty($product['coating']) && is_array($preferencesData)) {
                if (in_array(strtolower($product['coating']), array_map('strtolower', $preferencesData))) {
                    $score += 1;
                }
            }

            if ($score > 0) {
                $product['score'] = $score;
                $recommendations[] = $product;
            }
        }

        // 5. Urutkan berdasarkan score (descending)
        usort($recommendations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // 6. Return JSON
        return $this->response->setJSON($recommendations);
    }

    // POST /api/products
    public function apiCreate()
    {
        $data = $this->request->getJSON();

        if ($this->productModel->insert($data)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)
                ->setJSON(['message' => 'Product created successfully']);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
            ->setJSON(['message' => 'Failed to create product']);
    }

    // GET /api/products/{id}
    public function apiShow($id)
    {
        $product = $this->productModel->find($id);
        if ($product) {
            return $this->response->setJSON($product);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
            ->setJSON(['message' => 'Product not found']);
    }

    // PUT/PATCH /api/products/{id}
    public function apiUpdate($id)
    {
        $data = $this->request->getJSON();
        if ($this->productModel->update($id, $data)) {
            return $this->response->setJSON(['message' => 'Product updated successfully']);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
            ->setJSON(['message' => 'Failed to update product']);
    }

    // DELETE /api/products/{id}
    public function apiDelete($id)
    {
        if ($this->productModel->delete($id)) {
            return $this->response->setJSON(['message' => 'Product deleted successfully']);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
            ->setJSON(['message' => 'Failed to delete product']);
    }

    // =======================
    // WEB DASHBOARD FUNCTIONS
    // =======================

    // GET /products
    public function webIndex()
    {
        $currentPage = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $products = $this->productModel
            ->join('product_categories', 'product_categories.category_id = products.category_id')
            ->orderBy('created_at', 'DESC')
            ->findAll($totalLimit, $offset);

        $totalRows = $this->productModel
            ->join('product_categories', 'product_categories.category_id = products.category_id')
            ->countAllResults();

        $totalPages = ceil($totalRows / $totalLimit);

        $data = [
            "products" => $products,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
        ];

        return view('products/v_index', $data);
    }

    // GET /products/create
    public function webCreateForm()
    {
        $categories = $this->categoryModel->findAll();

        $data = [
            'categories' => $categories
        ];

        return view('products/v_form', $data);
    }

    // POST /products/store
    public function webStore()
    {
        helper(['form', 'url']);

        // Ambil data POST
        $data = [
            'category_id'    => $this->request->getPost('category_id'),
            'product_name'   => $this->request->getPost('product_name'),
            'product_price'  => $this->request->getPost('product_price'),
            'product_stock'  => $this->request->getPost('product_stock'),
            'product_brand'  => $this->request->getPost('product_brand'),
            'model'       => $this->request->getPost('model'),
            'duration'          => $this->request->getPost('duration'),
            'material'          => $this->request->getPost('material'),
            'base_curve'         => $this->request->getPost('base_curve'),
            'diameter'  => $this->request->getPost('diameter'),
            'power_range'  => $this->request->getPost('power_range'),
            'water_content'  => $this->request->getPost('water_content'),
            'water_content'  => $this->request->getPost('water_content'),
            'uv_protection'  => $this->request->getPost('uv_protection'),
            'color'  => $this->request->getPost('color'),
            'coating'  => $this->request->getPost('coating'),
        ];

        // Handle file upload
        $file = $this->request->getFile('product_image_url');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/products', $newName);
            $data['product_image_url'] = '/uploads/products/' . $newName;
        }

        // Simpan ke database
        $this->productModel->insert($data);

        return redirect()->to('/products')->with('success', 'Product added successfully');
    }

    // GET /products/edit/{id}
    public function webEditForm($id)
    {
        $product = $this->productModel->find($id);
        $categories = $this->categoryModel->findAll();

        $data = [
            'product' => $product,
            'categories' => $categories
        ];

        return view('products/v_form', $data);
    }

    // PUT /products/update/{id}
    public function webUpdate($id)
    {
        helper(['form', 'url']);

        // Ambil data POST
        $data = [
            'category_id'    => $this->request->getPost('category_id'),
            'product_name'   => $this->request->getPost('product_name'),
            'product_price'  => $this->request->getPost('product_price'),
            'product_stock'  => $this->request->getPost('product_stock'),
            'product_brand'  => $this->request->getPost('product_brand'),
            'model'          => $this->request->getPost('model'),
            'duration'       => $this->request->getPost('duration'),
            'material'       => $this->request->getPost('material'),
            'base_curve'     => $this->request->getPost('base_curve'),
            'diameter'       => $this->request->getPost('diameter'),
            'power_range'    => $this->request->getPost('power_range'),
            'water_content'  => $this->request->getPost('water_content'),
            'uv_protection'  => $this->request->getPost('uv_protection'),
            'color'          => $this->request->getPost('color'),
            'coating'        => $this->request->getPost('coating'),
        ];

        // Handle file upload jika ada file baru diunggah
        $file = $this->request->getFile('product_image_url');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/products', $newName);
            $data['product_image_url'] = '/uploads/products/' . $newName;
        }

        $this->productModel->update($id, $data);

        return redirect()->to('/products')->with('success', 'Product updated successfully');
    }


    // POST /web/products/delete/{id}
    public function webDelete($id)
    {
        $this->productModel->delete($id);
        return redirect()->to('products/v_index')->with('message', 'Product deleted successfully');
    }
}
