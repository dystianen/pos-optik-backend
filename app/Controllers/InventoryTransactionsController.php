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
        $search = $this->request->getVar('search'); // Ambil keyword dari input
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $builder = $this->inventoryTransactionsModel
            ->join('products p1', 'inventory_transactions.product_id = p1.product_id')
            ->join('product_categories p2', 'p1.category_id = p2.category_id')
            ->orderBy('transaction_date', 'DESC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('p1.product_name', $search)
                ->orLike('p2.category_name', $search)
                ->orLike('inventory_transactions.transaction_type', $search)
                ->groupEnd();
        }

        // Clone builder for count
        $countBuilder = clone $builder;

        $transactions = $builder->findAll($totalLimit, $offset);
        $totalRows = $countBuilder->countAllResults(false); // avoid re-joining

        $totalPages = ceil($totalRows / $totalLimit);

        $data = [
            "inventory_transactions" => $transactions,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
            "search" => $search, // kirim ke view supaya input tetap muncul
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
                return redirect()->to('/inventory')->with('failed', 'Transaction not found.');
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
            'transaction_type' => 'required|in_list[in,out]',
            'quantity' => 'required|integer',
            'description' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('failed', 'Please check your input.');
        }

        $productId = (int) $this->request->getVar('product_id');
        $transactionType = $this->request->getVar('transaction_type');
        $quantity = (int) $this->request->getVar('quantity');

        $product = $this->productModel->find($productId);
        if (!$product) {
            return redirect()->back()->withInput()->with('failed', 'Product not found.');
        }

        $currentStock = (int) $product['product_stock'];

        $data = [
            'product_id' => $productId,
            'transaction_type' => $transactionType,
            'quantity' => $quantity,
            'description' => $this->request->getVar('description'),
            'user_id' => $session->get('id'),
            'transaction_date' => date('Y-m-d H:i'),
        ];

        // Jalankan transaksi DB agar rollback jika error
        $this->db->transBegin();

        try {
            if ($id) {
                // Get old transaction
                $oldTransaction = $this->inventoryTransactionsModel->find($id);

                if (!$oldTransaction) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('failed', 'Old transaction not found.');
                }

                $oldQty = (int) $oldTransaction['quantity'];
                $oldType = $oldTransaction['transaction_type'];

                // Hitung rollback stok lama
                if ($oldType === 'in') {
                    $currentStock -= $oldQty;
                } else {
                    $currentStock += $oldQty;
                }

                // Hitung apply stok baru
                if ($transactionType === 'in') {
                    $currentStock += $quantity;
                } else {
                    $currentStock -= $quantity;
                }

                if ($currentStock < 0) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('failed', 'Insufficient stock after update.');
                }

                $this->productModel->update($productId, ['product_stock' => $currentStock]);
                $this->inventoryTransactionsModel->update($id, $data);
                $message = 'Transaction updated successfully!';
            } else {
                // INSERT baru
                if ($transactionType === 'in') {
                    $currentStock += $quantity;
                } else {
                    if ($currentStock < $quantity) {
                        $this->db->transRollback();
                        return redirect()->back()->withInput()->with('failed', 'Insufficient stock.');
                    }
                    $currentStock -= $quantity;
                }

                $this->productModel->update($productId, ['product_stock' => $currentStock]);
                $this->inventoryTransactionsModel->insert($data);
                $message = 'Transaction created successfully!';
            }

            $this->db->transCommit();
            return redirect()->to('/inventory')->with('success', $message);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('failed', 'Error: ' . $e->getMessage());
        }
    }


    public function delete($id)
    {
        $this->inventoryTransactionsModel->delete($id);
        return redirect()->to('/inventory')->with('success', 'Inventory transaction deleted successfully.');
    }
}
