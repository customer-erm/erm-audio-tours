/**
 * Audio Tour Builder - Element Picker
 * Allows visual selection of elements from the target page
 */

(function() {
    'use strict';
    
    class ElementPicker {
        constructor(config) {
            this.config = config;
            this.selectedElement = null;
            this.selectedSelector = '';
            this.hoveredElement = null;
            this.isActive = true;
            this.ignoreElements = [
                '.erm-at-picker-toolbar',
                '.erm-at-picker-info',
                '.erm-at-picker-toast'
            ];
            
            this.init();
        }
        
        init() {
            document.body.classList.add('erm-at-picker-active');
            this.createUI();
            this.bindEvents();
        }
        
        createUI() {
            // Toolbar
            const toolbar = document.createElement('div');
            toolbar.className = 'erm-at-picker-toolbar';
            toolbar.innerHTML = `
                <div class="erm-at-picker-title">
                    <div class="erm-at-picker-logo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polygon points="10,8 16,12 10,16" fill="currentColor" stroke="none"></polygon>
                        </svg>
                    </div>
                    <h3>Audio Tour Builder - Element Picker</h3>
                </div>
                <div class="erm-at-picker-instructions">
                    <strong>Click</strong> on any element to select it for your tour step
                </div>
                <div class="erm-at-picker-actions">
                    <button class="erm-at-picker-btn erm-at-picker-btn-secondary" id="erm-at-picker-cancel">
                        Cancel
                    </button>
                </div>
            `;
            document.body.insertBefore(toolbar, document.body.firstChild);
            
            // Info panel
            const infoPanel = document.createElement('div');
            infoPanel.className = 'erm-at-picker-info';
            infoPanel.id = 'erm-at-picker-info';
            infoPanel.innerHTML = `
                <div class="erm-at-picker-info-header">
                    <h4>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Element Selected
                    </h4>
                    <button class="erm-at-picker-info-close" id="erm-at-picker-info-close">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                
                <div class="erm-at-picker-step-select">
                    <label>Apply to Step:</label>
                    <select id="erm-at-picker-step-dropdown">
                        <option value="">Select a step...</option>
                    </select>
                </div>
                
                <div class="erm-at-picker-selector-row">
                    <input type="text" class="erm-at-picker-selector-input" id="erm-at-picker-selector" readonly>
                    <button class="erm-at-picker-copy-btn" id="erm-at-picker-copy">Copy</button>
                </div>
                
                <div class="erm-at-picker-element-info" id="erm-at-picker-element-info">
                    <span>Tag: <strong id="erm-at-picker-tag">-</strong></span>
                    <span>ID: <strong id="erm-at-picker-id">-</strong></span>
                    <span>Classes: <strong id="erm-at-picker-classes">-</strong></span>
                    <span>Size: <strong id="erm-at-picker-size">-</strong></span>
                </div>
                
                <div class="erm-at-picker-info-actions">
                    <button class="erm-at-picker-btn erm-at-picker-btn-secondary" id="erm-at-picker-reselect">
                        Pick Different Element
                    </button>
                    <button class="erm-at-picker-btn erm-at-picker-btn-primary" id="erm-at-picker-apply">
                        Apply to Step
                    </button>
                </div>
            `;
            document.body.appendChild(infoPanel);
            
            // Toast
            const toast = document.createElement('div');
            toast.className = 'erm-at-picker-toast';
            toast.id = 'erm-at-picker-toast';
            document.body.appendChild(toast);
            
            // Cache elements
            this.elements = {
                toolbar: toolbar,
                infoPanel: infoPanel,
                selectorInput: document.getElementById('erm-at-picker-selector'),
                stepDropdown: document.getElementById('erm-at-picker-step-dropdown'),
                toast: toast
            };
            
            // Load steps into dropdown
            this.loadSteps();
        }
        
        loadSteps() {
            // Fetch steps from admin via AJAX
            const urlParams = new URLSearchParams(window.location.search);
            const stepIndex = urlParams.get('step');
            
            fetch(this.config.adminUrl + 'admin-ajax.php?action=erm_at_get_tour_steps&tour_id=' + this.config.tourId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.steps) {
                        data.data.steps.forEach((step, index) => {
                            const option = document.createElement('option');
                            option.value = step.id;
                            option.textContent = `Step ${index + 1}: ${step.name || 'Unnamed'}`;
                            if (stepIndex && parseInt(stepIndex) === index) {
                                option.selected = true;
                            }
                            this.elements.stepDropdown.appendChild(option);
                        });
                    }
                })
                .catch(() => {
                    // Fallback - just allow manual entry
                    console.log('Could not load steps');
                });
        }
        
        bindEvents() {
            // Mouse move - highlight elements
            document.addEventListener('mousemove', (e) => this.onMouseMove(e), true);
            
            // Click - select element
            document.addEventListener('click', (e) => this.onClick(e), true);
            
            // Cancel button
            document.getElementById('erm-at-picker-cancel').addEventListener('click', () => this.close());
            
            // Info panel close
            document.getElementById('erm-at-picker-info-close').addEventListener('click', () => {
                this.elements.infoPanel.classList.remove('visible');
                this.clearSelection();
            });
            
            // Copy button
            document.getElementById('erm-at-picker-copy').addEventListener('click', () => this.copySelector());
            
            // Reselect button
            document.getElementById('erm-at-picker-reselect').addEventListener('click', () => {
                this.elements.infoPanel.classList.remove('visible');
                this.clearSelection();
                this.isActive = true;
            });
            
            // Apply button
            document.getElementById('erm-at-picker-apply').addEventListener('click', () => this.applySelection());
            
            // Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (this.elements.infoPanel.classList.contains('visible')) {
                        this.elements.infoPanel.classList.remove('visible');
                        this.clearSelection();
                        this.isActive = true;
                    } else {
                        this.close();
                    }
                }
            });
        }
        
        onMouseMove(e) {
            if (!this.isActive) return;
            
            const target = e.target;
            
            // Ignore picker UI elements
            if (this.isPickerElement(target)) return;
            
            // Remove previous hover
            if (this.hoveredElement && this.hoveredElement !== target) {
                this.hoveredElement.classList.remove('erm-at-picker-hover');
                this.hoveredElement.removeAttribute('data-erm-at-tag');
            }
            
            // Add hover to new element
            if (target && target !== document.body && target !== document.documentElement) {
                target.classList.add('erm-at-picker-hover');
                target.setAttribute('data-erm-at-tag', this.getElementLabel(target));
                this.hoveredElement = target;
            }
        }
        
        onClick(e) {
            if (!this.isActive) return;
            
            const target = e.target;
            
            // Ignore picker UI elements
            if (this.isPickerElement(target)) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            // Clear hover
            if (this.hoveredElement) {
                this.hoveredElement.classList.remove('erm-at-picker-hover');
                this.hoveredElement.removeAttribute('data-erm-at-tag');
            }
            
            // Select element
            this.selectElement(target);
        }
        
        selectElement(element) {
            // Clear previous selection
            this.clearSelection();
            
            this.selectedElement = element;
            this.selectedSelector = this.generateSelector(element);
            
            // Add selected class
            element.classList.add('erm-at-picker-selected');
            
            // Update info panel
            this.updateInfoPanel(element);
            
            // Show info panel
            this.elements.infoPanel.classList.add('visible');
            
            // Pause hovering
            this.isActive = false;
        }
        
        clearSelection() {
            if (this.selectedElement) {
                this.selectedElement.classList.remove('erm-at-picker-selected');
                this.selectedElement = null;
                this.selectedSelector = '';
            }
        }
        
        generateSelector(element) {
            // Priority: ID > unique class > tag with nth-child
            
            // Try ID
            if (element.id) {
                return '#' + CSS.escape(element.id);
            }
            
            // Try unique class
            const classes = Array.from(element.classList).filter(c => 
                !c.startsWith('erm-at-') && 
                document.querySelectorAll('.' + CSS.escape(c)).length === 1
            );
            if (classes.length > 0) {
                return '.' + CSS.escape(classes[0]);
            }
            
            // Try class combination
            const allClasses = Array.from(element.classList).filter(c => !c.startsWith('erm-at-'));
            if (allClasses.length > 0) {
                const classSelector = '.' + allClasses.map(c => CSS.escape(c)).join('.');
                if (document.querySelectorAll(classSelector).length === 1) {
                    return classSelector;
                }
            }
            
            // Try attribute selectors for common patterns
            const dataAttrs = Array.from(element.attributes).filter(attr => 
                attr.name.startsWith('data-') && attr.value
            );
            for (const attr of dataAttrs) {
                const selector = `[${attr.name}="${CSS.escape(attr.value)}"]`;
                if (document.querySelectorAll(selector).length === 1) {
                    return selector;
                }
            }
            
            // Build path-based selector
            return this.buildPathSelector(element);
        }
        
        buildPathSelector(element) {
            const path = [];
            let current = element;
            
            while (current && current !== document.body) {
                let selector = current.tagName.toLowerCase();
                
                if (current.id) {
                    selector = '#' + CSS.escape(current.id);
                    path.unshift(selector);
                    break;
                }
                
                const classes = Array.from(current.classList).filter(c => !c.startsWith('erm-at-'));
                if (classes.length > 0) {
                    selector += '.' + classes.slice(0, 2).map(c => CSS.escape(c)).join('.');
                }
                
                // Add nth-child if needed for uniqueness
                const parent = current.parentElement;
                if (parent) {
                    const siblings = Array.from(parent.children).filter(c => 
                        c.tagName === current.tagName
                    );
                    if (siblings.length > 1) {
                        const index = siblings.indexOf(current) + 1;
                        selector += `:nth-child(${index})`;
                    }
                }
                
                path.unshift(selector);
                current = current.parentElement;
                
                // Limit path length
                if (path.length >= 4) break;
            }
            
            return path.join(' > ');
        }
        
        getElementLabel(element) {
            let label = element.tagName.toLowerCase();
            
            if (element.id) {
                label += '#' + element.id;
            } else if (element.className && typeof element.className === 'string') {
                const classes = element.className.split(' ').filter(c => c && !c.startsWith('erm-at-')).slice(0, 2);
                if (classes.length) {
                    label += '.' + classes.join('.');
                }
            }
            
            return label;
        }
        
        updateInfoPanel(element) {
            // Selector
            this.elements.selectorInput.value = this.selectedSelector;
            
            // Element info
            document.getElementById('erm-at-picker-tag').textContent = element.tagName.toLowerCase();
            document.getElementById('erm-at-picker-id').textContent = element.id || '(none)';
            
            const classes = Array.from(element.classList).filter(c => !c.startsWith('erm-at-'));
            document.getElementById('erm-at-picker-classes').textContent = 
                classes.length ? classes.slice(0, 3).join(', ') + (classes.length > 3 ? '...' : '') : '(none)';
            
            const rect = element.getBoundingClientRect();
            document.getElementById('erm-at-picker-size').textContent = 
                `${Math.round(rect.width)} × ${Math.round(rect.height)}px`;
        }
        
        copySelector() {
            navigator.clipboard.writeText(this.selectedSelector).then(() => {
                const btn = document.getElementById('erm-at-picker-copy');
                btn.textContent = 'Copied!';
                btn.classList.add('copied');
                
                setTimeout(() => {
                    btn.textContent = 'Copy';
                    btn.classList.remove('copied');
                }, 2000);
            });
        }
        
        applySelection() {
            const stepId = this.elements.stepDropdown.value;
            
            if (!stepId) {
                this.showToast('Please select a step first');
                return;
            }
            
            // Get element name for display
            const selectorName = this.getElementLabel(this.selectedElement);
            
            // Send to parent window (admin)
            if (window.opener && window.opener.ERMATAdmin) {
                window.opener.ERMATAdmin.receiveSelector({
                    selector: this.selectedSelector,
                    selectorName: selectorName,
                    stepId: stepId
                });
                
                this.showToast('Selector applied! You can close this window.');
                
                // Auto-close after delay
                setTimeout(() => {
                    window.close();
                }, 1500);
            } else {
                // Fallback - save via AJAX
                this.saveViaAjax(stepId, selectorName);
            }
        }
        
        saveViaAjax(stepId, selectorName) {
            const formData = new FormData();
            formData.append('action', 'erm_at_save_selector');
            formData.append('nonce', this.config.nonce || '');
            formData.append('tour_id', this.config.tourId);
            formData.append('step_id', stepId);
            formData.append('selector', this.selectedSelector);
            formData.append('selector_name', selectorName);
            
            fetch(this.config.adminUrl + 'admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showToast('Selector saved successfully!');
                    setTimeout(() => window.close(), 1500);
                } else {
                    this.showToast('Error saving selector');
                }
            })
            .catch(() => {
                this.showToast('Error saving selector');
            });
        }
        
        showToast(message) {
            this.elements.toast.textContent = message;
            this.elements.toast.classList.add('visible');
            
            setTimeout(() => {
                this.elements.toast.classList.remove('visible');
            }, 3000);
        }
        
        isPickerElement(element) {
            for (const selector of this.ignoreElements) {
                if (element.closest(selector)) return true;
            }
            return false;
        }
        
        close() {
            // Clean up
            this.clearSelection();
            
            if (this.hoveredElement) {
                this.hoveredElement.classList.remove('erm-at-picker-hover');
                this.hoveredElement.removeAttribute('data-erm-at-tag');
            }
            
            document.body.classList.remove('erm-at-picker-active');
            
            // Remove UI elements
            this.elements.toolbar.remove();
            this.elements.infoPanel.remove();
            this.elements.toast.remove();
            
            // Close window or go back
            if (window.opener) {
                window.close();
            } else {
                history.back();
            }
        }
    }
    
    // Initialize
    if (typeof ermAtPicker !== 'undefined') {
        window.ermAtElementPicker = new ElementPicker(ermAtPicker);
    }
    
})();
