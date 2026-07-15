<?php

namespace App\Traits;

trait ValidationHelperTrait
{
    /**
     * Beautifies raw validation error messages from CodeIgniter's validator.
     *
     * @param array $errors Key-value array of field validation errors.
     * @return array Beautified key-value array of errors.
     */
    protected function beautifyValidationErrors(array $errors): array
    {
        $fieldLabels = [
            // Customer / Auth
            'customer_name'            => 'Name',
            'customer_email'           => 'Email address',
            'customer_password'        => 'Password',
            'customer_phone'           => 'Phone number',
            'customer_dob'             => 'Date of birth',
            'customer_gender'          => 'Gender',
            'customer_occupation'      => 'Occupation',
            'customer_eye_history'     => 'Eye history',
            'customer_preferences'     => 'Preferences',
            'confirm_password'         => 'Confirm password',
            'email'                    => 'Email address',
            'password'                 => 'Password',

            // Product
            'product_name'             => 'Product name',
            'product_price'            => 'Product price',
            'product_brand'            => 'Brand',
            'product_stock'            => 'Stock',
            'product_id'               => 'Product',
            'category_id'              => 'Category',
            'images'                   => 'Product images',

            // Variant
            'variant_id'               => 'Product variant',
            'variant_name'             => 'Variant name',

            // Attribute
            'attribute_name'           => 'Attribute name',
            'attribute_type'           => 'Attribute type',

            // Cart
            'quantity'                 => 'Quantity',

            // Order
            'order_id'                 => 'Order ID',

            // Cancellation & Refund
            'reason'                   => 'Reason',
            'additional_note'          => 'Additional note',
            'refund_type'              => 'Refund type',
            'refund_amount'            => 'Refund amount',
            'refund_id'                => 'Refund ID',
            'user_refund_account_id'   => 'Refund account',
            'evidence'                 => 'Evidence',

            // Return Shipping
            'courier'                  => 'Courier',
            'tracking_number'          => 'Tracking number',

            // Shipping Address
            'recipient_name'           => 'Recipient name',
            'phone'                    => 'Phone number',
            'address'                  => 'Address',
            'city'                     => 'City',
            'province'                 => 'Province',
            'postal_code'              => 'Postal code',

            // Refund Account
            'account_name'             => 'Account name',
            'bank_name'                => 'Bank name',
            'account_number'           => 'Account number',

            // Review
            'rating'                   => 'Rating',
            'comment'                  => 'Comment',
        ];

        $beautified = [];
        foreach ($errors as $field => $message) {
            // Determine friendly label
            $label = $fieldLabels[$field] ?? null;
            if (!$label) {
                // Strip common table prefixes and clean up
                $clean = preg_replace('/^(customer|product|order|user|variant|attribute)_/', '', $field);
                $label = ucwords(str_replace('_', ' ', $clean));
            }

            // Patterns to match the default CodeIgniter messages
            if (preg_match('/is required/i', $message)) {
                $beautified[$field] = "$label is required.";

            } elseif (preg_match('/must contain a unique value/i', $message)) {
                $beautified[$field] = "This $label is already taken.";

            } elseif (preg_match('/must contain a valid email/i', $message)) {
                $beautified[$field] = "Please enter a valid email address.";

            } elseif (preg_match('/must be at least (\d+) characters/i', $message, $m)) {
                $beautified[$field] = "$label must be at least {$m[1]} characters long.";

            } elseif (preg_match('/cannot exceed (\d+) characters/i', $message, $m)) {
                $beautified[$field] = "$label cannot exceed {$m[1]} characters.";

            } elseif (preg_match('/not exceed (\d+) characters/i', $message, $m)) {
                $beautified[$field] = "$label cannot exceed {$m[1]} characters.";

            } elseif (preg_match('/does not match the (\S+) field/i', $message, $m)) {
                $mf = $m[1];
                $ml = $fieldLabels[$mf] ?? ucwords(str_replace('_', ' ', preg_replace('/^(customer|product|order|user|variant|attribute)_/', '', $mf)));
                $beautified[$field] = "$label does not match $ml.";

            } elseif (preg_match('/must contain only numbers/i', $message)) {
                $beautified[$field] = "$label must be a number.";

            } elseif (preg_match('/must be a decimal/i', $message)) {
                $beautified[$field] = "$label must be a valid decimal number.";

            } elseif (preg_match('/must be numeric/i', $message)) {
                $beautified[$field] = "$label must be a numeric value.";

            } elseif (preg_match('/must be an integer/i', $message)) {
                $beautified[$field] = "$label must be a whole number.";

            } elseif (preg_match('/must be greater than (\S+)/i', $message, $m)) {
                $beautified[$field] = "$label must be greater than {$m[1]}.";

            } elseif (preg_match('/must be less than (\S+)/i', $message, $m)) {
                $beautified[$field] = "$label must be less than {$m[1]}.";

            } elseif (preg_match('/must be greater than or equal/i', $message, $m)) {
                $beautified[$field] = "$label must be 0 or more.";

            } elseif (preg_match('/must contain a valid date/i', $message)) {
                $beautified[$field] = "Please enter a valid date for $label.";

            } elseif (preg_match('/must be one of:/i', $message)) {
                $beautified[$field] = "Please select a valid $label.";

            } elseif (preg_match('/is not a permitted mime type/i', $message)) {
                $beautified[$field] = "The $label file format is not allowed.";

            } elseif (preg_match('/exceeds the allowed filesize/i', $message)) {
                $beautified[$field] = "The $label file is too large.";

            } elseif (preg_match('/no file was uploaded/i', $message)) {
                $beautified[$field] = "$label file is required.";

            } elseif (preg_match('/uploaded file did not pass/i', $message)) {
                $beautified[$field] = "The $label file upload failed. Please try again.";

            } elseif (preg_match('/must have a minimum length of (\d+)/i', $message, $m)) {
                $beautified[$field] = "$label must be exactly {$m[1]} characters long.";

            } elseif (preg_match('/must have an exact length of (\d+)/i', $message, $m)) {
                $beautified[$field] = "$label must be exactly {$m[1]} characters.";

            } else {
                // Fallback: strip the raw field name from the message and clean it up
                $cleanMsg = str_replace(["The $field field", "The " . str_replace('_', ' ', $field) . " field"], $label, $message);
                $cleanMsg = str_replace($field, $label, $cleanMsg);
                $beautified[$field] = ucfirst(rtrim($cleanMsg, '.') . '.');
            }
        }

        return $beautified;
    }
}
