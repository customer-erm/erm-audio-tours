# ERM Audio Tours - Plugin Documentation

**Version:** 2.0.0
**Author:** Elite Results Marketing
**License:** GPL-2.0+

---

## Table of Contents

1. [Overview](#overview)
2. [Installation & Setup](#installation--setup)
3. [User Guide](#user-guide)
4. [Developer Reference](#developer-reference)
5. [LLM Context for Development](#llm-context-for-development)

---

## Overview

### What This Plugin Does

ERM Audio Tours enables WordPress site owners to create immersive, audio-guided tours for any website. It combines visual tour building with element highlighting, audio playback, and navigation controls to guide users through designated page sections while narrating via audio.

### Key Features

- **Visual Tour Builder** - Create tours directly in WordPress admin with an intuitive interface
- **Element Picker** - Click-to-select any page element without writing CSS selectors
- **Audio Support** - Single master audio file with timestamps OR individual audio per step
- **10+ Highlight Effects** - Outline, pulse, spotlight, zoom, underline, border-draw, fill, bounce, shake, arrow
- **Responsive UI** - Mobile-friendly navigation with collapsible tray
- **Import/Export** - Share tours between sites via JSON
- **Keyboard Shortcuts** - Space (play/pause), arrows (navigate), Escape (exit)

### Use Cases

- Product walkthroughs and onboarding
- Homepage tours for new visitors
- Training content and tutorials
- Sales demos and case study tours
- Event page guides

---

## Installation & Setup

### Requirements

- WordPress 5.0+
- PHP 7.0+
- Modern browser with HTML5 audio support

### Installation Steps

1. **Upload Plugin**
   - Upload the `erm-audio-tours` folder to `/wp-content/plugins/`
   - OR upload as ZIP via Plugins → Add New → Upload

2. **Activate**
   - Go to Plugins in WordPress admin
   - Click "Activate" on ERM Audio Tours
   - Menu item "ERM Audio Tours" appears in admin

3. **Initial Setup**
   - Plugin automatically creates a demo tour on first activation
   - Creates `/uploads/erm-audio-tours/` directory for audio files

### Configuration

**Global Settings** (ERM Audio Tours → Settings)
- Default primary color for new tours
- Default secondary color for new tours
- Import previously exported tours

---

## User Guide

### Creating a New Tour

1. **Go to ERM Audio Tours → Add New**

2. **Configure Tour Settings:**
   - Enter target page URL (or click "Use Homepage")
   - Enable/disable the tour
   - Set button appearance delay (milliseconds)
   - Select audio mode (single file or per-step)
   - Add button text, modal title, description

3. **Add Tour Steps:**
   - Click "Add New Step"
   - Enter step name and select icon (80+ options)
   - Choose target element via CSS selector or Element Picker
   - Set audio timestamps (start/end in seconds)
   - Select highlight effect style
   - Add optional description
   - Configure sub-highlights for timeline markers

4. **Customize Appearance:**
   - Set primary and secondary colors
   - Configure navigation position (left/right)
   - Set button position and offset
   - Choose when navigation appears

5. **Upload Audio:**
   - Upload master audio file (single mode) or individual files
   - Use audio player timeline for precise timestamps

6. **Preview & Publish:**
   - Click "Preview Tour" to test on target page
   - Save and check "Enable this tour" to activate

### Using the Element Picker

1. Click "Open Element Picker" or "Pick Element" next to a step
2. Target page opens with picker interface
3. Hover over elements to see highlights
4. Click element to select
5. Choose which step to apply it to
6. CSS selector is automatically saved

### Audio Modes

**Single Master Audio (Recommended)**
- One continuous audio file
- Each step has start/end timestamps
- Seamless playback across steps

**Per-Step Audio**
- Individual audio file for each step
- Better for modular content
- More files to manage

### Highlight Effects

| Effect | Description |
|--------|-------------|
| Outline | Glow effect around element |
| Pulse | Pulsing animation |
| Spotlight | Dark overlay with element highlighted |
| Zoom | Zoom in effect |
| Underline | Underline animation |
| Border-draw | Border drawing animation |
| Fill | Fill overlay effect |
| Bounce | Bounce animation |
| Shake | Shake animation |
| Arrow | Arrow pointer to element |

### Troubleshooting

**Tour Not Appearing:**
- Check "Enable this tour" checkbox
- Verify Target Page URL matches current page exactly
- Check browser console for JavaScript errors
- Disable caching plugins temporarily

**Elements Not Highlighting:**
- Verify CSS selector using browser DevTools
- Confirm element exists on page load (not dynamically added)
- Try simpler/more specific selectors

**Audio Not Playing:**
- Verify audio file URL is correct
- Check file format (MP3 recommended)
- Check browser autoplay policies

---

## Developer Reference

### File Structure

```
erm-audio-tours/
├── erm-audio-tours.php                 # Main plugin file
├── includes/
│   ├── class-erm-at-admin.php          # Admin UI & menu
│   ├── class-erm-at-builder.php        # Tour builder metaboxes
│   ├── class-erm-at-frontend.php       # Frontend tour display
│   └── class-erm-at-ajax.php           # AJAX handlers
├── templates/
│   ├── admin/
│   │   ├── settings.php                # Global settings
│   │   └── help.php                    # Help documentation
│   └── frontend/
│       ├── tour-ui.php                 # Tour HTML structure
│       └── picker-ui.php               # Element picker
└── assets/
    ├── css/
    │   ├── admin.css                   # Admin styling
    │   ├── tour.css                    # Frontend tour styles
    │   └── picker.css                  # Element picker styles
    └── js/
        ├── admin.js                    # Admin interactions
        ├── tour.js                     # Tour functionality
        └── picker.js                   # Element picker logic
```

### Custom Post Type

**Post Type:** `erm_audio_tour`
- Capabilities: Standard post (create, read, update, delete)
- Supports: Title only
- Public: False (admin only)

### Post Meta Fields

All tour data stored with `_erm_at_` prefix:

| Meta Key | Type | Description |
|----------|------|-------------|
| `_erm_at_target_page` | string | URL where tour appears |
| `_erm_at_enabled` | bool | Is tour active? |
| `_erm_at_button_delay` | int | Milliseconds before button shows |
| `_erm_at_audio_mode` | string | 'single' or 'per_step' |
| `_erm_at_master_audio` | string | URL of master audio file |
| `_erm_at_button_text` | string | Launch button label |
| `_erm_at_button_position` | string | Position (bottom-right, etc.) |
| `_erm_at_nav_position` | string | 'left' or 'right' |
| `_erm_at_primary_color` | string | Hex color |
| `_erm_at_secondary_color` | string | Hex color |
| `_erm_at_steps` | array | Serialized steps data |

### Steps Data Structure

```php
[
    'id' => 'step_' . uniqid(),
    'name' => 'Step name',
    'icon' => 'icon-name',
    'selector' => '.css-selector',
    'selector_name' => 'Human-readable description',
    'audio_url' => 'https://example.com/audio.mp3',
    'start_time' => 0.0,
    'end_time' => 15.0,
    'scroll_to' => true,
    'highlight_style' => 'outline',
    'description' => 'Optional description',
    'sub_highlights' => [
        [
            'timestamp' => 5.0,
            'selector' => '.subelement',
            'highlight_style' => 'pulse'
        ]
    ]
]
```

### Key Classes

| Class | Purpose |
|-------|---------|
| `ERM_Audio_Tours` | Main plugin class (singleton) |
| `ERM_AT_Admin` | Admin interface and menus |
| `ERM_AT_Builder` | Tour creation metaboxes |
| `ERM_AT_Frontend` | Frontend rendering |
| `ERM_AT_Ajax` | AJAX request handlers |

### AJAX Endpoints

| Action | Purpose |
|--------|---------|
| `erm_at_save_selector` | Save element selector from picker |
| `erm_at_duplicate_tour` | Create copy of tour |
| `erm_at_export_tour` | Export tour as JSON |
| `erm_at_import_tour` | Import tour from JSON |
| `erm_at_get_page_content` | Fetch target page HTML |
| `erm_at_get_tour_steps` | Return steps for dropdown |

### WordPress Hooks Used

**Admin:**
- `admin_enqueue_scripts` - Load admin assets
- `admin_menu` - Add submenu pages
- `add_meta_boxes` - Register metaboxes
- `save_post_erm_audio_tour` - Save tour data

**Frontend:**
- `wp_enqueue_scripts` - Load frontend assets
- `wp_footer` - Render tour UI
- `wp_head` - Add picker mode styles

---

## LLM Context for Development

### Quick Reference for AI Assistants

When working with this plugin, here's what you need to know:

**Architecture:**
- Singleton pattern main class in `erm-audio-tours.php`
- Modular class structure in `/includes/`
- Templates in `/templates/` (admin and frontend)
- Assets in `/assets/` (css and js)

**Key Files to Edit:**

| Task | File(s) |
|------|---------|
| Add new highlight effect | `assets/css/tour.css`, `assets/js/tour.js` |
| Modify tour builder UI | `includes/class-erm-at-builder.php` |
| Change frontend rendering | `includes/class-erm-at-frontend.php`, `templates/frontend/tour-ui.php` |
| Add new AJAX endpoint | `includes/class-erm-at-ajax.php` |
| Modify admin settings | `includes/class-erm-at-admin.php`, `templates/admin/settings.php` |

**Data Flow:**
1. Admin creates tour → saved as custom post type with meta
2. Frontend loads → `ERM_AT_Frontend::get_tour_for_current_page()` finds matching tour
3. Tour config passed to JS via `wp_localize_script()`
4. JavaScript handles all UI interactions and audio playback

**Common Modifications:**

*Adding a new highlight effect:*
1. Add CSS animation in `tour.css`
2. Add option to highlight style dropdown in `class-erm-at-builder.php`
3. Handle in `tour.js` highlight application logic

*Adding a new tour setting:*
1. Add meta box field in `class-erm-at-builder.php`
2. Handle save in `save_meta_boxes()` method
3. Include in `get_tour_config()` in `class-erm-at-frontend.php`
4. Use in JavaScript as needed

**Security Patterns Used:**
- Nonce verification: `wp_verify_nonce()`
- Capability checks: `current_user_can('edit_posts')`
- Sanitization: `sanitize_text_field()`, `esc_url_raw()`, etc.
- Direct access prevention: `if (!defined('ABSPATH')) exit;`

**Testing a Change:**
1. Create or edit a tour in admin
2. Set target page and enable tour
3. Visit target page to see changes
4. Use browser DevTools console for JS debugging

### Prompt Template for Development Tasks

```
I need to modify the ERM Audio Tours WordPress plugin.

TASK: [describe what you want to change]

CONTEXT:
- Main plugin file: erm-audio-tours.php
- Admin class: includes/class-erm-at-admin.php
- Builder class: includes/class-erm-at-builder.php
- Frontend class: includes/class-erm-at-frontend.php
- AJAX class: includes/class-erm-at-ajax.php
- Frontend JS: assets/js/tour.js
- Admin JS: assets/js/admin.js

The plugin uses a custom post type 'erm_audio_tour' with meta fields
prefixed '_erm_at_'. Tours are matched to pages by URL and rendered
via wp_footer hook.

Please provide the specific code changes needed.
```

---

## Support

**Author:** Elite Results Marketing
**Email:** support@eliteresultsmarketing.com
**Website:** https://www.eliteresultsmarketing.com
