/**
 * Blazing Feedback - Module d'enregistrement vocal
 *
 * Permet l'enregistrement audio avec transcription automatique
 * utilisant l'API Web Speech pour la reconnaissance vocale
 *
 * @package Blazing_Feedback
 * @since 1.3.0
 */

(function(window, document) {
    'use strict';

    /**
     * Module Voice Recorder
     * @namespace
     */
    const BlazingVoiceRecorder = {

        /**
         * État du module
         * @type {Object}
         */
        state: {
            isRecording: false,
            isPaused: false,
            mediaRecorder: null,
            audioChunks: [],
            audioBlob: null,
            audioUrl: null,
            transcript: '',
            recognition: null,
            stream: null,
            startTime: null,
            duration: 0,
        },

        /**
         * Configuration
         * @type {Object}
         */
        config: {
            maxDuration: 120000, // 2 minutes max
            mimeType: 'audio/webm',
            audioBitsPerSecond: 128000,
            language: 'fr-FR', // Langue par défaut pour la reconnaissance
        },

        /**
         * Vérifier si l'enregistrement audio est supporté
         * @returns {boolean}
         */
        isSupported: function() {
            return !!(navigator.mediaDevices &&
                     navigator.mediaDevices.getUserMedia &&
                     window.MediaRecorder);
        },

        /**
         * Vérifier si la reconnaissance vocale est supportée
         * @returns {boolean}
         */
        isSpeechRecognitionSupported: function() {
            return !!(window.SpeechRecognition || window.webkitSpeechRecognition);
        },

        /**
         * Initialiser le module
         * @returns {void}
         */
        init: function() {
            if (!this.isSupported()) {
                console.warn('[Blazing Feedback] Enregistrement audio non supporté');
                return;
            }

            // Initialiser la reconnaissance vocale si supportée
            if (this.isSpeechRecognitionSupported()) {
                this.initSpeechRecognition();
            }

            console.log('[Blazing Feedback] Module Voice Recorder initialisé');
        },

        /**
         * Initialiser la reconnaissance vocale
         * @returns {void}
         */
        initSpeechRecognition: function() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.state.recognition = new SpeechRecognition();

            this.state.recognition.continuous = true;
            this.state.recognition.interimResults = true;
            this.state.recognition.lang = this.config.language;

            this.state.recognition.onresult = (event) => {
                let finalTranscript = '';
                let interimTranscript = '';

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript + ' ';
                    } else {
                        interimTranscript += transcript;
                    }
                }

                if (finalTranscript) {
                    this.state.transcript += finalTranscript;
                }

                // Émettre l'événement de transcription
                this.emitEvent('transcription-update', {
                    final: this.state.transcript,
                    interim: interimTranscript,
                });
            };

            this.state.recognition.onerror = (event) => {
                console.error('[Blazing Feedback] Erreur de reconnaissance vocale:', event.error);
                if (event.error !== 'no-speech') {
                    this.emitEvent('transcription-error', { error: event.error });
                }
            };

            this.state.recognition.onend = () => {
                // Redémarrer si toujours en enregistrement
                if (this.state.isRecording && !this.state.isPaused) {
                    try {
                        this.state.recognition.start();
                    } catch (e) {
                        // Ignorer si déjà démarré
                    }
                }
            };
        },

        /**
         * Démarrer l'enregistrement
         * @returns {Promise<boolean>}
         */
        start: async function() {
            if (this.state.isRecording) {
                console.warn('[Blazing Feedback] Enregistrement déjà en cours');
                return false;
            }

            try {
                // Demander l'accès au microphone
                this.state.stream = await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                    }
                });

                // Créer le MediaRecorder
                const options = { mimeType: this.config.mimeType };

                // Vérifier si le mimeType est supporté
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'audio/webm';
                    if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                        options.mimeType = 'audio/ogg';
                        if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                            delete options.mimeType; // Laisser le navigateur choisir
                        }
                    }
                }

                this.state.mediaRecorder = new MediaRecorder(this.state.stream, options);
                this.state.audioChunks = [];
                this.state.transcript = '';
                this.state.startTime = Date.now();

                // Gérer les données audio
                this.state.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        this.state.audioChunks.push(event.data);
                    }
                };

                // Quand l'enregistrement s'arrête
                this.state.mediaRecorder.onstop = () => {
                    this.state.duration = Date.now() - this.state.startTime;
                    this.state.audioBlob = new Blob(this.state.audioChunks, {
                        type: this.state.mediaRecorder.mimeType || 'audio/webm'
                    });
                    this.state.audioUrl = URL.createObjectURL(this.state.audioBlob);

                    this.emitEvent('recording-complete', {
                        audioBlob: this.state.audioBlob,
                        audioUrl: this.state.audioUrl,
                        duration: this.state.duration,
                        transcript: this.state.transcript,
                    });
                };

                // Démarrer l'enregistrement
                this.state.mediaRecorder.start(1000); // Chunks toutes les secondes
                this.state.isRecording = true;
                this.state.isPaused = false;

                // Démarrer la reconnaissance vocale
                if (this.state.recognition) {
                    try {
                        this.state.recognition.start();
                    } catch (e) {
                        console.warn('[Blazing Feedback] Erreur démarrage reconnaissance:', e);
                    }
                }

                // Timer pour la durée maximum
                this.maxDurationTimer = setTimeout(() => {
                    if (this.state.isRecording) {
                        this.stop();
                        this.emitEvent('max-duration-reached');
                    }
                }, this.config.maxDuration);

                this.emitEvent('recording-started');
                console.log('[Blazing Feedback] Enregistrement audio démarré');

                return true;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur accès microphone:', error);
                this.emitEvent('recording-error', { error: error.message });
                return false;
            }
        },

        /**
         * Mettre en pause l'enregistrement
         * @returns {void}
         */
        pause: function() {
            if (!this.state.isRecording || this.state.isPaused) return;

            if (this.state.mediaRecorder && this.state.mediaRecorder.state === 'recording') {
                this.state.mediaRecorder.pause();
                this.state.isPaused = true;

                if (this.state.recognition) {
                    try {
                        this.state.recognition.stop();
                    } catch (e) {}
                }

                this.emitEvent('recording-paused');
                console.log('[Blazing Feedback] Enregistrement en pause');
            }
        },

        /**
         * Reprendre l'enregistrement
         * @returns {void}
         */
        resume: function() {
            if (!this.state.isRecording || !this.state.isPaused) return;

            if (this.state.mediaRecorder && this.state.mediaRecorder.state === 'paused') {
                this.state.mediaRecorder.resume();
                this.state.isPaused = false;

                if (this.state.recognition) {
                    try {
                        this.state.recognition.start();
                    } catch (e) {}
                }

                this.emitEvent('recording-resumed');
                console.log('[Blazing Feedback] Enregistrement repris');
            }
        },

        /**
         * Arrêter l'enregistrement
         * @returns {void}
         */
        stop: function() {
            if (!this.state.isRecording) return;

            // Annuler le timer
            if (this.maxDurationTimer) {
                clearTimeout(this.maxDurationTimer);
                this.maxDurationTimer = null;
            }

            // Arrêter le MediaRecorder
            if (this.state.mediaRecorder && this.state.mediaRecorder.state !== 'inactive') {
                this.state.mediaRecorder.stop();
            }

            // Arrêter la reconnaissance vocale
            if (this.state.recognition) {
                try {
                    this.state.recognition.stop();
                } catch (e) {}
            }

            // Arrêter le stream
            if (this.state.stream) {
                this.state.stream.getTracks().forEach(track => track.stop());
                this.state.stream = null;
            }

            this.state.isRecording = false;
            this.state.isPaused = false;

            this.emitEvent('recording-stopped');
            console.log('[Blazing Feedback] Enregistrement arrêté');
        },

        /**
         * Annuler l'enregistrement
         * @returns {void}
         */
        cancel: function() {
            this.stop();
            this.clear();
            this.emitEvent('recording-cancelled');
        },

        /**
         * Effacer l'enregistrement
         * @returns {void}
         */
        clear: function() {
            if (this.state.audioUrl) {
                URL.revokeObjectURL(this.state.audioUrl);
            }

            this.state.audioChunks = [];
            this.state.audioBlob = null;
            this.state.audioUrl = null;
            this.state.transcript = '';
            this.state.duration = 0;
        },

        /**
         * Obtenir les données audio en base64
         * @returns {Promise<string|null>}
         */
        getAudioBase64: async function() {
            if (!this.state.audioBlob) return null;

            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(this.state.audioBlob);
            });
        },

        /**
         * Obtenir les données d'enregistrement
         * @returns {Object}
         */
        getData: function() {
            return {
                audioBlob: this.state.audioBlob,
                audioUrl: this.state.audioUrl,
                duration: this.state.duration,
                transcript: this.state.transcript,
                isRecording: this.state.isRecording,
                isPaused: this.state.isPaused,
            };
        },

        /**
         * Obtenir la durée formatée
         * @param {number} ms - Durée en millisecondes
         * @returns {string}
         */
        formatDuration: function(ms) {
            const seconds = Math.floor(ms / 1000);
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        },

        /**
         * Obtenir la durée actuelle
         * @returns {number} Durée en ms
         */
        getCurrentDuration: function() {
            if (!this.state.isRecording || !this.state.startTime) return 0;
            return Date.now() - this.state.startTime;
        },

        /**
         * Définir la langue de reconnaissance
         * @param {string} lang - Code langue (ex: 'fr-FR', 'en-US')
         */
        setLanguage: function(lang) {
            this.config.language = lang;
            if (this.state.recognition) {
                this.state.recognition.lang = lang;
            }
        },

        /**
         * Émettre un événement personnalisé
         * @param {string} name - Nom de l'événement
         * @param {Object} detail - Détails
         * @returns {void}
         */
        emitEvent: function(name, detail = {}) {
            const event = new CustomEvent('blazing-feedback:voice-' + name, {
                bubbles: true,
                detail: detail,
            });
            document.dispatchEvent(event);
        },
    };

    // Exposer le module globalement
    window.BlazingVoiceRecorder = BlazingVoiceRecorder;

    // Initialiser au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingVoiceRecorder.init());
    } else {
        BlazingVoiceRecorder.init();
    }

})(window, document);
