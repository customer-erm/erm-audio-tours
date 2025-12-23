/**
 * Audio Tour Builder - Admin JavaScript
 */

(function($) {
    'use strict';
    
    var ERMATAdmin = {
        
        init: function() {
            this.bindEvents();
            this.initSortable();
            this.initIconPickers();
            this.updateStepNumbers();
        },
        
        bindEvents: function() {
            var self = this;
            
            // Use homepage button
            $(document).on('click', '#erm_at_use_homepage', function() {
                var homeUrl = window.location.origin + '/';
                $('#erm_at_target_page').val(homeUrl);
            });
            
            // Audio mode toggle
            $(document).on('change', 'input[name="erm_at_audio_mode"]', function() {
                var mode = $(this).val();
                if (mode === 'single') {
                    $('.erm-at-master-audio').show();
                    $('.erm-at-audio-timestamps').show();
                    $('.erm-at-step-audio').hide();
                } else {
                    $('.erm-at-master-audio').hide();
                    $('.erm-at-audio-timestamps').hide();
                    $('.erm-at-step-audio').show();
                }
            });
            
            // Audio upload buttons
            $(document).on('click', '.erm-at-upload-audio', function(e) {
                e.preventDefault();
                var button = $(this);
                var targetId = button.data('target');
                var targetClass = button.data('target-class');
                
                self.openMediaUploader(button, targetId, targetClass);
            });
            
            // Remove audio buttons
            $(document).on('click', '.erm-at-remove-audio', function(e) {
                e.preventDefault();
                var button = $(this);
                var wrapper = button.closest('.erm-at-audio-upload');
                
                wrapper.find('input[type="hidden"], input[type="text"]').val('');
                wrapper.siblings('.erm-at-audio-preview').remove();
                button.hide();
            });
            
            // Step toggle
            $(document).on('click', '.erm-at-step-toggle, .erm-at-step-header', function(e) {
                if ($(e.target).closest('.erm-at-step-delete, .erm-at-step-drag').length) {
                    return;
                }
                var item = $(this).closest('.erm-at-step-item');
                item.toggleClass('collapsed');
            });
            
            // Step delete
            $(document).on('click', '.erm-at-step-delete', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (confirm(ermAtAdmin.strings.confirmDelete)) {
                    $(this).closest('.erm-at-step-item').slideUp(200, function() {
                        $(this).remove();
                        self.updateStepNumbers();
                    });
                }
            });
            
            // Add new step
            $(document).on('click', '#erm-at-add-step', function(e) {
                e.preventDefault();
                self.addNewStep();
            });
            
            // Collapse/Expand all
            $(document).on('click', '#erm-at-collapse-all', function() {
                $('.erm-at-step-item').addClass('collapsed');
            });
            
            $(document).on('click', '#erm-at-expand-all', function() {
                $('.erm-at-step-item').removeClass('collapsed');
            });
            
            // Step name input updates title
            $(document).on('input', '.erm-at-step-name-input', function() {
                var name = $(this).val() || 'New Step';
                $(this).closest('.erm-at-step-item').find('.erm-at-step-title').text(name);
            });
            
            // Color picker sync
            $(document).on('input', 'input[type="color"]', function() {
                $(this).siblings('code').text($(this).val());
            });
            
            // Pick element button
            $(document).on('click', '.erm-at-pick-element', function(e) {
                e.preventDefault();
                self.openElementPicker($(this));
            });
            
            // Duplicate tour
            $(document).on('click', '#erm-at-duplicate-tour', function(e) {
                e.preventDefault();
                self.duplicateTour();
            });
            
            // Export tour
            $(document).on('click', '#erm-at-export-tour', function(e) {
                e.preventDefault();
                self.exportTour();
            });
            
            // Close icon picker when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.erm-at-icon-picker').length) {
                    $('.erm-at-icon-picker').removeClass('open');
                }
            });
        },
        
        initSortable: function() {
            var self = this;
            
            $('#erm-at-steps-list').sortable({
                handle: '.erm-at-step-drag',
                placeholder: 'erm-at-step-item ui-sortable-placeholder',
                tolerance: 'pointer',
                update: function() {
                    self.updateStepNumbers();
                    self.updateInputNames();
                }
            });
        },
        
        initIconPickers: function() {
            var self = this;
            
            // Toggle icon picker
            $(document).on('click', '.erm-at-icon-picker-btn', function(e) {
                e.stopPropagation();
                var picker = $(this).closest('.erm-at-icon-picker');
                $('.erm-at-icon-picker').not(picker).removeClass('open');
                picker.toggleClass('open');
            });
            
            // Select icon
            $(document).on('click', '.erm-at-icon-option', function(e) {
                e.preventDefault();
                var option = $(this);
                var picker = option.closest('.erm-at-icon-picker');
                var icon = option.data('icon');
                var iconSvg = option.html();
                var iconName = option.attr('title');
                
                picker.find('.erm-at-icon-option').removeClass('selected');
                option.addClass('selected');
                
                picker.find('.erm-at-icon-value').val(icon);
                picker.find('.erm-at-icon-preview').html(iconSvg);
                picker.find('.erm-at-icon-name').text(iconName);
                
                // Update step header icon
                picker.closest('.erm-at-step-item').find('.erm-at-step-icon').html(iconSvg);
                
                picker.removeClass('open');
            });
            
            // Icon search
            $(document).on('input', '.erm-at-icon-search', function() {
                var search = $(this).val().toLowerCase();
                var grid = $(this).siblings('.erm-at-icon-grid');
                
                grid.find('.erm-at-icon-option').each(function() {
                    var name = $(this).attr('title').toLowerCase();
                    var icon = $(this).data('icon').toLowerCase();
                    $(this).toggle(name.includes(search) || icon.includes(search));
                });
            });
        },
        
        openMediaUploader: function(button, targetId, targetClass) {
            var mediaUploader = wp.media({
                title: ermAtAdmin.strings.uploadAudio,
                button: { text: ermAtAdmin.strings.useAudio },
                library: { type: 'audio' },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                var wrapper = button.closest('.erm-at-audio-upload');
                
                if (targetId) {
                    $('#' + targetId).val(attachment.url);
                    $('#' + targetId + '_display').val(attachment.url);
                } else if (targetClass) {
                    wrapper.find('.' + targetClass).val(attachment.url);
                    wrapper.find('.erm-at-audio-display').val(attachment.url);
                }
                
                // Show preview
                wrapper.siblings('.erm-at-audio-preview').remove();
                wrapper.after('<div class="erm-at-audio-preview"><audio controls src="' + attachment.url + '"></audio></div>');
                
                // Show remove button
                wrapper.find('.erm-at-remove-audio').show();
            });
            
            mediaUploader.open();
        },
        
        addNewStep: function() {
            var template = $('#erm-at-step-template').html();
            var index = $('.erm-at-step-item').length;
            var stepId = 'step_' + Date.now();
            
            // Replace placeholders
            template = template.replace(/\{\{INDEX\}\}/g, index);
            template = template.replace(/\{\{PREFIX\}\}/g, 'erm_at_steps[' + index + ']');
            template = template.replace(/\{\{NUM\}\}/g, index + 1);
            template = template.replace(/step_\{\{ID\}\}/g, stepId);
            
            var $newStep = $(template);
            $newStep.find('input[name$="[id]"]').val(stepId);
            
            $('#erm-at-steps-list').append($newStep);
            
            // Scroll to new step and open it
            $newStep.removeClass('collapsed');
            $('html, body').animate({
                scrollTop: $newStep.offset().top - 100
            }, 300);
            
            // Focus name input
            $newStep.find('.erm-at-step-name-input').focus();
            
            this.updateStepNumbers();
        },
        
        updateStepNumbers: function() {
            $('.erm-at-step-item').each(function(index) {
                $(this).find('.erm-at-step-number').text(index + 1);
                $(this).attr('data-index', index);
            });
            
            $('#erm-at-step-count').text($('.erm-at-step-item').length);
        },
        
        updateInputNames: function() {
            $('.erm-at-step-item').each(function(index) {
                $(this).find('input, select, textarea').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/erm_at_steps\[\d+\]/, 'erm_at_steps[' + index + ']');
                        $(this).attr('name', name);
                    }
                });
            });
        },
        
        openElementPicker: function(button) {
            var targetUrl = $('#erm_at_target_page').val();
            
            if (!targetUrl) {
                alert('Please set a target page URL first.');
                return;
            }
            
            var postId = $('#post_ID').val();
            var stepItem = button.closest('.erm-at-step-item');
            var stepIndex = stepItem.data('index');
            
            // Store which step we're picking for
            window.ermAtPickerStep = {
                index: stepIndex,
                item: stepItem
            };
            
            // Open picker in new window
            var pickerUrl = targetUrl + (targetUrl.includes('?') ? '&' : '?') + 'erm_at_picker=' + postId + '&step=' + stepIndex;
            window.open(pickerUrl, 'erm_at_picker', 'width=1200,height=800');
        },
        
        duplicateTour: function() {
            var postId = $('#post_ID').val();
            
            $.ajax({
                url: ermAtAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'erm_at_duplicate_tour',
                    nonce: ermAtAdmin.nonce,
                    tour_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        if (confirm('Tour duplicated! Would you like to edit the new tour?')) {
                            window.location.href = response.data.edit_url;
                        }
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        },
        
        exportTour: function() {
            var postId = $('#post_ID').val();
            
            $.ajax({
                url: ermAtAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'erm_at_export_tour',
                    nonce: ermAtAdmin.nonce,
                    tour_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        var dataStr = JSON.stringify(response.data, null, 2);
                        var dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
                        
                        var exportName = 'erm-audio-tour-' + postId + '.json';
                        
                        var linkElement = document.createElement('a');
                        linkElement.setAttribute('href', dataUri);
                        linkElement.setAttribute('download', exportName);
                        linkElement.click();
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        },
        
        // Called from picker window
        receiveSelector: function(data) {
            var stepItem = null;
            
            // First try to find by step ID if provided
            if (data.stepId) {
                $('.erm-at-step-item').each(function() {
                    var itemStepId = $(this).find('input[name$="[id]"]').val();
                    if (itemStepId === data.stepId) {
                        stepItem = $(this);
                        return false; // break
                    }
                });
            }
            
            // Fallback to the step that was being edited
            if (!stepItem && window.ermAtPickerStep) {
                stepItem = window.ermAtPickerStep.item;
            }
            
            if (stepItem) {
                stepItem.find('.erm-at-selector-input').val(data.selector);
                stepItem.find('.erm-at-selector-name-input').val(data.selectorName);
                stepItem.find('.erm-at-step-selector-preview').text(data.selectorName || data.selector);
                
                // Highlight the updated step briefly
                stepItem.css('background', '#e0f7fa');
                setTimeout(function() {
                    stepItem.css('background', '');
                }, 1500);
            }
        },
        
        // =========================================
        // Timeline Highlights
        // =========================================
        
        initTimeline: function() {
            var self = this;
            
            // Store audio elements for each step
            this.timelineAudios = {};
            
            // Initialize timeline players
            $('.erm-at-timeline-player').each(function() {
                self.initTimelinePlayer($(this));
            });
            
            // Add marker button
            $(document).on('click', '.erm-at-add-marker', function() {
                var player = $(this).closest('.erm-at-timeline-player');
                var stepItem = $(this).closest('.erm-at-step-item');
                self.addSubHighlight(player, stepItem);
            });
            
            // Delete sub-highlight
            $(document).on('click', '.erm-at-sh-delete', function() {
                $(this).closest('.erm-at-sub-highlight').remove();
            });
            
            // Update markers when sub-highlights change
            $(document).on('change', '.erm-at-sh-timestamp', function() {
                var stepItem = $(this).closest('.erm-at-step-item');
                self.updateTimelineMarkers(stepItem);
            });
        },
        
        initTimelinePlayer: function($player) {
            var self = this;
            var stepItem = $player.closest('.erm-at-step-item');
            var stepIndex = stepItem.data('index');
            
            // Get audio URL (step audio or master audio)
            var audioUrl = stepItem.find('.erm-at-audio-url').val();
            if (!audioUrl) {
                audioUrl = $('input[name="erm_at_master_audio"]').val();
            }
            
            if (!audioUrl) {
                $player.find('.erm-at-timeline-bar').css('opacity', '0.5');
                $player.find('.erm-at-add-marker').prop('disabled', true).text('Upload audio to use timeline');
                return;
            }
            
            // Create audio element
            var audio = new Audio(audioUrl);
            this.timelineAudios[stepIndex] = audio;
            
            var $playBtn = $player.find('.erm-at-timeline-play');
            var $current = $player.find('.erm-at-timeline-current');
            var $duration = $player.find('.erm-at-timeline-duration');
            var $progress = $player.find('.erm-at-timeline-progress');
            var $bar = $player.find('.erm-at-timeline-bar');
            
            // Audio loaded
            audio.addEventListener('loadedmetadata', function() {
                $duration.text(self.formatTime(audio.duration));
                self.updateTimelineMarkers(stepItem);
            });
            
            // Time update
            audio.addEventListener('timeupdate', function() {
                $current.text(self.formatTime(audio.currentTime));
                var progress = (audio.currentTime / audio.duration) * 100;
                $progress.css('width', progress + '%');
            });
            
            // Ended
            audio.addEventListener('ended', function() {
                $playBtn.find('.dashicons').removeClass('dashicons-controls-pause').addClass('dashicons-controls-play');
            });
            
            // Play/Pause button
            $playBtn.on('click', function() {
                if (audio.paused) {
                    // Pause all other audios first
                    Object.values(self.timelineAudios).forEach(function(a) {
                        if (a !== audio) a.pause();
                    });
                    audio.play();
                    $(this).find('.dashicons').removeClass('dashicons-controls-play').addClass('dashicons-controls-pause');
                } else {
                    audio.pause();
                    $(this).find('.dashicons').removeClass('dashicons-controls-pause').addClass('dashicons-controls-play');
                }
            });
            
            // Click on timeline to seek
            $bar.on('click', function(e) {
                var rect = this.getBoundingClientRect();
                var percent = (e.clientX - rect.left) / rect.width;
                audio.currentTime = percent * audio.duration;
            });
            
            // Initialize markers
            this.updateTimelineMarkers(stepItem);
        },
        
        updateTimelineMarkers: function(stepItem) {
            var $player = stepItem.find('.erm-at-timeline-player');
            var $markers = $player.find('.erm-at-timeline-markers');
            var stepIndex = stepItem.data('index');
            var audio = this.timelineAudios[stepIndex];
            
            $markers.empty();
            
            if (!audio || !audio.duration) return;
            
            stepItem.find('.erm-at-sub-highlight').each(function() {
                var timestamp = parseFloat($(this).find('.erm-at-sh-timestamp').val()) || 0;
                var percent = (timestamp / audio.duration) * 100;
                
                if (percent >= 0 && percent <= 100) {
                    var $marker = $('<div class="erm-at-timeline-marker"></div>')
                        .css('left', percent + '%')
                        .attr('title', 'Highlight at ' + timestamp.toFixed(1) + 's');
                    $markers.append($marker);
                }
            });
        },
        
        addSubHighlight: function($player, stepItem) {
            var stepIndex = stepItem.data('index');
            var audio = this.timelineAudios[stepIndex];
            var currentTime = audio ? audio.currentTime : 0;
            
            var $container = stepItem.find('.erm-at-sub-highlights');
            var prefix = $container.data('prefix');
            var shIndex = $container.find('.erm-at-sub-highlight').length;
            
            var html = '<div class="erm-at-sub-highlight" data-index="' + shIndex + '">' +
                '<div class="erm-at-sh-header">' +
                    '<span class="erm-at-sh-time">' + this.formatTime(currentTime) + '</span>' +
                    '<span class="erm-at-sh-name">Select element...</span>' +
                    '<button type="button" class="erm-at-sh-delete" title="Remove">' +
                        '<span class="dashicons dashicons-no-alt"></span>' +
                    '</button>' +
                '</div>' +
                '<div class="erm-at-sh-fields">' +
                    '<input type="hidden" name="' + prefix + '[sub_highlights][' + shIndex + '][timestamp]" value="' + currentTime.toFixed(2) + '" class="erm-at-sh-timestamp">' +
                    '<div class="erm-at-sh-row">' +
                        '<div class="erm-at-sh-field">' +
                            '<label>Element</label>' +
                            '<input type="text" name="' + prefix + '[sub_highlights][' + shIndex + '][selector]" class="erm-at-sh-selector" placeholder="#element-id or .class">' +
                            '<input type="hidden" name="' + prefix + '[sub_highlights][' + shIndex + '][selector_name]" class="erm-at-sh-selector-name">' +
                        '</div>' +
                        '<div class="erm-at-sh-field">' +
                            '<label>Effect</label>' +
                            '<select name="' + prefix + '[sub_highlights][' + shIndex + '][highlight_style]" class="erm-at-sh-style">' +
                                '<option value="outline">Outline Glow</option>' +
                                '<option value="pulse">Pulse</option>' +
                                '<option value="spotlight">Spotlight</option>' +
                                '<option value="zoom">Zoom</option>' +
                                '<option value="underline">Underline</option>' +
                                '<option value="border-draw">Border Draw</option>' +
                                '<option value="fill">Fill Overlay</option>' +
                                '<option value="bounce">Bounce</option>' +
                                '<option value="shake">Shake</option>' +
                                '<option value="arrow">Arrow Pointer</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
            
            $container.append(html);
            this.updateTimelineMarkers(stepItem);
        },
        
        formatTime: function(seconds) {
            var mins = Math.floor(seconds / 60);
            var secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        ERMATAdmin.init();
        ERMATAdmin.initTimeline();
    });
    
    // Expose for picker communication
    window.ERMATAdmin = ERMATAdmin;
    
})(jQuery);
