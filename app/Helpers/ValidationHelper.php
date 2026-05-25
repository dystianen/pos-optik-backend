<?php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Get standard validation messages in English
     */
    public static function getStandardMessages(): array
    {
        return [
            'required'        => '{field} is required and cannot be empty.',
            'max_length'      => '{field} cannot exceed {param} characters. You entered {value} characters.',
            'min_length'      => '{field} must be at least {param} characters long.',
            'valid_email'     => '{field} must be a valid email address (e.g., user@example.com).',
            'valid_date'      => '{field} must be a valid date (format: YYYY-MM-DD).',
            'numeric'         => '{field} must be a numeric value.',
            'integer'         => '{field} must be an integer without decimal points.',
            'decimal'         => '{field} must be a decimal number.',
            'is_natural'      => '{field} must be a positive number.',
            'is_natural_no_zero' => '{field} must be a positive number greater than zero.',
            'in_list'         => '{field} must be one of the allowed values.',
            'exact_length'    => '{field} must be exactly {param} characters long.',
            'alpha'           => '{field} can only contain alphabetic characters.',
            'alpha_dash'      => '{field} can only contain alphabetic characters, numbers, hyphens, and underscores.',
            'alpha_numeric'   => '{field} can only contain alphabetic characters and numbers.',
            'valid_url'       => '{field} must be a valid URL.',
            'regex_match'     => '{field} format is not valid.',
            'matches'         => '{field} does not match {param}.',
            'is_unique'       => '{field} already exists in the system. Please use a different value.',
            'is_not_unique'   => '{field} combination is not unique.',
            'greater_than'    => '{field} must be greater than {param}.',
            'less_than'       => '{field} must be less than {param}.',
            'greater_than_equal_to' => '{field} must be greater than or equal to {param}.',
            'less_than_equal_to' => '{field} must be less than or equal to {param}.',
            'permit_empty'    => '{field} is optional.',
        ];
    }

    /**
     * Get field-specific validation messages
     */
    public static function getFieldMessages(): array
    {
        return [
            'customer_name' => [
                'required'   => 'Customer name is required.',
                'max_length' => 'Customer name must not exceed 100 characters.',
            ],
            'customer_email' => [
                'required'    => 'Email address is required.',
                'valid_email' => 'Please enter a valid email address (e.g., customer@example.com).',
                'max_length'  => 'Email address must not exceed 100 characters.',
                'is_unique'   => 'This email address is already registered in the system.',
            ],
            'customer_password' => [
                'required'    => 'Password is required when creating a new customer account.',
                'max_length'  => 'Password must not exceed 255 characters.',
                'min_length'  => 'Password must be at least 8 characters long.',
            ],
            'customer_phone' => [
                'max_length'  => 'Phone number must not exceed 20 characters.',
            ],
            'customer_dob' => [
                'valid_date' => 'Date of birth must be a valid date (format: YYYY-MM-DD).',
            ],
            'customer_gender' => [
                'in_list' => 'Gender must be one of: Male, Female, or Other.',
            ],
            'product_name' => [
                'required'   => 'Product name is required.',
                'max_length' => 'Product name must not exceed 255 characters.',
            ],
            'product_price' => [
                'required'  => 'Product price is required.',
                'decimal'   => 'Product price must be a valid decimal number.',
                'greater_than' => 'Product price must be greater than 0.',
            ],
            'category_id' => [
                'required' => 'Please select a category for the product.',
            ],
            'user_email' => [
                'required'    => 'Email address is required.',
                'valid_email' => 'Please enter a valid email address.',
                'is_unique'   => 'This email address is already registered.',
            ],
            'user_password' => [
                'required'    => 'Password is required.',
                'min_length'  => 'Password must be at least 8 characters long.',
                'regex_match' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            ],
            'user_name' => [
                'required'   => 'Full name is required.',
                'max_length' => 'Full name must not exceed 100 characters.',
            ],
            'role_name' => [
                'required'   => 'Role name is required.',
                'max_length' => 'Role name must not exceed 100 characters.',
            ],
        ];
    }

    /**
     * Merge custom messages with standard ones
     */
    public static function mergeMessages(array $customMessages = []): array
    {
        $standard = self::getStandardMessages();
        $field = self::getFieldMessages();
        
        return array_merge($standard, $field, $customMessages);
    }

    /**
     * Common validation rules for customer form
     */
    public static function getCustomerValidationRules(): array
    {
        return [
            'customer_name'     => 'required|max_length[100]',
            'customer_email'    => 'required|valid_email|max_length[100]|is_unique[customers.customer_email,customer_id,{id}]',
            'customer_password' => 'required|max_length[255]',
            'customer_phone'    => 'permit_empty|max_length[20]',
            'customer_dob'      => 'permit_empty|valid_date',
            'customer_gender'   => 'permit_empty|in_list[male,female,other]',
        ];
    }

    /**
     * Common validation rules for product form
     */
    public static function getProductValidationRules(): array
    {
        return [
            'category_id'    => 'required',
            'product_name'   => 'required|max_length[255]',
            'product_price'  => 'required|decimal|greater_than[0]',
            'product_description' => 'permit_empty|max_length[1000]',
            'product_stock'  => 'permit_empty|is_natural_no_zero',
        ];
    }

    /**
     * Common validation rules for user form
     */
    public static function getUserValidationRules(): array
    {
        return [
            'user_name'     => 'required|max_length[100]',
            'user_email'    => 'required|valid_email|max_length[100]|is_unique[users.user_email,user_id,{id}]',
            'user_password' => 'required|min_length[8]|max_length[255]',
            'role_id'       => 'required',
        ];
    }

    /**
     * Get CSS classes for form group based on validation errors
     */
    public static function getFormGroupClass(array $errors = null, string $fieldName = ''): string
    {
        if ($errors && isset($errors[$fieldName])) {
            return 'form-group has-error';
        }
        return 'form-group';
    }

    /**
     * Format validation errors for display
     */
    public static function formatValidationErrors(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $html .= '<h5 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Validation Errors</h5>';
        $html .= '<ul class="mb-0">';

        foreach ($errors as $field => $message) {
            $html .= '<li>' . htmlspecialchars($message) . '</li>';
        }

        $html .= '</ul>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render form field with error display
     */
    public static function renderFormField(
        string $type = 'text',
        string $name = '',
        string $label = '',
        string $value = '',
        array $errors = null,
        array $options = []
    ): string {
        $required = $options['required'] ?? false;
        $placeholder = $options['placeholder'] ?? '';
        $helpText = $options['helpText'] ?? '';
        $class = $options['class'] ?? 'form-control';
        $hasError = $errors && isset($errors[$name]);

        $html = '<div class="mb-3">';
        
        if ($label) {
            $html .= '<label for="' . htmlspecialchars($name) . '" class="form-label">';
            $html .= htmlspecialchars($label);
            if ($required) {
                $html .= ' <span class="text-danger">*</span>';
            }
            $html .= '</label>';
        }

        $inputClass = $class;
        if ($hasError) {
            $inputClass .= ' is-invalid';
        }

        $attributes = [
            'type' => $type,
            'name' => $name,
            'id' => $name,
            'class' => $inputClass,
            'value' => htmlspecialchars($value),
        ];

        if ($placeholder) {
            $attributes['placeholder'] = $placeholder;
        }

        if ($required) {
            $attributes['required'] = 'required';
        }

        // Merge with additional options
        foreach ($options as $key => $val) {
            if (!in_array($key, ['required', 'placeholder', 'helpText', 'class'])) {
                $attributes[$key] = $val;
            }
        }

        $attrString = '';
        foreach ($attributes as $key => $val) {
            if ($val !== false) {
                $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
            }
        }

        $html .= '<input' . $attrString . '>';

        if ($hasError) {
            $html .= '<div class="invalid-feedback d-block">';
            $html .= htmlspecialchars($errors[$name]);
            $html .= '</div>';
        }

        if ($helpText) {
            $html .= '<small class="form-text text-muted d-block mt-1">' . htmlspecialchars($helpText) . '</small>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render select field with error display
     */
    public static function renderSelectField(
        string $name = '',
        string $label = '',
        string $value = '',
        array $options = [],
        array $errors = null,
        array $attributes = []
    ): string {
        $required = $attributes['required'] ?? false;
        $helpText = $attributes['helpText'] ?? '';
        $class = $attributes['class'] ?? 'form-control';
        $hasError = $errors && isset($errors[$name]);

        $html = '<div class="mb-3">';

        if ($label) {
            $html .= '<label for="' . htmlspecialchars($name) . '" class="form-label">';
            $html .= htmlspecialchars($label);
            if ($required) {
                $html .= ' <span class="text-danger">*</span>';
            }
            $html .= '</label>';
        }

        $selectClass = $class;
        if ($hasError) {
            $selectClass .= ' is-invalid';
        }

        $html .= '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) . '" class="' . htmlspecialchars($selectClass) . '"';

        if ($required) {
            $html .= ' required';
        }

        $html .= '>';

        foreach ($options as $optValue => $optLabel) {
            $selected = ($optValue === $value) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($optValue) . '"' . $selected . '>' . htmlspecialchars($optLabel) . '</option>';
        }

        $html .= '</select>';

        if ($hasError) {
            $html .= '<div class="invalid-feedback d-block">';
            $html .= htmlspecialchars($errors[$name]);
            $html .= '</div>';
        }

        if ($helpText) {
            $html .= '<small class="form-text text-muted d-block mt-1">' . htmlspecialchars($helpText) . '</small>';
        }

        $html .= '</div>';

        return $html;
    }
}
