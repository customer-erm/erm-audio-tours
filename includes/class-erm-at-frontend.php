<?php
/**
 * Frontend Class - Displays tours on the website
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERM_AT_Frontend {
    
    private static $instance = null;
    private $current_tour = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'maybe_enqueue_assets'));
        add_action('wp_footer', array($this, 'maybe_render_tour'));
        add_action('wp_head', array($this, 'maybe_add_picker_mode'));
    }

    /**
     * Find active tour for current page
     */
    private function get_tour_for_current_page() {
        if ($this->current_tour !== null) {
            return $this->current_tour;
        }
        
        // Check for preview mode
        if (isset($_GET['erm_at_preview'])) {
            $tour_id = absint($_GET['erm_at_preview']);
            $tour = get_post($tour_id);
            if ($tour && $tour->post_type === 'erm_audio_tour') {
                $this->current_tour = $tour;
                return $tour;
            }
        }
        
        // Find matching tour by URL
        $current_url = home_url($_SERVER['REQUEST_URI']);
        $current_url_clean = strtok($current_url, '?'); // Remove query strings
        
        $tours = get_posts(array(
            'post_type' => 'erm_audio_tour',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_erm_at_enabled',
                    'value' => '1',
                ),
            ),
        ));
        
        foreach ($tours as $tour) {
            $target_page = get_post_meta($tour->ID, '_erm_at_target_page', true);
            $target_clean = strtok($target_page, '?');
            
            // Check for homepage match
            if ($target_page === home_url('/') && is_front_page()) {
                $this->current_tour = $tour;
                return $tour;
            }
            
            // Check for exact URL match
            if ($target_clean === $current_url_clean) {
                $this->current_tour = $tour;
                return $tour;
            }
            
            // Check without trailing slashes
            if (rtrim($target_clean, '/') === rtrim($current_url_clean, '/')) {
                $this->current_tour = $tour;
                return $tour;
            }
        }
        
        $this->current_tour = false;
        return false;
    }
    
    public function maybe_enqueue_assets() {
        $tour = $this->get_tour_for_current_page();
        $is_picker_mode = isset($_GET['erm_at_picker']);
        
        if (!$tour && !$is_picker_mode) {
            return;
        }
        
        // Main tour styles
        wp_enqueue_style(
            'erm-at-tour',
            ERM_AT_PLUGIN_URL . 'assets/css/tour.css',
            array(),
            ERM_AT_VERSION
        );
        
        // Main tour script
        wp_enqueue_script(
            'erm-at-tour',
            ERM_AT_PLUGIN_URL . 'assets/js/tour.js',
            array(),
            ERM_AT_VERSION,
            true
        );
        
        // Picker mode styles and scripts
        if ($is_picker_mode) {
            wp_enqueue_style(
                'erm-at-picker',
                ERM_AT_PLUGIN_URL . 'assets/css/picker.css',
                array(),
                ERM_AT_VERSION
            );
            
            wp_enqueue_script(
                'erm-at-picker',
                ERM_AT_PLUGIN_URL . 'assets/js/picker.js',
                array(),
                ERM_AT_VERSION,
                true
            );
            
            wp_localize_script('erm-at-picker', 'ermAtPicker', array(
                'tourId' => absint($_GET['erm_at_picker']),
                'adminUrl' => admin_url(),
                'strings' => array(
                    'clickToSelect' => __('Click to select this element', 'erm-audio-tours'),
                    'selected' => __('Selected!', 'erm-audio-tours'),
                    'copySelector' => __('Copy Selector', 'erm-audio-tours'),
                ),
            ));
        }
        
        // Pass tour config to JS
        if ($tour) {
            wp_localize_script('erm-at-tour', 'ermAtTourConfig', $this->get_tour_config($tour));
            
            // Add custom colors
            $this->add_custom_colors($tour->ID);
        }
    }
    
    private function get_tour_config($tour) {
        $tour_id = $tour->ID;
        $steps = get_post_meta($tour_id, '_erm_at_steps', true);
        $audio_mode = get_post_meta($tour_id, '_erm_at_audio_mode', true) ?: 'single';
        
        if (!is_array($steps)) {
            $steps = array();
        }
        
        $formatted_steps = array();
        foreach ($steps as $step) {
            // Handle legacy 'highlight' boolean by converting to 'outline' style
            $highlight_style = '';
            if (isset($step['highlight_style'])) {
                $highlight_style = $step['highlight_style'];
            } elseif (!empty($step['highlight'])) {
                $highlight_style = 'outline'; // Legacy support
            }
            
            // Format sub-highlights
            $sub_highlights = array();
            if (!empty($step['sub_highlights'])) {
                foreach ($step['sub_highlights'] as $sh) {
                    $sub_highlights[] = array(
                        'timestamp' => floatval($sh['timestamp'] ?? 0),
                        'selector' => $sh['selector'] ?? '',
                        'highlightStyle' => $sh['highlight_style'] ?? 'outline',
                    );
                }
            }
            
            $formatted_steps[] = array(
                'id' => $step['id'],
                'name' => $step['name'],
                'icon' => $step['icon'],
                'selector' => $step['selector'],
                'audioUrl' => $step['audio_url'] ?? '',
                'startTime' => floatval($step['start_time'] ?? 0),
                'endTime' => floatval($step['end_time'] ?? 0),
                'scrollTo' => !empty($step['scroll_to']),
                'highlightStyle' => $highlight_style,
                'description' => $step['description'] ?? '',
                'subHighlights' => $sub_highlights,
            );
        }
        
        return array(
            'tourId' => $tour_id,
            'audioMode' => $audio_mode,
            'masterAudio' => get_post_meta($tour_id, '_erm_at_master_audio', true),
            'buttonDelay' => absint(get_post_meta($tour_id, '_erm_at_button_delay', true) ?: 2000),
            'buttonText' => get_post_meta($tour_id, '_erm_at_button_text', true) ?: 'Start Guided Tour',
            'navPosition' => get_post_meta($tour_id, '_erm_at_nav_position', true) ?: 'right',
            'buttonPosition' => get_post_meta($tour_id, '_erm_at_button_position', true) ?: 'bottom-right',
            'buttonOffsetX' => absint(get_post_meta($tour_id, '_erm_at_button_offset_x', true) ?: 30),
            'buttonOffsetY' => absint(get_post_meta($tour_id, '_erm_at_button_offset_y', true) ?: 30),
            'showNavOnLoad' => get_post_meta($tour_id, '_erm_at_show_nav_on_load', true) ?: 'after_start',
            'steps' => $formatted_steps,
            'isPreview' => isset($_GET['erm_at_preview']),
            'editUrl' => admin_url('post.php?post=' . $tour_id . '&action=edit'),
            'settings' => array(
                'autoScroll' => true,
                'scrollOffset' => 100,
                'highlightDuration' => 500,
                'scrollDuration' => 800,
            ),
        );
    }
    
    private function add_custom_colors($tour_id) {
        $primary = get_post_meta($tour_id, '_erm_at_primary_color', true) ?: '#0066FF';
        $secondary = get_post_meta($tour_id, '_erm_at_secondary_color', true) ?: '#00D4FF';
        $offset_x = absint(get_post_meta($tour_id, '_erm_at_button_offset_x', true) ?: 30);
        $offset_y = absint(get_post_meta($tour_id, '_erm_at_button_offset_y', true) ?: 30);
        
        $custom_css = "
            :root {
                --erm-at-primary: {$primary};
                --erm-at-primary-glow: {$primary}4d;
                --erm-at-secondary: {$secondary};
                --erm-at-gradient: linear-gradient(135deg, {$primary} 0%, {$secondary} 100%);
                --erm-at-button-offset-x: {$offset_x}px;
                --erm-at-button-offset-y: {$offset_y}px;
            }
        ";
        
        wp_add_inline_style('erm-at-tour', $custom_css);
    }
    
    public function maybe_render_tour() {
        $tour = $this->get_tour_for_current_page();
        
        if (!$tour) {
            // Check for picker mode
            if (isset($_GET['erm_at_picker'])) {
                $this->render_picker_ui();
            }
            return;
        }
        
        $this->render_tour_ui($tour);
    }
    
    private function render_tour_ui($tour) {
        $steps = get_post_meta($tour->ID, '_erm_at_steps', true);
        $nav_position = get_post_meta($tour->ID, '_erm_at_nav_position', true) ?: 'right';
        $button_position = get_post_meta($tour->ID, '_erm_at_button_position', true) ?: 'bottom-right';
        $button_text = get_post_meta($tour->ID, '_erm_at_button_text', true) ?: 'Start Guided Tour';
        $modal_title = get_post_meta($tour->ID, '_erm_at_modal_title', true) ?: 'Guided Tour';
        $modal_subtitle = get_post_meta($tour->ID, '_erm_at_modal_subtitle', true);
        $modal_description = get_post_meta($tour->ID, '_erm_at_modal_description', true);
        
        if (!is_array($steps) || empty($steps)) {
            return;
        }
        
        include ERM_AT_PLUGIN_DIR . 'templates/frontend/tour-ui.php';
    }
    
    private function render_picker_ui() {
        include ERM_AT_PLUGIN_DIR . 'templates/frontend/picker-ui.php';
    }
    
    public function maybe_add_picker_mode() {
        if (isset($_GET['erm_at_picker']) && current_user_can('edit_posts')) {
            echo '<style>body { cursor: crosshair !important; }</style>';
        }
    }
}

ERM_AT_Frontend::get_instance();
