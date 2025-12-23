<?php
/**
 * Help & Documentation Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap erm-at-help-page">
    <h1><?php _e('Audio Tour Builder - Help & Documentation', 'erm-audio-tours'); ?></h1>
    
    <div class="erm-at-help-section">
        <h2><?php _e('Getting Started', 'erm-audio-tours'); ?></h2>
        
        <h3><?php _e('Creating Your First Tour', 'erm-audio-tours'); ?></h3>
        <ol>
            <li>
                <strong><?php _e('Create a New Tour', 'erm-audio-tours'); ?></strong><br>
                <?php _e('Navigate to Audio Tours → Add New in your WordPress admin.', 'erm-audio-tours'); ?>
            </li>
            <li>
                <strong><?php _e('Set the Target Page', 'erm-audio-tours'); ?></strong><br>
                <?php _e('Enter the full URL of the page where you want the tour to appear. Use the "Use Homepage" button for your site\'s homepage.', 'erm-audio-tours'); ?>
            </li>
            <li>
                <strong><?php _e('Add Tour Steps', 'erm-audio-tours'); ?></strong><br>
                <?php _e('Click "Add New Step" to create steps. Each step needs:', 'erm-audio-tours'); ?>
                <ul>
                    <li><?php _e('A name (displayed in the navigation)', 'erm-audio-tours'); ?></li>
                    <li><?php _e('An icon (choose from 80+ options)', 'erm-audio-tours'); ?></li>
                    <li><?php _e('A target element (CSS selector)', 'erm-audio-tours'); ?></li>
                    <li><?php _e('Audio timing (start and end times)', 'erm-audio-tours'); ?></li>
                </ul>
            </li>
            <li>
                <strong><?php _e('Upload Audio', 'erm-audio-tours'); ?></strong><br>
                <?php _e('Upload a single master audio file or individual audio files for each step.', 'erm-audio-tours'); ?>
            </li>
            <li>
                <strong><?php _e('Enable & Preview', 'erm-audio-tours'); ?></strong><br>
                <?php _e('Check "Enable this tour" and use the Preview button to test.', 'erm-audio-tours'); ?>
            </li>
        </ol>
    </div>
    
    <div class="erm-at-help-section">
        <h2><?php _e('Using the Element Picker', 'erm-audio-tours'); ?></h2>
        
        <p><?php _e('The Element Picker lets you visually select elements on your page instead of writing CSS selectors manually.', 'erm-audio-tours'); ?></p>
        
        <h3><?php _e('How to Use', 'erm-audio-tours'); ?></h3>
        <ol>
            <li><?php _e('Make sure you\'ve set a Target Page URL', 'erm-audio-tours'); ?></li>
            <li><?php _e('Click "Open Element Picker" in the Quick Actions sidebar, or click "Pick Element" next to any step', 'erm-audio-tours'); ?></li>
            <li><?php _e('A new window will open showing your target page with a picker toolbar', 'erm-audio-tours'); ?></li>
            <li><?php _e('Hover over elements to see them highlighted', 'erm-audio-tours'); ?></li>
            <li><?php _e('Click an element to select it', 'erm-audio-tours'); ?></li>
            <li><?php _e('Choose which step to apply it to and click "Apply to Step"', 'erm-audio-tours'); ?></li>
        </ol>
        
        <h3><?php _e('Tips for Selecting Elements', 'erm-audio-tours'); ?></h3>
        <ul>
            <li><?php _e('Select the most specific container for each section', 'erm-audio-tours'); ?></li>
            <li><?php _e('Elements with IDs or unique classes work best', 'erm-audio-tours'); ?></li>
            <li><?php _e('You can manually edit the CSS selector after picking', 'erm-audio-tours'); ?></li>
        </ul>
    </div>
    
    <div class="erm-at-help-section">
        <h2><?php _e('Audio Options', 'erm-audio-tours'); ?></h2>
        
        <h3><?php _e('Single Audio File (Recommended)', 'erm-audio-tours'); ?></h3>
        <p><?php _e('Record one continuous audio file for your entire tour, then set start/end timestamps for each step. This creates a seamless experience.', 'erm-audio-tours'); ?></p>
        
        <h3><?php _e('Separate Audio Per Step', 'erm-audio-tours'); ?></h3>
        <p><?php _e('Upload individual audio files for each step. Good for tours where sections may be viewed independently.', 'erm-audio-tours'); ?></p>
        
        <h3><?php _e('Recommended Settings', 'erm-audio-tours'); ?></h3>
        <ul>
            <li><strong><?php _e('Format:', 'erm-audio-tours'); ?></strong> MP3</li>
            <li><strong><?php _e('Bitrate:', 'erm-audio-tours'); ?></strong> 192kbps</li>
            <li><strong><?php _e('Sample Rate:', 'erm-audio-tours'); ?></strong> 44.1kHz</li>
        </ul>
        
        <h3><?php _e('AI Voice Generation', 'erm-audio-tours'); ?></h3>
        <p><?php _e('We recommend these services for generating professional voiceovers:', 'erm-audio-tours'); ?></p>
        <ul>
            <li><a href="https://elevenlabs.io" target="_blank">ElevenLabs</a> - <?php _e('Most natural sounding', 'erm-audio-tours'); ?></li>
            <li><a href="https://play.ht" target="_blank">Play.ht</a> - <?php _e('Good variety of voices', 'erm-audio-tours'); ?></li>
            <li><a href="https://murf.ai" target="_blank">Murf.ai</a> - <?php _e('Easy to use', 'erm-audio-tours'); ?></li>
            <li><a href="https://speechify.com" target="_blank">Speechify</a> - <?php _e('Fast generation', 'erm-audio-tours'); ?></li>
        </ul>
    </div>
    
    <div class="erm-at-help-section">
        <h2><?php _e('CSS Selectors Guide', 'erm-audio-tours'); ?></h2>
        
        <p><?php _e('CSS selectors tell the tour which elements to scroll to and highlight. Here are common patterns:', 'erm-audio-tours'); ?></p>
        
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Selector Type', 'erm-audio-tours'); ?></th>
                    <th><?php _e('Example', 'erm-audio-tours'); ?></th>
                    <th><?php _e('Selects', 'erm-audio-tours'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('ID', 'erm-audio-tours'); ?></td>
                    <td><code>#hero-section</code></td>
                    <td><?php _e('Element with id="hero-section"', 'erm-audio-tours'); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Class', 'erm-audio-tours'); ?></td>
                    <td><code>.services-grid</code></td>
                    <td><?php _e('Elements with class="services-grid"', 'erm-audio-tours'); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Tag', 'erm-audio-tours'); ?></td>
                    <td><code>section</code></td>
                    <td><?php _e('All &lt;section&gt; elements', 'erm-audio-tours'); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Attribute Contains', 'erm-audio-tours'); ?></td>
                    <td><code>[class*="pricing"]</code></td>
                    <td><?php _e('Elements with "pricing" in class', 'erm-audio-tours'); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Data Attribute', 'erm-audio-tours'); ?></td>
                    <td><code>[data-section="about"]</code></td>
                    <td><?php _e('Elements with data-section="about"', 'erm-audio-tours'); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Multiple Options', 'erm-audio-tours'); ?></td>
                    <td><code>.pricing, #plans</code></td>
                    <td><?php _e('First element matching either', 'erm-audio-tours'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="erm-at-help-section">
        <h2><?php _e('Keyboard Shortcuts', 'erm-audio-tours'); ?></h2>
        
        <p><?php _e('When a tour is active, users can use these keyboard shortcuts:', 'erm-audio-tours'); ?></p>
        
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Key', 'erm-audio-tours'); ?></th>
                    <th><?php _e('Action', 'erm-audio-tours'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><kbd>Space</kbd> <?php _e('or', 'erm-audio-tours'); ?> <kbd>K</kbd></td>
                    <td><?php _e('Play / Pause', 'erm-audio-tours'); ?></td>
                </tr>
                <tr>
                    <td><kbd>←</kbd> <?php _e('or', 'erm-audio-tours'); ?> <kbd>J</kbd></td>
                    <td><?php _e('Previous Step', 'erm-audio-tours'); ?></td>
                </tr>
                <tr>
                    <td><kbd>→</kbd> <?php _e('or', 'erm-audio-tours'); ?> <kbd>L</kbd></td>
                    <td><?php _e('Next Step', 'erm-audio-tours'); ?></td>
                </tr>
                <tr>
                    <td><kbd>Escape</kbd></td>
                    <td><?php _e('Exit Tour', 'erm-audio-tours'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="erm-at-help-section">
        <h2><?php _e('Troubleshooting', 'erm-audio-tours'); ?></h2>
        
        <h3><?php _e('Tour not appearing?', 'erm-audio-tours'); ?></h3>
        <ul>
            <li><?php _e('Make sure the tour is enabled (check the "Enable this tour" checkbox)', 'erm-audio-tours'); ?></li>
            <li><?php _e('Verify the Target Page URL exactly matches your page\'s URL', 'erm-audio-tours'); ?></li>
            <li><?php _e('Check for JavaScript errors in the browser console', 'erm-audio-tours'); ?></li>
            <li><?php _e('Clear any caching plugins', 'erm-audio-tours'); ?></li>
        </ul>
        
        <h3><?php _e('Elements not scrolling/highlighting?', 'erm-audio-tours'); ?></h3>
        <ul>
            <li><?php _e('Verify your CSS selectors are correct using browser DevTools', 'erm-audio-tours'); ?></li>
            <li><?php _e('Check that elements exist when the page loads (not dynamically added later)', 'erm-audio-tours'); ?></li>
            <li><?php _e('Try using simpler, more specific selectors', 'erm-audio-tours'); ?></li>
        </ul>
        
        <h3><?php _e('Audio not playing?', 'erm-audio-tours'); ?></h3>
        <ul>
            <li><?php _e('Check that the audio file URL is correct and accessible', 'erm-audio-tours'); ?></li>
            <li><?php _e('Verify the audio file is a supported format (MP3 recommended)', 'erm-audio-tours'); ?></li>
            <li><?php _e('Browser autoplay policies may require user interaction first - the tour handles this', 'erm-audio-tours'); ?></li>
        </ul>
    </div>
    
    <div class="erm-at-help-section">
        <h2><?php _e('Need More Help?', 'erm-audio-tours'); ?></h2>
        <p>
            <?php _e('Contact us at', 'erm-audio-tours'); ?> 
            <a href="mailto:support@eliteresultsmarketing.com">support@eliteresultsmarketing.com</a>
        </p>
        <p>
            <a href="https://www.eliteresultsmarketing.com" target="_blank" class="button button-primary">
                <?php _e('Visit Elite Results Marketing', 'erm-audio-tours'); ?>
            </a>
        </p>
    </div>
</div>

<style>
.erm-at-help-page {
    max-width: 900px;
}

.erm-at-help-section {
    background: #fff;
    padding: 25px 30px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.erm-at-help-section h2 {
    margin-top: 0;
    padding-bottom: 15px;
    border-bottom: 2px solid #0066FF;
    color: #1e1e1e;
}

.erm-at-help-section h3 {
    color: #0066FF;
    margin-top: 25px;
}

.erm-at-help-section ol,
.erm-at-help-section ul {
    line-height: 1.8;
}

.erm-at-help-section code {
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 13px;
}

.erm-at-help-section kbd {
    background: #1e1e1e;
    color: #fff;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-family: monospace;
}

.erm-at-help-section table {
    margin-top: 15px;
}

.erm-at-help-section table th,
.erm-at-help-section table td {
    padding: 12px 15px;
    text-align: left;
}

.erm-at-help-section table code {
    background: #e0f2fe;
    color: #0066FF;
}
</style>
