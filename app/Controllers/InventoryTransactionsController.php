<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventoryTransactionsModel;
use App\Models\ProductModel;

class InventoryTransactionsController extends BaseController
{
    protected $inventoryTransactionsModel, $productModel;

    public function __construct()
    {
        $this->inventoryTransactionsModel = new InventoryTransactionsModel();
        $this->productModel = new ProductModel();
    }

    public function webIndex()
    {
        $currentPage = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $transactions = $this->inventoryTransactionsModel
            ->join('products p1', 'inventory_transactions.product_id = p1.product_id')
            ->join('product_categories p2', 'p1.category_id = p2.category_id')
            ->findAll($totalLimit, $offset);

        $totalRows = $this->inventoryTransactionsModel
            ->join('products', 'inventory_transactions.product_id = products.product_id')
            ->countAllResults();

        $totalPages = ceil($totalRows / $totalLimit);

        $data = [
            "inventory_transactions" => $transactions,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
        ];

        return view('inventory_transactions/v_index', $data);
    }

    public function form()
    {
        $id = $this->request->getVar('id');
        $data['products'] = $this->productModel->findAll();

        if ($id) {
            $transaction = $this->inventoryTransactionsModel->find($id);
            if (!$transaction) {
                return redirect()->to('/inventory-transactions')->with('error', 'Transaction not found.');
            }
            $data['transaction'] = $transaction;
        }

        return view('inventory_transactions/v_form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');
        $session = session();

        $rules = [
            'product_id' => 'required|integer',
            'transaction_type' => 'required|in_list[IN,OUT]',
            'quantity' => 'required|integer',
            'description' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input.');
        }

        $data = [
            'product_id' => $this->request->getVar('product_id'),
            'transaction_type' => $this->request->getVar('transaction_type'),
            'quantity' => $this->request->getVar('quantity'),
            'description' => $this->request->getVar('description'),
            'user_id' => $session->get('id')
        ];

        if ($id) {
            $this->inventoryTransactionsModel->update($id, $data);
            $message = 'Transaction updated successfully!';
        } else {
            $this->inventoryTransactionsModel->insert($data);
            $message = 'Transaction created successfully!';
        }

        return redirect()->to('/inventory-transactions')->with('success', $message);
    }
}
