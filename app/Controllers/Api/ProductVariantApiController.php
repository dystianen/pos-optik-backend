<?php

namespace App\Controllers\Api;

use App\Models\ProductVariantModel;
use CodeIgniter\API\ResponseTrait;

class ProductVariantApiController extends BaseApiController
{
    protected $variantModel;
    public function __construct()
    {
        $this->variantModel = new ProductVariantModel();
    }

    // GET /api/variants
    public function getByProductId()
    {
        $request = $this->request;
        $productId = $request->getVar('productId');

        $variants = $this->variantModel
            ->where('product_id', $productId)
            ->findAll();

        return $this->successResponse($variants);
    }
}
