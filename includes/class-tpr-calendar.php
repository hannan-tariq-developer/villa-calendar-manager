<?php
/**
 * TPR Calendar Renderer
 */

if (!defined('ABSPATH')) {
    exit;
}

class TPR_Calendar {
    
    /**
     * Render visitor calendar (view only)
     */
    public static function render_visitor_calendar($villa_id = 1, $atts = array()) {
        $settings = TPR_Settings::get_settings();
        $colors = TPR_Settings::get_colors();
        $minimum_nights = TPR_Settings::get_minimum_nights();
        
        $current_month = isset($atts['month']) ? intval($atts['month']) : date('n');
        $current_year = isset($atts['year']) ? intval($atts['year']) : date('Y');
        
        ob_start();
        ?>
        <div class="tpr-calendar-wrapper tpr-visitor-mode" data-villa-id="<?php echo esc_attr($villa_id); ?>" data-mode="visitor">
            <div class="tpr-calendar-header">
                <button class="tpr-nav-btn tpr-prev-month" data-action="prev">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"/>
                    </svg>
                </button>
                <h2 class="tpr-calendar-title">
                    <span class="tpr-month-name"></span>
                    <span class="tpr-year-name"></span>
                </h2>
                <button class="tpr-nav-btn tpr-next-month" data-action="next">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                    </svg>
                </button>
            </div>
            
            <div class="tpr-calendar-legend">
                <div class="tpr-legend-item">
                    <span class="tpr-legend-color" style="background-color: <?php echo esc_attr($colors['available']); ?>"></span>
                    <span><?php _e('Available', 'tpr-villa-calendar'); ?></span>
                </div>
                <div class="tpr-legend-item">
                    <span class="tpr-legend-color" style="background-color: <?php echo esc_attr($colors['booked']); ?>"></span>
                    <span><?php _e('Booked', 'tpr-villa-calendar'); ?></span>
                </div>
            </div>
            
            <div class="tpr-calendar-grid">
                <div class="tpr-weekday-header">
                    <div class="tpr-weekday"><?php _e('Sun', 'tpr-villa-calendar'); ?></div>
                    <div class="tpr-weekday"><?php _e('Mon', 'tpr-villa-calendar'); ?></div>
                    <div class="tpr-weekday"><?php _e('Tue', 'tpr-villa-calendar'); ?></div>
                    <div class="tpr-weekday"><?php _e('Wed', 'tpr-villa-calendar'); ?></div>
                    <div class="tpr-weekday"><?php _e('Thu', 'tpr-villa-calendar'); ?></div>
                    <div class="tpr-weekday"><?php _e('Fri', 'tpr-villa-calendar'); ?></div>
                    <div class="tpr-weekday"><?php _e('Sat', 'tpr-villa-calendar'); ?></div>
                </div>
                <div class="tpr-dates-container"></div>
            </div>
            
            <div class="tpr-loading-overlay" style="display: none;">
                <div class="tpr-spinner"></div>
            </div>
        </div>
        
        <style>
            :root {
                --tpr-color-available: <?php echo esc_attr($colors['available']); ?>;
                --tpr-color-booked: <?php echo esc_attr($colors['booked']); ?>;
                --tpr-color-selected: <?php echo esc_attr($colors['selected']); ?>;
                --tpr-color-disabled: <?php echo esc_attr($colors['disabled']); ?>;
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof TPRCalendar !== 'undefined') {
                    new TPRCalendar({
                        container: '.tpr-calendar-wrapper[data-villa-id="<?php echo esc_js($villa_id); ?>"]',
                        villaId: <?php echo intval($villa_id); ?>,
                        mode: 'visitor',
                        minimumNights: <?php echo intval($minimum_nights); ?>,
                        currentMonth: <?php echo intval($current_month); ?>,
                        currentYear: <?php echo intval($current_year); ?>
                    });
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render manager calendar (editable)
     */
    public static function render_manager_calendar($villa_id = 1, $atts = array()) {
        $settings = TPR_Settings::get_settings();
        $colors = TPR_Settings::get_colors();
        $minimum_nights = TPR_Settings::get_minimum_nights();
        $access_code_enabled = $settings['access_code_enabled'];
        
        ob_start();
        ?>
        <div class="tpr-manager-wrapper" data-villa-id="<?php echo esc_attr($villa_id); ?>">
            
            <?php if ($access_code_enabled): ?>
            <div class="tpr-access-gate" id="tprAccessGate">
                <div class="tpr-access-card">
                    <h2><?php _e('Manager Access', 'tpr-villa-calendar'); ?></h2>
                    <p><?php _e('Enter access code to manage reservations', 'tpr-villa-calendar'); ?></p>
                    <div class="tpr-access-form">
                        <input type="text" 
                               id="tprAccessCode" 
                               class="tpr-access-input" 
                               placeholder="<?php esc_attr_e('Enter access code', 'tpr-villa-calendar'); ?>"
                               autocomplete="off">
                        <button type="button" id="tprAccessSubmit" class="tpr-access-submit">
                            <?php _e('Access Calendar', 'tpr-villa-calendar'); ?>
                        </button>
                    </div>
                    <div class="tpr-access-error" style="display: none;"></div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="tpr-manager-content" <?php echo $access_code_enabled ? 'style="display: none;"' : ''; ?>>
                
                <?php if ($access_code_enabled): ?>
                <!-- Logout Section - Top Right -->
                <div class="tpr-logout-section">
                    <button type="button" id="tprLogout" class="tpr-logout-btn">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 11-2 0V4H5v12h10v-2a1 1 0 112 0v3a1 1 0 01-1 1H4a1 1 0 01-1-1V3z"/>
                            <path d="M11 10a1 1 0 011-1h5.586l-1.293-1.293a1 1 0 011.414-1.414l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L17.586 11H12a1 1 0 01-1-1z"/>
                        </svg>
                        <span><?php _e('Logout', 'tpr-villa-calendar'); ?></span>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="tpr-manager-layout">
                    
                    <!-- Calendar Section -->
                    <div class="tpr-manager-main">
                        <div class="tpr-calendar-wrapper tpr-manager-mode" data-villa-id="<?php echo esc_attr($villa_id); ?>" data-mode="manager">
                            <div class="tpr-calendar-header">
                                <button class="tpr-nav-btn tpr-prev-month" data-action="prev">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"/>
                                    </svg>
                                </button>
                                <h2 class="tpr-calendar-title">
                                    <span class="tpr-month-name"></span>
                                    <span class="tpr-year-name"></span>
                                </h2>
                                <button class="tpr-nav-btn tpr-next-month" data-action="next">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="tpr-selection-info">
                                <div class="tpr-selection-text">
                                    <span id="tprSelectionText"><?php _e('Click a date to start booking', 'tpr-villa-calendar'); ?></span>
                                </div>
                                <div class="tpr-selection-actions">
                                    <button type="button" id="tprBookDates" class="tpr-btn tpr-btn-primary" style="display: none;">
                                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="margin-right: 5px;">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                        </svg>
                                        <?php _e('Book Selected Date(s)', 'tpr-villa-calendar'); ?>
                                    </button>
                                    <button type="button" id="tprClearSelection" class="tpr-btn tpr-btn-secondary" style="display: none;">
                                        <?php _e('Clear', 'tpr-villa-calendar'); ?>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="tpr-calendar-legend">
                                <div class="tpr-legend-item">
                                    <span class="tpr-legend-color" style="background-color: <?php echo esc_attr($colors['available']); ?>"></span>
                                    <span><?php _e('Available', 'tpr-villa-calendar'); ?></span>
                                </div>
                                <div class="tpr-legend-item">
                                    <span class="tpr-legend-color" style="background-color: <?php echo esc_attr($colors['booked']); ?>"></span>
                                    <span><?php _e('Booked', 'tpr-villa-calendar'); ?></span>
                                </div>
                                <div class="tpr-legend-item">
                                    <span class="tpr-legend-color" style="background-color: <?php echo esc_attr($colors['selected']); ?>"></span>
                                    <span><?php _e('Selected', 'tpr-villa-calendar'); ?></span>
                                </div>
                            </div>
                            
                            <div class="tpr-calendar-grid">
                                <div class="tpr-weekday-header">
                                    <div class="tpr-weekday"><?php _e('Sun', 'tpr-villa-calendar'); ?></div>
                                    <div class="tpr-weekday"><?php _e('Mon', 'tpr-villa-calendar'); ?></div>
                                    <div class="tpr-weekday"><?php _e('Tue', 'tpr-villa-calendar'); ?></div>
                                    <div class="tpr-weekday"><?php _e('Wed', 'tpr-villa-calendar'); ?></div>
                                    <div class="tpr-weekday"><?php _e('Thu', 'tpr-villa-calendar'); ?></div>
                                    <div class="tpr-weekday"><?php _e('Fri', 'tpr-villa-calendar'); ?></div>
                                    <div class="tpr-weekday"><?php _e('Sat', 'tpr-villa-calendar'); ?></div>
                                </div>
                                <div class="tpr-dates-container"></div>
                            </div>
                            
                            <div class="tpr-loading-overlay" style="display: none;">
                                <div class="tpr-spinner"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reservations Panel -->
                    <div class="tpr-reservations-panel">
                        <div class="tpr-panel-header">
                            <h3><?php _e('Reservations', 'tpr-villa-calendar'); ?></h3>
                            <button type="button" class="tpr-toggle-panel" title="<?php esc_attr_e('Toggle Panel', 'tpr-villa-calendar'); ?>">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="tpr-panel-filters">
                            <select id="tprReservationFilter" class="tpr-filter-select">
                                <option value="all"><?php _e('All Reservations', 'tpr-villa-calendar'); ?></option>
                                <option value="upcoming" selected><?php _e('Upcoming', 'tpr-villa-calendar'); ?></option>
                                <option value="past"><?php _e('Past', 'tpr-villa-calendar'); ?></option>
                            </select>
                        </div>
                        
                        <div class="tpr-reservations-list" id="tprReservationsList">
                            <div class="tpr-loading-text"><?php _e('Loading...', 'tpr-villa-calendar'); ?></div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <style>
            :root {
                --tpr-color-available: <?php echo esc_attr($colors['available']); ?>;
                --tpr-color-booked: <?php echo esc_attr($colors['booked']); ?>;
                --tpr-color-selected: <?php echo esc_attr($colors['selected']); ?>;
                --tpr-color-disabled: <?php echo esc_attr($colors['disabled']); ?>;
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Check if user is already authenticated
                const villaId = <?php echo intval($villa_id); ?>;
                const sessionKey = 'tpr_manager_authenticated_' + villaId;
                
                try {
                    const isAuthenticated = sessionStorage.getItem(sessionKey) === 'true';
                    
                    console.log('TPR Villa Calendar: Checking authentication');
                    console.log('Villa ID:', villaId);
                    console.log('Session Key:', sessionKey);
                    console.log('Is Authenticated:', isAuthenticated);
                    console.log('Session Value:', sessionStorage.getItem(sessionKey));
                    
                    <?php if ($access_code_enabled): ?>
                    if (isAuthenticated) {
                        console.log('TPR: User is authenticated, showing manager content');
                        // User is authenticated, show manager content directly
                        const accessGate = document.getElementById('tprAccessGate');
                        const managerContent = document.querySelector('.tpr-manager-content');
                        
                        if (accessGate) accessGate.style.display = 'none';
                        if (managerContent) managerContent.style.display = 'block';
                    } else {
                        console.log('TPR: User not authenticated, showing access gate');
                    }
                    <?php else: ?>
                    console.log('TPR: Access code disabled, showing manager content');
                    <?php endif; ?>
                } catch (error) {
                    console.error('TPR: Session check error:', error);
                }
                
                // Initialize calendar
                if (typeof TPRManagerCalendar !== 'undefined') {
                    console.log('TPR: Initializing TPRManagerCalendar');
                    new TPRManagerCalendar({
                        container: '.tpr-manager-wrapper[data-villa-id="<?php echo esc_js($villa_id); ?>"]',
                        villaId: villaId,
                        minimumNights: <?php echo intval($minimum_nights); ?>,
                        accessCodeEnabled: <?php echo $access_code_enabled ? 'true' : 'false'; ?>
                    });
                } else {
                    console.error('TPR: TPRManagerCalendar class not found!');
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
