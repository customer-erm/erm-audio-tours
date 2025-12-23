/**
 * ERM Audio Tours - Frontend Tour Player
 */

(function() {
    'use strict';
    
    class AudioTourPlayer {
        constructor(config) {
            this.config = config;
            this.audio = null;
            this.isPlaying = false;
            this.currentStep = 0;
            this.currentSubHighlight = -1; // Track which sub-highlight is active
            this.isActive = false;
            this.demoMode = false;
            this.perStepMode = false;
            this.stepAudios = [];
            this.demoTime = 0;
            this.demoInterval = null;
            this.elements = {};
            
            this.init();
        }
        
        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }
        
        setup() {
            this.cacheElements();
            this.createCompletionModal();
            this.createAudio();
            this.bindEvents();
            
            // Show preview banner if in preview mode
            if (this.config.isPreview) {
                this.showPreviewBanner();
            }
            
            // Show launch button after delay
            setTimeout(() => this.showLaunchButton(), this.config.buttonDelay || 2000);
            
            // Show nav on load if setting is enabled
            if (this.config.showNavOnLoad === 'on_load') {
                setTimeout(() => this.showNavOnly(), this.config.buttonDelay || 2000);
            }
        }
        
        showNavOnly() {
            // Show nav without starting the tour (preview mode)
            if (this.elements.nav) {
                this.elements.nav.classList.add('visible');
            }
        }
        
        cacheElements() {
            this.elements = {
                // Desktop elements
                launchBtn: document.querySelector('.erm-at-launch-btn'),
                nav: document.querySelector('.erm-at-nav'),
                navItems: document.querySelectorAll('.erm-at-nav-item:not(.erm-at-nav-close-item)'),
                navClose: document.querySelector('.erm-at-nav-close'),
                player: document.querySelector('.erm-at-player'),
                playBtn: document.querySelector('.erm-at-btn-play'),
                prevBtn: document.querySelector('.erm-at-btn-prev'),
                nextBtn: document.querySelector('.erm-at-btn-next'),
                closeBtn: document.querySelector('.erm-at-btn-close'),
                progressBar: document.querySelector('.erm-at-progress-bar'),
                progressFill: document.querySelector('.erm-at-progress-fill'),
                currentTime: document.querySelector('.erm-at-time-current'),
                totalTime: document.querySelector('.erm-at-time-total'),
                sectionName: document.querySelector('.erm-at-section-name'),
                volumeSlider: document.querySelector('.erm-at-volume-slider'),
                
                // Mobile elements
                mobileTab: document.querySelector('.erm-at-mobile-tab'),
                mobileTabIcon: document.querySelector('.erm-at-mobile-tab-icon'),
                mobileTabLabel: document.querySelector('.erm-at-mobile-tab-label'),
                mobileBackdrop: document.querySelector('.erm-at-mobile-backdrop'),
                mobileTray: document.querySelector('.erm-at-mobile-tray'),
                trayClose: document.querySelector('.erm-at-tray-close'),
                traySteps: document.querySelectorAll('.erm-at-tray-step'),
                mobilePlay: document.querySelector('.erm-at-mobile-play'),
                mobilePrev: document.querySelector('.erm-at-mobile-prev'),
                mobileNext: document.querySelector('.erm-at-mobile-next'),
                mobileProgressFill: document.querySelector('.erm-at-mobile-progress-fill'),
                mobileStepName: document.querySelector('.erm-at-mobile-step-name'),
                trayExit: document.querySelector('.erm-at-tray-exit')
            };
            
            // Store step icons for mobile tab
            this.stepIcons = {};
            this.elements.navItems.forEach((item, index) => {
                const dot = item.querySelector('.erm-at-nav-dot');
                if (dot) {
                    this.stepIcons[index] = dot.innerHTML;
                }
            });
        }
        
        createCompletionModal() {
            // Create custom completion modal (replaces browser confirm)
            const modal = document.createElement('div');
            modal.className = 'erm-at-completion-modal';
            modal.innerHTML = `
                <div class="erm-at-completion-content">
                    <div class="erm-at-completion-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h3>Tour Complete!</h3>
                    <p>Thank you for taking the tour.</p>
                    <div class="erm-at-completion-actions">
                        <button class="erm-at-completion-btn erm-at-completion-replay">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                            </svg>
                            Replay Tour
                        </button>
                        <button class="erm-at-completion-btn erm-at-completion-close">
                            Close
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            this.elements.completionModal = modal;
            
            // Bind completion modal events
            modal.querySelector('.erm-at-completion-replay').addEventListener('click', () => {
                this.hideCompletionModal();
                this.replayTour();
            });
            
            modal.querySelector('.erm-at-completion-close').addEventListener('click', () => {
                this.hideCompletionModal();
                this.endTour();
            });
            
            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.hideCompletionModal();
                    this.endTour();
                }
            });
        }
        
        showCompletionModal() {
            if (this.elements.completionModal) {
                this.elements.completionModal.classList.add('active');
            }
        }
        
        hideCompletionModal() {
            if (this.elements.completionModal) {
                this.elements.completionModal.classList.remove('active');
            }
        }
        
        createAudio() {
            console.log('Audio config:', {
                audioMode: this.config.audioMode,
                masterAudio: this.config.masterAudio,
                steps: this.config.steps
            });
            
            if (this.config.audioMode === 'single' && this.config.masterAudio) {
                // Single master audio file mode
                this.audio = new Audio(this.config.masterAudio);
                this.audio.preload = 'auto';
                
                this.audio.addEventListener('loadedmetadata', () => {
                    console.log('Audio loaded, duration:', this.audio.duration);
                    if (this.elements.totalTime) {
                        this.elements.totalTime.textContent = this.formatTime(this.audio.duration);
                    }
                });
                
                this.audio.addEventListener('timeupdate', () => this.onTimeUpdate());
                this.audio.addEventListener('ended', () => this.onAudioEnded());
                
                this.audio.addEventListener('error', (e) => {
                    console.warn('Audio failed to load:', e);
                    this.enableDemoMode();
                });
                
            } else if (this.config.audioMode === 'per_step') {
                // Per-step audio mode
                this.perStepMode = true;
                this.stepAudios = [];
                
                // Pre-load all step audio files
                let hasAnyAudio = false;
                this.config.steps.forEach((step, index) => {
                    if (step.audioUrl) {
                        hasAnyAudio = true;
                        const audio = new Audio(step.audioUrl);
                        audio.preload = 'auto';
                        audio.addEventListener('ended', () => this.onStepAudioEnded());
                        audio.addEventListener('timeupdate', () => this.onPerStepTimeUpdate());
                        audio.addEventListener('error', (e) => {
                            console.warn(`Step ${index + 1} audio failed to load:`, e);
                        });
                        this.stepAudios[index] = audio;
                    } else {
                        this.stepAudios[index] = null;
                    }
                });
                
                if (!hasAnyAudio) {
                    console.log('No step audio files found, using demo mode');
                    this.enableDemoMode();
                } else {
                    // Calculate total duration from step audio files
                    this.updateTotalDuration();
                }
            } else {
                console.log('No audio configured, using demo mode');
                this.enableDemoMode();
            }
        }
        
        updateTotalDuration() {
            // For per-step mode, we show step progress not total time
            if (this.elements.totalTime) {
                this.elements.totalTime.textContent = `${this.config.steps.length} steps`;
            }
        }
        
        onPerStepTimeUpdate() {
            if (!this.perStepMode || !this.isActive) return;
            
            const currentAudio = this.stepAudios[this.currentStep];
            if (currentAudio && currentAudio.duration) {
                const progress = (currentAudio.currentTime / currentAudio.duration) * 100;
                
                // Calculate overall progress including completed steps
                const stepProgress = (this.currentStep / this.config.steps.length) * 100;
                const withinStepProgress = (progress / 100) * (100 / this.config.steps.length);
                const totalProgress = stepProgress + withinStepProgress;
                
                // Desktop progress
                if (this.elements.progressFill) {
                    this.elements.progressFill.style.width = `${totalProgress}%`;
                }
                
                // Mobile progress
                if (this.elements.mobileProgressFill) {
                    this.elements.mobileProgressFill.style.width = `${totalProgress}%`;
                }
                
                if (this.elements.currentTime) {
                    this.elements.currentTime.textContent = this.formatTime(currentAudio.currentTime);
                }
                
                if (this.elements.totalTime) {
                    this.elements.totalTime.textContent = this.formatTime(currentAudio.duration);
                }
                
                // Check for sub-highlights (per-step mode - time is relative to step audio)
                this.checkSubHighlights(currentAudio.currentTime);
            }
        }

        onStepAudioEnded() {
            if (!this.perStepMode || !this.isActive) return;
            
            // Move to next step automatically
            if (this.currentStep < this.config.steps.length - 1) {
                this.goToStep(this.currentStep + 1);
                // Auto-play next step if we were playing
                if (this.isPlaying) {
                    this.playCurrentStepAudio();
                }
            } else {
                // Tour complete
                this.onAudioEnded();
            }
        }
        
        playCurrentStepAudio() {
            if (!this.perStepMode) return;
            
            // Pause all step audios first
            this.stepAudios.forEach(audio => {
                if (audio) {
                    audio.pause();
                    audio.currentTime = 0;
                }
            });
            
            // Play current step audio
            const currentAudio = this.stepAudios[this.currentStep];
            if (currentAudio) {
                currentAudio.play().catch(err => {
                    console.log('Could not autoplay step audio:', err);
                });
            }
        }
        
        pauseCurrentStepAudio() {
            if (!this.perStepMode) return;
            
            const currentAudio = this.stepAudios[this.currentStep];
            if (currentAudio) {
                currentAudio.pause();
            }
        }
        
        enableDemoMode() {
            this.demoMode = true;
            
            // Calculate total duration from steps
            let totalDuration = 0;
            if (this.config.steps && this.config.steps.length > 0) {
                const lastStep = this.config.steps[this.config.steps.length - 1];
                totalDuration = lastStep ? (lastStep.endTime || 0) : 0;
                
                // If no timestamps set, create default duration (10 sec per step)
                if (totalDuration === 0) {
                    totalDuration = this.config.steps.length * 10;
                    // Auto-assign timestamps
                    this.config.steps.forEach((step, i) => {
                        step.startTime = i * 10;
                        step.endTime = (i + 1) * 10;
                    });
                }
            }
            
            if (this.elements.totalTime) {
                this.elements.totalTime.textContent = this.formatTime(totalDuration);
            }
        }
        
        bindEvents() {
            // Launch button - starts tour directly (no modal)
            if (this.elements.launchBtn) {
                this.elements.launchBtn.addEventListener('click', () => this.startTour());
            }
            
            // Nav close button
            if (this.elements.navClose) {
                this.elements.navClose.addEventListener('click', () => this.endTour());
            }
            
            // Player controls
            if (this.elements.playBtn) {
                this.elements.playBtn.addEventListener('click', () => this.togglePlay());
            }
            
            if (this.elements.prevBtn) {
                this.elements.prevBtn.addEventListener('click', () => this.previousStep());
            }
            
            if (this.elements.nextBtn) {
                this.elements.nextBtn.addEventListener('click', () => this.nextStep());
            }
            
            if (this.elements.closeBtn) {
                this.elements.closeBtn.addEventListener('click', () => this.endTour());
            }
            
            // Progress bar
            if (this.elements.progressBar) {
                this.elements.progressBar.addEventListener('click', (e) => this.seek(e));
            }
            
            // Volume
            if (this.elements.volumeSlider) {
                this.elements.volumeSlider.addEventListener('input', (e) => {
                    if (this.audio) this.audio.volume = e.target.value;
                });
            }
            
            // Navigation items
            this.elements.navItems.forEach((item, index) => {
                item.addEventListener('click', () => this.goToStep(index));
            });
            
            // Keyboard controls
            document.addEventListener('keydown', (e) => this.handleKeyboard(e));
            
            // ===== MOBILE CONTROLS =====
            
            // Mobile tab button
            if (this.elements.mobileTab) {
                this.elements.mobileTab.addEventListener('click', () => {
                    if (!this.isActive) {
                        this.startTour();
                        this.openMobileTray();
                    } else {
                        this.toggleMobileTray();
                    }
                });
            }
            
            // Mobile backdrop
            if (this.elements.mobileBackdrop) {
                this.elements.mobileBackdrop.addEventListener('click', () => this.closeMobileTray());
            }
            
            // Tray close button
            if (this.elements.trayClose) {
                this.elements.trayClose.addEventListener('click', () => this.closeMobileTray());
            }
            
            // Tray step items
            this.elements.traySteps.forEach((item, index) => {
                item.addEventListener('click', () => {
                    this.goToStep(index);
                    this.updateMobileTraySteps();
                });
            });
            
            // Mobile play button
            if (this.elements.mobilePlay) {
                this.elements.mobilePlay.addEventListener('click', () => this.togglePlay());
            }
            
            // Mobile prev/next
            if (this.elements.mobilePrev) {
                this.elements.mobilePrev.addEventListener('click', () => this.previousStep());
            }
            
            if (this.elements.mobileNext) {
                this.elements.mobileNext.addEventListener('click', () => this.nextStep());
            }
            
            // Tray exit button
            if (this.elements.trayExit) {
                this.elements.trayExit.addEventListener('click', () => {
                    this.closeMobileTray();
                    this.endTour();
                });
            }
        }
        
        // ===== MOBILE TRAY METHODS =====
        
        openMobileTray() {
            if (this.elements.mobileTray) {
                this.elements.mobileTray.classList.add('open');
            }
            if (this.elements.mobileBackdrop) {
                this.elements.mobileBackdrop.classList.add('visible');
            }
            this.updateMobileTraySteps();
        }
        
        closeMobileTray() {
            if (this.elements.mobileTray) {
                this.elements.mobileTray.classList.remove('open');
            }
            if (this.elements.mobileBackdrop) {
                this.elements.mobileBackdrop.classList.remove('visible');
            }
        }
        
        toggleMobileTray() {
            if (this.elements.mobileTray && this.elements.mobileTray.classList.contains('open')) {
                this.closeMobileTray();
            } else {
                this.openMobileTray();
            }
        }
        
        updateMobileTraySteps() {
            this.elements.traySteps.forEach((item, i) => {
                item.classList.remove('active', 'completed');
                
                if (i < this.currentStep) {
                    item.classList.add('completed');
                } else if (i === this.currentStep) {
                    item.classList.add('active');
                }
            });
        }
        
        updateMobileTab() {
            if (!this.elements.mobileTab || !this.elements.mobileTabIcon) return;
            
            if (this.isActive) {
                // Show current step icon
                this.elements.mobileTab.classList.add('playing');
                const stepIcon = this.stepIcons[this.currentStep];
                if (stepIcon) {
                    this.elements.mobileTabIcon.innerHTML = stepIcon;
                }
            } else {
                // Show play icon
                this.elements.mobileTab.classList.remove('playing');
                this.elements.mobileTabIcon.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polygon points="10,8 16,12 10,16" fill="currentColor" stroke="none"></polygon>
                    </svg>
                `;
            }
        }
        
        updateMobilePlayButton() {
            if (!this.elements.mobilePlay) return;
            
            if (this.isPlaying) {
                this.elements.mobilePlay.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <rect x="6" y="4" width="4" height="16"></rect>
                        <rect x="14" y="4" width="4" height="16"></rect>
                    </svg>
                `;
            } else {
                this.elements.mobilePlay.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="5,3 19,12 5,21"></polygon>
                    </svg>
                `;
            }
        }
        
        updateMobileStepName() {
            if (!this.elements.mobileStepName) return;
            
            const step = this.config.steps[this.currentStep];
            if (step) {
                this.elements.mobileStepName.textContent = step.name || `Step ${this.currentStep + 1}`;
            }
        }
        
        showLaunchButton() {
            if (this.elements.launchBtn) {
                this.elements.launchBtn.classList.add('visible');
            }
            // Also show mobile tab
            if (this.elements.mobileTab) {
                this.elements.mobileTab.classList.add('visible');
            }
        }
        
        showPreviewBanner() {
            const banner = document.createElement('div');
            banner.className = 'erm-at-preview-banner';
            banner.innerHTML = `
                <span>Preview Mode</span>
                <a href="${this.config.editUrl || '#'}">Edit Tour</a>
            `;
            document.body.appendChild(banner);
        }
        
        startTour() {
            this.isActive = true;
            
            // Hide launch button
            if (this.elements.launchBtn) {
                this.elements.launchBtn.classList.add('erm-at-hidden');
            }
            
            // Show nav and player (change visible to active)
            if (this.elements.nav) {
                this.elements.nav.classList.remove('visible');
                this.elements.nav.classList.add('active');
            }
            if (this.elements.player) {
                this.elements.player.classList.add('active');
            }
            
            // Add body class
            document.documentElement.classList.add('erm-at-active');
            
            // Start at first step
            this.currentStep = 0;
            this.demoTime = 0;
            
            // Reset audio position
            if (this.audio && !this.demoMode) {
                this.audio.currentTime = 0;
            }
            
            this.updateActiveStep();
            
            // Update mobile elements
            this.updateMobileTab();
            this.updateMobileTraySteps();
            this.updateMobileStepName();
            
            // Start playing
            this.play();
            
            // Go to first step
            const firstStep = this.config.steps[0];
            if (firstStep) {
                if (firstStep.scrollTo) {
                    this.scrollToStep(0);
                }
                if (firstStep.highlightStyle) {
                    this.highlightStep(0);
                }
                if (this.elements.sectionName) {
                    this.elements.sectionName.textContent = firstStep.name || 'Step 1';
                }
            }
        }
        
        endTour() {
            this.isActive = false;
            this.pause();
            
            if (this.demoInterval) {
                clearInterval(this.demoInterval);
                this.demoInterval = null;
            }
            
            // Reset audio
            if (this.audio && !this.demoMode && !this.perStepMode) {
                this.audio.currentTime = 0;
            }
            
            // Stop all step audios
            if (this.perStepMode && this.stepAudios) {
                this.stepAudios.forEach(audio => {
                    if (audio) {
                        audio.pause();
                        audio.currentTime = 0;
                    }
                });
            }
            
            this.demoTime = 0;
            
            // Hide nav and player
            if (this.elements.nav) {
                this.elements.nav.classList.remove('active');
                // Restore visible state if showNavOnLoad is on_load
                if (this.config.showNavOnLoad === 'on_load') {
                    this.elements.nav.classList.add('visible');
                }
            }
            if (this.elements.player) {
                this.elements.player.classList.remove('active');
            }
            
            // Show launch button
            if (this.elements.launchBtn) {
                this.elements.launchBtn.classList.remove('erm-at-hidden');
                this.elements.launchBtn.classList.add('visible');
            }
            
            // Clear highlights
            this.clearHighlights();
            
            // Remove body class
            document.documentElement.classList.remove('erm-at-active');
            
            // Reset nav items
            this.elements.navItems.forEach(item => {
                item.classList.remove('active', 'completed');
            });
            
            // Reset progress
            if (this.elements.progressFill) {
                this.elements.progressFill.style.width = '0%';
            }
            if (this.elements.currentTime) {
                this.elements.currentTime.textContent = '0:00';
            }
            
            // Reset mobile elements
            this.updateMobileTab();
            this.closeMobileTray();
            if (this.elements.mobileProgressFill) {
                this.elements.mobileProgressFill.style.width = '0%';
            }
            this.elements.traySteps.forEach(item => {
                item.classList.remove('active', 'completed');
            });
        }
        
        replayTour() {
            // Reset state
            this.currentStep = 0;
            this.demoTime = 0;
            if (this.audio && !this.demoMode) {
                this.audio.currentTime = 0;
            }
            
            // Reset nav items
            this.elements.navItems.forEach(item => {
                item.classList.remove('active', 'completed');
            });
            
            // Start fresh
            this.updateActiveStep();
            this.play();
            this.scrollToStep(0);
        }
        
        togglePlay() {
            if (this.isPlaying) {
                this.pause();
            } else {
                this.play();
            }
        }
        
        play() {
            this.isPlaying = true;
            this.updatePlayButton();
            
            if (this.perStepMode) {
                this.playCurrentStepAudio();
            } else if (this.demoMode) {
                this.startDemoPlayback();
            } else if (this.audio) {
                this.audio.play().catch(() => {
                    console.log('Autoplay prevented, switching to demo mode');
                    this.enableDemoMode();
                    this.startDemoPlayback();
                });
            }
        }
        
        pause() {
            this.isPlaying = false;
            this.updatePlayButton();
            
            if (this.perStepMode) {
                this.pauseCurrentStepAudio();
            } else if (this.demoMode) {
                if (this.demoInterval) {
                    clearInterval(this.demoInterval);
                    this.demoInterval = null;
                }
            } else if (this.audio) {
                this.audio.pause();
            }
        }
        
        startDemoPlayback() {
            if (this.demoInterval) {
                clearInterval(this.demoInterval);
            }
            
            this.demoInterval = setInterval(() => {
                if (!this.isPlaying) return;
                
                this.demoTime += 0.1;
                this.onTimeUpdate();
                
                // Check if tour is complete
                const duration = this.getDuration();
                if (duration > 0 && this.demoTime >= duration) {
                    this.onAudioEnded();
                }
            }, 100);
        }
        
        updatePlayButton() {
            // Desktop play button
            if (this.elements.playBtn) {
                if (this.isPlaying) {
                    this.elements.playBtn.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <rect x="6" y="4" width="4" height="16"></rect>
                            <rect x="14" y="4" width="4" height="16"></rect>
                        </svg>
                    `;
                    this.elements.playBtn.setAttribute('aria-label', 'Pause');
                } else {
                    this.elements.playBtn.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="5,3 19,12 5,21"></polygon>
                        </svg>
                    `;
                    this.elements.playBtn.setAttribute('aria-label', 'Play');
                }
            }
            
            // Also update mobile play button
            this.updateMobilePlayButton();
        }
        
        getDuration() {
            if (this.demoMode) {
                const lastStep = this.config.steps[this.config.steps.length - 1];
                return lastStep ? (lastStep.endTime || 0) : 0;
            }
            return this.audio ? this.audio.duration : 0;
        }
        
        getCurrentTime() {
            if (this.demoMode) {
                return this.demoTime;
            }
            return this.audio ? this.audio.currentTime : 0;
        }
        
        setCurrentTime(time) {
            if (this.demoMode) {
                this.demoTime = time;
            } else if (this.audio) {
                this.audio.currentTime = time;
            }
        }
        
        onTimeUpdate() {
            const currentTime = this.getCurrentTime();
            const duration = this.getDuration();
            
            // Update progress bar (desktop)
            if (this.elements.progressFill && duration) {
                const progress = (currentTime / duration) * 100;
                this.elements.progressFill.style.width = `${progress}%`;
            }
            
            // Update progress bar (mobile)
            if (this.elements.mobileProgressFill && duration) {
                const progress = (currentTime / duration) * 100;
                this.elements.mobileProgressFill.style.width = `${progress}%`;
            }
            
            // Update time display
            if (this.elements.currentTime) {
                this.elements.currentTime.textContent = this.formatTime(currentTime);
            }
            
            // Check for step transitions
            this.checkStepTransition(currentTime);
            
            // Check for sub-highlight transitions (single audio mode)
            if (!this.perStepMode) {
                this.checkSubHighlights(currentTime);
            }
        }
        
        checkStepTransition(currentTime) {
            const steps = this.config.steps;
            if (!steps || steps.length === 0) return;
            
            for (let i = 0; i < steps.length; i++) {
                const step = steps[i];
                
                if (currentTime >= step.startTime && currentTime < step.endTime) {
                    if (this.currentStep !== i) {
                        this.currentStep = i;
                        this.onStepChange(i);
                    }
                    break;
                }
            }
        }
        
        onStepChange(index) {
            this.updateActiveStep();
            
            // Reset sub-highlight tracker
            this.currentSubHighlight = -1;
            
            const step = this.config.steps[index];
            if (!step) return;
            
            // Always clear previous highlights first
            this.clearHighlights();
            
            // Scroll if enabled
            if (step.scrollTo) {
                this.scrollToStep(index);
            }
            
            // Apply new highlight if enabled
            if (step.highlightStyle) {
                this.highlightStep(index);
            }
            
            // Update section name
            if (this.elements.sectionName) {
                this.elements.sectionName.textContent = step.name || `Step ${index + 1}`;
            }
            
            // Update mobile elements
            this.updateMobileTab();
            this.updateMobileTraySteps();
            this.updateMobileStepName();
        }
        
        updateActiveStep() {
            this.elements.navItems.forEach((item, i) => {
                item.classList.remove('active');
                
                if (i < this.currentStep) {
                    item.classList.add('completed');
                } else if (i === this.currentStep) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('completed');
                }
            });
        }
        
        scrollToStep(index) {
            const step = this.config.steps[index];
            if (!step || !step.selector) return;
            
            // Try to find element
            let target = null;
            const selectors = step.selector.split(',');
            
            for (const selector of selectors) {
                try {
                    target = document.querySelector(selector.trim());
                    if (target) break;
                } catch (e) {
                    console.warn('Invalid selector:', selector);
                }
            }
            
            if (target) {
                const rect = target.getBoundingClientRect();
                const offset = 170; // Account for player bar + extra padding
                
                window.scrollTo({
                    top: window.scrollY + rect.top - offset,
                    behavior: 'smooth'
                });
            }
        }
        
        highlightStep(index) {
            // Clear previous highlights
            this.clearHighlights();
            
            const step = this.config.steps[index];
            if (!step || !step.selector || !step.highlightStyle) return;
            
            // Find and highlight element
            const selectors = step.selector.split(',');
            
            for (const selector of selectors) {
                try {
                    const target = document.querySelector(selector.trim());
                    if (target) {
                        // Add base highlight class and specific style class
                        target.classList.add('erm-at-highlight');
                        target.classList.add('erm-at-highlight-' + step.highlightStyle);
                        
                        // For spotlight effect, create overlay
                        if (step.highlightStyle === 'spotlight') {
                            this.createSpotlightOverlay(target);
                        }
                        
                        // For arrow effect, create arrow pointer
                        if (step.highlightStyle === 'arrow') {
                            this.createArrowPointer(target);
                        }
                        
                        break;
                    }
                } catch (e) {
                    console.warn('Invalid selector:', selector);
                }
            }
        }
        
        createSpotlightOverlay(target) {
            // Remove existing overlay
            const existing = document.querySelector('.erm-at-spotlight-overlay');
            if (existing) existing.remove();
            
            const overlay = document.createElement('div');
            overlay.className = 'erm-at-spotlight-overlay';
            document.body.appendChild(overlay);
            
            // Position the spotlight hole
            const rect = target.getBoundingClientRect();
            const padding = 10;
            
            overlay.style.setProperty('--spotlight-top', (rect.top + window.scrollY - padding) + 'px');
            overlay.style.setProperty('--spotlight-left', (rect.left - padding) + 'px');
            overlay.style.setProperty('--spotlight-width', (rect.width + padding * 2) + 'px');
            overlay.style.setProperty('--spotlight-height', (rect.height + padding * 2) + 'px');
        }
        
        createArrowPointer(target) {
            // Remove existing arrow
            const existing = document.querySelector('.erm-at-arrow-pointer');
            if (existing) existing.remove();
            
            const arrow = document.createElement('div');
            arrow.className = 'erm-at-arrow-pointer';
            arrow.innerHTML = `
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L8 6h3v8H8l4 4 4-4h-3V6h3L12 2z"/>
                </svg>
            `;
            document.body.appendChild(arrow);
            
            // Position above the element
            const rect = target.getBoundingClientRect();
            arrow.style.top = (rect.top + window.scrollY - 50) + 'px';
            arrow.style.left = (rect.left + rect.width / 2 - 20) + 'px';
        }
        
        clearHighlights() {
            // Remove all highlight classes
            const highlightClasses = [
                'erm-at-highlight',
                'erm-at-highlight-outline',
                'erm-at-highlight-pulse',
                'erm-at-highlight-spotlight',
                'erm-at-highlight-zoom',
                'erm-at-highlight-underline',
                'erm-at-highlight-border-draw',
                'erm-at-highlight-fill',
                'erm-at-highlight-bounce',
                'erm-at-highlight-shake',
                'erm-at-highlight-arrow'
            ];
            
            document.querySelectorAll('.erm-at-highlight').forEach(el => {
                highlightClasses.forEach(cls => el.classList.remove(cls));
                // Reset any inline styles that might have been applied
                el.style.transform = '';
                el.style.boxShadow = '';
                el.style.zIndex = '';
                el.style.position = '';
            });
            
            // Remove spotlight overlay
            const spotlightOverlay = document.querySelector('.erm-at-spotlight-overlay');
            if (spotlightOverlay) spotlightOverlay.remove();
            
            // Remove arrow pointer
            const arrowPointer = document.querySelector('.erm-at-arrow-pointer');
            if (arrowPointer) arrowPointer.remove();
        }
        
        checkSubHighlights(currentTime) {
            const step = this.config.steps[this.currentStep];
            if (!step || !step.subHighlights || step.subHighlights.length === 0) return;
            
            // For single audio mode, calculate time relative to step start
            let relativeTime = currentTime;
            if (!this.perStepMode) {
                relativeTime = currentTime - step.startTime;
            }
            
            // Find which sub-highlight should be active
            let activeSubIndex = -1;
            for (let i = step.subHighlights.length - 1; i >= 0; i--) {
                if (relativeTime >= step.subHighlights[i].timestamp) {
                    activeSubIndex = i;
                    break;
                }
            }
            
            // Only update if sub-highlight changed
            if (activeSubIndex !== this.currentSubHighlight) {
                this.currentSubHighlight = activeSubIndex;
                
                if (activeSubIndex >= 0) {
                    const subHighlight = step.subHighlights[activeSubIndex];
                    this.applySubHighlight(subHighlight);
                }
                // Note: We don't clear on -1 because the main step highlight should remain
            }
        }
        
        applySubHighlight(subHighlight) {
            if (!subHighlight || !subHighlight.selector) return;
            
            // Clear previous highlights
            this.clearHighlights();
            
            // Find and highlight element
            const selectors = subHighlight.selector.split(',');
            
            for (const selector of selectors) {
                try {
                    const target = document.querySelector(selector.trim());
                    if (target) {
                        // Add base highlight class and specific style class
                        target.classList.add('erm-at-highlight');
                        target.classList.add('erm-at-highlight-' + subHighlight.highlightStyle);
                        
                        // For spotlight effect, create overlay
                        if (subHighlight.highlightStyle === 'spotlight') {
                            this.createSpotlightOverlay(target);
                        }
                        
                        // For arrow effect, create arrow pointer
                        if (subHighlight.highlightStyle === 'arrow') {
                            this.createArrowPointer(target);
                        }
                        
                        // Scroll to the element
                        const rect = target.getBoundingClientRect();
                        const offset = 170;
                        
                        window.scrollTo({
                            top: window.scrollY + rect.top - offset,
                            behavior: 'smooth'
                        });
                        
                        break;
                    }
                } catch (e) {
                    console.warn('Invalid sub-highlight selector:', selector);
                }
            }
        }
        
        goToStep(index) {
            if (index < 0 || index >= this.config.steps.length) return;
            
            const wasPlaying = this.isPlaying;
            
            // Stop current audio if in per-step mode
            if (this.perStepMode && this.stepAudios[this.currentStep]) {
                this.stepAudios[this.currentStep].pause();
                this.stepAudios[this.currentStep].currentTime = 0;
            }
            
            // Always clear previous highlights first
            this.clearHighlights();
            
            // Reset sub-highlight tracker
            this.currentSubHighlight = -1;
            
            this.currentStep = index;
            const step = this.config.steps[index];
            
            if (!step) return;
            
            // Jump to step time (for single audio mode)
            if (!this.perStepMode) {
                this.setCurrentTime(step.startTime);
            }
            
            // Update UI
            this.updateActiveStep();
            
            // Update progress for per-step mode
            if (this.perStepMode) {
                const progress = (index / this.config.steps.length) * 100;
                if (this.elements.progressFill) {
                    this.elements.progressFill.style.width = `${progress}%`;
                }
                if (this.elements.mobileProgressFill) {
                    this.elements.mobileProgressFill.style.width = `${progress}%`;
                }
                if (this.elements.currentTime) {
                    this.elements.currentTime.textContent = '0:00';
                }
                
                // Play new step audio if we were playing
                if (wasPlaying && this.stepAudios[index]) {
                    this.stepAudios[index].play().catch(err => {
                        console.log('Could not play step audio:', err);
                    });
                }
            }
            
            if (step.scrollTo) {
                this.scrollToStep(index);
            }
            
            if (step.highlightStyle) {
                this.highlightStep(index);
            }
            
            if (this.elements.sectionName) {
                this.elements.sectionName.textContent = step.name || `Step ${index + 1}`;
            }
            
            // Update mobile elements
            this.updateMobileTab();
            this.updateMobileTraySteps();
            this.updateMobileStepName();
        }
        
        previousStep() {
            if (this.currentStep > 0) {
                this.goToStep(this.currentStep - 1);
            }
        }
        
        nextStep() {
            if (this.currentStep < this.config.steps.length - 1) {
                this.goToStep(this.currentStep + 1);
            }
        }
        
        seek(e) {
            const rect = this.elements.progressBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            const duration = this.getDuration();
            
            if (duration) {
                this.setCurrentTime(percent * duration);
            }
        }
        
        onAudioEnded() {
            this.pause();
            
            // Mark all as completed
            this.elements.navItems.forEach(item => {
                item.classList.add('completed');
                item.classList.remove('active');
            });
            
            // Show custom completion modal (no browser alert!)
            setTimeout(() => {
                this.showCompletionModal();
            }, 300);
        }
        
        handleKeyboard(e) {
            if (!this.isActive) return;
            
            // Don't capture if user is typing in an input
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            switch(e.key) {
                case ' ':
                case 'k':
                    e.preventDefault();
                    this.togglePlay();
                    break;
                case 'ArrowLeft':
                case 'j':
                    e.preventDefault();
                    this.previousStep();
                    break;
                case 'ArrowRight':
                case 'l':
                    e.preventDefault();
                    this.nextStep();
                    break;
                case 'Escape':
                    this.endTour();
                    break;
            }
        }
        
        formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    }
    
    // Initialize when config is available
    if (typeof ermAtTourConfig !== 'undefined') {
        window.ermAtTour = new AudioTourPlayer(ermAtTourConfig);
    }
    
})();
