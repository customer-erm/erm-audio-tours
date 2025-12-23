<?php
/**
 * AJAX Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERM_AT_Ajax {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Admin AJAX handlers
        add_action('wp_ajax_erm_at_save_selector', array($this, 'save_selector'));
        add_action('wp_ajax_erm_at_duplicate_tour', array($this, 'duplicate_tour'));
        add_action('wp_ajax_erm_at_export_tour', array($this, 'export_tour'));
        add_action('wp_ajax_erm_at_import_tour', array($this, 'import_tour'));
        add_action('wp_ajax_erm_at_get_page_content', array($this, 'get_page_content'));
        add_action('wp_ajax_erm_at_get_tour_steps', array($this, 'get_tour_steps'));
        
        // Also allow nopriv for picker (runs on frontend)
        add_action('wp_ajax_nopriv_erm_at_get_tour_steps', array($this, 'get_tour_steps'));
    }
    
    /**
     * Get tour steps for picker dropdown
     */
    public function get_tour_steps() {
        $tour_id = absint($_GET['tour_id'] ?? $_POST['tour_id'] ?? 0);
        
        if (!$tour_id) {
            wp_send_json_error('No tour ID provided');
        }
        
        $tour = get_post($tour_id);
        if (!$tour || $tour->post_type !== 'erm_audio_tour') {
            wp_send_json_error('Invalid tour');
        }
        
        $steps = get_post_meta($tour_id, '_erm_at_steps', true);
        
        if (!is_array($steps)) {
            $steps = array();
        }
        
        // Return simplified step data
        $step_data = array();
        foreach ($steps as $step) {
            $step_data[] = array(
                'id' => $step['id'],
                'name' => $step['name'],
            );
        }
        
        wp_send_json_success(array(
            'steps' => $step_data,
            'tour_title' => $tour->post_title,
        ));
    }
    
    /**
     * Save selector from picker
     */
    public function save_selector() {
        check_ajax_referer('erm_at_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $tour_id = absint($_POST['tour_id']);
        $step_id = sanitize_text_field($_POST['step_id']);
        $selector = sanitize_text_field($_POST['selector']);
        $selector_name = sanitize_text_field($_POST['selector_name']);
        
        $steps = get_post_meta($tour_id, '_erm_at_steps', true);
        
        if (is_array($steps)) {
            foreach ($steps as &$step) {
                if ($step['id'] === $step_id) {
                    $step['selector'] = $selector;
                    $step['selector_name'] = $selector_name;
                    break;
                }
            }
            update_post_meta($tour_id, '_erm_at_steps', $steps);
        }
        
        wp_send_json_success(array(
            'message' => 'Selector saved',
            'selector' => $selector,
            'selector_name' => $selector_name,
        ));
    }
    
    /**
     * Duplicate a tour
     */
    public function duplicate_tour() {
        check_ajax_referer('erm_at_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $tour_id = absint($_POST['tour_id']);
        $tour = get_post($tour_id);
        
        if (!$tour || $tour->post_type !== 'erm_audio_tour') {
            wp_send_json_error('Invalid tour');
        }
        
        // Create duplicate post
        $new_tour_id = wp_insert_post(array(
            'post_title' => $tour->post_title . ' (Copy)',
            'post_type' => 'erm_audio_tour',
            'post_status' => 'draft',
        ));
        
        if (is_wp_error($new_tour_id)) {
            wp_send_json_error($new_tour_id->get_error_message());
        }
        
        // Copy all meta
        $meta = get_post_meta($tour_id);
        foreach ($meta as $key => $values) {
            if (strpos($key, '_erm_at_') === 0) {
                foreach ($values as $value) {
                    update_post_meta($new_tour_id, $key, maybe_unserialize($value));
                }
            }
        }
        
        // Disable the duplicate by default
        update_post_meta($new_tour_id, '_erm_at_enabled', '0');
        
        wp_send_json_success(array(
            'message' => 'Tour duplicated',
            'new_tour_id' => $new_tour_id,
            'edit_url' => get_edit_post_link($new_tour_id, 'raw'),
        ));
    }
    
    /**
     * Export tour as JSON
     */
    public function export_tour() {
        check_ajax_referer('erm_at_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $tour_id = absint($_POST['tour_id']);
        $tour = get_post($tour_id);
        
        if (!$tour || $tour->post_type !== 'erm_audio_tour') {
            wp_send_json_error('Invalid tour');
        }
        
        $export_data = array(
            'title' => $tour->post_title,
            'version' => ERM_AT_VERSION,
            'exported_at' => current_time('mysql'),
            'settings' => array(),
            'steps' => array(),
        );
        
        // Get all meta
        $meta_keys = array(
            '_erm_at_target_page',
            '_erm_at_enabled',
            '_erm_at_button_delay',
            '_erm_at_audio_mode',
            '_erm_at_master_audio',
            '_erm_at_button_text',
            '_erm_at_modal_title',
            '_erm_at_modal_subtitle',
            '_erm_at_modal_description',
            '_erm_at_primary_color',
            '_erm_at_secondary_color',
            '_erm_at_nav_position',
            '_erm_at_button_position',
        );
        
        foreach ($meta_keys as $key) {
            $clean_key = str_replace('_erm_at_', '', $key);
            $export_data['settings'][$clean_key] = get_post_meta($tour_id, $key, true);
        }
        
        $export_data['steps'] = get_post_meta($tour_id, '_erm_at_steps', true);
        
        wp_send_json_success($export_data);
    }
    
    /**
     * Import tour from JSON
     */
    public function import_tour() {
        check_ajax_referer('erm_at_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $import_data = json_decode(stripslashes($_POST['import_data']), true);
        
        if (!$import_data || !isset($import_data['title'])) {
            wp_send_json_error('Invalid import data');
        }
        
        // Create new tour
        $tour_id = wp_insert_post(array(
            'post_title' => $import_data['title'] . ' (Imported)',
            'post_type' => 'erm_audio_tour',
            'post_status' => 'draft',
        ));
        
        if (is_wp_error($tour_id)) {
            wp_send_json_error($tour_id->get_error_message());
        }
        
        // Import settings
        if (isset($import_data['settings'])) {
            foreach ($import_data['settings'] as $key => $value) {
                update_post_meta($tour_id, '_erm_at_' . $key, $value);
            }
        }
        
        // Import steps
        if (isset($import_data['steps'])) {
            // Generate new step IDs
            foreach ($import_data['steps'] as &$step) {
                $step['id'] = 'step_' . uniqid();
            }
            update_post_meta($tour_id, '_erm_at_steps', $import_data['steps']);
        }
        
        // Disable by default
        update_post_meta($tour_id, '_erm_at_enabled', '0');
        
        wp_send_json_success(array(
            'message' => 'Tour imported',
            'tour_id' => $tour_id,
            'edit_url' => get_edit_post_link($tour_id, 'raw'),
        ));
    }
    
    /**
     * Get page content for visual builder
     */
    public function get_page_content() {
        check_ajax_referer('erm_at_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $url = esc_url_raw($_POST['url']);
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'sslverify' => false,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        
        wp_send_json_success(array(
            'html' => $body,
        ));
    }
}

ERM_AT_Ajax::get_instance();
