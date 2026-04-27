<?php

if (!function_exists('t')) {
    /**
     * Translate text based on current language
     */
    function t($key, $default = '')
    {
        $session = session();
        $language = $session->get('language') ?: 'en';
        
        $db = \Config\Database::connect();
        $query = $db->table('translations')
            ->select('value')
            ->where('language_code', $language)
            ->where('key', $key)
            ->get()
            ->getRow();
        
        if ($query && !empty($query->value)) {
            return $query->value;
        }
        
        return $default ?: $key;
    }
}

if (!function_exists('getBMICategoryUrdu')) {
    /**
     * Get BMI category in Urdu
     */
    function getBMICategoryUrdu($category)
    {
        $categories = [
            'underweight' => '?? ???',
            'normal' => '?????',
            'overweight' => '????? ???',
            'obese' => '??????'
        ];
        
        return $categories[$category] ?? $category;
    }
}