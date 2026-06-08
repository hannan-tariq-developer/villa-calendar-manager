<?php
/**
 * TPR Settings Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class TPR_Settings {
    
    const OPTION_NAME = 'tpr_villa_settings';
    
    /**
     * Set default settings on activation
     */
    public static function set_default_settings() {
        $defaults = array(
            'access_code_enabled' => false,
            'access_code' => self::generate_random_code(),
            'minimum_nights' => 3,
            'color_available' => '#22c55e',
            'color_booked' => '#374151',
            'color_selected' => '#3b82f6',
            'color_disabled' => '#e5e7eb',
            'multiple_villas' => false
        );
        
        $existing = get_option(self::OPTION_NAME);
        if (!$existing) {
            update_option(self::OPTION_NAME, $defaults);
        }
    }
    
    /**
     * Get all settings
     */
    public static function get_settings() {
        $settings = get_option(self::OPTION_NAME);
        if (!$settings) {
            self::set_default_settings();
            $settings = get_option(self::OPTION_NAME);
        }
        return $settings;
    }
    
    /**
     * Get a specific setting
     */
    public static function get($key, $default = null) {
        $settings = self::get_settings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    /**
     * Update a setting
     */
    public static function update($key, $value) {
        $settings = self::get_settings();
        $settings[$key] = $value;
        return update_option(self::OPTION_NAME, $settings);
    }
    
    /**
     * Update multiple settings
     */
    public static function update_multiple($data) {
        $settings = self::get_settings();
        $settings = array_merge($settings, $data);
        return update_option(self::OPTION_NAME, $settings);
    }
    
    /**
     * Verify access code
     */
    public static function verify_access_code($code) {
        $enabled = self::get('access_code_enabled', false);
        
        // If access code is disabled, always return true
        if (!$enabled) {
            return true;
        }
        
        $stored_code = self::get('access_code', '');
        return $code === $stored_code;
    }
    
    /**
     * Generate random access code
     */
    private static function generate_random_code($length = 8) {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }
    
    /**
     * Get minimum nights setting
     */
    public static function get_minimum_nights() {
        return absint(self::get('minimum_nights', 3));
    }
    
    /**
     * Get color settings
     */
    public static function get_colors() {
        return array(
            'available' => self::get('color_available', '#22c55e'),
            'booked' => self::get('color_booked', '#374151'),
            'selected' => self::get('color_selected', '#3b82f6'),
            'disabled' => self::get('color_disabled', '#e5e7eb')
        );
    }
}
