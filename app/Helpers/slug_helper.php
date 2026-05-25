<?php

if (!function_exists('generate_slug')) {
    function generate_slug($text) {
        // 1. lowercase
        $text = strtolower($text);
        // 2. trim whitespace
        $text = trim($text);
        // 3. remove special characters (keep alphanumeric, space, hyphens)
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        // 4. replace space with "-"
        $text = preg_replace('/[\s-]+/', '-', $text);
        // 5. trim leading/trailing hyphens
        $text = trim($text, '-');
        return $text;
    }
}

if (!function_exists('generate_unique_category_slug')) {
    function generate_unique_category_slug($name, $excludeId = null) {
        $slug = generate_slug($name);
        if (empty($slug)) {
            $slug = 'category';
        }
        $db = \Config\Database::connect();
        $builder = $db->table('product_categories');
        
        $originalSlug = $slug;
        $counter = 2;
        
        while (true) {
            $builder->where('category_slug', $slug);
            if ($excludeId !== null) {
                $builder->where('category_id !=', $excludeId);
            }
            $exists = $builder->countAllResults() > 0;
            if (!$exists) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
