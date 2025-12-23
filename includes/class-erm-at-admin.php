<?php
/**
 * Admin Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERM_AT_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_filter('post_row_actions', array($this, 'add_row_actions'), 10, 2);
        add_action('admin_menu', array($this, 'add_submenu_pages'));
        add_filter('manage_erm_audio_tour_posts_columns', array($this, 'add_columns'));
        add_action('manage_erm_audio_tour_posts_custom_column', array($this, 'render_columns'), 10, 2);
    }
    
    public function enqueue_assets($hook) {
        global $post_type;
        
        if ($post_type !== 'erm_audio_tour') {
            return;
        }
        
        // WordPress media uploader
        wp_enqueue_media();
        
        // Admin styles
        wp_enqueue_style(
            'erm-at-admin',
            ERM_AT_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ERM_AT_VERSION
        );
        
        // Sortable for drag and drop
        wp_enqueue_script('jquery-ui-sortable');
        
        // Admin scripts
        wp_enqueue_script(
            'erm-at-admin',
            ERM_AT_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            ERM_AT_VERSION,
            true
        );
        
        wp_localize_script('erm-at-admin', 'ermAtAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('erm_at_admin_nonce'),
            'icons' => ERM_Audio_Tours::get_icons(),
            'strings' => array(
                'selectElement' => __('Click on any element to select it', 'erm-audio-tours'),
                'confirmDelete' => __('Are you sure you want to delete this step?', 'erm-audio-tours'),
                'uploadAudio' => __('Select Audio File', 'erm-audio-tours'),
                'useAudio' => __('Use This Audio', 'erm-audio-tours'),
                'removeAudio' => __('Remove', 'erm-audio-tours'),
            ),
        ));
    }
    
    public function add_submenu_pages() {
        add_submenu_page(
            'edit.php?post_type=erm_audio_tour',
            __('Settings', 'erm-audio-tours'),
            __('Settings', 'erm-audio-tours'),
            'manage_options',
            'erm-at-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=erm_audio_tour',
            __('Help & Documentation', 'erm-audio-tours'),
            __('Help', 'erm-audio-tours'),
            'manage_options',
            'erm-at-help',
            array($this, 'render_help_page')
        );
    }
    
    public function add_row_actions($actions, $post) {
        if ($post->post_type === 'erm_audio_tour') {
            $target_page = get_post_meta($post->ID, '_erm_at_target_page', true);
            if ($target_page) {
                $preview_url = add_query_arg('erm_at_preview', $post->ID, $target_page);
                $actions['preview_tour'] = '<a href="' . esc_url($preview_url) . '" target="_blank">' . __('Preview Tour', 'erm-audio-tours') . '</a>';
            }
            
            $builder_url = add_query_arg(array(
                'post_type' => 'erm_audio_tour',
                'page' => 'erm-at-builder',
                'tour_id' => $post->ID
            ), admin_url('edit.php'));
            $actions['visual_builder'] = '<a href="' . esc_url($builder_url) . '">' . __('Visual Builder', 'erm-audio-tours') . '</a>';
        }
        return $actions;
    }
    
    public function add_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['target_page'] = __('Target Page', 'erm-audio-tours');
                $new_columns['steps'] = __('Steps', 'erm-audio-tours');
                $new_columns['status'] = __('Status', 'erm-audio-tours');
            }
        }
        return $new_columns;
    }
    
    public function render_columns($column, $post_id) {
        switch ($column) {
            case 'target_page':
                $target = get_post_meta($post_id, '_erm_at_target_page', true);
                if ($target) {
                    echo '<a href="' . esc_url($target) . '" target="_blank">' . esc_html($target) . '</a>';
                } else {
                    echo '<span class="erm-at-not-set">' . __('Not set', 'erm-audio-tours') . '</span>';
                }
                break;
                
            case 'steps':
                $steps = get_post_meta($post_id, '_erm_at_steps', true);
                $count = is_array($steps) ? count($steps) : 0;
                echo '<span class="erm-at-step-count">' . $count . ' ' . _n('step', 'steps', $count, 'erm-audio-tours') . '</span>';
                break;
                
            case 'status':
                $enabled = get_post_meta($post_id, '_erm_at_enabled', true);
                if ($enabled) {
                    echo '<span class="erm-at-status erm-at-status-active">' . __('Active', 'erm-audio-tours') . '</span>';
                } else {
                    echo '<span class="erm-at-status erm-at-status-inactive">' . __('Inactive', 'erm-audio-tours') . '</span>';
                }
                break;
        }
    }
    
    public function render_settings_page() {
        include ERM_AT_PLUGIN_DIR . 'templates/admin/settings.php';
    }
    
    public function render_help_page() {
        include ERM_AT_PLUGIN_DIR . 'templates/admin/help.php';
    }
}

ERM_AT_Admin::get_instance();
