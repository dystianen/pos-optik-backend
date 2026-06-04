<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventoryTransactionModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;

class InventoryTransactionsController extends BaseController
{
    protected $InventoryTransactionModel, $productModel, $productVariantModel;

    public function __construct()
    {
        $this->InventoryTransactionModel = new InventoryTransactionModel();
        $this->productModel = new ProductModel();
        $this->productVariantModel = new ProductVariantModel();
    }

    public function webIndex()
    {
        $currentPage = $this->request->getVar('page')
            ? (int) $this->request->getVar('page')
            : 1;

        $search = $this->request->getVar('search');
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $builder = $this->InventoryTransactionModel
            ->select('
                inventory_transactions.*,
                p1.product_name,
                p1.product_stock,
                p1.product_sku,
                v1.variant_id,
                v1.variant_name,
                v1.stock AS variant_stock,
                p2.category_name,
                CASE 
                    WHEN v1.variant_id IS NOT NULL THEN v1.variant_name
                    ELSE "No Variant"
                END AS display_variant,
                CASE 
                    WHEN v1.variant_id IS NOT NULL THEN v1.stock
                    ELSE p1.product_stock
                END AS current_stock
            ', false)
            ->join('products p1', 'inventory_transactions.product_id = p1.product_id')
            ->join('product_variants v1', 'inventory_transactions.variant_id = v1.variant_id', 'left') // ✅ LEFT JOIN
            ->join('product_categories p2', 'p1.category_id = p2.category_id', 'left')
            ->orderBy('inventory_transactions.transaction_date', 'DESC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('p1.product_name', $search)
                ->orLike('p1.product_sku', $search)
                ->orLike('v1.variant_name', $search)
                ->orLike('p2.category_name', $search)
                ->orLike('inventory_transactions.transaction_type', $search)
                ->groupEnd();
        }

        // clone builder untuk total data
        $countBuilder = clone $builder;

        $transactions = $builder->findAll($totalLimit, $offset);
        $totalRows    = $countBuilder->countAllResults(false);

        $totalPages = (int) ceil($totalRows / $totalLimit);

        $data = [
            'inventory_transactions' => $transactions,
            'pager' => [
                'totalPages'  => $totalPages,
                'currentPage' => $currentPage,
                'limit'       => $totalLimit,
            ],
            'search' => $search,
        ];

        return view('inventory_transactions/v_index', $data);
    }

    public function form()
    {
        $id = $this->request->getVar('id');
        $data['products'] = $this->productModel->findAll();

        if ($id) {
            $transaction = $this->InventoryTransactionModel->find($id);
            if (!$transaction) {
                return redirect()->to('/inventory')->with('failed', 'Transaction not found.');
            }
            $data['transaction'] = $transaction;
        }

        return view('inventory_transactions/v_form', $data);
    }

    private function updateVariantStock($variantId, string $type, int $qty)
    {
        $variant = $this->productVariantModel->find($variantId);

        if (!$variant || !is_array($variant)) {
            throw new \Exception('Variant not found or invalid data');
        }

        if (!isset($variant['stock'])) {
            throw new \Exception('Stock key not found in variant. Keys: ' . implode(', ', array_keys($variant)));
        }

        $stock = (int) $variant['stock'];

        if ($type === 'in') {
            $stock += $qty;
        } else {
            if ($stock < $qty) {
                throw new \Exception('Insufficient variant stock.');
            }
            $stock -= $qty;
        }

        $this->productVariantModel->update($variantId, [
            'stock' => $stock
        ]);
    }

    private function adjustVariantStock($variantId, int $adjustment)
    {
        $variant = $this->productVariantModel->find($variantId);

        if (!$variant || !is_array($variant)) {
            throw new \Exception('Variant not found or invalid data');
        }

        if (!isset($variant['stock'])) {
            throw new \Exception('Stock key not found in variant. Keys: ' . implode(', ', array_keys($variant)));
        }

        $newStock = (int) $variant['stock'] + $adjustment;

        if ($newStock < 0) {
            throw new \Exception('Insufficient variant stock after adjustment.');
        }

        $this->productVariantModel->update($variantId, [
            'stock' => $newStock
        ]);
    }

    private function recalcProductStock($productId)
    {
        $query = $this->productVariantModel
            ->select('SUM(stock) as total_stock')
            ->where('product_id', $productId)
            ->get();

        $result = $query->getRowArray();

        $total = 0;
        if ($result && isset($result['total_stock'])) {
            $total = (int) $result['total_stock'];
        }

        $this->productModel->update($productId, [
            'product_stock' => $total
        ]);
    }

    public function save()
    {
        $id = $this->request->getVar('id');
        $session = session();

        $productId = $this->request->getVar('product_id');
        $variantId = $this->request->getVar('variant_id'); // ✅ Bisa NULL
        $type      = $this->request->getVar('transaction_type');
        $reference_type = $this->request->getVar('reference_type');
        $reference_id   = $this->request->getVar('reference_id');
        $qty       = (int) $this->request->getVar('quantity');

        // ✅ Validasi: product harus ada
        $product = $this->productModel->find($productId);
        if (!$product) {
            return redirect()->back()->with('failed', 'Product not found.');
        }

        // ✅ Validasi: jika ada variant_id, variant harus exist
        if ($variantId) {
            $variant = $this->productVariantModel->find($variantId);
            if (!$variant) {
                return redirect()->back()->with('failed', 'Variant not found.');
            }
        }

        $data = [
            'product_id' => $productId,
            'variant_id' => $variantId, // ✅ Bisa NULL untuk product tanpa variant
            'transaction_type' => $type,
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
            'quantity' => $qty,
            'description' => $this->request->getVar('description'),
            'user_id' => $session->get('id'),
            'transaction_date' => date('Y-m-d H:i:s'),
        ];

        $this->db->transBegin();

        try {

            /** =========================
             * UPDATE TRANSACTION
             * ========================= */
            if ($id) {
                // 1️⃣ Ambil transaksi lama
                $old = $this->InventoryTransactionModel->find($id);
                if (!$old) {
                    throw new \Exception('Old transaction not found');
                }

                // 2️⃣ Hitung selisih qty
                $oldQty = (int) $old['quantity'];
                $qtyDiff = $qty - $oldQty;

                // 3️⃣ Adjust stock berdasarkan selisih dan type
                if ($qtyDiff != 0) {
                    if ($type === 'in') {
                        // ✅ Update variant atau product stock
                        if ($variantId) {
                            $this->adjustVariantStock($variantId, $qtyDiff);
                        } else {
                            $this->adjustProductStock($productId, $qtyDiff);
                        }
                    } else {
                        // ✅ Update variant atau product stock
                        if ($variantId) {
                            $this->adjustVariantStock($variantId, -$qtyDiff);
                        } else {
                            $this->adjustProductStock($productId, -$qtyDiff);
                        }
                    }
                }

                // 4️⃣ Update transaksi
                $this->InventoryTransactionModel->update($id, $data);
            }

            /** =========================
             * INSERT TRANSACTION (BARU)
             * ========================= */
            else {
                // ✅ Update stock: variant atau product
                if ($variantId) {
                    $this->updateVariantStock($variantId, $type, $qty);
                } else {
                    $this->updateProductStock($productId, $type, $qty);
                }

                // Insert transaksi
                $this->InventoryTransactionModel->insert($data);
            }

            // 5️⃣ Recalculate stok product (hanya jika ada variant)
            if ($variantId) {
                $this->recalcProductStock($productId);
            }

            $this->db->transCommit();

            // 🔥 TRIGGER REAL-TIME UPDATE
            \App\Libraries\Realtime::triggerUpdate('stock-update');

            return redirect()->to('/inventory')->with('success', 'Transaction saved.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->with('failed', $e->getMessage());
        }
    }

    /**
     * ✅ Helper: Adjust product stock (untuk product tanpa variant)
     */
    private function adjustProductStock($productId, $qtyDiff)
    {
        $product = $this->productModel->find($productId);
        if (!$product) {
            throw new \Exception('Product not found');
        }

        $newStock = $product['product_stock'] + $qtyDiff;

        if ($newStock < 0) {
            throw new \Exception('Insufficient stock');
        }

        $this->productModel->update($productId, [
            'product_stock' => $newStock
        ]);
    }

    /**
     * ✅ Helper: Update product stock langsung (untuk insert baru)
     */
    private function updateProductStock($productId, $type, $qty)
    {
        $product = $this->productModel->find($productId);
        if (!$product) {
            throw new \Exception('Product not found');
        }

        $currentStock = $product['product_stock'];

        if ($type === 'in') {
            $newStock = $currentStock + $qty;
        } else { // 'out'
            $newStock = $currentStock - $qty;
            if ($newStock < 0) {
                throw new \Exception('Insufficient stock');
            }
        }

        $this->productModel->update($productId, [
            'product_stock' => $newStock
        ]);
    }

    public function delete($id)
    {
        $this->InventoryTransactionModel->delete($id);
        return redirect()->to('/inventory')->with('success', 'Inventory transaction deleted successfully.');
    }
}
