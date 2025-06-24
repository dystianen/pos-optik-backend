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

    // GET /api/products/new-eyewear
    public function apiListNewEyewear()
    {
        $products = $this->productModel
            ->builder()
            ->limit(10)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $response = [
            'status' => 200,
            'message' => 'Succesfully!',
            'data' => $products
        ];
        return $this->response->setJSON($response);
    }

    // GET /api/product/recommendations
    public function apiProductRecommendations()
    {
        $limit = (int) $this->request->getVar('limit');
        if ($limit <= 0) {
            $limit = 10; // default limit
        }

        try {
            $decode = $this->decodedToken();
            $customer = $this->customerModel->find($decode->user_id);
        } catch (\Exception $e) {
            // Token tidak ada atau invalid
            $customer = null;
        }

        $products = $this->productModel
            ->builder()
            ->limit($limit)
            ->get()
            ->getResultArray();

        $recommendations = [];


        if (!$customer || empty($customer['customer_eye_history']) || empty($customer['customer_preferences'])) {
            // fallback: tampilkan semua produk dengan skor 0
            foreach ($products as $product) {
                $product['score'] = 0;
                $recommendations[] = $product;
            }
        } else {
            // customer ada, lanjut hitung score rekomendasi seperti biasa
            $eyeHistoryData = json_decode($customer['customer_eye_history'], true);
            $preferencesData = json_decode($customer['customer_preferences'], true);

            foreach ($products as $product) {
                $score = 0;

                // Power range matching
                if (!empty($product['power_range']) && is_array($eyeHistoryData)) {
                    $range = explode('-', $product['power_range']);
                    if (count($range) === 2) {
                        $min = floatval(trim($range[0]));
                        $max = floatval(trim($range[1]));
                        $leftSphere = isset($eyeHistoryData['left_eye']['sphere']) ? floatval($eyeHistoryData['left_eye']['sphere']) : null;
                        $rightSphere = isset($eyeHistoryData['right_eye']['sphere']) ? floatval($eyeHistoryData['right_eye']['sphere']) : null;

                        if (
                            ($leftSphere !== null && $leftSphere >= $min && $leftSphere <= $max) ||
                            ($rightSphere !== null && $rightSphere >= $min && $rightSphere <= $max)
                        ) {
                            $score += 2;
                        }
                    }
                }

                // UV protection matching
                if (!empty($product['uv_protection']) && is_array($preferencesData)) {
                    if (in_array(strtolower($product['uv_protection']), array_map('strtolower', (array)$preferencesData))) {
                        $score += 1;
                    }
                }

                // Color matching
                if (!empty($product['color']) && is_array($preferencesData)) {
                    if (in_array(strtolower($product['color']), array_map('strtolower', (array)$preferencesData))) {
                        $score += 1;
                    }
                }

                // Coating matching
                if (!empty($product['coating']) && is_array($preferencesData)) {
                    if (in_array(strtolower($product['coating']), array_map('strtolower', (array)$preferencesData))) {
                        $score += 1;
                    }
                }

                $product['score'] = $score;
                $recommendations[] = $product;
            }

            // Urutkan berdasarkan score
            usort($recommendations, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });
        }

        $response = [
            'status' => 200,
            'message' => 'Succesfully!',
            'data' => $recommendations
        ];

        return $this->response->setJSON($response);
    }


    // GET /api/products/{id}
    public function apiProductDetail($id)
    {
        $product = $this->productModel->find($id);
        if ($product) {
            $response = [
                'status' => 200,
                'message' => 'Succesfully!',
                'data' => $product
            ];

            return $this->response->setJSON($response);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
            ->setJSON(['message' => 'Product not found']);
    }

    public function apiProduct()
    {
        $category = $this->request->getVar('category');
        $search   = $this->request->getVar('search');
        $page     = $this->request->getVar('page') ?? 1;
        $limit    = $this->request->getVar('limit') ?? 10;

        $builder = $this->productModel;

        if ($category) {
            $builder = $builder->where('category_id', $category);
        }

        if ($search) {
            $builder = $builder->groupStart()
                ->like('product_name', $search)
                ->orLike('product_brand', $search)
                ->groupEnd();
        }

        $totalItems = $builder->countAllResults(false);

        $products = $builder
            ->orderBy('product_id', 'DESC')
            ->paginate($limit, 'products', $page);

        if (!$products) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['message' => 'No products found']);
        }

        $pager = [
            'currentPage' => $this->productModel->pager->getCurrentPage('products'),
            'totalPages'  => $this->productModel->pager->getPageCount('products'),
            'limit'       => $limit,
            'totalItems'  => $totalItems,
        ];

        $response = [
            'status'  => 200,
            'message' => 'Succesfully!',
            'data'    => $products,
            'pager'   => $pager,
        ];

        return $this->response->setJSON($response);
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
            ->orderBy('products.created_at', 'DESC')
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

    public function form()
    {

        $data = [];
        $id = $this->request->getVar('id');
        $categories = $this->categoryModel->findAll();
        $data['categories'] = $categories;

        if ($id) {
            $product = $this->productModel->find($id);
            if (!$product) {
                return redirect()->to('/product-category')->with('error', 'Transaction not found.');
            }
            $data['product'] = $product;
        }

        return view('products/v_form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');

        $rules = [
            'category_id' => 'required',
            'product_name' => 'required',
            'product_price' => 'required',
            'product_stock' => 'required',
            'product_brand' => 'required',
            'model' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input.');
        };

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
            'water_content'  => $this->request->getPost('water_content'),
            'uv_protection'  => $this->request->getPost('uv_protection'),
            'color'          => $this->request->getPost('color'),
            'coating'        => $this->request->getPost('coating'),
        ];

        $file = $this->request->getFile('product_image_url');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/products', $newName);
            $data['product_image_url'] = '/uploads/products/' . $newName;
        }

        if ($id) {
            $this->productModel->update($id, $data);
            $message = 'Product updated successfully!';
        } else {
            $this->productModel->insert($data);
            $message = 'Product created successfully!';
        }

        return redirect()->to('/products')->with('success', $message);
    }


    public function webDelete($id)
    {
        $this->productModel->delete($id);
        return redirect()->to('products/v_index')->with('message', 'Product deleted successfully');
    }
}
