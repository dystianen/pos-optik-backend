<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductCategoryModel;

class ProductCategoryController extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new ProductCategoryModel();
    }

    public function apiListProductCategory()
    {
        $categories = $this->categoryModel->findAll();

        $response = [
            'status' => 200,
            'message' => 'Successfully',
            'data' => $categories
        ];
        return $this->response->setJSON($response);
    }

    // READ
    public function webIndex()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 10;
        $categories = $this->categoryModel->paginate($perPage, 'default', $page);
        $pager = [
            'currentPage' => $this->categoryModel->pager->getCurrentPage('default'),
            'totalPages' => $this->categoryModel->pager->getPageCount('default'),
            'limit' => $perPage
        ];

        return view('product_category/v_index', [
            'categories' => $categories,
            'pager' => $pager
        ]);
    }

    public function form()
    {

        $id = $this->request->getVar('id');
        $data = [];
        if ($id) {
            $category = $this->categoryModel->find($id);
            if (!$category) {
                return redirect()->to('/product-category')->with('failed', 'Transaction not found.');
            }
            $data['category'] = $category;
        }

        return view('product_category/v_form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');

        $rules = [
            'category_name' => 'required',
            'category_description' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('failed', 'Please check your input.');
        };

        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'category_description' => $this->request->getPost('category_description'),
        ];

        if ($id) {
            $this->categoryModel->update($id, $data);
            $message = 'Transaction updated successfully!';
        } else {
            $this->categoryModel->insert($data);
            $message = 'Transaction created successfully!';
        }

        return redirect()->to('/product-category')->with('success', $message);
    }

    // DELETE
    public function webDelete($id)
    {
        $this->categoryModel->delete($id);
        return redirect()->to('/product-category')->with('success', 'Category deleted successfully.');
    }
}
