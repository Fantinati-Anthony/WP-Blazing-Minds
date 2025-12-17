/**
 * Blazing Feedback - Module d'annotation avec DOM Anchoring
 *
 * Système de positionnement des pins basé sur l'ancrage DOM
 * comme ProjectHuddle/SureFeedback
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Module Annotation avec DOM Anchoring
     * @namespace
     */
    const BlazingAnnotation = {

        /**
         * État du module
         * @type {Object}
         */
        state: {
            isActive: false,           // Mode annotation actif
            currentPin: null,          // Pin en cours de placement
            pins: [],                  // Liste des pins affichés
            selectedPin: null,         // Pin sélectionné
            clickPosition: null,       // Position du dernier clic
            clickHandler: null,        // Référence au gestionnaire de clics global
            repositionTimeout: null,   // Timeout pour le repositionnement debounced
        },

        /**
         * Configuration
         * @type {Object}
         */
        config: {
            pinSize: 28,
            pinColor: '#e74c3c',
            pinActiveColor: '#c0392b',
            animationDuration: 200,
            repositionDebounce: 100,   // ms pour debounce du repositionnement
        },

        /**
         * Éléments DOM
         * @type {Object}
         */
        elements: {
            overlay: null,
            pinsContainer: null,
            hint: null,
        },

        /**
         * Initialiser le module
         * @returns {void}
         */
        init: function() {
            this.cacheElements();
            this.setupPinsContainer();
            this.bindEvents();

            console.log('[Blazing Feedback] Module Annotation avec DOM Anchoring initialisé');
        },

        /**
         * Configurer le conteneur des pins
         * @returns {void}
         */
        setupPinsContainer: function() {
            if (!this.elements.pinsContainer) return;

            // Déplacer le conteneur au niveau du body
            document.body.appendChild(this.elements.pinsContainer);

            // Style du conteneur - couvre toute la page
            this.elements.pinsContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 999990;
                overflow: visible;
            `;
        },

        /**
         * Mettre en cache les éléments DOM
         * @returns {void}
         */
        cacheElements: function() {
            this.elements.overlay = document.getElementById('wpvfh-annotation-overlay');
            this.elements.pinsContainer = document.getElementById('wpvfh-pins-container');
        },

        /**
         * Attacher les gestionnaires d'événements
         * @returns {void}
         */
        bindEvents: function() {
            // Écouter les événements personnalisés
            document.addEventListener('blazing-feedback:start-annotation', this.activate.bind(this));
            document.addEventListener('blazing-feedback:stop-annotation', this.deactivate.bind(this));
            document.addEventListener('blazing-feedback:load-pins', this.loadPins.bind(this));
            document.addEventListener('blazing-feedback:clear-pins', this.clearPins.bind(this));

            // Redimensionnement - repositionner tous les pins
            window.addEventListener('resize', this.debouncedReposition.bind(this));
            window.addEventListener('scroll', this.debouncedReposition.bind(this), { passive: true });

            // Observer les changements du DOM
            this.observeDOMChanges();

            // Créer le gestionnaire de clics global
            this.state.clickHandler = this.handleGlobalClick.bind(this);

            // Annuler avec Echap
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.state.isActive) {
                    this.deactivate();
                }
            });
        },

        /**
         * Observer les changements du DOM pour repositionner les pins
         * @returns {void}
         */
        observeDOMChanges: function() {
            if (typeof MutationObserver === 'undefined') return;

            const observer = new MutationObserver((mutations) => {
                // Ignorer les mutations liées au widget lui-même
                const shouldReposition = mutations.some(mutation => {
                    return !mutation.target.closest('.wpvfh-widget') &&
                           !mutation.target.closest('.wpvfh-pins-container');
                });

                if (shouldReposition) {
                    this.debouncedReposition();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class'],
            });
        },

        /**
         * Repositionnement debounced pour éviter les appels trop fréquents
         * @returns {void}
         */
        debouncedReposition: function() {
            if (this.state.repositionTimeout) {
                clearTimeout(this.state.repositionTimeout);
            }

            this.state.repositionTimeout = setTimeout(() => {
                this.repositionAllPins();
            }, this.config.repositionDebounce);
        },

        /**
         * Activer le mode annotation
         * @returns {void}
         */
        activate: function() {
            if (this.state.isActive) return;

            this.state.isActive = true;

            // Afficher l'overlay
            if (this.elements.overlay) {
                this.elements.overlay.hidden = false;
                this.elements.overlay.setAttribute('aria-hidden', 'false');
            }

            // Ajouter la classe au body
            document.body.classList.add('wpvfh-annotation-mode');

            // Changer le curseur
            document.body.style.cursor = 'crosshair';

            // Ajouter les gestionnaires de clics globaux
            document.addEventListener('click', this.state.clickHandler, true);
            document.addEventListener('mousedown', this.preventInteraction, true);
            document.addEventListener('mouseup', this.preventInteraction, true);
            document.addEventListener('touchstart', this.state.clickHandler, true);
            document.addEventListener('touchend', this.preventInteraction, true);

            // Émettre l'événement
            this.emitEvent('annotation-activated');

            console.log('[Blazing Feedback] Mode annotation activé');
        },

        /**
         * Désactiver le mode annotation
         * @returns {void}
         */
        deactivate: function() {
            if (!this.state.isActive) return;

            this.state.isActive = false;
            this.state.currentPin = null;

            // Masquer l'overlay
            if (this.elements.overlay) {
                this.elements.overlay.hidden = true;
                this.elements.overlay.setAttribute('aria-hidden', 'true');
            }

            // Retirer la classe du body
            document.body.classList.remove('wpvfh-annotation-mode');

            // Restaurer le curseur
            document.body.style.cursor = '';

            // Retirer les gestionnaires de clics globaux
            document.removeEventListener('click', this.state.clickHandler, true);
            document.removeEventListener('mousedown', this.preventInteraction, true);
            document.removeEventListener('mouseup', this.preventInteraction, true);
            document.removeEventListener('touchstart', this.state.clickHandler, true);
            document.removeEventListener('touchend', this.preventInteraction, true);

            // Émettre l'événement
            this.emitEvent('annotation-deactivated');

            console.log('[Blazing Feedback] Mode annotation désactivé');
        },

        /**
         * Empêcher les interactions pendant le mode annotation
         * @param {Event} event - Événement
         * @returns {void}
         */
        preventInteraction: function(event) {
            if (event.target.closest('.wpvfh-widget') ||
                event.target.closest('.wpvfh-pin') ||
                event.target.classList.contains('wpvfh-hint-close')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
        },

        /**
         * Gérer le clic global - DOM Anchoring
         * @param {MouseEvent|TouchEvent} event - Événement de clic
         * @returns {void}
         */
        handleGlobalClick: function(event) {
            if (!this.state.isActive) return;

            // Ignorer si clic sur le widget
            if (event.target.closest('.wpvfh-widget') ||
                event.target.closest('.wpvfh-pin')) {
                return;
            }

            // Ignorer si clic sur le bouton annuler
            if (event.target.classList.contains('wpvfh-hint-close')) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                this.deactivate();
                return;
            }

            // Bloquer l'événement
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            // Obtenir les coordonnées du clic
            let clientX, clientY;
            if (event.type === 'touchstart' && event.touches && event.touches.length > 0) {
                clientX = event.touches[0].clientX;
                clientY = event.touches[0].clientY;
            } else {
                clientX = event.clientX;
                clientY = event.clientY;
            }

            console.log('[Blazing Feedback] Clic capturé à:', clientX, clientY);

            // Masquer temporairement l'overlay pour trouver l'élément sous le clic
            if (this.elements.overlay) {
                this.elements.overlay.style.visibility = 'hidden';
            }

            // Obtenir l'élément cible (anchor element)
            const targetElement = document.elementFromPoint(clientX, clientY);

            // Restaurer l'overlay
            if (this.elements.overlay) {
                this.elements.overlay.style.visibility = '';
            }

            if (!targetElement) {
                console.warn('[Blazing Feedback] Aucun élément trouvé sous le clic');
                return;
            }

            // Calculer la position DOM Anchoring
            const anchorData = this.calculateAnchorPosition(targetElement, clientX, clientY);

            // Stocker les données de position
            this.state.clickPosition = anchorData;

            console.log('[Blazing Feedback] DOM Anchor:', anchorData);

            // Créer le pin temporaire
            this.createTemporaryPin(clientX, clientY);

            // Émettre l'événement avec les données d'ancrage
            this.emitEvent('pin-placed', anchorData);

            // Désactiver le mode annotation
            this.deactivate();
        },

        /**
         * Calculer la position d'ancrage DOM
         * @param {HTMLElement} element - Élément cible
         * @param {number} clientX - Position X du clic (viewport)
         * @param {number} clientY - Position Y du clic (viewport)
         * @returns {Object} Données d'ancrage
         */
        calculateAnchorPosition: function(element, clientX, clientY) {
            // Obtenir le rectangle de l'élément
            const rect = element.getBoundingClientRect();

            // Position du clic relative à l'élément (en pourcentage)
            const elementOffsetX = ((clientX - rect.left) / rect.width) * 100;
            const elementOffsetY = ((clientY - rect.top) / rect.height) * 100;

            // Position absolue sur la page (fallback)
            const scrollX = window.scrollX || window.pageXOffset;
            const scrollY = window.scrollY || window.pageYOffset;
            const absoluteX = clientX + scrollX;
            const absoluteY = clientY + scrollY;

            // Position en pourcentage de la page (fallback)
            const pageWidth = document.documentElement.scrollWidth;
            const pageHeight = document.documentElement.scrollHeight;
            const percentX = (absoluteX / pageWidth) * 100;
            const percentY = (absoluteY / pageHeight) * 100;

            // Générer le sélecteur CSS robuste
            const selector = this.generateRobustSelector(element);

            return {
                // DOM Anchoring - données principales
                selector: selector,
                element_offset_x: elementOffsetX,
                element_offset_y: elementOffsetY,

                // Fallback - pourcentages de la page
                percentX: percentX,
                percentY: percentY,
                position_x: percentX,  // Pour compatibilité avec l'API
                position_y: percentY,

                // Métadonnées supplémentaires
                absoluteX: absoluteX,
                absoluteY: absoluteY,
                clientX: clientX,
                clientY: clientY,
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight,
                scrollX: scrollX,
                scrollY: scrollY,

                // Info sur l'élément d'ancrage
                anchor_tag: element.tagName.toLowerCase(),
                anchor_rect: {
                    width: rect.width,
                    height: rect.height,
                },
            };
        },

        /**
         * Générer un sélecteur CSS robuste pour l'élément
         * @param {HTMLElement} element - Élément
         * @returns {string} Sélecteur CSS
         */
        generateRobustSelector: function(element) {
            if (!element || element === document.body || element === document.documentElement) {
                return 'body';
            }

            // 1. Priorité à l'ID unique
            if (element.id && document.querySelectorAll('#' + CSS.escape(element.id)).length === 1) {
                return '#' + CSS.escape(element.id);
            }

            // 2. Data attributes uniques
            const dataId = element.dataset.id || element.dataset.key || element.dataset.testid;
            if (dataId) {
                const dataSelector = `[data-id="${dataId}"], [data-key="${dataId}"], [data-testid="${dataId}"]`;
                try {
                    if (document.querySelectorAll(dataSelector.split(', ')[0]).length === 1) {
                        return dataSelector.split(', ')[0];
                    }
                } catch (e) {}
            }

            // 3. Construire un chemin de sélecteur
            const path = [];
            let current = element;
            let depth = 0;
            const maxDepth = 6;

            while (current && current !== document.body && depth < maxDepth) {
                let selector = current.tagName.toLowerCase();

                // Ajouter l'ID s'il existe
                if (current.id) {
                    selector = '#' + CSS.escape(current.id);
                    path.unshift(selector);
                    break; // On a un ID, on peut s'arrêter
                }

                // Ajouter des classes significatives (ignorer les classes dynamiques)
                if (current.className && typeof current.className === 'string') {
                    const classes = current.className.split(/\s+/)
                        .filter(c => {
                            return c &&
                                   !c.startsWith('wpvfh-') &&
                                   !c.startsWith('is-') &&
                                   !c.startsWith('has-') &&
                                   !c.match(/^(active|open|visible|hidden|hover|focus|selected)$/i) &&
                                   !c.match(/^[a-z]+-\d+$/i); // classes avec nombres dynamiques
                        })
                        .slice(0, 2);

                    if (classes.length) {
                        selector += '.' + classes.map(c => CSS.escape(c)).join('.');
                    }
                }

                // Ajouter nth-of-type pour unicité
                if (current.parentElement) {
                    const siblings = Array.from(current.parentElement.children)
                        .filter(el => el.tagName === current.tagName);

                    if (siblings.length > 1) {
                        const index = siblings.indexOf(current) + 1;
                        selector += `:nth-of-type(${index})`;
                    }
                }

                path.unshift(selector);
                current = current.parentElement;
                depth++;
            }

            const finalSelector = path.join(' > ');

            // Vérifier que le sélecteur retourne bien l'élément attendu
            try {
                const found = document.querySelector(finalSelector);
                if (found !== element) {
                    console.warn('[Blazing Feedback] Sélecteur imprécis:', finalSelector);
                }
            } catch (e) {
                console.warn('[Blazing Feedback] Sélecteur invalide:', finalSelector);
            }

            return finalSelector;
        },

        /**
         * Créer un pin temporaire (avant soumission)
         * @param {number} x - Position X en pixels (viewport)
         * @param {number} y - Position Y en pixels (viewport)
         * @returns {void}
         */
        createTemporaryPin: function(x, y) {
            // Supprimer l'ancien pin temporaire
            const oldPin = document.querySelector('.wpvfh-pin-temp');
            if (oldPin) {
                oldPin.remove();
            }

            // Créer le nouveau pin (position fixe pour le temporaire)
            const pin = document.createElement('div');
            pin.className = 'wpvfh-pin wpvfh-pin-temp';
            pin.style.cssText = `
                position: fixed;
                left: ${x}px;
                top: ${y}px;
                width: ${this.config.pinSize}px;
                height: ${this.config.pinSize}px;
                background: ${this.config.pinColor};
                border: 3px solid #fff;
                border-radius: 50%;
                transform: translate(-50%, -50%);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                z-index: 2147483647;
                cursor: pointer;
                animation: wpvfh-pin-appear 0.2s ease-out;
                pointer-events: auto;
            `;

            pin.innerHTML = '<span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-weight:bold;font-size:12px;">+</span>';

            document.body.appendChild(pin);
            this.state.currentPin = pin;

            console.log('[Blazing Feedback] Pin temporaire créé à:', x, y);
        },

        /**
         * Supprimer le pin temporaire
         * @returns {void}
         */
        removeTemporaryPin: function() {
            const tempPin = document.querySelector('.wpvfh-pin-temp');
            if (tempPin) {
                tempPin.remove();
            }
            this.state.currentPin = null;
            this.state.clickPosition = null;
        },

        /**
         * Charger et afficher les pins existants
         * @param {CustomEvent} event - Événement avec les données des pins
         * @returns {void}
         */
        loadPins: function(event) {
            const { pins } = event.detail || {};

            if (!pins || !Array.isArray(pins)) {
                return;
            }

            // Effacer les pins existants
            this.clearPins();

            // Créer les nouveaux pins
            pins.forEach(pinData => this.createPin(pinData));

            // Repositionner immédiatement
            setTimeout(() => this.repositionAllPins(), 100);
        },

        /**
         * Créer un pin permanent avec DOM Anchoring
         * @param {Object} data - Données du pin
         * @returns {HTMLElement} Élément du pin
         */
        createPin: function(data) {
            const pin = document.createElement('div');
            pin.className = 'wpvfh-pin wpvfh-pin-saved';
            pin.dataset.feedbackId = data.id;
            pin.dataset.status = data.status || 'new';

            // Couleur selon le statut
            const statusColors = {
                new: '#3498db',
                in_progress: '#f39c12',
                resolved: '#27ae60',
                rejected: '#e74c3c',
            };
            const color = statusColors[data.status] || statusColors.new;

            // Style de base du pin
            pin.style.cssText = `
                position: absolute;
                width: ${this.config.pinSize}px;
                height: ${this.config.pinSize}px;
                background: ${color};
                border: 3px solid #fff;
                border-radius: 50%;
                transform: translate(-50%, -50%);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                cursor: pointer;
                transition: transform 0.2s ease, opacity 0.2s ease;
                pointer-events: auto;
            `;

            // Icône selon le statut
            const icons = {
                new: '!',
                in_progress: '⏳',
                resolved: '✓',
                rejected: '✗',
            };
            pin.innerHTML = `<span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-weight:bold;font-size:12px;">${icons[data.status] || '!'}</span>`;

            // Événements
            pin.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectPin(data.id);
            });

            pin.addEventListener('mouseenter', () => {
                pin.style.transform = 'translate(-50%, -50%) scale(1.2)';
            });

            pin.addEventListener('mouseleave', () => {
                pin.style.transform = 'translate(-50%, -50%)';
            });

            // Ajouter au conteneur
            if (this.elements.pinsContainer) {
                this.elements.pinsContainer.appendChild(pin);
            }

            // Stocker la référence avec toutes les données d'ancrage
            this.state.pins.push({
                element: pin,
                data: data,
            });

            // Positionner le pin selon son ancrage DOM
            this.positionPinByAnchor(pin, data);

            return pin;
        },

        /**
         * Positionner un pin selon son ancrage DOM
         * @param {HTMLElement} pin - Élément du pin
         * @param {Object} data - Données du pin
         * @returns {void}
         */
        positionPinByAnchor: function(pin, data) {
            let positioned = false;

            // 1. Essayer de positionner via le sélecteur DOM
            if (data.selector) {
                const anchorElement = this.findElement(data.selector);

                if (anchorElement) {
                    const rect = anchorElement.getBoundingClientRect();
                    const scrollX = window.scrollX || window.pageXOffset;
                    const scrollY = window.scrollY || window.pageYOffset;

                    // Utiliser l'offset relatif à l'élément si disponible
                    let offsetX = data.element_offset_x;
                    let offsetY = data.element_offset_y;

                    // Fallback: centre de l'élément si pas d'offset
                    if (typeof offsetX !== 'number') offsetX = 50;
                    if (typeof offsetY !== 'number') offsetY = 50;

                    // Calculer la position absolue
                    const absoluteX = rect.left + scrollX + (rect.width * offsetX / 100);
                    const absoluteY = rect.top + scrollY + (rect.height * offsetY / 100);

                    pin.style.left = absoluteX + 'px';
                    pin.style.top = absoluteY + 'px';
                    pin.style.opacity = '1';

                    positioned = true;

                    console.log('[Blazing Feedback] Pin #' + data.id + ' positionné via DOM anchor');
                }
            }

            // 2. Fallback: utiliser les pourcentages de la page
            if (!positioned) {
                const pageWidth = document.documentElement.scrollWidth;
                const pageHeight = document.documentElement.scrollHeight;

                const posX = data.position_x || data.percentX || 50;
                const posY = data.position_y || data.percentY || 50;

                const absoluteX = (posX / 100) * pageWidth;
                const absoluteY = (posY / 100) * pageHeight;

                pin.style.left = absoluteX + 'px';
                pin.style.top = absoluteY + 'px';
                pin.style.opacity = '0.7'; // Légèrement transparent si fallback

                console.log('[Blazing Feedback] Pin #' + data.id + ' positionné via fallback (%)', posX, posY);
            }
        },

        /**
         * Repositionner tous les pins selon leur ancrage DOM
         * @returns {void}
         */
        repositionAllPins: function() {
            this.state.pins.forEach(pin => {
                this.positionPinByAnchor(pin.element, pin.data);
            });
        },

        /**
         * Trouver un élément par son sélecteur
         * @param {string} selector - Sélecteur CSS
         * @returns {HTMLElement|null} Élément trouvé
         */
        findElement: function(selector) {
            if (!selector || selector === 'body') {
                return document.body;
            }

            try {
                return document.querySelector(selector);
            } catch (e) {
                console.warn('[Blazing Feedback] Sélecteur invalide:', selector);
                return null;
            }
        },

        /**
         * Sélectionner un pin
         * @param {number} feedbackId - ID du feedback
         * @returns {void}
         */
        selectPin: function(feedbackId) {
            // Désélectionner l'ancien
            if (this.state.selectedPin) {
                const oldPin = this.state.pins.find(p => p.data.id === this.state.selectedPin);
                if (oldPin) {
                    oldPin.element.classList.remove('wpvfh-pin-selected');
                }
            }

            // Sélectionner le nouveau
            this.state.selectedPin = feedbackId;
            const pin = this.state.pins.find(p => p.data.id === feedbackId);
            if (pin) {
                pin.element.classList.add('wpvfh-pin-selected');
            }

            // Émettre l'événement
            this.emitEvent('pin-selected', { feedbackId, pinData: pin?.data });
        },

        /**
         * Effacer tous les pins
         * @returns {void}
         */
        clearPins: function() {
            if (this.elements.pinsContainer) {
                this.elements.pinsContainer.innerHTML = '';
            }
            this.state.pins = [];
            this.state.selectedPin = null;
        },

        /**
         * Mettre à jour un pin existant
         * @param {number} feedbackId - ID du feedback
         * @param {Object} newData - Nouvelles données
         * @returns {void}
         */
        updatePin: function(feedbackId, newData) {
            const pinIndex = this.state.pins.findIndex(p => p.data.id === feedbackId);

            if (pinIndex === -1) return;

            const pin = this.state.pins[pinIndex];

            // Mettre à jour les données
            Object.assign(pin.data, newData);

            // Mettre à jour l'apparence si le statut a changé
            if (newData.status) {
                pin.element.dataset.status = newData.status;

                const statusColors = {
                    new: '#3498db',
                    in_progress: '#f39c12',
                    resolved: '#27ae60',
                    rejected: '#e74c3c',
                };
                pin.element.style.background = statusColors[newData.status] || statusColors.new;

                const icons = {
                    new: '!',
                    in_progress: '⏳',
                    resolved: '✓',
                    rejected: '✗',
                };
                pin.element.querySelector('span').textContent = icons[newData.status] || '!';
            }
        },

        /**
         * Supprimer un pin
         * @param {number} feedbackId - ID du feedback
         * @returns {void}
         */
        removePin: function(feedbackId) {
            const pinIndex = this.state.pins.findIndex(p => p.data.id === feedbackId);

            if (pinIndex === -1) return;

            const pin = this.state.pins[pinIndex];
            pin.element.remove();
            this.state.pins.splice(pinIndex, 1);

            if (this.state.selectedPin === feedbackId) {
                this.state.selectedPin = null;
            }
        },

        /**
         * Scroller vers un pin
         * @param {number} feedbackId - ID du feedback
         * @returns {void}
         */
        scrollToPin: function(feedbackId) {
            const pin = this.state.pins.find(p => p.data.id === feedbackId);

            if (!pin) return;

            const rect = pin.element.getBoundingClientRect();
            const scrollY = window.scrollY || window.pageYOffset;

            window.scrollTo({
                top: scrollY + rect.top - (window.innerHeight / 2),
                behavior: 'smooth',
            });

            // Sélectionner le pin après le scroll
            setTimeout(() => this.selectPin(feedbackId), 500);
        },

        /**
         * Obtenir la position actuelle du pin
         * @returns {Object|null} Position du pin
         */
        getPosition: function() {
            return this.state.clickPosition;
        },

        /**
         * Vérifier si le mode annotation est actif
         * @returns {boolean}
         */
        isActive: function() {
            return this.state.isActive;
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

    // Exposer le module globalement
    window.BlazingAnnotation = BlazingAnnotation;

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingAnnotation.init());
    } else {
        BlazingAnnotation.init();
    }

})(window, document);
