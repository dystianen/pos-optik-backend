<?php

if (!function_exists('get_category_shortcode')) {
    function get_category_shortcode($categoryName) {
        $word = explode(' ', trim($categoryName))[0];
        $word = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $word));
        if (strlen($word) > 3 && substr($word, -1) === 'S') {
            $word = substr($word, 0, -1);
        }
        return $word;
    }
}

if (!function_exists('generate_unique_product_sku')) {
    function generate_unique_product_sku($categoryId, $excludeProductId = null) {
        $db = \Config\Database::connect();
        
        $category = $db->table('product_categories')
            ->where('category_id', $categoryId)
            ->get()
            ->getRowArray();
            
        $categoryName = $category['category_name'] ?? 'TEMP';
        $shortcode = get_category_shortcode($categoryName);
        
        // Count products with this category
        $count = $db->table('products')
            ->where('category_id', $categoryId)
            ->countAllResults();
            
        $counter = $count + 1;
        
        while (true) {
            $sku = "OPT-" . $shortcode . "-" . str_pad($counter, 4, '0', STR_PAD_LEFT);
            
            // Check if unique
            $builder = $db->table('products')->where('product_sku', $sku);
            if ($excludeProductId !== null) {
                $builder->where('product_id !=', $excludeProductId);
            }
            $exists = $builder->countAllResults() > 0;
            if (!$exists) {
                return $sku;
            }
            $counter++;
        }
    }
}

if (!function_exists('abbreviate_value')) {
    function abbreviate_value($value) {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        
        // If it is numeric (e.g. 52, 1.5, etc.), keep digits only
        $digits = preg_replace('/[^0-9]/', '', $value);
        if ($digits !== '' && (is_numeric($value) || preg_match('/^\d+(\.\d+)?$/', $value))) {
            return $digits;
        }
        
        // Clean string: letters, numbers and spaces/hyphens
        $clean = preg_replace('/[^A-Za-z0-9\s-]/', '', $value);
        
        // Split into words
        $words = preg_split('/[\s-]+/', $clean);
        $words = array_filter($words);
        if (count($words) > 1) {
            $abbr = '';
            foreach ($words as $w) {
                if (!empty($w)) {
                    $abbr .= $w[0];
                }
            }
            return strtoupper($abbr);
        }
        
        // Single word, e.g. "Black" -> consonants "BLK"
        $upper = strtoupper($clean);
        $consonants = preg_replace('/[AEIOU]/', '', $upper);
        if (strlen($consonants) >= 3) {
            return substr($consonants, 0, 3);
        }
        return substr($upper, 0, min(3, strlen($upper)));
    }
}

if (!function_exists('generate_variant_sku')) {
    function generate_variant_sku($productSku, $mapping) {
        if (empty($mapping)) {
            return $productSku;
        }
        
        // Sort mapping by attribute_id ASC to ensure deterministic ordering
        usort($mapping, function($a, $b) {
            return strcmp($a['attribute_id'], $b['attribute_id']);
        });
        
        $abbrs = [];
        foreach ($mapping as $item) {
            $val = $item['value'] ?? '';
            $abbr = abbreviate_value($val);
            if ($abbr !== '') {
                $abbrs[] = $abbr;
            }
        }
        
        if (!empty($abbrs)) {
            return $productSku . '-' . implode('-', $abbrs);
        }
        return $productSku;
    }
}
