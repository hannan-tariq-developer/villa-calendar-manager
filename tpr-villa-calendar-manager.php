<?php
/**
 * Plugin Name: Villa Calendar Manager
 * Plugin URI: https://example.com/tpr-villa-calendar-manager
 * Description: Professional frontend-based villa availability & reservation management system with AJAX controls
 * Version: 1.0.0
 * Author: TPR Development
 * Author URI: https://example.com
 * Text Domain: tpr-villa-calendar
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TPR_VILLA_VERSION', '1.0.0');
define('TPR_VILLA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TPR_VILLA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TPR_VILLA_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once TPR_VILLA_PLUGIN_DIR . 'includes/class-tpr-database.php';
require_once TPR_VILLA_PLUGIN_DIR . 'includes/class-tpr-settings.php';
require_once TPR_VILLA_PLUGIN_DIR . 'includes/class-tpr-calendar.php';
require_once TPR_VILLA_PLUGIN_DIR . 'includes/class-tpr-ajax.php';
require_once TPR_VILLA_PLUGIN_DIR . 'includes/class-tpr-shortcodes.php';

/**
 * Main TPR Villa Calendar Manager Class
 */
class TPR_Villa_Calendar_Manager {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        TPR_Database::create_tables();
        TPR_Settings::set_default_settings();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('tpr-villa-calendar', false, dirname(TPR_VILLA_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        TPR_AJAX::init();
        TPR_Shortcodes::init();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Villa Calendar', 'tpr-villa-calendar'),
            __('Villa Calendar', 'tpr-villa-calendar'),
            'manage_options',
            'tpr-villa-calendar',
            array($this, 'render_admin_page'),
            'dashicons-calendar-alt',
            30
        );
    }
    
    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        include TPR_VILLA_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_tpr-villa-calendar' !== $hook) {
            return;
        }
        
        wp_enqueue_style('tpr-admin-css', TPR_VILLA_PLUGIN_URL . 'assets/css/admin.css', array(), TPR_VILLA_VERSION);
        wp_enqueue_script('tpr-admin-js', TPR_VILLA_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), TPR_VILLA_VERSION, true);
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('tpr-frontend-css', TPR_VILLA_PLUGIN_URL . 'assets/css/frontend.css?version=0.9', array(), TPR_VILLA_VERSION);
        wp_enqueue_script('tpr-frontend-js', TPR_VILLA_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), TPR_VILLA_VERSION, true);
        
        // Localize script
        wp_localize_script('tpr-frontend-js', 'tprVilla', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tpr_villa_nonce'),
            'strings' => array(
                'minimumStay' => __('Minimum stay:', 'tpr-villa-calendar'),
                'nights' => __('nights', 'tpr-villa-calendar'),
                'selectStartDate' => __('Select start date', 'tpr-villa-calendar'),
                'selectEndDate' => __('Select end date', 'tpr-villa-calendar'),
                'invalidCode' => __('Invalid access code', 'tpr-villa-calendar'),
                'errorOccurred' => __('An error occurred', 'tpr-villa-calendar'),
                'confirmDelete' => __('Are you sure you want to delete this reservation?', 'tpr-villa-calendar'),
            )
        ));
    }
}

// Initialize the plugin
function tpr_villa_calendar_manager() {
    return TPR_Villa_Calendar_Manager::get_instance();
}

// Start the plugin
tpr_villa_calendar_manager();
