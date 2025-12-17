/**
 * Blazing Feedback - Widget Principal
 *
 * Contrôleur principal qui orchestre les modules
 * Screenshot et Annotation
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Widget Blazing Feedback
     * @namespace
     */
    const BlazingFeedback = {

        /**
         * Configuration depuis WordPress
         * @type {Object}
         */
        config: window.wpvfhData || {},

        /**
         * État du widget
         * @type {Object}
         */
        state: {
            isOpen: false,
            isSubmitting: false,
            feedbackMode: 'view',      // 'view' | 'create' | 'annotate'
            currentFeedbacks: [],
            screenshotData: null,
            pinPosition: null,
        },

        /**
         * Éléments DOM
         * @type {Object}
         */
        elements: {},

        /**
         * Initialiser le widget
         * @returns {void}
         */
        init: function() {
            // Vérifier les permissions
            if (!this.config.canCreate && !this.config.canModerate) {
                console.log('[Blazing Feedback] Utilisateur sans permissions');
                return;
            }

            this.cacheElements();
            this.bindEvents();
            this.loadExistingFeedbacks();

            console.log('[Blazing Feedback] Widget initialisé');
        },

        /**
         * Mettre en cache les éléments DOM
         * @returns {void}
         */
        cacheElements: function() {
            this.elements = {
                container: document.getElementById('wpvfh-container'),
                toggleBtn: document.getElementById('wpvfh-toggle-btn'),
                panel: document.getElementById('wpvfh-panel'),
                form: document.getElementById('wpvfh-form'),
                commentField: document.getElementById('wpvfh-comment'),
                screenshotPreview: document.getElementById('wpvfh-screenshot-preview'),
                screenshotData: document.getElementById('wpvfh-screenshot-data'),
                positionX: document.getElementById('wpvfh-position-x'),
                positionY: document.getElementById('wpvfh-position-y'),
                pinInfo: document.querySelector('.wpvfh-pin-info'),
                closeBtn: document.querySelector('.wpvfh-close-btn'),
                cancelBtn: document.querySelector('.wpvfh-cancel-btn'),
                submitBtn: document.querySelector('.wpvfh-submit-btn'),
                notifications: document.getElementById('wpvfh-notifications'),
                overlay: document.getElementById('wpvfh-annotation-overlay'),
                // Nouveaux éléments média
                mediaToolbar: document.querySelector('.wpvfh-media-toolbar'),
                toolButtons: document.querySelectorAll('.wpvfh-tool-btn'),
                voiceSection: document.getElementById('wpvfh-voice-section'),
                voiceRecordBtn: document.getElementById('wpvfh-voice-record'),
                voicePreview: document.getElementById('wpvfh-voice-preview'),
                transcriptPreview: document.getElementById('wpvfh-transcript-preview'),
                videoSection: document.getElementById('wpvfh-video-section'),
                videoRecordBtn: document.getElementById('wpvfh-video-record'),
                videoPreview: document.getElementById('wpvfh-video-preview'),
                audioData: document.getElementById('wpvfh-audio-data'),
                videoData: document.getElementById('wpvfh-video-data'),
                transcriptField: document.getElementById('wpvfh-transcript'),
            };
        },

        /**
         * Attacher les gestionnaires d'événements
         * @returns {void}
         */
        bindEvents: function() {
            // Bouton toggle
            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.addEventListener('click', this.handleToggle.bind(this));
            }

            // Boutons du panel
            if (this.elements.closeBtn) {
                this.elements.closeBtn.addEventListener('click', this.closePanel.bind(this));
            }
            if (this.elements.cancelBtn) {
                this.elements.cancelBtn.addEventListener('click', this.handleCancel.bind(this));
            }

            // Soumission du formulaire
            if (this.elements.form) {
                this.elements.form.addEventListener('submit', this.handleSubmit.bind(this));
            }

            // Boutons de la barre d'outils média
            if (this.elements.toolButtons) {
                this.elements.toolButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => this.handleToolClick(e, btn.dataset.tool));
                });
            }

            // Bouton enregistrement vocal
            if (this.elements.voiceRecordBtn) {
                this.elements.voiceRecordBtn.addEventListener('click', this.handleVoiceRecord.bind(this));
            }

            // Bouton enregistrement vidéo
            if (this.elements.videoRecordBtn) {
                this.elements.videoRecordBtn.addEventListener('click', this.handleVideoRecord.bind(this));
            }

            // Boutons supprimer média
            document.querySelectorAll('.wpvfh-remove-media').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const parent = e.target.closest('.wpvfh-screenshot-preview, .wpvfh-audio-preview, .wpvfh-video-preview');
                    if (parent) {
                        if (parent.classList.contains('wpvfh-screenshot-preview')) this.clearScreenshot();
                        if (parent.classList.contains('wpvfh-audio-preview')) this.clearVoiceRecording();
                        if (parent.classList.contains('wpvfh-video-preview')) this.clearVideoRecording();
                    }
                });
            });

            // Événements des modules
            document.addEventListener('blazing-feedback:pin-placed', this.handlePinPlaced.bind(this));
            document.addEventListener('blazing-feedback:pin-selected', this.handlePinSelected.bind(this));
            document.addEventListener('blazing-feedback:capture-success', this.handleCaptureSuccess.bind(this));
            document.addEventListener('blazing-feedback:capture-error', this.handleCaptureError.bind(this));

            // Événements enregistrement vocal
            document.addEventListener('blazing-feedback:voice-recording-complete', this.handleVoiceComplete.bind(this));
            document.addEventListener('blazing-feedback:voice-transcription-update', this.handleTranscriptionUpdate.bind(this));

            // Événements enregistrement vidéo
            document.addEventListener('blazing-feedback:screen-recording-complete', this.handleVideoComplete.bind(this));

            // Échap pour fermer
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.state.isOpen) {
                    this.closePanel();
                }
            });

            // Annuler dans l'overlay
            const hintClose = document.querySelector('.wpvfh-hint-close');
            if (hintClose) {
                hintClose.addEventListener('click', () => {
                    this.emitEvent('stop-annotation');
                    this.state.feedbackMode = 'view';
                });
            }
        },

        /**
         * Gérer le clic sur un outil média
         * @param {Event} event - Événement
         * @param {string} tool - Nom de l'outil
         */
        handleToolClick: function(event, tool) {
            event.preventDefault();

            // Désactiver tous les boutons
            this.elements.toolButtons.forEach(btn => btn.classList.remove('active'));

            // Masquer toutes les sections
            if (this.elements.voiceSection) this.elements.voiceSection.hidden = true;
            if (this.elements.videoSection) this.elements.videoSection.hidden = true;

            switch (tool) {
                case 'screenshot':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    this.captureScreenshot();
                    break;
                case 'voice':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    if (this.elements.voiceSection) this.elements.voiceSection.hidden = false;
                    break;
                case 'video':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    if (this.elements.videoSection) this.elements.videoSection.hidden = false;
                    break;
            }
        },

        /**
         * Gérer l'enregistrement vocal
         */
        handleVoiceRecord: async function() {
            if (!window.BlazingVoiceRecorder) {
                this.showNotification('Enregistrement vocal non disponible', 'error');
                return;
            }

            const recorder = window.BlazingVoiceRecorder;

            if (recorder.state.isRecording) {
                recorder.stop();
                this.elements.voiceRecordBtn.classList.remove('recording');
                this.elements.voiceRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Enregistrer';
                if (this.voiceTimer) clearInterval(this.voiceTimer);
            } else {
                const started = await recorder.start();
                if (started) {
                    this.elements.voiceRecordBtn.classList.add('recording');
                    this.elements.voiceRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Arrêter';
                    this.startVoiceTimer();
                } else {
                    this.showNotification('Impossible d\'accéder au microphone', 'error');
                }
            }
        },

        /**
         * Démarrer le timer d'enregistrement vocal
         */
        startVoiceTimer: function() {
            const timeDisplay = this.elements.voiceSection?.querySelector('.wpvfh-recorder-time');
            if (!timeDisplay) return;

            this.voiceTimer = setInterval(() => {
                if (window.BlazingVoiceRecorder) {
                    const duration = window.BlazingVoiceRecorder.getCurrentDuration();
                    timeDisplay.textContent = window.BlazingVoiceRecorder.formatDuration(duration);
                }
            }, 100);
        },

        /**
         * Gérer la fin de l'enregistrement vocal
         * @param {CustomEvent} event
         */
        handleVoiceComplete: async function(event) {
            const { audioUrl, transcript } = event.detail;

            if (this.elements.voicePreview) {
                const audio = this.elements.voicePreview.querySelector('audio');
                if (audio) audio.src = audioUrl;
                this.elements.voicePreview.hidden = false;
            }

            // Stocker les données audio en base64
            if (window.BlazingVoiceRecorder && this.elements.audioData) {
                const base64 = await window.BlazingVoiceRecorder.getAudioBase64();
                this.elements.audioData.value = base64 || '';
            }

            // Afficher la transcription
            if (transcript && this.elements.transcriptPreview) {
                const textEl = this.elements.transcriptPreview.querySelector('.wpvfh-transcript-text');
                if (textEl) textEl.textContent = transcript;
                this.elements.transcriptPreview.hidden = false;
                if (this.elements.transcriptField) {
                    this.elements.transcriptField.value = transcript;
                }
            }
        },

        /**
         * Gérer la mise à jour de la transcription
         * @param {CustomEvent} event
         */
        handleTranscriptionUpdate: function(event) {
            const { final, interim } = event.detail;
            if (this.elements.transcriptPreview) {
                const textEl = this.elements.transcriptPreview.querySelector('.wpvfh-transcript-text');
                if (textEl) {
                    textEl.textContent = final + (interim ? ' ' + interim : '');
                }
                this.elements.transcriptPreview.hidden = false;
            }
        },

        /**
         * Effacer l'enregistrement vocal
         */
        clearVoiceRecording: function() {
            if (window.BlazingVoiceRecorder) {
                window.BlazingVoiceRecorder.clear();
            }
            if (this.elements.voicePreview) {
                this.elements.voicePreview.hidden = true;
                const audio = this.elements.voicePreview.querySelector('audio');
                if (audio) audio.src = '';
            }
            if (this.elements.transcriptPreview) {
                this.elements.transcriptPreview.hidden = true;
            }
            if (this.elements.audioData) this.elements.audioData.value = '';
            if (this.elements.transcriptField) this.elements.transcriptField.value = '';
        },

        /**
         * Gérer l'enregistrement vidéo
         */
        handleVideoRecord: async function() {
            if (!window.BlazingScreenRecorder) {
                this.showNotification('Enregistrement d\'écran non disponible', 'error');
                return;
            }

            const recorder = window.BlazingScreenRecorder;

            if (recorder.state.isRecording) {
                recorder.stop();
                this.elements.videoRecordBtn.classList.remove('recording');
                this.elements.videoRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Enregistrer l\'écran';
                if (this.videoTimer) clearInterval(this.videoTimer);
            } else {
                const started = await recorder.start({ includeMicrophone: true });
                if (started) {
                    this.elements.videoRecordBtn.classList.add('recording');
                    this.elements.videoRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Arrêter';
                    this.startVideoTimer();
                } else {
                    this.showNotification('Impossible d\'accéder à l\'écran', 'error');
                }
            }
        },

        /**
         * Démarrer le timer d'enregistrement vidéo
         */
        startVideoTimer: function() {
            const timeDisplay = this.elements.videoSection?.querySelector('.wpvfh-recorder-time');
            if (!timeDisplay) return;

            this.videoTimer = setInterval(() => {
                if (window.BlazingScreenRecorder) {
                    const duration = window.BlazingScreenRecorder.getCurrentDuration();
                    timeDisplay.textContent = window.BlazingScreenRecorder.formatDuration(duration);
                }
            }, 100);
        },

        /**
         * Gérer la fin de l'enregistrement vidéo
         * @param {CustomEvent} event
         */
        handleVideoComplete: async function(event) {
            const { videoUrl } = event.detail;

            if (this.elements.videoPreview) {
                const video = this.elements.videoPreview.querySelector('video');
                if (video) video.src = videoUrl;
                this.elements.videoPreview.hidden = false;
            }

            // Note: Les vidéos sont trop volumineuses pour base64
            // On stocke l'URL blob pour l'upload séparé
            this.state.videoBlob = event.detail.videoBlob;
        },

        /**
         * Effacer l'enregistrement vidéo
         */
        clearVideoRecording: function() {
            if (window.BlazingScreenRecorder) {
                window.BlazingScreenRecorder.clear();
            }
            if (this.elements.videoPreview) {
                this.elements.videoPreview.hidden = true;
                const video = this.elements.videoPreview.querySelector('video');
                if (video) video.src = '';
            }
            this.state.videoBlob = null;
        },

        /**
         * Gérer le toggle du widget
         * @param {Event} event - Événement de clic
         * @returns {void}
         */
        handleToggle: function(event) {
            event.preventDefault();

            if (this.state.isOpen) {
                this.closePanel();
            } else {
                this.startFeedbackMode();
            }
        },

        /**
         * Démarrer le mode feedback
         * @returns {void}
         */
        startFeedbackMode: function() {
            this.state.feedbackMode = 'annotate';

            // Activer le mode annotation
            this.emitEvent('start-annotation');

            // Notification
            this.showNotification(this.config.i18n?.clickToPin || 'Cliquez pour placer un marqueur', 'info');
        },

        /**
         * Ouvrir le panel de feedback
         * @returns {void}
         */
        openPanel: function() {
            this.state.isOpen = true;

            if (this.elements.panel) {
                this.elements.panel.hidden = false;
                this.elements.panel.setAttribute('aria-hidden', 'false');
            }

            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.setAttribute('aria-expanded', 'true');
            }

            // Focus sur le champ de commentaire
            if (this.elements.commentField) {
                setTimeout(() => this.elements.commentField.focus(), 100);
            }

            this.emitEvent('panel-opened');
        },

        /**
         * Fermer le panel de feedback
         * @returns {void}
         */
        closePanel: function() {
            this.state.isOpen = false;
            this.state.feedbackMode = 'view';

            if (this.elements.panel) {
                this.elements.panel.hidden = true;
                this.elements.panel.setAttribute('aria-hidden', 'true');
            }

            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.setAttribute('aria-expanded', 'false');
            }

            // Nettoyer le formulaire
            this.resetForm();

            this.emitEvent('panel-closed');
        },

        /**
         * Gérer le placement d'un pin
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinPlaced: function(event) {
            const position = event.detail;

            // Stocker la position
            this.state.pinPosition = position;

            // Mettre à jour les champs cachés
            if (this.elements.positionX) {
                this.elements.positionX.value = position.percentX;
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = position.percentY;
            }

            // Afficher l'info du pin
            if (this.elements.pinInfo) {
                this.elements.pinInfo.hidden = false;
            }

            // Capturer le screenshot si activé
            if (this.elements.screenshotToggle && this.elements.screenshotToggle.checked) {
                this.captureScreenshot();
            }

            // Ouvrir le panel
            this.state.feedbackMode = 'create';
            this.openPanel();
        },

        /**
         * Gérer la sélection d'un pin existant
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinSelected: function(event) {
            const { feedbackId, pinData } = event.detail;

            if (!pinData) return;

            // Afficher les détails du feedback
            this.showFeedbackDetails(pinData);
        },

        /**
         * Capturer le screenshot
         * @returns {void}
         */
        captureScreenshot: async function() {
            if (!window.BlazingScreenshot || !window.BlazingScreenshot.isAvailable()) {
                console.warn('[Blazing Feedback] Screenshot non disponible');
                return;
            }

            try {
                this.showNotification(this.config.i18n?.loadingMessage || 'Capture en cours...', 'info');

                const dataUrl = await window.BlazingScreenshot.capture();

                // Redimensionner si nécessaire
                const resizedDataUrl = await window.BlazingScreenshot.resize(dataUrl, 1200, 900);

                this.state.screenshotData = resizedDataUrl;

                // Mettre à jour le champ caché
                if (this.elements.screenshotData) {
                    this.elements.screenshotData.value = resizedDataUrl;
                }

                // Afficher l'aperçu
                this.showScreenshotPreview(resizedDataUrl);

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture:', error);
                this.showNotification('Erreur lors de la capture', 'error');
            }
        },

        /**
         * Afficher l'aperçu du screenshot
         * @param {string} dataUrl - Image en base64
         * @returns {void}
         */
        showScreenshotPreview: function(dataUrl) {
            if (!this.elements.screenshotPreview) return;

            const img = this.elements.screenshotPreview.querySelector('img');
            if (img) {
                img.src = dataUrl;
            }

            this.elements.screenshotPreview.hidden = false;
        },

        /**
         * Effacer le screenshot
         * @returns {void}
         */
        clearScreenshot: function() {
            this.state.screenshotData = null;

            if (this.elements.screenshotData) {
                this.elements.screenshotData.value = '';
            }

            if (this.elements.screenshotPreview) {
                this.elements.screenshotPreview.hidden = true;
                const img = this.elements.screenshotPreview.querySelector('img');
                if (img) {
                    img.src = '';
                }
            }
        },

        /**
         * Gérer le succès de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureSuccess: function(event) {
            // Capture gérée dans captureScreenshot
        },

        /**
         * Gérer l'erreur de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureError: function(event) {
            console.warn('[Blazing Feedback] Erreur de capture:', event.detail.error);
        },

        /**
         * Gérer l'annulation
         * @returns {void}
         */
        handleCancel: function() {
            // Supprimer le pin temporaire
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.removeTemporaryPin();
            }

            this.closePanel();
        },

        /**
         * Gérer la soumission du formulaire
         * @param {Event} event - Événement de soumission
         * @returns {void}
         */
        handleSubmit: async function(event) {
            event.preventDefault();

            if (this.state.isSubmitting) return;

            // Validation
            const comment = this.elements.commentField?.value?.trim();
            if (!comment) {
                this.showNotification(this.config.i18n?.errorMessage || 'Veuillez entrer un commentaire', 'error');
                this.elements.commentField?.focus();
                return;
            }

            this.state.isSubmitting = true;
            this.setSubmitState(true);

            try {
                // Collecter les métadonnées
                const metadata = window.BlazingScreenshot ? window.BlazingScreenshot.getMetadata() : {};

                // Préparer les données
                const feedbackData = {
                    comment: comment,
                    url: this.config.currentUrl || window.location.href,
                    position_x: this.elements.positionX?.value || null,
                    position_y: this.elements.positionY?.value || null,
                    screenshot_data: this.state.screenshotData || null,
                    screen_width: metadata.screenWidth,
                    screen_height: metadata.screenHeight,
                    viewport_width: metadata.viewportWidth,
                    viewport_height: metadata.viewportHeight,
                    browser: metadata.browser,
                    os: metadata.os,
                    device: metadata.device,
                    user_agent: metadata.userAgent,
                    selector: this.state.pinPosition?.selector || null,
                    scroll_x: metadata.scrollX,
                    scroll_y: metadata.scrollY,
                };

                // Envoyer à l'API
                const response = await this.apiRequest('POST', 'feedbacks', feedbackData);

                if (response.id) {
                    // Succès
                    this.showNotification(this.config.i18n?.successMessage || 'Feedback envoyé avec succès !', 'success');

                    // Supprimer le pin temporaire et créer le permanent
                    if (window.BlazingAnnotation) {
                        window.BlazingAnnotation.removeTemporaryPin();
                        window.BlazingAnnotation.createPin(response);
                    }

                    // Fermer le panel
                    this.closePanel();

                    // Ajouter à la liste locale
                    this.state.currentFeedbacks.push(response);

                    // Émettre l'événement
                    this.emitEvent('feedback-created', response);
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de soumission:', error);
                this.showNotification(error.message || this.config.i18n?.errorMessage || 'Erreur lors de l\'envoi', 'error');
            } finally {
                this.state.isSubmitting = false;
                this.setSubmitState(false);
            }
        },

        /**
         * Réinitialiser le formulaire
         * @returns {void}
         */
        resetForm: function() {
            if (this.elements.form) {
                this.elements.form.reset();
            }

            this.state.pinPosition = null;
            this.clearScreenshot();

            if (this.elements.pinInfo) {
                this.elements.pinInfo.hidden = true;
            }

            if (this.elements.positionX) {
                this.elements.positionX.value = '';
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = '';
            }

            // Supprimer le pin temporaire
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.removeTemporaryPin();
            }
        },

        /**
         * Définir l'état du bouton de soumission
         * @param {boolean} isLoading - En cours de chargement
         * @returns {void}
         */
        setSubmitState: function(isLoading) {
            if (!this.elements.submitBtn) return;

            this.elements.submitBtn.disabled = isLoading;

            if (isLoading) {
                this.elements.submitBtn.innerHTML = '<span class="wpvfh-spinner"></span> ' + (this.config.i18n?.loadingMessage || 'Envoi...');
            } else {
                this.elements.submitBtn.textContent = this.config.i18n?.submitButton || 'Envoyer';
            }
        },

        /**
         * Charger les feedbacks existants pour cette page
         * @returns {void}
         */
        loadExistingFeedbacks: async function() {
            try {
                const url = encodeURIComponent(this.config.currentUrl || window.location.href);
                const response = await this.apiRequest('GET', `feedbacks/by-url?url=${url}`);

                if (Array.isArray(response)) {
                    this.state.currentFeedbacks = response;

                    // Afficher les pins
                    this.emitEvent('load-pins', { pins: response });
                }

            } catch (error) {
                console.warn('[Blazing Feedback] Impossible de charger les feedbacks:', error);
            }
        },

        /**
         * Afficher les détails d'un feedback
         * @param {Object} feedback - Données du feedback
         * @returns {void}
         */
        showFeedbackDetails: function(feedback) {
            // Créer une modal ou un panel pour afficher les détails
            const modal = this.createDetailsModal(feedback);
            document.body.appendChild(modal);

            // Fermer avec Échap
            const closeHandler = (e) => {
                if (e.key === 'Escape') {
                    modal.remove();
                    document.removeEventListener('keydown', closeHandler);
                }
            };
            document.addEventListener('keydown', closeHandler);
        },

        /**
         * Créer la modal de détails
         * @param {Object} feedback - Données du feedback
         * @returns {HTMLElement} Modal
         */
        createDetailsModal: function(feedback) {
            const modal = document.createElement('div');
            modal.className = 'wpvfh-details-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000000;
            `;

            const statuses = this.config.statuses || {};
            const statusData = statuses[feedback.status] || { label: feedback.status, color: '#666' };

            modal.innerHTML = `
                <div class="wpvfh-details-content" style="
                    background: #fff;
                    border-radius: 8px;
                    max-width: 600px;
                    width: 90%;
                    max-height: 80vh;
                    overflow: auto;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                ">
                    <div class="wpvfh-details-header" style="
                        padding: 20px;
                        border-bottom: 1px solid #eee;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    ">
                        <h3 style="margin: 0;">Feedback #${feedback.id}</h3>
                        <button type="button" class="wpvfh-modal-close" style="
                            background: none;
                            border: none;
                            font-size: 24px;
                            cursor: pointer;
                            color: #666;
                        ">&times;</button>
                    </div>
                    <div class="wpvfh-details-body" style="padding: 20px;">
                        <div style="margin-bottom: 15px;">
                            <span style="
                                display: inline-block;
                                padding: 4px 12px;
                                border-radius: 20px;
                                background: ${statusData.color}20;
                                color: ${statusData.color};
                                font-size: 13px;
                                font-weight: 500;
                            ">${statusData.icon || ''} ${statusData.label}</span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong>Auteur:</strong> ${feedback.author?.name || 'Anonyme'}
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong>Date:</strong> ${new Date(feedback.date).toLocaleString()}
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong>Commentaire:</strong>
                            <p style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin: 10px 0 0;">${this.escapeHtml(feedback.comment)}</p>
                        </div>
                        ${feedback.screenshot_url ? `
                            <div style="margin-bottom: 15px;">
                                <strong>Screenshot:</strong>
                                <img src="${feedback.screenshot_url}" alt="Screenshot" style="max-width: 100%; margin-top: 10px; border-radius: 4px; border: 1px solid #eee;">
                            </div>
                        ` : ''}
                        ${feedback.replies && feedback.replies.length ? `
                            <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                                <strong>Réponses (${feedback.replies.length}):</strong>
                                ${feedback.replies.map(reply => `
                                    <div style="background: #f9f9f9; padding: 12px; border-radius: 4px; margin-top: 10px;">
                                        <div style="font-size: 12px; color: #666; margin-bottom: 5px;">
                                            ${reply.author?.name || 'Anonyme'} - ${new Date(reply.date).toLocaleString()}
                                        </div>
                                        <div>${this.escapeHtml(reply.content)}</div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                        ${this.config.canModerate ? `
                            <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                                <strong>Changer le statut:</strong>
                                <select class="wpvfh-status-select" data-feedback-id="${feedback.id}" style="
                                    margin-left: 10px;
                                    padding: 5px 10px;
                                    border-radius: 4px;
                                    border: 1px solid #ddd;
                                ">
                                    ${Object.entries(statuses).map(([key, data]) => `
                                        <option value="${key}" ${feedback.status === key ? 'selected' : ''}>${data.icon || ''} ${data.label}</option>
                                    `).join('')}
                                </select>
                            </div>
                            <div style="margin-top: 15px;">
                                <strong>Ajouter une réponse:</strong>
                                <textarea class="wpvfh-reply-input" style="
                                    width: 100%;
                                    margin-top: 10px;
                                    padding: 10px;
                                    border-radius: 4px;
                                    border: 1px solid #ddd;
                                    resize: vertical;
                                " rows="3" placeholder="${this.config.i18n?.replyPlaceholder || 'Votre réponse...'}"></textarea>
                                <button type="button" class="wpvfh-reply-submit" data-feedback-id="${feedback.id}" style="
                                    margin-top: 10px;
                                    padding: 8px 16px;
                                    background: #2271b1;
                                    color: #fff;
                                    border: none;
                                    border-radius: 4px;
                                    cursor: pointer;
                                ">Envoyer la réponse</button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;

            // Événements
            modal.querySelector('.wpvfh-modal-close').addEventListener('click', () => modal.remove());
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });

            // Changement de statut
            const statusSelect = modal.querySelector('.wpvfh-status-select');
            if (statusSelect) {
                statusSelect.addEventListener('change', async (e) => {
                    await this.updateFeedbackStatus(feedback.id, e.target.value);
                });
            }

            // Envoi de réponse
            const replySubmit = modal.querySelector('.wpvfh-reply-submit');
            if (replySubmit) {
                replySubmit.addEventListener('click', async () => {
                    const input = modal.querySelector('.wpvfh-reply-input');
                    const content = input?.value?.trim();
                    if (content) {
                        await this.addReply(feedback.id, content);
                        input.value = '';
                        modal.remove();
                        // Recharger les détails
                        const updatedFeedback = await this.apiRequest('GET', `feedbacks/${feedback.id}`);
                        this.showFeedbackDetails(updatedFeedback);
                    }
                });
            }

            return modal;
        },

        /**
         * Mettre à jour le statut d'un feedback
         * @param {number} feedbackId - ID du feedback
         * @param {string} status - Nouveau statut
         * @returns {void}
         */
        updateFeedbackStatus: async function(feedbackId, status) {
            try {
                await this.apiRequest('PUT', `feedbacks/${feedbackId}/status`, { status });

                // Mettre à jour le pin
                if (window.BlazingAnnotation) {
                    window.BlazingAnnotation.updatePin(feedbackId, { status });
                }

                this.showNotification('Statut mis à jour', 'success');

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de mise à jour:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
            }
        },

        /**
         * Ajouter une réponse à un feedback
         * @param {number} feedbackId - ID du feedback
         * @param {string} content - Contenu de la réponse
         * @returns {void}
         */
        addReply: async function(feedbackId, content) {
            try {
                await this.apiRequest('POST', `feedbacks/${feedbackId}/replies`, { content });
                this.showNotification('Réponse ajoutée', 'success');
            } catch (error) {
                console.error('[Blazing Feedback] Erreur d\'ajout de réponse:', error);
                this.showNotification('Erreur lors de l\'ajout de la réponse', 'error');
            }
        },

        /**
         * Effectuer une requête à l'API REST
         * @param {string} method - Méthode HTTP
         * @param {string} endpoint - Endpoint
         * @param {Object} data - Données (optionnel)
         * @returns {Promise<Object>} Réponse
         */
        apiRequest: async function(method, endpoint, data = null) {
            const url = this.config.restUrl + endpoint;

            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.config.restNonce,
                },
            };

            if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Erreur HTTP ${response.status}`);
            }

            return response.json();
        },

        /**
         * Afficher une notification
         * @param {string} message - Message
         * @param {string} type - Type (success, error, info, warning)
         * @returns {void}
         */
        showNotification: function(message, type = 'info') {
            if (!this.elements.notifications) return;

            const notification = document.createElement('div');
            notification.className = `wpvfh-notification wpvfh-notification-${type}`;
            notification.textContent = message;

            this.elements.notifications.appendChild(notification);

            // Animation d'entrée
            requestAnimationFrame(() => {
                notification.classList.add('wpvfh-notification-show');
            });

            // Supprimer après 4 secondes
            setTimeout(() => {
                notification.classList.remove('wpvfh-notification-show');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        },

        /**
         * Échapper le HTML
         * @param {string} text - Texte à échapper
         * @returns {string} Texte échappé
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Émettre un événement personnalisé
         * @param {string} name - Nom de l'événement
         * @param {Object} detail - Détails
         * @returns {void}
         */
        emitEvent: function(name, detail = {}) {
            const event = new CustomEvent('blazing-feedback:' + name, {
                bubbles: true,
                detail: detail,
            });
            document.dispatchEvent(event);
        },
    };

    // Exposer le widget globalement
    window.BlazingFeedback = BlazingFeedback;

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingFeedback.init());
    } else {
        BlazingFeedback.init();
    }

})(window, document);
