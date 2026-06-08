<?php
/**
 * TPR Database Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class TPR_Database {
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Reservations table
        $table_reservations = $wpdb->prefix . 'tpr_reservations';
        $sql_reservations = "CREATE TABLE IF NOT EXISTS $table_reservations (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            villa_id bigint(20) NOT NULL DEFAULT 1,
            start_date date NOT NULL,
            end_date date NOT NULL,
            total_nights int(11) NOT NULL,
            status varchar(20) DEFAULT 'booked',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY villa_id (villa_id),
            KEY start_date (start_date),
            KEY end_date (end_date),
            KEY status (status)
        ) $charset_collate;";
        
        // Booked dates table (for quick lookups)
        $table_dates = $wpdb->prefix . 'tpr_booked_dates';
        $sql_dates = "CREATE TABLE IF NOT EXISTS $table_dates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            villa_id bigint(20) NOT NULL DEFAULT 1,
            reservation_id bigint(20) NOT NULL,
            booked_date date NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_date (villa_id, booked_date),
            KEY reservation_id (reservation_id),
            KEY booked_date (booked_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_reservations);
        dbDelta($sql_dates);
    }
    
    /**
     * Get all reservations for a villa
     */
    public static function get_reservations($villa_id = 1, $filters = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'tpr_reservations';
        
        $where = array("villa_id = %d", "status = 'booked'");
        $values = array($villa_id);
        
        // Apply filters
        if (!empty($filters['start_date'])) {
            $where[] = "start_date >= %s";
            $values[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "end_date <= %s";
            $values[] = $filters['end_date'];
        }
        
        if (isset($filters['upcoming']) && $filters['upcoming']) {
            $where[] = "start_date >= CURDATE()";
        }
        
        if (isset($filters['past']) && $filters['past']) {
            $where[] = "end_date < CURDATE()";
        }
        
        $where_clause = implode(' AND ', $where);
        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY start_date ASC";
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }
    
    /**
     * Get booked dates for a villa in a specific month/year
     */
    public static function get_booked_dates($villa_id = 1, $year = null, $month = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'tpr_booked_dates';
        
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');
        
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $query = $wpdb->prepare(
            "SELECT booked_date FROM $table 
            WHERE villa_id = %d 
            AND booked_date BETWEEN %s AND %s
            ORDER BY booked_date ASC",
            $villa_id,
            $start_date,
            $end_date
        );
        
        $results = $wpdb->get_col($query);
        return $results;
    }
    
    /**
     * Create a new reservation
     */
    public static function create_reservation($villa_id, $start_date, $end_date) {
        global $wpdb;
        
        // Calculate total nights
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        $total_nights = $interval->days;
        
        // Check if dates are already booked
        if (self::are_dates_booked($villa_id, $start_date, $end_date)) {
            return new WP_Error('dates_booked', __('Some dates are already booked', 'tpr-villa-calendar'));
        }
        
        // Insert reservation
        $table_reservations = $wpdb->prefix . 'tpr_reservations';
        $inserted = $wpdb->insert(
            $table_reservations,
            array(
                'villa_id' => $villa_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_nights' => $total_nights,
                'status' => 'booked'
            ),
            array('%d', '%s', '%s', '%d', '%s')
        );
        
        if (!$inserted) {
            return new WP_Error('db_error', __('Failed to create reservation', 'tpr-villa-calendar'));
        }
        
        $reservation_id = $wpdb->insert_id;
        
        // Insert individual dates
        self::insert_booked_dates($reservation_id, $villa_id, $start_date, $end_date);
        
        return $reservation_id;
    }
    
    /**
     * Delete a reservation
     */
    public static function delete_reservation($reservation_id) {
        global $wpdb;
        
        $table_reservations = $wpdb->prefix . 'tpr_reservations';
        $table_dates = $wpdb->prefix . 'tpr_booked_dates';
        
        // Delete booked dates
        $wpdb->delete($table_dates, array('reservation_id' => $reservation_id), array('%d'));
        
        // Delete reservation
        $deleted = $wpdb->delete($table_reservations, array('id' => $reservation_id), array('%d'));
        
        return $deleted !== false;
    }
    
    /**
     * Insert booked dates for a reservation
     */
    private static function insert_booked_dates($reservation_id, $villa_id, $start_date, $end_date) {
        global $wpdb;
        $table = $wpdb->prefix . 'tpr_booked_dates';
        
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        $values = array();
        // Include end date - guest checks out on this day, so it's unavailable
        while ($start <= $end) {
            $date = $start->format('Y-m-d');
            $values[] = $wpdb->prepare("(%d, %d, %s)", $reservation_id, $villa_id, $date);
            $start->modify('+1 day');
        }
        
        if (!empty($values)) {
            $query = "INSERT IGNORE INTO $table (reservation_id, villa_id, booked_date) VALUES " . implode(', ', $values);
            $wpdb->query($query);
        }
    }
    
    /**
     * Check if dates are already booked
     */
    public static function are_dates_booked($villa_id, $start_date, $end_date) {
        global $wpdb;
        $table = $wpdb->prefix . 'tpr_booked_dates';
        
        // Check if any date from start to end (inclusive) is booked
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
            WHERE villa_id = %d 
            AND booked_date >= %s 
            AND booked_date <= %s",
            $villa_id,
            $start_date,
            $end_date
        );
        
        $count = $wpdb->get_var($query);
        return $count > 0;
    }
    
    /**
     * Get reservation by ID
     */
    public static function get_reservation($reservation_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'tpr_reservations';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $reservation_id
        ));
    }
}
