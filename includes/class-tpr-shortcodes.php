<?php
/**
 * TPR Shortcodes Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class TPR_Shortcodes {
    
    /**
     * Initialize shortcodes
     */
    public static function init() {
        add_shortcode('villa_calendar', array(__CLASS__, 'visitor_calendar_shortcode'));
        add_shortcode('villa_calendar_manager', array(__CLASS__, 'manager_calendar_shortcode'));
    }
    
    /**
     * Visitor calendar shortcode
     * Usage: [villa_calendar id="1"]
     */
    public static function visitor_calendar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 1,
            'month' => date('n'),
            'year' => date('Y')
        ), $atts, 'villa_calendar');
        
        return TPR_Calendar::render_visitor_calendar(intval($atts['id']), $atts);
    }
    
    /**
     * Manager calendar shortcode
     * Usage: [villa_calendar_manager id="1"]
     */
    public static function manager_calendar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 1
        ), $atts, 'villa_calendar_manager');
        
        return TPR_Calendar::render_manager_calendar(intval($atts['id']), $atts);
    }
}
