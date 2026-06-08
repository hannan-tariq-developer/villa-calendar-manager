<?php
/**
 * TPR AJAX Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class TPR_AJAX {
    
    /**
     * Initialize AJAX hooks
     */
    public static function init() {
        // Public actions (no login required)
        add_action('wp_ajax_nopriv_tpr_get_booked_dates', array(__CLASS__, 'get_booked_dates'));
        add_action('wp_ajax_tpr_get_booked_dates', array(__CLASS__, 'get_booked_dates'));
        
        add_action('wp_ajax_nopriv_tpr_verify_access', array(__CLASS__, 'verify_access'));
        add_action('wp_ajax_tpr_verify_access', array(__CLASS__, 'verify_access'));
        
        add_action('wp_ajax_nopriv_tpr_create_reservation', array(__CLASS__, 'create_reservation'));
        add_action('wp_ajax_tpr_create_reservation', array(__CLASS__, 'create_reservation'));
        
        add_action('wp_ajax_nopriv_tpr_delete_reservation', array(__CLASS__, 'delete_reservation'));
        add_action('wp_ajax_tpr_delete_reservation', array(__CLASS__, 'delete_reservation'));
        
        add_action('wp_ajax_nopriv_tpr_get_reservations', array(__CLASS__, 'get_reservations'));
        add_action('wp_ajax_tpr_get_reservations', array(__CLASS__, 'get_reservations'));
        
        // Admin actions
        add_action('wp_ajax_tpr_save_settings', array(__CLASS__, 'save_settings'));
    }
    
    /**
     * Verify nonce
     */
    private static function verify_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tpr_villa_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'tpr-villa-calendar')));
            exit;
        }
    }
    
    /**
     * Get booked dates for a month
     */
    public static function get_booked_dates() {
        self::verify_nonce();
        
        $villa_id = isset($_POST['villa_id']) ? intval($_POST['villa_id']) : 1;
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        $month = isset($_POST['month']) ? intval($_POST['month']) : date('m');
        
        $booked_dates = TPR_Database::get_booked_dates($villa_id, $year, $month);
        
        wp_send_json_success(array(
            'booked_dates' => $booked_dates,
            'year' => $year,
            'month' => $month
        ));
    }
    
    /**
     * Verify access code
     */
    public static function verify_access() {
        self::verify_nonce();
        
        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        
        if (TPR_Settings::verify_access_code($code)) {
            wp_send_json_success(array('message' => __('Access granted', 'tpr-villa-calendar')));
        } else {
            wp_send_json_error(array('message' => __('Invalid access code', 'tpr-villa-calendar')));
        }
    }
    
    /**
     * Create a new reservation
     */
    public static function create_reservation() {
        self::verify_nonce();
        
        $villa_id = isset($_POST['villa_id']) ? intval($_POST['villa_id']) : 1;
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        
        // Validate dates
        if (empty($start_date) || empty($end_date)) {
            wp_send_json_error(array('message' => __('Start date and end date are required', 'tpr-villa-calendar')));
        }
        
        // Validate date format
        if (!self::validate_date($start_date) || !self::validate_date($end_date)) {
            wp_send_json_error(array('message' => __('Invalid date format', 'tpr-villa-calendar')));
        }
        
        // Check if start date is in the past
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $start = new DateTime($start_date);
        
        if ($start < $today) {
            wp_send_json_error(array('message' => __('Cannot book dates in the past', 'tpr-villa-calendar')));
        }
        
        // Create reservation
        $result = TPR_Database::create_reservation($villa_id, $start_date, $end_date);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array(
            'message' => __('Reservation created successfully', 'tpr-villa-calendar'),
            'reservation_id' => $result
        ));
    }
    
    /**
     * Delete a reservation
     */
    public static function delete_reservation() {
        self::verify_nonce();
        
        $reservation_id = isset($_POST['reservation_id']) ? intval($_POST['reservation_id']) : 0;
        
        if (!$reservation_id) {
            wp_send_json_error(array('message' => __('Invalid reservation ID', 'tpr-villa-calendar')));
        }
        
        $result = TPR_Database::delete_reservation($reservation_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Reservation deleted successfully', 'tpr-villa-calendar')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete reservation', 'tpr-villa-calendar')));
        }
    }
    
    /**
     * Get reservations list
     */
    public static function get_reservations() {
        self::verify_nonce();
        
        $villa_id = isset($_POST['villa_id']) ? intval($_POST['villa_id']) : 1;
        $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : 'all';
        
        $filters = array();
        
        if ($filter === 'upcoming') {
            $filters['upcoming'] = true;
        } elseif ($filter === 'past') {
            $filters['past'] = true;
        }
        
        $reservations = TPR_Database::get_reservations($villa_id, $filters);
        
        // Format reservations for response
        $formatted = array();
        foreach ($reservations as $reservation) {
            $formatted[] = array(
                'id' => $reservation->id,
                'start_date' => $reservation->start_date,
                'end_date' => $reservation->end_date,
                'total_nights' => $reservation->total_nights,
                'status' => $reservation->status,
                'created_at' => $reservation->created_at
            );
        }
        
        wp_send_json_success(array('reservations' => $formatted));
    }
    
    /**
     * Save settings (admin only)
     */
    public static function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tpr-villa-calendar')));
        }
        
        check_ajax_referer('tpr_admin_nonce', 'nonce');
        
        $settings = array();
        
        // Access code settings
        if (isset($_POST['access_code_enabled'])) {
            $settings['access_code_enabled'] = (bool) $_POST['access_code_enabled'];
        }
        
        if (isset($_POST['access_code'])) {
            $settings['access_code'] = sanitize_text_field($_POST['access_code']);
        }
        
        // Minimum nights
        if (isset($_POST['minimum_nights'])) {
            $settings['minimum_nights'] = absint($_POST['minimum_nights']);
            if ($settings['minimum_nights'] < 1) {
                $settings['minimum_nights'] = 1;
            }
        }
        
        // Color settings
        if (isset($_POST['color_available'])) {
            $settings['color_available'] = sanitize_hex_color($_POST['color_available']);
        }
        
        if (isset($_POST['color_booked'])) {
            $settings['color_booked'] = sanitize_hex_color($_POST['color_booked']);
        }
        
        if (isset($_POST['color_selected'])) {
            $settings['color_selected'] = sanitize_hex_color($_POST['color_selected']);
        }
        
        if (isset($_POST['color_disabled'])) {
            $settings['color_disabled'] = sanitize_hex_color($_POST['color_disabled']);
        }
        
        TPR_Settings::update_multiple($settings);
        
        wp_send_json_success(array('message' => __('Settings saved successfully', 'tpr-villa-calendar')));
    }
    
    /**
     * Validate date format (Y-m-d)
     */
    private static function validate_date($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
