<?php
/**
 * Plugin Name: ERM Audio Tours
 * Plugin URI: https://www.eliteresultsmarketing.com
 * Description: Create immersive audio-guided tours for any website. Features visual tour builder, front-end element picker, and beautiful playback experience.
 * Version: 2.0.0
 * Author: Elite Results Marketing
 * Author URI: https://www.eliteresultsmarketing.com
 * License: GPL-2.0+
 * Text Domain: erm-audio-tours
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

define('ERM_AT_VERSION', '2.0.0');
define('ERM_AT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ERM_AT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ERM_AT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class ERM_Audio_Tours {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        // Admin classes
        if (is_admin()) {
            require_once ERM_AT_PLUGIN_DIR . 'includes/class-erm-at-admin.php';
            require_once ERM_AT_PLUGIN_DIR . 'includes/class-erm-at-builder.php';
        }
        
        // Frontend classes
        require_once ERM_AT_PLUGIN_DIR . 'includes/class-erm-at-frontend.php';
        require_once ERM_AT_PLUGIN_DIR . 'includes/class-erm-at-ajax.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'register_post_type'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    public function activate() {
        $this->register_post_type();
        flush_rewrite_rules();
        
        // Create default ERM tour on first activation
        if (!get_option('erm_at_default_tour_created')) {
            $this->create_default_tour();
            update_option('erm_at_default_tour_created', true);
        }
        
        // Create plugin directories
        $upload_dir = wp_upload_dir();
        $erm_dir = $upload_dir['basedir'] . '/erm-audio-tours';
        if (!file_exists($erm_dir)) {
            wp_mkdir_p($erm_dir);
        }
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('erm-audio-tours', false, dirname(ERM_AT_PLUGIN_BASENAME) . '/languages');
    }
    
    public function register_post_type() {
        $labels = array(
            'name'               => __('ERM Audio Tours', 'erm-audio-tours'),
            'singular_name'      => __('ERM Audio Tour', 'erm-audio-tours'),
            'menu_name'          => __('ERM Audio Tours', 'erm-audio-tours'),
            'add_new'            => __('Add New Tour', 'erm-audio-tours'),
            'add_new_item'       => __('Add New Audio Tour', 'erm-audio-tours'),
            'edit_item'          => __('Edit Audio Tour', 'erm-audio-tours'),
            'new_item'           => __('New Audio Tour', 'erm-audio-tours'),
            'view_item'          => __('View Audio Tour', 'erm-audio-tours'),
            'search_items'       => __('Search Audio Tours', 'erm-audio-tours'),
            'not_found'          => __('No audio tours found', 'erm-audio-tours'),
            'not_found_in_trash' => __('No audio tours found in trash', 'erm-audio-tours'),
        );
        
        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 30,
            'menu_icon'           => 'dashicons-controls-volumeon',
            'supports'            => array('title'),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'rewrite'             => false,
        );
        
        register_post_type('erm_audio_tour', $args);
    }
    
    /**
     * Create the default ERM Homepage Tour
     */
    private function create_default_tour() {
        $tour_id = wp_insert_post(array(
            'post_title'  => 'Elite Results Marketing Homepage Tour',
            'post_type'   => 'erm_audio_tour',
            'post_status' => 'publish',
        ));
        
        if ($tour_id && !is_wp_error($tour_id)) {
            // Tour settings
            update_post_meta($tour_id, '_erm_at_target_page', home_url('/'));
            update_post_meta($tour_id, '_erm_at_enabled', '1');
            update_post_meta($tour_id, '_erm_at_button_delay', 2000);
            update_post_meta($tour_id, '_erm_at_primary_color', '#0066FF');
            update_post_meta($tour_id, '_erm_at_secondary_color', '#00D4FF');
            update_post_meta($tour_id, '_erm_at_button_text', 'Start Guided Tour');
            update_post_meta($tour_id, '_erm_at_modal_title', 'Guided Tour');
            update_post_meta($tour_id, '_erm_at_modal_subtitle', 'Experience Elite Results Marketing');
            update_post_meta($tour_id, '_erm_at_modal_description', 'Sit back and let us guide you through our services, approach, and how we help glass businesses dominate their markets. This interactive audio experience takes about 3 minutes.');
            
            // Default steps
            $steps = array(
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'Welcome',
                    'icon' => 'play-circle',
                    'selector' => 'h1',
                    'selector_name' => 'Main Heading',
                    'audio_url' => '',
                    'start_time' => 0,
                    'end_time' => 12,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'Introduction to Elite Results Marketing',
                ),
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'Why Us',
                    'icon' => 'shield',
                    'selector' => '[class*="why"], [class*="choose"]',
                    'selector_name' => 'Why Glass Companies Choose Us',
                    'audio_url' => '',
                    'start_time' => 12,
                    'end_time' => 28,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'Why glass companies choose us',
                ),
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'Difference',
                    'icon' => 'check-circle',
                    'selector' => 'table, [class*="comparison"]',
                    'selector_name' => 'Comparison Table',
                    'audio_url' => '',
                    'start_time' => 28,
                    'end_time' => 45,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'ERM vs Generic Agencies',
                ),
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'Services',
                    'icon' => 'briefcase',
                    'selector' => '[class*="service"]',
                    'selector_name' => 'Our Services Section',
                    'audio_url' => '',
                    'start_time' => 45,
                    'end_time' => 75,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'Our comprehensive services',
                ),
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'Pricing',
                    'icon' => 'dollar-sign',
                    'selector' => '[class*="pricing"], [class*="plan"]',
                    'selector_name' => 'Pricing Section',
                    'audio_url' => '',
                    'start_time' => 75,
                    'end_time' => 95,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'Transparent pricing plans',
                ),
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'Team',
                    'icon' => 'users',
                    'selector' => '[class*="team"]',
                    'selector_name' => 'Team Section',
                    'audio_url' => '',
                    'start_time' => 95,
                    'end_time' => 110,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'Meet our expert team',
                ),
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'AI Tools',
                    'icon' => 'cpu',
                    'selector' => '[class*="tool"], [class*="ai-power"]',
                    'selector_name' => 'AI Tools Section',
                    'audio_url' => '',
                    'start_time' => 110,
                    'end_time' => 130,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'Free AI-powered tools',
                ),
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'Results',
                    'icon' => 'trending-up',
                    'selector' => '[class*="case"], [class*="success"]',
                    'selector_name' => 'Case Studies Section',
                    'audio_url' => '',
                    'start_time' => 130,
                    'end_time' => 150,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'Proven results & case studies',
                ),
                array(
                    'id' => 'step_' . uniqid(),
                    'name' => 'Get Started',
                    'icon' => 'rocket',
                    'selector' => '[class*="how-it-works"], [class*="contact"]',
                    'selector_name' => 'How It Works Section',
                    'audio_url' => '',
                    'start_time' => 150,
                    'end_time' => 170,
                    'scroll_to' => true,
                    'highlight' => true,
                    'description' => 'How to get started',
                ),
            );
            
            update_post_meta($tour_id, '_erm_at_steps', $steps);
        }
    }
    
    /**
     * Get available icons
     */
    public static function get_icons() {
        return array(
            'play-circle' => 'Play Circle',
            'shield' => 'Shield',
            'check-circle' => 'Check Circle',
            'briefcase' => 'Briefcase',
            'dollar-sign' => 'Dollar Sign',
            'users' => 'Users',
            'cpu' => 'CPU / AI',
            'trending-up' => 'Trending Up',
            'rocket' => 'Rocket',
            'star' => 'Star',
            'heart' => 'Heart',
            'home' => 'Home',
            'settings' => 'Settings',
            'mail' => 'Mail',
            'phone' => 'Phone',
            'map-pin' => 'Map Pin',
            'calendar' => 'Calendar',
            'clock' => 'Clock',
            'award' => 'Award',
            'zap' => 'Zap / Lightning',
            'target' => 'Target',
            'layers' => 'Layers',
            'grid' => 'Grid',
            'book' => 'Book',
            'message-circle' => 'Message',
            'thumbs-up' => 'Thumbs Up',
            'eye' => 'Eye',
            'lock' => 'Lock',
            'unlock' => 'Unlock',
            'download' => 'Download',
            'upload' => 'Upload',
            'share' => 'Share',
            'link' => 'Link',
            'image' => 'Image',
            'video' => 'Video',
            'music' => 'Music',
            'mic' => 'Microphone',
            'headphones' => 'Headphones',
            'camera' => 'Camera',
            'globe' => 'Globe',
            'compass' => 'Compass',
            'navigation' => 'Navigation',
            'flag' => 'Flag',
            'bookmark' => 'Bookmark',
            'tag' => 'Tag',
            'folder' => 'Folder',
            'file' => 'File',
            'file-text' => 'File Text',
            'clipboard' => 'Clipboard',
            'edit' => 'Edit',
            'trash' => 'Trash',
            'search' => 'Search',
            'filter' => 'Filter',
            'bar-chart' => 'Bar Chart',
            'pie-chart' => 'Pie Chart',
            'activity' => 'Activity',
            'gift' => 'Gift',
            'shopping-cart' => 'Shopping Cart',
            'shopping-bag' => 'Shopping Bag',
            'credit-card' => 'Credit Card',
            'truck' => 'Truck',
            'package' => 'Package',
            'box' => 'Box',
            'tool' => 'Tool',
            'wrench' => 'Wrench',
            'hammer' => 'Hammer',
            'key' => 'Key',
            'life-buoy' => 'Life Buoy',
            'coffee' => 'Coffee',
            'sun' => 'Sun',
            'moon' => 'Moon',
            'cloud' => 'Cloud',
            'umbrella' => 'Umbrella',
            'thermometer' => 'Thermometer',
            'droplet' => 'Droplet',
            'wind' => 'Wind',
            'feather' => 'Feather',
            'smile' => 'Smile',
            'frown' => 'Frown',
            'meh' => 'Meh',
            'user' => 'User',
            'user-plus' => 'User Plus',
            'user-check' => 'User Check',
        );
    }
}

// Initialize plugin
function erm_audio_tours() {
    return ERM_Audio_Tours::get_instance();
}

add_action('plugins_loaded', 'erm_audio_tours');
