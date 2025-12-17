/**
 * Blazing Feedback - Module de capture d'écran
 *
 * Utilise html2canvas pour capturer le viewport de la page
 * Gère les cas d'erreur avec fallback gracieux
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Module Screenshot
     * @namespace
     */
    const BlazingScreenshot = {

        /**
         * Options de configuration par défaut pour html2canvas
         * @type {Object}
         */
        defaultOptions: {
            useCORS: true,              // Tenter de charger les images cross-origin
            allowTaint: false,          // Ne pas permettre les images qui salissent le canvas
            backgroundColor: '#ffffff', // Fond blanc par défaut
            scale: 1,                   // Échelle 1:1 pour performance
            logging: false,             // Pas de logs en production
            imageTimeout: 5000,         // Timeout pour le chargement des images
            removeContainer: true,      // Nettoyer après capture
            foreignObjectRendering: false, // Désactivé pour compatibilité
        },

        /**
         * État du module
         * @type {Object}
         */
        state: {
            isCapturing: false,
            lastCapture: null,
            lastCaptureTime: null,
        },

        /**
         * Initialiser le module
         * @returns {void}
         */
        init: function() {
            // Vérifier la disponibilité de html2canvas
            if (typeof html2canvas === 'undefined') {
                console.warn('[Blazing Feedback] html2canvas non disponible, captures désactivées');
                return;
            }

            // Écouter l'événement de demande de capture
            document.addEventListener('blazing-feedback:capture-request', this.handleCaptureRequest.bind(this));

            console.log('[Blazing Feedback] Module Screenshot initialisé');
        },

        /**
         * Capturer le viewport actuel
         * @param {Object} options - Options personnalisées
         * @returns {Promise<string>} Data URL de l'image
         */
        capture: async function(options = {}) {
            // Empêcher les captures simultanées
            if (this.state.isCapturing) {
                return Promise.reject(new Error('Capture déjà en cours'));
            }

            this.state.isCapturing = true;

            try {
                // Masquer temporairement le widget de feedback
                this.hideWidget();

                // Fusionner les options
                const mergedOptions = { ...this.defaultOptions, ...options };

                // Ajouter les dimensions du viewport
                mergedOptions.width = window.innerWidth;
                mergedOptions.height = window.innerHeight;
                mergedOptions.windowWidth = window.innerWidth;
                mergedOptions.windowHeight = window.innerHeight;

                // Ignorer certains éléments
                mergedOptions.ignoreElements = (element) => {
                    // Ignorer le conteneur du plugin
                    if (element.id && element.id.startsWith('wpvfh-')) {
                        return true;
                    }
                    // Ignorer les éléments marqués
                    if (element.hasAttribute('data-blazing-ignore')) {
                        return true;
                    }
                    return false;
                };

                // Capturer avec html2canvas
                const canvas = await html2canvas(document.body, mergedOptions);

                // Convertir en data URL
                const dataUrl = canvas.toDataURL('image/png', 0.9);

                // Stocker la capture
                this.state.lastCapture = dataUrl;
                this.state.lastCaptureTime = Date.now();

                // Émettre l'événement de succès
                this.emitEvent('capture-success', { dataUrl });

                return dataUrl;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture:', error);

                // Émettre l'événement d'erreur
                this.emitEvent('capture-error', { error: error.message });

                // Tenter le fallback
                return this.fallbackCapture();

            } finally {
                // Réafficher le widget
                this.showWidget();
                this.state.isCapturing = false;
            }
        },

        /**
         * Capture de fallback (génère une image placeholder)
         * @returns {Promise<string>} Data URL de l'image placeholder
         */
        fallbackCapture: function() {
            return new Promise((resolve) => {
                const canvas = document.createElement('canvas');
                canvas.width = 800;
                canvas.height = 600;

                const ctx = canvas.getContext('2d');

                // Fond gris clair
                ctx.fillStyle = '#f5f5f5';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Bordure
                ctx.strokeStyle = '#ddd';
                ctx.lineWidth = 2;
                ctx.strokeRect(1, 1, canvas.width - 2, canvas.height - 2);

                // Icône et texte
                ctx.fillStyle = '#999';
                ctx.font = '48px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('📷', canvas.width / 2, canvas.height / 2 - 30);

                ctx.font = '16px Arial';
                ctx.fillText('Capture non disponible', canvas.width / 2, canvas.height / 2 + 20);

                // URL de la page
                ctx.font = '12px monospace';
                ctx.fillStyle = '#666';
                ctx.fillText(window.location.href, canvas.width / 2, canvas.height / 2 + 50);

                resolve(canvas.toDataURL('image/png'));
            });
        },

        /**
         * Capturer une zone spécifique
         * @param {HTMLElement} element - Élément à capturer
         * @param {Object} options - Options personnalisées
         * @returns {Promise<string>} Data URL de l'image
         */
        captureElement: async function(element, options = {}) {
            if (!element) {
                return Promise.reject(new Error('Élément non spécifié'));
            }

            this.state.isCapturing = true;

            try {
                this.hideWidget();

                const mergedOptions = { ...this.defaultOptions, ...options };

                const canvas = await html2canvas(element, mergedOptions);
                const dataUrl = canvas.toDataURL('image/png', 0.9);

                this.emitEvent('element-capture-success', { dataUrl, element });

                return dataUrl;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture élément:', error);
                this.emitEvent('element-capture-error', { error: error.message });
                throw error;

            } finally {
                this.showWidget();
                this.state.isCapturing = false;
            }
        },

        /**
         * Obtenir les métadonnées de l'environnement
         * @returns {Object} Métadonnées
         */
        getMetadata: function() {
            const ua = navigator.userAgent;

            return {
                // Dimensions
                screenWidth: window.screen.width,
                screenHeight: window.screen.height,
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight,
                scrollX: window.scrollX || window.pageXOffset,
                scrollY: window.scrollY || window.pageYOffset,
                devicePixelRatio: window.devicePixelRatio || 1,

                // Navigateur
                browser: this.detectBrowser(ua),
                os: this.detectOS(ua),
                device: this.detectDevice(),
                userAgent: ua,

                // Page
                url: window.location.href,
                title: document.title,
                timestamp: new Date().toISOString(),
            };
        },

        /**
         * Détecter le navigateur
         * @param {string} ua - User agent
         * @returns {string} Nom du navigateur
         */
        detectBrowser: function(ua) {
            if (ua.includes('Firefox')) {
                const match = ua.match(/Firefox\/(\d+)/);
                return 'Firefox ' + (match ? match[1] : '');
            }
            if (ua.includes('Edg/')) {
                const match = ua.match(/Edg\/(\d+)/);
                return 'Edge ' + (match ? match[1] : '');
            }
            if (ua.includes('Chrome')) {
                const match = ua.match(/Chrome\/(\d+)/);
                return 'Chrome ' + (match ? match[1] : '');
            }
            if (ua.includes('Safari') && !ua.includes('Chrome')) {
                const match = ua.match(/Version\/(\d+)/);
                return 'Safari ' + (match ? match[1] : '');
            }
            if (ua.includes('Opera') || ua.includes('OPR')) {
                return 'Opera';
            }
            return 'Inconnu';
        },

        /**
         * Détecter le système d'exploitation
         * @param {string} ua - User agent
         * @returns {string} Nom de l'OS
         */
        detectOS: function(ua) {
            if (ua.includes('Windows NT 10')) return 'Windows 10/11';
            if (ua.includes('Windows NT 6.3')) return 'Windows 8.1';
            if (ua.includes('Windows NT 6.2')) return 'Windows 8';
            if (ua.includes('Windows NT 6.1')) return 'Windows 7';
            if (ua.includes('Windows')) return 'Windows';
            if (ua.includes('Mac OS X')) {
                const match = ua.match(/Mac OS X (\d+[._]\d+)/);
                return 'macOS ' + (match ? match[1].replace('_', '.') : '');
            }
            if (ua.includes('Android')) {
                const match = ua.match(/Android (\d+\.?\d*)/);
                return 'Android ' + (match ? match[1] : '');
            }
            if (ua.includes('iOS') || ua.includes('iPhone') || ua.includes('iPad')) {
                const match = ua.match(/OS (\d+[._]\d+)/);
                return 'iOS ' + (match ? match[1].replace('_', '.') : '');
            }
            if (ua.includes('Linux')) return 'Linux';
            return 'Inconnu';
        },

        /**
         * Détecter le type d'appareil
         * @returns {string} Type d'appareil
         */
        detectDevice: function() {
            const width = window.innerWidth;

            // Vérifier si c'est un appareil tactile
            const isTouch = 'ontouchstart' in window ||
                           navigator.maxTouchPoints > 0 ||
                           navigator.msMaxTouchPoints > 0;

            if (width <= 480) {
                return 'Mobile';
            }
            if (width <= 1024) {
                return isTouch ? 'Tablet' : 'Desktop';
            }
            return 'Desktop';
        },

        /**
         * Masquer le widget de feedback
         * @returns {void}
         */
        hideWidget: function() {
            const container = document.getElementById('wpvfh-container');
            if (container) {
                container.style.visibility = 'hidden';
            }
        },

        /**
         * Afficher le widget de feedback
         * @returns {void}
         */
        showWidget: function() {
            const container = document.getElementById('wpvfh-container');
            if (container) {
                container.style.visibility = 'visible';
            }
        },

        /**
         * Gérer la demande de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureRequest: async function(event) {
            const { callback, options } = event.detail || {};

            try {
                const dataUrl = await this.capture(options);
                const metadata = this.getMetadata();

                if (typeof callback === 'function') {
                    callback(null, { dataUrl, metadata });
                }
            } catch (error) {
                if (typeof callback === 'function') {
                    callback(error, null);
                }
            }
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

        /**
         * Obtenir la dernière capture
         * @returns {Object|null} Données de la dernière capture
         */
        getLastCapture: function() {
            if (!this.state.lastCapture) {
                return null;
            }

            return {
                dataUrl: this.state.lastCapture,
                capturedAt: this.state.lastCaptureTime,
            };
        },

        /**
         * Effacer la dernière capture
         * @returns {void}
         */
        clearLastCapture: function() {
            this.state.lastCapture = null;
            this.state.lastCaptureTime = null;
        },

        /**
         * Vérifier si les captures sont disponibles
         * @returns {boolean}
         */
        isAvailable: function() {
            return typeof html2canvas !== 'undefined';
        },

        /**
         * Redimensionner une image base64
         * @param {string} dataUrl - Image en base64
         * @param {number} maxWidth - Largeur maximale
         * @param {number} maxHeight - Hauteur maximale
         * @returns {Promise<string>} Image redimensionnée
         */
        resize: function(dataUrl, maxWidth = 1200, maxHeight = 900) {
            return new Promise((resolve, reject) => {
                const img = new Image();

                img.onload = function() {
                    let { width, height } = img;

                    // Calculer les nouvelles dimensions
                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }
                    if (height > maxHeight) {
                        width = (width * maxHeight) / height;
                        height = maxHeight;
                    }

                    // Créer le canvas redimensionné
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    resolve(canvas.toDataURL('image/png', 0.85));
                };

                img.onerror = function() {
                    reject(new Error('Erreur de chargement de l\'image'));
                };

                img.src = dataUrl;
            });
        },

        /**
         * Calculer la taille d'une image base64 en octets
         * @param {string} dataUrl - Image en base64
         * @returns {number} Taille en octets
         */
        getSize: function(dataUrl) {
            // Retirer le préfixe data:...;base64,
            const base64 = dataUrl.split(',')[1] || '';
            // Calculer la taille (base64 = ~4/3 de la taille réelle)
            return Math.round((base64.length * 3) / 4);
        },

        /**
         * Formater la taille en unités lisibles
         * @param {number} bytes - Taille en octets
         * @returns {string} Taille formatée
         */
        formatSize: function(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        },
    };

    // Exposer le module globalement
    window.BlazingScreenshot = BlazingScreenshot;

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingScreenshot.init());
    } else {
        BlazingScreenshot.init();
    }

})(window, document);
