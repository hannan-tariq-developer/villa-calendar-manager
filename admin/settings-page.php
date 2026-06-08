<?php
/**
 * Admin Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = TPR_Settings::get_settings();
$colors = TPR_Settings::get_colors();
?>

<div class="wrap tpr-admin-wrap">
    <h1><?php _e('Villa Calendar Manager - Settings', 'tpr-villa-calendar'); ?></h1>
    
    <div class="tpr-admin-container">
        
        <form id="tprSettingsForm" class="tpr-settings-form">
            <?php wp_nonce_field('tpr_admin_nonce', 'nonce'); ?>
            
            <!-- Access Control Section -->
            <div class="tpr-settings-section">
                <h2><?php _e('Access Control', 'tpr-villa-calendar'); ?></h2>
                <p class="description"><?php _e('Configure frontend manager access settings', 'tpr-villa-calendar'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="access_code_enabled"><?php _e('Enable Access Code', 'tpr-villa-calendar'); ?></label>
                        </th>
                        <td>
                            <label class="tpr-switch">
                                <input type="checkbox" 
                                       name="access_code_enabled" 
                                       id="access_code_enabled" 
                                       value="1" 
                                       <?php checked($settings['access_code_enabled'], true); ?>>
                                <span class="tpr-slider"></span>
                            </label>
                            <p class="description">
                                <?php _e('Require an access code to use the manager calendar', 'tpr-villa-calendar'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr id="tprAccessCodeRow" <?php echo !$settings['access_code_enabled'] ? 'style="display:none;"' : ''; ?>>
                        <th scope="row">
                            <label for="access_code"><?php _e('Access Code', 'tpr-villa-calendar'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="access_code" 
                                   id="access_code" 
                                   value="<?php echo esc_attr($settings['access_code']); ?>" 
                                   class="regular-text">
                            <button type="button" id="tprGenerateCode" class="button">
                                <?php _e('Generate New Code', 'tpr-villa-calendar'); ?>
                            </button>
                            <p class="description">
                                <?php _e('Managers will need this code to access the calendar management page', 'tpr-villa-calendar'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Color Settings Section -->
            <div class="tpr-settings-section">
                <h2><?php _e('Calendar Colors', 'tpr-villa-calendar'); ?></h2>
                <p class="description"><?php _e('Customize the calendar appearance', 'tpr-villa-calendar'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="color_available"><?php _e('Available Dates', 'tpr-villa-calendar'); ?></label>
                        </th>
                        <td>
                            <input type="color" 
                                   name="color_available" 
                                   id="color_available" 
                                   value="<?php echo esc_attr($colors['available']); ?>" 
                                   class="tpr-color-picker">
                            <span class="tpr-color-preview" style="background-color: <?php echo esc_attr($colors['available']); ?>"></span>
                            <code><?php echo esc_html($colors['available']); ?></code>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="color_booked"><?php _e('Booked Dates', 'tpr-villa-calendar'); ?></label>
                        </th>
                        <td>
                            <input type="color" 
                                   name="color_booked" 
                                   id="color_booked" 
                                   value="<?php echo esc_attr($colors['booked']); ?>" 
                                   class="tpr-color-picker">
                            <span class="tpr-color-preview" style="background-color: <?php echo esc_attr($colors['booked']); ?>"></span>
                            <code><?php echo esc_html($colors['booked']); ?></code>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="color_selected"><?php _e('Selected Dates', 'tpr-villa-calendar'); ?></label>
                        </th>
                        <td>
                            <input type="color" 
                                   name="color_selected" 
                                   id="color_selected" 
                                   value="<?php echo esc_attr($colors['selected']); ?>" 
                                   class="tpr-color-picker">
                            <span class="tpr-color-preview" style="background-color: <?php echo esc_attr($colors['selected']); ?>"></span>
                            <code><?php echo esc_html($colors['selected']); ?></code>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="color_disabled"><?php _e('Disabled Dates', 'tpr-villa-calendar'); ?></label>
                        </th>
                        <td>
                            <input type="color" 
                                   name="color_disabled" 
                                   id="color_disabled" 
                                   value="<?php echo esc_attr($colors['disabled']); ?>" 
                                   class="tpr-color-picker">
                            <span class="tpr-color-preview" style="background-color: <?php echo esc_attr($colors['disabled']); ?>"></span>
                            <code><?php echo esc_html($colors['disabled']); ?></code>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Shortcode Reference Section -->
            <div class="tpr-settings-section">
                <h2><?php _e('Shortcode Reference', 'tpr-villa-calendar'); ?></h2>
                <p class="description"><?php _e('Use these shortcodes in your pages or Elementor', 'tpr-villa-calendar'); ?></p>
                
                <div class="tpr-shortcode-box">
                    <h4><?php _e('Visitor Calendar (View Only)', 'tpr-villa-calendar'); ?></h4>
                    <code class="tpr-shortcode">[villa_calendar id="1"]</code>
                    <p class="description"><?php _e('Displays the calendar for visitors to view availability', 'tpr-villa-calendar'); ?></p>
                </div>
                
                <div class="tpr-shortcode-box">
                    <h4><?php _e('Manager Calendar (Editable)', 'tpr-villa-calendar'); ?></h4>
                    <code class="tpr-shortcode">[villa_calendar_manager id="1"]</code>
                    <p class="description"><?php _e('Displays the calendar with management controls for the owner', 'tpr-villa-calendar'); ?></p>
                </div>
            </div>
            
            <div class="tpr-submit-section">
                <button type="submit" class="button button-primary button-large">
                    <?php _e('Save Settings', 'tpr-villa-calendar'); ?>
                </button>
                <span class="tpr-save-status"></span>
            </div>
            
        </form>
        
    </div>
</div>
