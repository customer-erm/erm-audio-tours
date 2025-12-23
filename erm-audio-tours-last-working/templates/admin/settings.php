<?php
/**
 * Admin Settings Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get saved options
$options = get_option('erm_at_global_settings', array());
$default_colors = array(
    'primary' => $options['default_primary_color'] ?? '#0066FF',
    'secondary' => $options['default_secondary_color'] ?? '#00D4FF',
);
?>

<div class="wrap erm-at-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="erm-at-settings-header">
        <div class="erm-at-settings-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="40" height="40">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="10,8 16,12 10,16" fill="currentColor" stroke="none"></polygon>
            </svg>
        </div>
        <div class="erm-at-settings-info">
            <h2><?php _e('Audio Tour Builder', 'erm-audio-tours'); ?></h2>
            <p><?php _e('Global settings for all audio tours', 'erm-audio-tours'); ?></p>
        </div>
    </div>
    
    <form method="post" action="options.php">
        <?php settings_fields('erm_at_global_settings'); ?>
        
        <div class="erm-at-settings-section">
            <h3><?php _e('Default Colors', 'erm-audio-tours'); ?></h3>
            <p class="description"><?php _e('These colors will be used as defaults for new tours.', 'erm-audio-tours'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="erm_at_default_primary"><?php _e('Primary Color', 'erm-audio-tours'); ?></label>
                    </th>
                    <td>
                        <input type="color" id="erm_at_default_primary" 
                               name="erm_at_global_settings[default_primary_color]" 
                               value="<?php echo esc_attr($default_colors['primary']); ?>">
                        <code><?php echo esc_html($default_colors['primary']); ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="erm_at_default_secondary"><?php _e('Secondary Color', 'erm-audio-tours'); ?></label>
                    </th>
                    <td>
                        <input type="color" id="erm_at_default_secondary" 
                               name="erm_at_global_settings[default_secondary_color]" 
                               value="<?php echo esc_attr($default_colors['secondary']); ?>">
                        <code><?php echo esc_html($default_colors['secondary']); ?></code>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="erm-at-settings-section">
            <h3><?php _e('Import Tour', 'erm-audio-tours'); ?></h3>
            <p class="description"><?php _e('Import a tour from a JSON export file.', 'erm-audio-tours'); ?></p>
            
            <div class="erm-at-import-area">
                <textarea id="erm-at-import-json" placeholder="<?php _e('Paste exported tour JSON here...', 'erm-audio-tours'); ?>" rows="6"></textarea>
                <button type="button" class="button button-secondary" id="erm-at-import-btn">
                    <span class="dashicons dashicons-upload"></span>
                    <?php _e('Import Tour', 'erm-audio-tours'); ?>
                </button>
            </div>
        </div>
        
        <?php submit_button(); ?>
    </form>
    
    <div class="erm-at-settings-section erm-at-settings-info-box">
        <h3><?php _e('Quick Start Guide', 'erm-audio-tours'); ?></h3>
        <ol>
            <li><?php _e('Go to <strong>Audio Tours → Add New</strong> to create a tour', 'erm-audio-tours'); ?></li>
            <li><?php _e('Set the target page URL where the tour should appear', 'erm-audio-tours'); ?></li>
            <li><?php _e('Add steps with names and icons', 'erm-audio-tours'); ?></li>
            <li><?php _e('Use the <strong>Element Picker</strong> to visually select target elements', 'erm-audio-tours'); ?></li>
            <li><?php _e('Upload your audio file and set timestamps', 'erm-audio-tours'); ?></li>
            <li><?php _e('Enable the tour and preview it on your page', 'erm-audio-tours'); ?></li>
        </ol>
        
        <p>
            <a href="<?php echo admin_url('post-new.php?post_type=audio_tour'); ?>" class="button button-primary">
                <?php _e('Create Your First Tour', 'erm-audio-tours'); ?>
            </a>
            <a href="<?php echo admin_url('edit.php?post_type=audio_tour&page=erm-at-help'); ?>" class="button">
                <?php _e('View Full Documentation', 'erm-audio-tours'); ?>
            </a>
        </p>
    </div>
</div>

<style>
.erm-at-settings-page {
    max-width: 800px;
}

.erm-at-settings-header {
    display: flex;
    align-items: center;
    gap: 20px;
    background: linear-gradient(135deg, #0066FF 0%, #00D4FF 100%);
    padding: 25px 30px;
    border-radius: 12px;
    margin: 20px 0 30px;
    color: white;
}

.erm-at-settings-logo {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.erm-at-settings-logo svg {
    color: white;
}

.erm-at-settings-info h2 {
    margin: 0 0 5px;
    font-size: 24px;
    color: white;
}

.erm-at-settings-info p {
    margin: 0;
    opacity: 0.9;
}

.erm-at-settings-section {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.erm-at-settings-section h3 {
    margin-top: 0;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.erm-at-import-area {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.erm-at-import-area textarea {
    width: 100%;
    font-family: monospace;
}

.erm-at-settings-info-box {
    background: #f0f7ff;
    border-left: 4px solid #0066FF;
}

.erm-at-settings-info-box ol {
    margin: 15px 0;
    padding-left: 25px;
}

.erm-at-settings-info-box li {
    margin-bottom: 8px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Color picker sync
    $('input[type="color"]').on('input', function() {
        $(this).next('code').text($(this).val());
    });
    
    // Import functionality
    $('#erm-at-import-btn').on('click', function() {
        var json = $('#erm-at-import-json').val();
        
        if (!json) {
            alert('Please paste JSON data first');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'erm_at_import_tour',
                nonce: '<?php echo wp_create_nonce('erm_at_admin_nonce'); ?>',
                import_data: json
            },
            success: function(response) {
                if (response.success) {
                    if (confirm('Tour imported successfully! Would you like to edit it now?')) {
                        window.location.href = response.data.edit_url;
                    }
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Import failed. Please check the JSON format.');
            }
        });
    });
});
</script>
