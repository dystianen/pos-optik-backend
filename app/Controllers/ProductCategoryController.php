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

    // CREATE
    public function webCreate()
    {
        return view('product_category/v_form');
    }

    public function webStore()
    {
        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'category_description' => $this->request->getPost('category_description'),
        ];

        $this->categoryModel->insert($data);
        return redirect()->to('/product-category')->with('success', 'Category added successfully.');
    }

    // EDIT
    public function webEdit($id)
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return redirect()->to('/product-category')->with('error', 'Category not found.');
        }

        return view('product_category/v_form', [
            'category' => $category
        ]);
    }

    public function webUpdate($id)
    {
        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'category_description' => $this->request->getPost('category_description'),
        ];

        $this->categoryModel->update($id, $data);
        return redirect()->to('/product-category')->with('success', 'Category updated successfully.');
    }

    // DELETE
    public function webDelete($id)
    {
        $this->categoryModel->delete($id);
        return redirect()->to('/product-category')->with('success', 'Category deleted successfully.');
    }
}
