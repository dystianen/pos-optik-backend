<?php

namespace App\Controllers\Api;

use App\Models\CustomerShippingAddressModel;

class CustomerShippingAddressApiController extends BaseApiController
{
    protected $csaModel;

    public function __construct()
    {
        $this->csaModel = new CustomerShippingAddressModel();
    }

    // =======================
    // GET /api/shipping
    // =======================
    public function getAllShippingAddress()
    {
        $jwtUser = getJWTUser();
        if (!$jwtUser) {
            return $this->unauthorizedResponse();
        }

        $customerId = $jwtUser->user_id;

        $data = $this->csaModel
            ->where('customer_id', $customerId)
            ->findAll();

        return $this->successResponse(
            $data,
            'Get all shipping address successfully'
        );
    }

    // =======================
    // GET /api/shipping/{id}
    // =======================
    public function getById($id)
    {
        $customerId = $this->getAuthenticatedCustomerId();
        $data = $this->csaModel->where('csa_id', $id)->where('customer_id', $customerId)->first();

        if (!$data) {
            return $this->notFoundResponse('Shipping address not found');
        }

        return $this->successResponse(
            $data,
            'Get shipping address successfully'
        );
    }

    // =======================
    // POST /api/shipping
    // =======================
    public function save()
    {
        try {
            $customerId = $this->getAuthenticatedCustomerId();
            $id = $this->request->getVar('id');

            $data = [
                'customer_id'    => $customerId,
                'recipient_name' => $this->request->getVar('recipient_name'),
                'phone'          => $this->request->getVar('phone'),
                'address'        => $this->request->getVar('address'),
                'city'           => $this->request->getVar('city'),
                'city_id'        => $this->request->getVar('city_id'),
                'district'       => $this->request->getVar('district'),
                'district_id'    => $this->request->getVar('district_id'),
                'province'       => $this->request->getVar('province'),
                'province_id'    => $this->request->getVar('province_id'),
                'postal_code'    => $this->request->getVar('postal_code'),
            ];

            if (!$this->validate($this->csaModel->validationRules)) {
                return $this->validationErrorResponse(
                    $this->validator->getErrors()
                );
            }

            if ($id) {
                if (!$this->csaModel->update($id, $data)) {
                    return $this->serverErrorResponse(
                        'Failed to update shipping address'
                    );
                }

                return $this->successResponse(
                    ['csa_id' => $id],
                    'Shipping address updated successfully'
                );
            }

            if (!$this->csaModel->insert($data)) {
                return $this->serverErrorResponse(
                    'Failed to create shipping address'
                );
            }

            $newId = $this->csaModel->getInsertID();
            return $this->successResponse(
                ['csa_id' => $newId],
                'Shipping address created successfully'
            );
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
