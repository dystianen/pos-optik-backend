<?php

namespace App\Controllers\Api;

use App\Models\CartItemModel;
use App\Models\CartItemPrescriptionModel;
use App\Models\CartModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;

class CartApiController extends BaseApiController
{
    protected $cartModel;
    protected $cartItemModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $cartItemPrescriptionModel;

    public function __construct()
    {
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->cartItemPrescriptionModel = new CartItemPrescriptionModel();
    }

    // =======================
    // GET /api/cart/add-to-cart
    // =======================
    public function addToCart()
    {
        $db = db_connect();
        $db->transStart();

        try {
            $customerId = $this->getAuthenticatedCustomerId();
            $payload = $this->getRequestBody(true);

            $productId    = $payload['product_id'] ?? null;
            $variantId    = $payload['variant_id'] ?? null;
            $qty          = (int) ($payload['quantity'] ?? 0);
            $prescription = $payload['prescription'] ?? null;

            if (!$productId || $qty <= 0) {
                return $this->validationErrorResponse([
                    'product_id' => 'Product is required',
                    'quantity'   => 'Quantity must be greater than 0',
                ]);
            }

            // 🔎 Product
            $product = $db->table('products')
                ->where('product_id', $productId)
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$product) {
                return $this->notFoundResponse('Product not found');
            }

            // Check if product requires variant selection
            $hasVariants = isset($product['has_variants']) ? (int)$product['has_variants'] : 0;
            if ($hasVariants === 1 && !$variantId) {
                return $this->validationErrorResponse([
                    'variant_id' => 'Please select a variant for this product'
                ]);
            }
            if ($hasVariants === 0 && $variantId) {
                return $this->validationErrorResponse([
                    'variant_id' => 'This product does not have variants'
                ]);
            }

            // 🧮 Price & stock
            if ($variantId) {
                $variant = $db->table('product_variants')
                    ->where('variant_id', $variantId)
                    ->where('product_id', $productId)
                    ->get()
                    ->getRowArray();

                if (!$variant) {
                    return $this->notFoundResponse('Invalid variant');
                }

                if ($variant['stock'] < $qty) {
                    return $this->conflictResponse('Insufficient variant stock');
                }

                $price = $variant['price'];
            } else {
                if ($product['product_stock'] < $qty) {
                    return $this->conflictResponse('Insufficient product stock');
                }

                $price = $product['product_price'];
            }

            // 🛒 Cart
            $cart = $this->cartModel
                ->where('customer_id', $customerId)
                ->where('deleted_at', null)
                ->first();

            if (!$cart) {
                $this->cartModel->insert([
                    'customer_id' => $customerId,
                ]);

                $cart = $this->cartModel
                    ->where('customer_id', $customerId)
                    ->where('deleted_at', null)
                    ->first();
            }

            // 🧾 Cart Item
            $this->cartItemModel->insert([
                'cart_id'    => $cart['cart_id'],
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity'   => $qty,
                'price'      => $price,
            ]);

            $cartItemId = $this->cartItemModel->getInsertID();

            // 👓 Prescription
            if ($prescription && ($prescription['type'] ?? 'none') !== 'none') {
                $this->cartItemPrescriptionModel->insert([
                    'cart_item_id' => $cartItemId,

                    'right_sph'  => $prescription['right']['sph'] ?? null,
                    'right_cyl'  => $prescription['right']['cyl'] ?? null,
                    'right_axis' => $prescription['right']['axis'] ?? null,
                    'right_add'  => $prescription['right']['add'] ?? null,

                    'left_sph'   => $prescription['left']['sph'] ?? null,
                    'left_cyl'   => $prescription['left']['cyl'] ?? null,
                    'left_axis'  => $prescription['left']['axis'] ?? null,
                    'left_add'   => $prescription['left']['add'] ?? null,

                    'pd_left'    => $prescription['left']['pd'] ?? null,
                    'pd_right'   => $prescription['right']['pd'] ?? null,
                ]);
            }

            $db->transComplete();

            return $this->messageResponse('Item added to cart');
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // =======================
    // GET /api/cart
    // =======================
    public function listCart()
    {
        try {
            $customerId = $this->getAuthenticatedCustomerId();

            $cart = $this->cartModel
                ->where('customer_id', $customerId)
                ->where('deleted_at', null)
                ->first();

            if (!$cart) {
                return $this->successResponse([
                    'items' => [],
                    'summary' => [
                        'total_qty' => 0,
                        'total_price' => 0,
                    ],
                ]);
            }

            // 🔥 ITEMS QUERY (UNCHANGED)
            $items = $this->cartItemModel
                ->select("
                    cart_items.cart_item_id,
                    cart_items.product_id,
                    cart_items.variant_id,
                    cart_items.quantity,
                    cart_items.price,
                    products.product_name,
                    product_variants.variant_name,
                    COALESCE(pvi_img.url, pi_img.url) AS image
                ")
                ->join('products', 'products.product_id = cart_items.product_id')
                ->join('product_variants', 'product_variants.variant_id = cart_items.variant_id', 'left')
                ->join('product_variant_images pvi', 'pvi.variant_id = cart_items.variant_id AND pvi.deleted_at IS NULL', 'left')
                ->join('product_images pvi_img', 'pvi_img.product_image_id = pvi.product_image_id AND pvi_img.deleted_at IS NULL', 'left')
                ->join('product_images pi_img', 'pi_img.product_id = products.product_id AND pi_img.is_primary = 1 AND pi_img.deleted_at IS NULL', 'left')
                ->where('cart_items.cart_id', $cart['cart_id'])
                ->where('cart_items.deleted_at', null)
                ->findAll();

            // 👓 PRESCRIPTIONS
            $prescriptions = [];
            $cartItemIds = array_column($items, 'cart_item_id');

            if ($cartItemIds) {
                foreach (
                    $this->cartItemPrescriptionModel->whereIn('cart_item_id', $cartItemIds)->findAll()
                    as $row
                ) {
                    $prescriptions[$row['cart_item_id']] = [
                        'right' => [
                            'sph' => $row['right_sph'],
                            'cyl' => $row['right_cyl'],
                            'axis' => $row['right_axis'],
                            'add' => $row['right_add'],
                            'pd' => $row['pd_right'],
                        ],
                        'left' => [
                            'sph' => $row['left_sph'],
                            'cyl' => $row['left_cyl'],
                            'axis' => $row['left_axis'],
                            'add' => $row['left_add'],
                            'pd' => $row['pd_left'],
                        ],
                    ];
                }
            }

            // 🧮 MAP
            $totalQty = 0;
            $totalPrice = 0;

            $mappedItems = array_map(function ($item) use (&$totalQty, &$totalPrice, $prescriptions) {
                $subtotal = $item['price'] * $item['quantity'];
                $totalQty += $item['quantity'];
                $totalPrice += $subtotal;

                return [
                    'cart_item_id' => $item['cart_item_id'],
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'product_name' => $item['product_name'],
                    'variant_name' => $item['variant_name'],
                    'image' => $item['image'],
                    'price' => (int) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'subtotal' => (int) $subtotal,
                    'prescription' => $prescriptions[$item['cart_item_id']] ?? null,
                ];
            }, $items);

            return $this->successResponse([
                'items' => $mappedItems,
                'summary' => [
                    'total_qty' => $totalQty,
                    'total_price' => $totalPrice,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // =======================
    // GET /api/cart/total-cart
    // =======================
    public function getTotalCart()
    {
        try {
            $customerId = $this->getAuthenticatedCustomerId();

            // 🛒 Cart
            $cart = $this->cartModel
                ->where('customer_id', $customerId)
                ->where('deleted_at', null)
                ->first();

            if (!$cart) {
                return $this->successResponse([
                    'total_items' => 0
                ]);
            }

            // 🧮 Total quantity
            $totalItems = $this->cartItemModel
                ->select('SUM(quantity) AS total_items')
                ->where('cart_id', $cart['cart_id'])
                ->where('deleted_at', null)
                ->get()
                ->getRow()
                ->total_items ?? 0;

            return $this->successResponse([
                'total_items' => (int) $totalItems
            ]);
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // =======================
    // GET /api/cart/delete/{id}
    // =======================
    public function deleteCartItem($cartItemId)
    {
        $db = db_connect();
        $db->transStart();

        try {
            $customerId = $this->getAuthenticatedCustomerId();

            $cart = $this->cartModel
                ->where('customer_id', $customerId)
                ->where('deleted_at', null)
                ->first();

            if (!$cart) {
                return $this->notFoundResponse('Cart not found');
            }

            $cartItem = $this->cartItemModel
                ->where('cart_item_id', $cartItemId)
                ->where('cart_id', $cart['cart_id'])
                ->where('deleted_at', null)
                ->first();

            if (!$cartItem) {
                return $this->notFoundResponse('Cart item not found');
            }

            $this->cartItemModel->delete($cartItemId);

            $summary = $this->cartItemModel
                ->select('SUM(quantity) AS total_qty, SUM(quantity * price) AS total_price')
                ->where('cart_id', $cart['cart_id'])
                ->where('deleted_at', null)
                ->get()
                ->getRow();

            $db->transComplete();

            return $this->successResponse([
                'summary' => [
                    'total_qty' => (int) ($summary->total_qty ?? 0),
                    'total_price' => (int) ($summary->total_price ?? 0),
                ],
            ], 'Item removed from cart');
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    // =======================
    // PUT /api/cart/update/{id}
    // =======================
    public function updateCartItemQuantity($cartItemId)
    {
        $db = db_connect();
        $db->transStart();

        try {
            $customerId = $this->getAuthenticatedCustomerId();
            $payload = $this->getRequestBody(true);
            $qty = (int) ($payload['quantity'] ?? 0);

            if ($qty <= 0) {
                return $this->validationErrorResponse([
                    'quantity' => 'Quantity must be greater than 0',
                ]);
            }

            // 🛒 Cart
            $cart = $this->cartModel
                ->where('customer_id', $customerId)
                ->where('deleted_at', null)
                ->first();

            if (!$cart) {
                return $this->notFoundResponse('Cart not found');
            }

            // 🧾 Cart Item
            $cartItem = $this->cartItemModel
                ->where('cart_item_id', $cartItemId)
                ->where('cart_id', $cart['cart_id'])
                ->where('deleted_at', null)
                ->first();

            if (!$cartItem) {
                return $this->notFoundResponse('Cart item not found');
            }

            $productId = $cartItem['product_id'];
            $variantId = $cartItem['variant_id'];

            // 🔎 Product
            $product = $db->table('products')
                ->where('product_id', $productId)
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$product) {
                return $this->notFoundResponse('Product not found');
            }

            // Check stock
            if ($variantId) {
                $variant = $db->table('product_variants')
                    ->where('variant_id', $variantId)
                    ->where('product_id', $productId)
                    ->get()
                    ->getRowArray();

                if (!$variant) {
                    return $this->notFoundResponse('Invalid variant');
                }

                if ($variant['stock'] < $qty) {
                    return $this->conflictResponse('Insufficient variant stock');
                }
            } else {
                if ($product['product_stock'] < $qty) {
                    return $this->conflictResponse('Insufficient product stock');
                }
            }

            // Update quantity
            $this->cartItemModel->update($cartItemId, [
                'quantity' => $qty,
            ]);

            // Get updated summary
            $summary = $this->cartItemModel
                ->select('SUM(quantity) AS total_qty, SUM(quantity * price) AS total_price')
                ->where('cart_id', $cart['cart_id'])
                ->where('deleted_at', null)
                ->get()
                ->getRow();

            $db->transComplete();

            return $this->successResponse([
                'summary' => [
                    'total_qty' => (int) ($summary->total_qty ?? 0),
                    'total_price' => (int) ($summary->total_price ?? 0),
                ],
            ], 'Cart item quantity updated');
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
