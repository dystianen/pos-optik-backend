<?php

namespace App\Controllers\Api;

use App\Libraries\RajaOngkir;
use App\Models\CustomerShippingAddressModel;
use App\Models\CartModel;
use App\Models\CartItemModel;

class ShippingApiController extends BaseApiController
{
    protected $csaModel;
    protected $cartModel;
    protected $cartItemModel;

    public function __construct()
    {
        $this->csaModel = new CustomerShippingAddressModel();
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
    }

    public function getProvinces()
    {
        try {
            $provinces = RajaOngkir::getProvinces();
            return $this->successResponse($provinces, 'Get provinces successfully');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getCities()
    {
        try {
            $provinceId = $this->request->getGet('province_id');
            $cities = RajaOngkir::getCities($provinceId);
            return $this->successResponse($cities, 'Get cities successfully');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getDistricts()
    {
        try {
            $cityId = $this->request->getGet('city_id');
            $districts = RajaOngkir::getDistricts($cityId);
            return $this->successResponse($districts, 'Get districts successfully');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function calculateCost()
    {
        try {
            $customerId = $this->getAuthenticatedCustomerId();
            if (!$customerId) {
                return $this->unauthorizedResponse();
            }

            $addressId = $this->request->getVar('address_id');
            if (!$addressId) {
                return $this->validationErrorResponse(['address_id' => 'address_id is required']);
            }

            $address = $this->csaModel
                ->where('customer_id', $customerId)
                ->find($addressId);

            if (!$address) {
                return $this->notFoundResponse('Shipping address not found');
            }

            $destinationId = $address['district_id'] ?? null;
            $destinationType = 'subdistrict';

            if (!$destinationId) {
                $destinationId = $address['city_id'] ?? null;
                $destinationType = 'city';
            }

            if (!$destinationId) {
                return $this->validationErrorResponse(['district_id' => 'Select a valid district/city with RajaOngkir ID']);
            }

            // Calculate total weight of cart items
            $cart = $this->cartModel
                ->where('customer_id', $customerId)
                ->where('deleted_at', null)
                ->first();

            $totalWeight = 500; // Default weight in grams (0.5 kg) if cart is empty

            if ($cart) {
                $items = $this->cartItemModel
                    ->where('cart_id', $cart['cart_id'])
                    ->where('deleted_at', null)
                    ->findAll();

                $totalQty = 0;
                foreach ($items as $item) {
                    $totalQty += (int)$item['quantity'];
                }

                if ($totalQty > 0) {
                    $totalWeight = $totalQty * 500; // 500 grams per item
                }
            }

            $shippingOptions = [];
            $couriers = ['jne', 'pos', 'tiki'];

            foreach ($couriers as $courier) {
                $res = RajaOngkir::calculateCost($destinationId, $totalWeight, $courier, $destinationType);
                if ($res && isset($res['costs'])) {
                    foreach ($res['costs'] as $serviceCost) {
                        $shippingOptions[] = [
                            'courier' => $res['code'],
                            'courier_name' => $res['name'],
                            'service' => $serviceCost['service'],
                            'description' => $serviceCost['description'],
                            'cost' => (int)($serviceCost['cost'][0]['value'] ?? 0),
                            'etd' => $serviceCost['cost'][0]['etd'] ?? ''
                        ];
                    }
                }
            }

            return $this->successResponse($shippingOptions, 'Calculate shipping cost successfully');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
