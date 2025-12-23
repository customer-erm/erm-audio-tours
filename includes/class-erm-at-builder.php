<?php
/**
 * Tour Builder Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERM_AT_Builder {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_erm_audio_tour', array($this, 'save_meta_boxes'), 10, 2);
        add_action('admin_menu', array($this, 'add_builder_page'));
    }
    
    public function add_meta_boxes() {
        // Tour Settings
        add_meta_box(
            'erm_at_tour_settings',
            __('Tour Settings', 'erm-audio-tours'),
            array($this, 'render_settings_metabox'),
            'erm_audio_tour',
            'normal',
            'high'
        );
        
        // Tour Steps Builder
        add_meta_box(
            'erm_at_tour_steps',
            __('Tour Steps', 'erm-audio-tours'),
            array($this, 'render_steps_metabox'),
            'erm_audio_tour',
            'normal',
            'high'
        );
        
        // Appearance Settings
        add_meta_box(
            'erm_at_appearance',
            __('Appearance', 'erm-audio-tours'),
            array($this, 'render_appearance_metabox'),
            'erm_audio_tour',
            'side',
            'default'
        );
        
        // Quick Actions
        add_meta_box(
            'erm_at_quick_actions',
            __('Quick Actions', 'erm-audio-tours'),
            array($this, 'render_actions_metabox'),
            'erm_audio_tour',
            'side',
            'default'
        );
    }
    
    public function add_builder_page() {
        add_submenu_page(
            null, // Hidden from menu
            __('Visual Builder', 'erm-audio-tours'),
            __('Visual Builder', 'erm-audio-tours'),
            'edit_posts',
            'erm-at-builder',
            array($this, 'render_visual_builder')
        );
    }
    
    public function render_settings_metabox($post) {
        wp_nonce_field('erm_at_save_tour', 'erm_at_tour_nonce');
        
        $target_page = get_post_meta($post->ID, '_erm_at_target_page', true);
        $enabled = get_post_meta($post->ID, '_erm_at_enabled', true);
        $button_delay = get_post_meta($post->ID, '_erm_at_button_delay', true) ?: 2000;
        $audio_mode = get_post_meta($post->ID, '_erm_at_audio_mode', true) ?: 'single';
        $master_audio = get_post_meta($post->ID, '_erm_at_master_audio', true);
        $button_text = get_post_meta($post->ID, '_erm_at_button_text', true) ?: 'Start Guided Tour';
        $modal_title = get_post_meta($post->ID, '_erm_at_modal_title', true) ?: 'Guided Tour';
        $modal_subtitle = get_post_meta($post->ID, '_erm_at_modal_subtitle', true);
        $modal_description = get_post_meta($post->ID, '_erm_at_modal_description', true);
        
        ?>
        <div class="erm-at-settings-grid">
            <div class="erm-at-setting-row">
                <label for="erm_at_target_page"><?php _e('Target Page URL', 'erm-audio-tours'); ?></label>
                <div class="erm-at-input-group">
                    <input type="url" id="erm_at_target_page" name="erm_at_target_page" 
                           value="<?php echo esc_url($target_page); ?>" 
                           placeholder="<?php echo esc_url(home_url('/')); ?>"
                           class="regular-text">
                    <button type="button" class="button" id="erm_at_use_homepage">
                        <?php _e('Use Homepage', 'erm-audio-tours'); ?>
                    </button>
                </div>
                <p class="description"><?php _e('The page where this tour will appear.', 'erm-audio-tours'); ?></p>
            </div>
            
            <div class="erm-at-setting-row">
                <label>
                    <input type="checkbox" name="erm_at_enabled" value="1" <?php checked($enabled, '1'); ?>>
                    <?php _e('Enable this tour', 'erm-audio-tours'); ?>
                </label>
            </div>
            
            <div class="erm-at-setting-row">
                <label for="erm_at_button_delay"><?php _e('Button Appear Delay', 'erm-audio-tours'); ?></label>
                <input type="number" id="erm_at_button_delay" name="erm_at_button_delay" 
                       value="<?php echo esc_attr($button_delay); ?>" 
                       min="0" max="30000" step="500" class="small-text"> ms
            </div>
            
            <hr>
            
            <div class="erm-at-setting-row">
                <label><?php _e('Audio Mode', 'erm-audio-tours'); ?></label>
                <div class="erm-at-radio-group">
                    <label>
                        <input type="radio" name="erm_at_audio_mode" value="single" <?php checked($audio_mode, 'single'); ?>>
                        <?php _e('Single audio file with timestamps', 'erm-audio-tours'); ?>
                    </label>
                    <label>
                        <input type="radio" name="erm_at_audio_mode" value="per_step" <?php checked($audio_mode, 'per_step'); ?>>
                        <?php _e('Separate audio file per step', 'erm-audio-tours'); ?>
                    </label>
                </div>
            </div>
            
            <div class="erm-at-setting-row erm-at-master-audio" <?php echo $audio_mode === 'per_step' ? 'style="display:none;"' : ''; ?>>
                <label for="erm_at_master_audio"><?php _e('Master Audio File', 'erm-audio-tours'); ?></label>
                <div class="erm-at-audio-upload">
                    <input type="hidden" id="erm_at_master_audio" name="erm_at_master_audio" value="<?php echo esc_url($master_audio); ?>">
                    <input type="text" id="erm_at_master_audio_display" value="<?php echo esc_url($master_audio); ?>" class="regular-text" readonly>
                    <button type="button" class="button erm-at-upload-audio" data-target="erm_at_master_audio">
                        <span class="dashicons dashicons-upload"></span> <?php _e('Upload', 'erm-audio-tours'); ?>
                    </button>
                    <button type="button" class="button erm-at-remove-audio" data-target="erm_at_master_audio" <?php echo empty($master_audio) ? 'style="display:none;"' : ''; ?>>
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
                <?php if ($master_audio) : ?>
                <div class="erm-at-audio-preview">
                    <audio controls src="<?php echo esc_url($master_audio); ?>"></audio>
                </div>
                <?php endif; ?>
            </div>
            
            <hr>
            
            <h4><?php _e('Modal Settings', 'erm-audio-tours'); ?></h4>
            
            <div class="erm-at-setting-row">
                <label for="erm_at_button_text"><?php _e('Button Text', 'erm-audio-tours'); ?></label>
                <input type="text" id="erm_at_button_text" name="erm_at_button_text" 
                       value="<?php echo esc_attr($button_text); ?>" class="regular-text">
            </div>
            
            <div class="erm-at-setting-row">
                <label for="erm_at_modal_title"><?php _e('Modal Title', 'erm-audio-tours'); ?></label>
                <input type="text" id="erm_at_modal_title" name="erm_at_modal_title" 
                       value="<?php echo esc_attr($modal_title); ?>" class="regular-text">
            </div>
            
            <div class="erm-at-setting-row">
                <label for="erm_at_modal_subtitle"><?php _e('Modal Subtitle', 'erm-audio-tours'); ?></label>
                <input type="text" id="erm_at_modal_subtitle" name="erm_at_modal_subtitle" 
                       value="<?php echo esc_attr($modal_subtitle); ?>" class="regular-text">
            </div>
            
            <div class="erm-at-setting-row">
                <label for="erm_at_modal_description"><?php _e('Modal Description', 'erm-audio-tours'); ?></label>
                <textarea id="erm_at_modal_description" name="erm_at_modal_description" rows="3" class="large-text"><?php echo esc_textarea($modal_description); ?></textarea>
            </div>
        </div>
        <?php
    }
    
    public function render_steps_metabox($post) {
        $steps = get_post_meta($post->ID, '_erm_at_steps', true);
        $audio_mode = get_post_meta($post->ID, '_erm_at_audio_mode', true) ?: 'single';
        $icons = ERM_Audio_Tours::get_icons();
        
        if (!is_array($steps)) {
            $steps = array();
        }
        ?>
        <div class="erm-at-steps-builder">
            <div class="erm-at-steps-header">
                <div class="erm-at-steps-count">
                    <span id="erm-at-step-count"><?php echo count($steps); ?></span> <?php _e('steps', 'erm-audio-tours'); ?>
                </div>
                <div class="erm-at-steps-actions">
                    <button type="button" class="button" id="erm-at-collapse-all">
                        <span class="dashicons dashicons-arrow-up-alt2"></span> <?php _e('Collapse All', 'erm-audio-tours'); ?>
                    </button>
                    <button type="button" class="button" id="erm-at-expand-all">
                        <span class="dashicons dashicons-arrow-down-alt2"></span> <?php _e('Expand All', 'erm-audio-tours'); ?>
                    </button>
                </div>
            </div>
            
            <div class="erm-at-steps-list" id="erm-at-steps-list">
                <?php 
                if (!empty($steps)) {
                    foreach ($steps as $index => $step) {
                        $this->render_step_item($step, $index, $icons, $audio_mode);
                    }
                }
                ?>
            </div>
            
            <div class="erm-at-steps-footer">
                <button type="button" class="button button-primary button-large" id="erm-at-add-step">
                    <span class="dashicons dashicons-plus-alt2"></span> <?php _e('Add New Step', 'erm-audio-tours'); ?>
                </button>
            </div>
            
            <!-- Step Template (hidden) -->
            <script type="text/template" id="erm-at-step-template">
                <?php $this->render_step_item(array(), '{{INDEX}}', $icons, $audio_mode, true); ?>
            </script>
        </div>
        <?php
    }
    
    private function render_step_item($step, $index, $icons, $audio_mode, $is_template = false) {
        $step = wp_parse_args($step, array(
            'id' => 'step_' . uniqid(),
            'name' => '',
            'icon' => 'play-circle',
            'selector' => '',
            'selector_name' => '',
            'audio_url' => '',
            'start_time' => 0,
            'end_time' => 0,
            'scroll_to' => true,
            'highlight_style' => 'outline',
            'description' => '',
            'sub_highlights' => array(),
        ));
        
        $prefix = $is_template ? '{{PREFIX}}' : "erm_at_steps[$index]";
        ?>
        <div class="erm-at-step-item" data-index="<?php echo esc_attr($index); ?>">
            <div class="erm-at-step-header">
                <span class="erm-at-step-drag dashicons dashicons-menu"></span>
                <span class="erm-at-step-number"><?php echo is_numeric($index) ? ($index + 1) : '{{NUM}}'; ?></span>
                <span class="erm-at-step-icon" data-icon="<?php echo esc_attr($step['icon']); ?>">
                    <?php echo $this->get_icon_svg($step['icon']); ?>
                </span>
                <span class="erm-at-step-title"><?php echo esc_html($step['name'] ?: __('New Step', 'erm-audio-tours')); ?></span>
                <span class="erm-at-step-selector-preview"><?php echo esc_html($step['selector_name'] ?: $step['selector']); ?></span>
                <button type="button" class="erm-at-step-toggle">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
                <button type="button" class="erm-at-step-delete">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            
            <div class="erm-at-step-content">
                <input type="hidden" name="<?php echo $prefix; ?>[id]" value="<?php echo esc_attr($step['id']); ?>">
                
                <div class="erm-at-step-row erm-at-step-row-2col">
                    <div class="erm-at-step-field">
                        <label><?php _e('Step Name', 'erm-audio-tours'); ?></label>
                        <input type="text" name="<?php echo $prefix; ?>[name]" 
                               value="<?php echo esc_attr($step['name']); ?>" 
                               class="erm-at-step-name-input"
                               placeholder="<?php _e('e.g., Welcome, Services, Pricing', 'erm-audio-tours'); ?>">
                    </div>
                    
                    <div class="erm-at-step-field">
                        <label><?php _e('Icon', 'erm-audio-tours'); ?></label>
                        <div class="erm-at-icon-picker">
                            <button type="button" class="erm-at-icon-picker-btn">
                                <span class="erm-at-icon-preview"><?php echo $this->get_icon_svg($step['icon']); ?></span>
                                <span class="erm-at-icon-name"><?php echo esc_html($icons[$step['icon']] ?? $step['icon']); ?></span>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </button>
                            <input type="hidden" name="<?php echo $prefix; ?>[icon]" value="<?php echo esc_attr($step['icon']); ?>" class="erm-at-icon-value">
                            <div class="erm-at-icon-dropdown">
                                <input type="text" class="erm-at-icon-search" placeholder="<?php _e('Search icons...', 'erm-audio-tours'); ?>">
                                <div class="erm-at-icon-grid">
                                    <?php foreach ($icons as $icon_key => $icon_name) : ?>
                                    <button type="button" class="erm-at-icon-option <?php echo $icon_key === $step['icon'] ? 'selected' : ''; ?>" 
                                            data-icon="<?php echo esc_attr($icon_key); ?>" 
                                            title="<?php echo esc_attr($icon_name); ?>">
                                        <?php echo $this->get_icon_svg($icon_key); ?>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="erm-at-step-row">
                    <div class="erm-at-step-field">
                        <label><?php _e('Target Element', 'erm-audio-tours'); ?></label>
                        <div class="erm-at-selector-group">
                            <input type="text" name="<?php echo $prefix; ?>[selector]" 
                                   value="<?php echo esc_attr($step['selector']); ?>" 
                                   class="erm-at-selector-input"
                                   placeholder="<?php _e('CSS selector or click to pick', 'erm-audio-tours'); ?>">
                            <input type="hidden" name="<?php echo $prefix; ?>[selector_name]" 
                                   value="<?php echo esc_attr($step['selector_name']); ?>" 
                                   class="erm-at-selector-name-input">
                            <button type="button" class="button erm-at-pick-element">
                                <span class="dashicons dashicons-admin-customizer"></span> <?php _e('Pick Element', 'erm-audio-tours'); ?>
                            </button>
                        </div>
                        <p class="description"><?php _e('Enter a CSS selector or use the picker to select an element from the target page.', 'erm-audio-tours'); ?></p>
                    </div>
                </div>
                
                <div class="erm-at-step-row erm-at-step-row-2col erm-at-audio-timestamps" <?php echo $audio_mode === 'per_step' ? 'style="display:none;"' : ''; ?>>
                    <div class="erm-at-step-field">
                        <label><?php _e('Start Time (seconds)', 'erm-audio-tours'); ?></label>
                        <input type="number" name="<?php echo $prefix; ?>[start_time]" 
                               value="<?php echo esc_attr($step['start_time']); ?>" 
                               min="0" step="0.1" class="small-text">
                    </div>
                    <div class="erm-at-step-field">
                        <label><?php _e('End Time (seconds)', 'erm-audio-tours'); ?></label>
                        <input type="number" name="<?php echo $prefix; ?>[end_time]" 
                               value="<?php echo esc_attr($step['end_time']); ?>" 
                               min="0" step="0.1" class="small-text">
                    </div>
                </div>
                
                <div class="erm-at-step-row erm-at-step-audio" <?php echo $audio_mode === 'single' ? 'style="display:none;"' : ''; ?>>
                    <div class="erm-at-step-field">
                        <label><?php _e('Step Audio File', 'erm-audio-tours'); ?></label>
                        <div class="erm-at-audio-upload">
                            <input type="hidden" name="<?php echo $prefix; ?>[audio_url]" 
                                   value="<?php echo esc_url($step['audio_url']); ?>" 
                                   class="erm-at-audio-url">
                            <input type="text" value="<?php echo esc_url($step['audio_url']); ?>" class="regular-text erm-at-audio-display" readonly>
                            <button type="button" class="button erm-at-upload-audio" data-target-class="erm-at-audio-url">
                                <span class="dashicons dashicons-upload"></span>
                            </button>
                            <button type="button" class="button erm-at-remove-audio" <?php echo empty($step['audio_url']) ? 'style="display:none;"' : ''; ?>>
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="erm-at-step-row erm-at-step-row-2col">
                    <div class="erm-at-step-field">
                        <label>
                            <input type="checkbox" name="<?php echo $prefix; ?>[scroll_to]" value="1" <?php checked($step['scroll_to']); ?>>
                            <?php _e('Scroll to element', 'erm-audio-tours'); ?>
                        </label>
                    </div>
                    <div class="erm-at-step-field">
                        <label><?php _e('Highlight Effect', 'erm-audio-tours'); ?></label>
                        <select name="<?php echo $prefix; ?>[highlight_style]" class="erm-at-highlight-select">
                            <option value="" <?php selected($step['highlight_style'], ''); ?>><?php _e('None', 'erm-audio-tours'); ?></option>
                            <option value="outline" <?php selected($step['highlight_style'], 'outline'); ?>><?php _e('Outline Glow', 'erm-audio-tours'); ?></option>
                            <option value="pulse" <?php selected($step['highlight_style'], 'pulse'); ?>><?php _e('Pulse', 'erm-audio-tours'); ?></option>
                            <option value="spotlight" <?php selected($step['highlight_style'], 'spotlight'); ?>><?php _e('Spotlight', 'erm-audio-tours'); ?></option>
                            <option value="zoom" <?php selected($step['highlight_style'], 'zoom'); ?>><?php _e('Zoom', 'erm-audio-tours'); ?></option>
                            <option value="underline" <?php selected($step['highlight_style'], 'underline'); ?>><?php _e('Underline', 'erm-audio-tours'); ?></option>
                            <option value="border-draw" <?php selected($step['highlight_style'], 'border-draw'); ?>><?php _e('Border Draw', 'erm-audio-tours'); ?></option>
                            <option value="fill" <?php selected($step['highlight_style'], 'fill'); ?>><?php _e('Fill Overlay', 'erm-audio-tours'); ?></option>
                            <option value="bounce" <?php selected($step['highlight_style'], 'bounce'); ?>><?php _e('Bounce', 'erm-audio-tours'); ?></option>
                            <option value="shake" <?php selected($step['highlight_style'], 'shake'); ?>><?php _e('Shake', 'erm-audio-tours'); ?></option>
                            <option value="arrow" <?php selected($step['highlight_style'], 'arrow'); ?>><?php _e('Arrow Pointer', 'erm-audio-tours'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="erm-at-step-row">
                    <div class="erm-at-step-field">
                        <label><?php _e('Description (optional)', 'erm-audio-tours'); ?></label>
                        <input type="text" name="<?php echo $prefix; ?>[description]" 
                               value="<?php echo esc_attr($step['description']); ?>" 
                               class="large-text"
                               placeholder="<?php _e('Brief description of this step', 'erm-audio-tours'); ?>">
                    </div>
                </div>
                
                <!-- Timeline Sub-Highlights -->
                <div class="erm-at-step-row erm-at-timeline-section">
                    <div class="erm-at-step-field">
                        <label><?php _e('Timeline Highlights', 'erm-audio-tours'); ?></label>
                        <p class="description"><?php _e('Add additional elements to highlight at specific timestamps within this step.', 'erm-audio-tours'); ?></p>
                        
                        <!-- Audio Timeline Player -->
                        <div class="erm-at-timeline-player" data-step-index="<?php echo esc_attr($index); ?>">
                            <div class="erm-at-timeline-controls">
                                <button type="button" class="button erm-at-timeline-play">
                                    <span class="dashicons dashicons-controls-play"></span>
                                </button>
                                <span class="erm-at-timeline-current">0:00</span>
                                <div class="erm-at-timeline-bar">
                                    <div class="erm-at-timeline-progress"></div>
                                    <div class="erm-at-timeline-markers"></div>
                                </div>
                                <span class="erm-at-timeline-duration">0:00</span>
                            </div>
                            <button type="button" class="button erm-at-add-marker">
                                <span class="dashicons dashicons-plus-alt2"></span>
                                <?php _e('Add Highlight at Current Time', 'erm-audio-tours'); ?>
                            </button>
                        </div>
                        
                        <!-- Sub-highlights List -->
                        <div class="erm-at-sub-highlights" data-prefix="<?php echo esc_attr($prefix); ?>">
                            <?php 
                            $sub_highlights = isset($step['sub_highlights']) ? $step['sub_highlights'] : array();
                            if (!empty($sub_highlights)) :
                                foreach ($sub_highlights as $sh_index => $sh) : 
                            ?>
                            <div class="erm-at-sub-highlight" data-index="<?php echo $sh_index; ?>">
                                <div class="erm-at-sh-header">
                                    <span class="erm-at-sh-time"><?php echo esc_html($this->format_time($sh['timestamp'] ?? 0)); ?></span>
                                    <span class="erm-at-sh-name"><?php echo esc_html($sh['selector_name'] ?? $sh['selector'] ?? ''); ?></span>
                                    <button type="button" class="erm-at-sh-delete" title="<?php _e('Remove', 'erm-audio-tours'); ?>">
                                        <span class="dashicons dashicons-no-alt"></span>
                                    </button>
                                </div>
                                <div class="erm-at-sh-fields">
                                    <input type="hidden" name="<?php echo $prefix; ?>[sub_highlights][<?php echo $sh_index; ?>][timestamp]" 
                                           value="<?php echo esc_attr($sh['timestamp'] ?? 0); ?>" class="erm-at-sh-timestamp">
                                    <div class="erm-at-sh-row">
                                        <div class="erm-at-sh-field">
                                            <label><?php _e('Element', 'erm-audio-tours'); ?></label>
                                            <div class="erm-at-sh-selector-group">
                                                <input type="text" name="<?php echo $prefix; ?>[sub_highlights][<?php echo $sh_index; ?>][selector]"
                                                       value="<?php echo esc_attr($sh['selector'] ?? ''); ?>"
                                                       class="erm-at-sh-selector" placeholder="#element-id or .class">
                                                <input type="hidden" name="<?php echo $prefix; ?>[sub_highlights][<?php echo $sh_index; ?>][selector_name]"
                                                       value="<?php echo esc_attr($sh['selector_name'] ?? ''); ?>" class="erm-at-sh-selector-name">
                                                <button type="button" class="button button-small erm-at-pick-element-sh">
                                                    <span class="dashicons dashicons-admin-customizer"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="erm-at-sh-field">
                                            <label><?php _e('Effect', 'erm-audio-tours'); ?></label>
                                            <select name="<?php echo $prefix; ?>[sub_highlights][<?php echo $sh_index; ?>][highlight_style]" class="erm-at-sh-style">
                                                <option value="outline" <?php selected($sh['highlight_style'] ?? '', 'outline'); ?>><?php _e('Outline Glow', 'erm-audio-tours'); ?></option>
                                                <option value="pulse" <?php selected($sh['highlight_style'] ?? '', 'pulse'); ?>><?php _e('Pulse', 'erm-audio-tours'); ?></option>
                                                <option value="spotlight" <?php selected($sh['highlight_style'] ?? '', 'spotlight'); ?>><?php _e('Spotlight', 'erm-audio-tours'); ?></option>
                                                <option value="zoom" <?php selected($sh['highlight_style'] ?? '', 'zoom'); ?>><?php _e('Zoom', 'erm-audio-tours'); ?></option>
                                                <option value="underline" <?php selected($sh['highlight_style'] ?? '', 'underline'); ?>><?php _e('Underline', 'erm-audio-tours'); ?></option>
                                                <option value="border-draw" <?php selected($sh['highlight_style'] ?? '', 'border-draw'); ?>><?php _e('Border Draw', 'erm-audio-tours'); ?></option>
                                                <option value="fill" <?php selected($sh['highlight_style'] ?? '', 'fill'); ?>><?php _e('Fill Overlay', 'erm-audio-tours'); ?></option>
                                                <option value="bounce" <?php selected($sh['highlight_style'] ?? '', 'bounce'); ?>><?php _e('Bounce', 'erm-audio-tours'); ?></option>
                                                <option value="shake" <?php selected($sh['highlight_style'] ?? '', 'shake'); ?>><?php _e('Shake', 'erm-audio-tours'); ?></option>
                                                <option value="arrow" <?php selected($sh['highlight_style'] ?? '', 'arrow'); ?>><?php _e('Arrow Pointer', 'erm-audio-tours'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                endforeach;
                            endif; 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function format_time($seconds) {
        $mins = floor($seconds / 60);
        $secs = floor($seconds % 60);
        return sprintf('%d:%02d', $mins, $secs);
    }
    
    public function render_appearance_metabox($post) {
        $primary_color = get_post_meta($post->ID, '_erm_at_primary_color', true) ?: '#0066FF';
        $secondary_color = get_post_meta($post->ID, '_erm_at_secondary_color', true) ?: '#00D4FF';
        $nav_position = get_post_meta($post->ID, '_erm_at_nav_position', true) ?: 'right';
        $button_position = get_post_meta($post->ID, '_erm_at_button_position', true) ?: 'bottom-right';
        $button_offset_x = get_post_meta($post->ID, '_erm_at_button_offset_x', true) ?: 30;
        $button_offset_y = get_post_meta($post->ID, '_erm_at_button_offset_y', true) ?: 30;
        $show_nav_on_load = get_post_meta($post->ID, '_erm_at_show_nav_on_load', true) ?: 'after_start';
        ?>
        <div class="erm-at-appearance-settings">
            <div class="erm-at-setting-row">
                <label><?php _e('Primary Color', 'erm-audio-tours'); ?></label>
                <div class="erm-at-color-field">
                    <input type="color" name="erm_at_primary_color" value="<?php echo esc_attr($primary_color); ?>">
                    <code><?php echo esc_html($primary_color); ?></code>
                </div>
            </div>
            
            <div class="erm-at-setting-row">
                <label><?php _e('Secondary Color', 'erm-audio-tours'); ?></label>
                <div class="erm-at-color-field">
                    <input type="color" name="erm_at_secondary_color" value="<?php echo esc_attr($secondary_color); ?>">
                    <code><?php echo esc_html($secondary_color); ?></code>
                </div>
            </div>
            
            <div class="erm-at-setting-row">
                <label><?php _e('Navigation Position', 'erm-audio-tours'); ?></label>
                <select name="erm_at_nav_position">
                    <option value="right" <?php selected($nav_position, 'right'); ?>><?php _e('Right Side', 'erm-audio-tours'); ?></option>
                    <option value="left" <?php selected($nav_position, 'left'); ?>><?php _e('Left Side', 'erm-audio-tours'); ?></option>
                </select>
            </div>
            
            <div class="erm-at-setting-row">
                <label><?php _e('Show Step Navigation', 'erm-audio-tours'); ?></label>
                <div class="erm-at-radio-group">
                    <label>
                        <input type="radio" name="erm_at_show_nav_on_load" value="after_start" <?php checked($show_nav_on_load, 'after_start'); ?>>
                        <?php _e('After clicking Start Tour (button only on load)', 'erm-audio-tours'); ?>
                    </label>
                    <label>
                        <input type="radio" name="erm_at_show_nav_on_load" value="on_load" <?php checked($show_nav_on_load, 'on_load'); ?>>
                        <?php _e('On page load (show steps + button together)', 'erm-audio-tours'); ?>
                    </label>
                </div>
            </div>
            
            <div class="erm-at-setting-row">
                <label><?php _e('Button Position', 'erm-audio-tours'); ?></label>
                <select name="erm_at_button_position">
                    <option value="bottom-right" <?php selected($button_position, 'bottom-right'); ?>><?php _e('Bottom Right', 'erm-audio-tours'); ?></option>
                    <option value="bottom-left" <?php selected($button_position, 'bottom-left'); ?>><?php _e('Bottom Left', 'erm-audio-tours'); ?></option>
                    <option value="top-right" <?php selected($button_position, 'top-right'); ?>><?php _e('Top Right', 'erm-audio-tours'); ?></option>
                    <option value="top-left" <?php selected($button_position, 'top-left'); ?>><?php _e('Top Left', 'erm-audio-tours'); ?></option>
                </select>
            </div>
            
            <div class="erm-at-setting-row">
                <label><?php _e('Button Offset from Edge', 'erm-audio-tours'); ?></label>
                <div class="erm-at-offset-fields">
                    <label class="erm-at-inline-label">
                        <?php _e('Horizontal', 'erm-audio-tours'); ?>
                        <input type="number" name="erm_at_button_offset_x" value="<?php echo esc_attr($button_offset_x); ?>" min="0" max="200" class="small-text"> px
                    </label>
                    <label class="erm-at-inline-label">
                        <?php _e('Vertical', 'erm-audio-tours'); ?>
                        <input type="number" name="erm_at_button_offset_y" value="<?php echo esc_attr($button_offset_y); ?>" min="0" max="200" class="small-text"> px
                    </label>
                </div>
                <p class="description"><?php _e('Adjust to avoid overlapping with other sticky elements like chat widgets.', 'erm-audio-tours'); ?></p>
            </div>
        </div>
        <?php
    }
    
    public function render_actions_metabox($post) {
        $target_page = get_post_meta($post->ID, '_erm_at_target_page', true);
        ?>
        <div class="erm-at-quick-actions">
            <?php if ($target_page) : ?>
            <a href="<?php echo esc_url(add_query_arg('erm_at_preview', $post->ID, $target_page)); ?>" 
               class="button button-large" target="_blank">
                <span class="dashicons dashicons-visibility"></span>
                <?php _e('Preview Tour', 'erm-audio-tours'); ?>
            </a>
            
            <a href="<?php echo esc_url(add_query_arg('erm_at_picker', $post->ID, $target_page)); ?>" 
               class="button button-large" target="_blank" id="erm-at-open-picker">
                <span class="dashicons dashicons-admin-customizer"></span>
                <?php _e('Open Element Picker', 'erm-audio-tours'); ?>
            </a>
            <?php else : ?>
            <p class="description"><?php _e('Set a target page URL to enable preview and element picker.', 'erm-audio-tours'); ?></p>
            <?php endif; ?>
            
            <hr>
            
            <a href="#" class="button" id="erm-at-duplicate-tour">
                <span class="dashicons dashicons-admin-page"></span>
                <?php _e('Duplicate Tour', 'erm-audio-tours'); ?>
            </a>
            
            <a href="#" class="button" id="erm-at-export-tour">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Export Tour', 'erm-audio-tours'); ?>
            </a>
        </div>
        <?php
    }
    
    public function render_visual_builder() {
        $tour_id = isset($_GET['tour_id']) ? absint($_GET['tour_id']) : 0;
        
        if (!$tour_id) {
            wp_die(__('No tour specified.', 'erm-audio-tours'));
        }
        
        $tour = get_post($tour_id);
        if (!$tour || $tour->post_type !== 'erm_audio_tour') {
            wp_die(__('Invalid tour.', 'erm-audio-tours'));
        }
        
        $target_page = get_post_meta($tour_id, '_erm_at_target_page', true);
        
        include ERM_AT_PLUGIN_DIR . 'templates/admin/visual-builder.php';
    }
    
    public function save_meta_boxes($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['erm_at_tour_nonce']) || !wp_verify_nonce($_POST['erm_at_tour_nonce'], 'erm_at_save_tour')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save settings
        $fields = array(
            'erm_at_target_page' => 'url',
            'erm_at_enabled' => 'bool',
            'erm_at_button_delay' => 'int',
            'erm_at_audio_mode' => 'text',
            'erm_at_master_audio' => 'url',
            'erm_at_button_text' => 'text',
            'erm_at_modal_title' => 'text',
            'erm_at_modal_subtitle' => 'text',
            'erm_at_modal_description' => 'textarea',
            'erm_at_primary_color' => 'color',
            'erm_at_secondary_color' => 'color',
            'erm_at_nav_position' => 'text',
            'erm_at_button_position' => 'text',
            'erm_at_button_offset_x' => 'int',
            'erm_at_button_offset_y' => 'int',
            'erm_at_show_nav_on_load' => 'text',
        );
        
        foreach ($fields as $field => $type) {
            if (isset($_POST[$field])) {
                $value = $this->sanitize_field($_POST[$field], $type);
                update_post_meta($post_id, '_' . $field, $value);
            } else if ($type === 'bool') {
                update_post_meta($post_id, '_' . $field, '0');
            }
        }
        
        // Save steps
        if (isset($_POST['erm_at_steps']) && is_array($_POST['erm_at_steps'])) {
            $steps = array();
            foreach ($_POST['erm_at_steps'] as $step) {
                // Process sub_highlights
                $sub_highlights = array();
                if (!empty($step['sub_highlights']) && is_array($step['sub_highlights'])) {
                    foreach ($step['sub_highlights'] as $sh) {
                        if (!empty($sh['selector'])) {
                            $sub_highlights[] = array(
                                'timestamp' => floatval($sh['timestamp'] ?? 0),
                                'selector' => sanitize_text_field($sh['selector']),
                                'selector_name' => sanitize_text_field($sh['selector_name'] ?? ''),
                                'highlight_style' => sanitize_text_field($sh['highlight_style'] ?? 'outline'),
                            );
                        }
                    }
                    // Sort by timestamp
                    usort($sub_highlights, function($a, $b) {
                        return $a['timestamp'] <=> $b['timestamp'];
                    });
                }
                
                $steps[] = array(
                    'id' => sanitize_text_field($step['id'] ?? 'step_' . uniqid()),
                    'name' => sanitize_text_field($step['name'] ?? ''),
                    'icon' => sanitize_text_field($step['icon'] ?? 'play-circle'),
                    'selector' => sanitize_text_field($step['selector'] ?? ''),
                    'selector_name' => sanitize_text_field($step['selector_name'] ?? ''),
                    'audio_url' => esc_url_raw($step['audio_url'] ?? ''),
                    'start_time' => floatval($step['start_time'] ?? 0),
                    'end_time' => floatval($step['end_time'] ?? 0),
                    'scroll_to' => !empty($step['scroll_to']),
                    'highlight_style' => sanitize_text_field($step['highlight_style'] ?? 'outline'),
                    'description' => sanitize_text_field($step['description'] ?? ''),
                    'sub_highlights' => $sub_highlights,
                );
            }
            update_post_meta($post_id, '_erm_at_steps', $steps);
        }
    }
    
    private function sanitize_field($value, $type) {
        switch ($type) {
            case 'url':
                return esc_url_raw($value);
            case 'bool':
                return $value ? '1' : '0';
            case 'int':
                return absint($value);
            case 'color':
                return sanitize_hex_color($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            default:
                return sanitize_text_field($value);
        }
    }
    
    private function get_icon_svg($icon) {
        $icons = array(
            'play-circle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10,8 16,12 10,16" fill="currentColor" stroke="none"></polygon></svg>',
            'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
            'check-circle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            'briefcase' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>',
            'dollar-sign' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
            'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
            'cpu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line></svg>',
            'trending-up' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
            'rocket' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path></svg>',
            'star' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>',
            'heart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>',
            'home' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
            'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
            'mail' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
            'phone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>',
            'map-pin' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
            'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
            'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
            'award' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>',
            'zap' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
            'target' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>',
            'layers' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>',
            'grid' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
            'book' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>',
            'message-circle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>',
            'thumbs-up' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>',
            'eye' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
            'globe' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
            'user' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        );
        
        return $icons[$icon] ?? $icons['play-circle'];
    }
}

ERM_AT_Builder::get_instance();
