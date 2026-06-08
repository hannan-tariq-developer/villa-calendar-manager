/**
 * TPR Villa Calendar Manager - Admin Scripts
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Toggle access code field visibility
        $('#access_code_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('#tprAccessCodeRow').slideDown(300);
            } else {
                $('#tprAccessCodeRow').slideUp(300);
            }
        });
        
        // Generate random access code
        $('#tprGenerateCode').on('click', function() {
            const code = generateRandomCode(8);
            $('#access_code').val(code);
        });
        
        // Color picker live preview
        $('.tpr-color-picker').on('input', function() {
            const color = $(this).val();
            $(this).siblings('.tpr-color-preview').css('background-color', color);
            $(this).siblings('code').text(color);
        });
        
        // Save settings form
        $('#tprSettingsForm').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const $status = $('.tpr-save-status');
            
            // Disable submit button
            $submitBtn.prop('disabled', true).text('Saving...');
            $status.removeClass('success error').text('');
            
            // Prepare data
            const formData = {
                action: 'tpr_save_settings',
                nonce: $form.find('#nonce').val(),
                access_code_enabled: $('#access_code_enabled').is(':checked') ? 1 : 0,
                access_code: $('#access_code').val(),
                minimum_nights: $('#minimum_nights').val(),
                color_available: $('#color_available').val(),
                color_booked: $('#color_booked').val(),
                color_selected: $('#color_selected').val(),
                color_disabled: $('#color_disabled').val()
            };
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $status.addClass('success').text('✓ ' + response.data.message);
                        setTimeout(function() {
                            $status.fadeOut(300, function() {
                                $(this).text('').show();
                            });
                        }, 3000);
                    } else {
                        $status.addClass('error').text('✗ ' + response.data.message);
                    }
                },
                error: function() {
                    $status.addClass('error').text('✗ An error occurred');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text('Save Settings');
                }
            });
        });
        
        /**
         * Generate random access code
         */
        function generateRandomCode(length) {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let code = '';
            for (let i = 0; i < length; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return code;
        }
        
    });
    
})(jQuery);
