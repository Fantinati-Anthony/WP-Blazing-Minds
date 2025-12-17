/**
 * Blazing Feedback - Module d'enregistrement d'écran
 *
 * Permet l'enregistrement vidéo de l'écran avec audio optionnel
 * utilisant l'API getDisplayMedia et MediaRecorder
 *
 * @package Blazing_Feedback
 * @since 1.3.0
 */

(function(window, document) {
    'use strict';

    /**
     * Module Screen Recorder
     * @namespace
     */
    const BlazingScreenRecorder = {

        /**
         * État du module
         * @type {Object}
         */
        state: {
            isRecording: false,
            isPaused: false,
            mediaRecorder: null,
            videoChunks: [],
            videoBlob: null,
            videoUrl: null,
            displayStream: null,
            audioStream: null,
            combinedStream: null,
            startTime: null,
            duration: 0,
            previewElement: null,
        },

        /**
         * Configuration
         * @type {Object}
         */
        config: {
            maxDuration: 300000, // 5 minutes max
            mimeType: 'video/webm;codecs=vp9',
            videoBitsPerSecond: 2500000, // 2.5 Mbps
            includeAudio: true,
            includeMicrophone: false,
        },

        /**
         * Vérifier si l'enregistrement d'écran est supporté
         * @returns {boolean}
         */
        isSupported: function() {
            return !!(navigator.mediaDevices &&
                     navigator.mediaDevices.getDisplayMedia &&
                     window.MediaRecorder);
        },

        /**
         * Initialiser le module
         * @returns {void}
         */
        init: function() {
            if (!this.isSupported()) {
                console.warn('[Blazing Feedback] Enregistrement d\'écran non supporté');
                return;
            }

            console.log('[Blazing Feedback] Module Screen Recorder initialisé');
        },

        /**
         * Démarrer l'enregistrement
         * @param {Object} options - Options d'enregistrement
         * @returns {Promise<boolean>}
         */
        start: async function(options = {}) {
            if (this.state.isRecording) {
                console.warn('[Blazing Feedback] Enregistrement déjà en cours');
                return false;
            }

            const config = { ...this.config, ...options };

            try {
                // Demander l'accès à l'écran
                this.state.displayStream = await navigator.mediaDevices.getDisplayMedia({
                    video: {
                        cursor: 'always',
                        displaySurface: 'browser',
                    },
                    audio: config.includeAudio,
                });

                // Gérer l'arrêt du partage d'écran
                this.state.displayStream.getVideoTracks()[0].onended = () => {
                    if (this.state.isRecording) {
                        this.stop();
                    }
                };

                let combinedStream = this.state.displayStream;

                // Ajouter le microphone si demandé
                if (config.includeMicrophone) {
                    try {
                        this.state.audioStream = await navigator.mediaDevices.getUserMedia({
                            audio: {
                                echoCancellation: true,
                                noiseSuppression: true,
                            }
                        });

                        // Combiner les flux
                        const tracks = [
                            ...this.state.displayStream.getVideoTracks(),
                            ...this.state.displayStream.getAudioTracks(),
                            ...this.state.audioStream.getAudioTracks(),
                        ];
                        combinedStream = new MediaStream(tracks);
                    } catch (audioError) {
                        console.warn('[Blazing Feedback] Microphone non disponible:', audioError);
                    }
                }

                this.state.combinedStream = combinedStream;

                // Déterminer le mimeType supporté
                let mimeType = config.mimeType;
                const mimeTypes = [
                    'video/webm;codecs=vp9',
                    'video/webm;codecs=vp8',
                    'video/webm',
                    'video/mp4',
                ];

                for (const type of mimeTypes) {
                    if (MediaRecorder.isTypeSupported(type)) {
                        mimeType = type;
                        break;
                    }
                }

                // Créer le MediaRecorder
                const recorderOptions = {
                    mimeType: mimeType,
                    videoBitsPerSecond: config.videoBitsPerSecond,
                };

                this.state.mediaRecorder = new MediaRecorder(combinedStream, recorderOptions);
                this.state.videoChunks = [];
                this.state.startTime = Date.now();

                // Gérer les données vidéo
                this.state.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        this.state.videoChunks.push(event.data);
                    }
                };

                // Quand l'enregistrement s'arrête
                this.state.mediaRecorder.onstop = () => {
                    this.state.duration = Date.now() - this.state.startTime;
                    this.state.videoBlob = new Blob(this.state.videoChunks, { type: mimeType });
                    this.state.videoUrl = URL.createObjectURL(this.state.videoBlob);

                    this.emitEvent('recording-complete', {
                        videoBlob: this.state.videoBlob,
                        videoUrl: this.state.videoUrl,
                        duration: this.state.duration,
                    });
                };

                // Démarrer l'enregistrement
                this.state.mediaRecorder.start(1000); // Chunks toutes les secondes
                this.state.isRecording = true;
                this.state.isPaused = false;

                // Timer pour la durée maximum
                this.maxDurationTimer = setTimeout(() => {
                    if (this.state.isRecording) {
                        this.stop();
                        this.emitEvent('max-duration-reached');
                    }
                }, config.maxDuration);

                this.emitEvent('recording-started');
                console.log('[Blazing Feedback] Enregistrement écran démarré');

                return true;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur enregistrement écran:', error);
                this.emitEvent('recording-error', { error: error.message });
                this.cleanup();
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

            // Arrêter les streams
            this.stopStreams();

            this.state.isRecording = false;
            this.state.isPaused = false;

            this.emitEvent('recording-stopped');
            console.log('[Blazing Feedback] Enregistrement écran arrêté');
        },

        /**
         * Arrêter les flux média
         * @returns {void}
         */
        stopStreams: function() {
            if (this.state.displayStream) {
                this.state.displayStream.getTracks().forEach(track => track.stop());
                this.state.displayStream = null;
            }

            if (this.state.audioStream) {
                this.state.audioStream.getTracks().forEach(track => track.stop());
                this.state.audioStream = null;
            }

            this.state.combinedStream = null;
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
            if (this.state.videoUrl) {
                URL.revokeObjectURL(this.state.videoUrl);
            }

            this.state.videoChunks = [];
            this.state.videoBlob = null;
            this.state.videoUrl = null;
            this.state.duration = 0;
        },

        /**
         * Nettoyer les ressources
         * @returns {void}
         */
        cleanup: function() {
            this.stopStreams();
            this.clear();
            this.state.isRecording = false;
            this.state.isPaused = false;
        },

        /**
         * Obtenir les données vidéo en base64
         * @returns {Promise<string|null>}
         */
        getVideoBase64: async function() {
            if (!this.state.videoBlob) return null;

            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(this.state.videoBlob);
            });
        },

        /**
         * Créer une miniature de la vidéo
         * @param {number} seekTime - Position en secondes pour la miniature
         * @returns {Promise<string|null>}
         */
        createThumbnail: async function(seekTime = 0) {
            if (!this.state.videoUrl) return null;

            return new Promise((resolve, reject) => {
                const video = document.createElement('video');
                video.src = this.state.videoUrl;
                video.crossOrigin = 'anonymous';
                video.muted = true;

                video.onloadedmetadata = () => {
                    video.currentTime = Math.min(seekTime, video.duration);
                };

                video.onseeked = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0);

                    try {
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                        resolve(dataUrl);
                    } catch (e) {
                        reject(e);
                    }
                };

                video.onerror = reject;
            });
        },

        /**
         * Obtenir les données d'enregistrement
         * @returns {Object}
         */
        getData: function() {
            return {
                videoBlob: this.state.videoBlob,
                videoUrl: this.state.videoUrl,
                duration: this.state.duration,
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
         * Estimer la taille du fichier
         * @returns {number} Taille en octets
         */
        estimateFileSize: function() {
            const duration = this.getCurrentDuration() || this.state.duration;
            // Estimation basée sur le bitrate
            return Math.floor((this.config.videoBitsPerSecond / 8) * (duration / 1000));
        },

        /**
         * Formater la taille du fichier
         * @param {number} bytes - Taille en octets
         * @returns {string}
         */
        formatFileSize: function(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },

        /**
         * Émettre un événement personnalisé
         * @param {string} name - Nom de l'événement
         * @param {Object} detail - Détails
         * @returns {void}
         */
        emitEvent: function(name, detail = {}) {
            const event = new CustomEvent('blazing-feedback:screen-' + name, {
                bubbles: true,
                detail: detail,
            });
            document.dispatchEvent(event);
        },
    };

    // Exposer le module globalement
    window.BlazingScreenRecorder = BlazingScreenRecorder;

    // Initialiser au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingScreenRecorder.init());
    } else {
        BlazingScreenRecorder.init();
    }

})(window, document);
