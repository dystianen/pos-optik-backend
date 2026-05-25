<?php

namespace App\Database\Seeds;

use App\Models\OrderStatusModel;
use CodeIgniter\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    public function run()
    {
        $orderStatuseModel = new OrderStatusModel();

        $data = [
            [
                'status_id'   => '2aa5c9be-906c-402c-a5fc-a16663125c3a',
                'status_code' => 'pending',
                'status_name' => 'Pending Payment',
            ],
            [
                'status_id'   => '7f39039d-d2ef-46d1-93f5-8dbc0b5211fe',
                'status_code' => 'waiting_confirmation',
                'status_name' => 'Waiting Payment Confirmation',
            ],
            [
                'status_id'   => 'cc46d2a8-436c-42fc-96a1-ffb537dbabed',
                'status_code' => 'processing',
                'status_name' => 'Order Processing',
            ],
            [
                'status_id'   => '4d609622-8392-469b-acd1-c7859424633a',
                'status_code' => 'shipped',
                'status_name' => 'Shipped to Courier',
            ],
            [
                'status_id'   => '8d434de4-ba22-4698-8438-8318ef3f6d8f',
                'status_code' => 'completed',
                'status_name' => 'Order Completed',
            ],
            [
                'status_id'   => '0ab780fe-49da-4a95-ad73-56c3c74f2416',
                'status_code' => 'cancelled',
                'status_name' => 'Order Cancelled',
            ],
            [
                'status_id'   => 'ae12a448-98b3-4dc1-9c71-87468abc7bb5',
                'status_code' => 'refunded',
                'status_name' => 'Order Refunded',
            ],
            [
                'status_id'   => '09137a62-99b7-48ba-bf27-8c4177ddc185',
                'status_code' => 'partially_refunded',
                'status_name' => 'Partially Refunded',
            ],
            [
                'status_id'   => 'f1a3c2b4-9e77-4e8d-9b12-2c5a7e8f91ab',
                'status_code' => 'rejected',
                'status_name' => 'Payment Rejected',
            ],
            [
                'status_id'   => '234af2ae-bba4-4fa3-b386-5f1390c51146',
                'status_code' => 'expired',
                'status_name' => 'Payment Expired',
            ],
        ];


        $orderStatuseModel->insertBatch($data);
    }
}
