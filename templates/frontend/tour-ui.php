<?php
/**
 * Tour UI Template
 * 
 * @var object $tour       The tour post object
 * @var array  $steps      Array of tour steps
 * @var string $nav_position       'left' or 'right'
 * @var string $button_position    Position class for launch button
 * @var string $button_text        Launch button text
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get icon SVGs
$icon_svgs = array(
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
    'globe' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
    'zap' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
    'award' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>',
    'target' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>',
);

// Helper function to get icon
function erm_at_get_icon($icon_name, $icons) {
    return isset($icons[$icon_name]) ? $icons[$icon_name] : $icons['play-circle'];
}
?>

<!-- Launch Button - Starts Tour Directly -->
<button class="erm-at-launch-btn <?php echo esc_attr($button_position); ?>" aria-label="<?php echo esc_attr($button_text); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <polygon points="10,8 16,12 10,16" fill="currentColor" stroke="none"></polygon>
    </svg>
    <span><?php echo esc_html($button_text); ?></span>
</button>

<!-- Navigation Bar with Close Button -->
<nav class="erm-at-nav <?php echo esc_attr($nav_position); ?>" aria-label="Tour Navigation">
    <?php foreach ($steps as $index => $step) : ?>
    <div class="erm-at-nav-item" data-step="<?php echo $index; ?>" data-step-id="<?php echo esc_attr($step['id']); ?>">
        <span class="erm-at-nav-label"><?php echo esc_html($step['name']); ?></span>
        <div class="erm-at-nav-dot">
            <?php echo erm_at_get_icon($step['icon'], $icon_svgs); ?>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Close Button at Bottom of Nav - styled like nav items -->
    <div class="erm-at-nav-item erm-at-nav-close-item">
        <span class="erm-at-nav-label"><?php _e('Exit Tour', 'erm-audio-tours'); ?></span>
        <button class="erm-at-nav-close" aria-label="<?php _e('Exit Tour', 'erm-audio-tours'); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </button>
    </div>
</nav>

<!-- Player Bar -->
<div class="erm-at-player">
    <div class="erm-at-section-info">
        <div class="erm-at-section-label"><?php _e('Now Playing', 'erm-audio-tours'); ?></div>
        <div class="erm-at-section-name"><?php echo esc_html($steps[0]['name'] ?? 'Welcome'); ?></div>
    </div>
    
    <div class="erm-at-progress-container">
        <span class="erm-at-time erm-at-time-current">0:00</span>
        <div class="erm-at-progress-bar">
            <div class="erm-at-progress-fill"></div>
        </div>
        <span class="erm-at-time erm-at-time-total">0:00</span>
    </div>
    
    <div class="erm-at-controls">
        <button class="erm-at-btn erm-at-btn-prev" aria-label="<?php _e('Previous', 'erm-audio-tours'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="19,20 9,12 19,4"></polygon>
                <rect x="5" y="4" width="2" height="16"></rect>
            </svg>
        </button>
        
        <button class="erm-at-btn erm-at-btn-play" aria-label="<?php _e('Play', 'erm-audio-tours'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="5,3 19,12 5,21"></polygon>
            </svg>
        </button>
        
        <button class="erm-at-btn erm-at-btn-next" aria-label="<?php _e('Next', 'erm-audio-tours'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="5,4 15,12 5,20"></polygon>
                <rect x="17" y="4" width="2" height="16"></rect>
            </svg>
        </button>
    </div>
    
    <div class="erm-at-volume">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
        </svg>
        <input type="range" class="erm-at-volume-slider" min="0" max="1" step="0.1" value="0.8">
    </div>
    
    <button class="erm-at-btn erm-at-btn-close" aria-label="<?php _e('Close Tour', 'erm-audio-tours'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
</div>

<!-- Mobile Tab Button -->
<button class="erm-at-mobile-tab" aria-label="<?php _e('Start Tour', 'erm-audio-tours'); ?>">
    <span class="erm-at-mobile-tab-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polygon points="10,8 16,12 10,16" fill="currentColor" stroke="none"></polygon>
        </svg>
    </span>
    <span class="erm-at-mobile-tab-label"><?php _e('Start Tour', 'erm-audio-tours'); ?></span>
</button>

<!-- Mobile Backdrop -->
<div class="erm-at-mobile-backdrop"></div>

<!-- Mobile Step Tray -->
<div class="erm-at-mobile-tray">
    <div class="erm-at-tray-handle"></div>
    
    <div class="erm-at-tray-header">
        <span class="erm-at-tray-title"><?php _e('Tour Steps', 'erm-audio-tours'); ?></span>
        <button class="erm-at-tray-close" aria-label="<?php _e('Close', 'erm-audio-tours'); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    
    <div class="erm-at-tray-steps">
        <?php foreach ($steps as $index => $step) : ?>
        <div class="erm-at-tray-step" data-step="<?php echo $index; ?>">
            <div class="erm-at-tray-step-icon">
                <?php echo erm_at_get_icon($step['icon'], $icon_svgs); ?>
            </div>
            <div class="erm-at-tray-step-info">
                <div class="erm-at-tray-step-name"><?php echo esc_html($step['name']); ?></div>
                <div class="erm-at-tray-step-num"><?php printf(__('Step %d of %d', 'erm-audio-tours'), $index + 1, count($steps)); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="erm-at-mobile-player">
        <button class="erm-at-mobile-play" aria-label="<?php _e('Play', 'erm-audio-tours'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="5,3 19,12 5,21"></polygon>
            </svg>
        </button>
        
        <div class="erm-at-mobile-progress">
            <div class="erm-at-mobile-step-name"><?php echo esc_html($steps[0]['name'] ?? 'Welcome'); ?></div>
            <div class="erm-at-mobile-progress-bar">
                <div class="erm-at-mobile-progress-fill"></div>
            </div>
        </div>
        
        <div class="erm-at-mobile-nav">
            <button class="erm-at-mobile-prev" aria-label="<?php _e('Previous', 'erm-audio-tours'); ?>">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="19,20 9,12 19,4"></polygon>
                    <rect x="5" y="4" width="2" height="16"></rect>
                </svg>
            </button>
            <button class="erm-at-mobile-next" aria-label="<?php _e('Next', 'erm-audio-tours'); ?>">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="5,4 15,12 5,20"></polygon>
                    <rect x="17" y="4" width="2" height="16"></rect>
                </svg>
            </button>
        </div>
    </div>
    
    <button class="erm-at-tray-exit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        <?php _e('Exit Tour', 'erm-audio-tours'); ?>
    </button>
</div>
