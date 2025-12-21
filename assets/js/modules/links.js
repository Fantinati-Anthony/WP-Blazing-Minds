/**
 * Module Links - Blazing Feedback
 * Gestion des liens enrichis
 * @package Blazing_Feedback
 */
(function(window, document) {
    'use strict';

    const Links = {
        links: [],

        init: function(widget) {
            this.widget = widget;
            this.links = [];
        },

        addLink: function() {
            const el = this.widget.elements;
            const url = el.linkUrlInput?.value?.trim();

            if (!url) {
                this.widget.modules.notifications.show('Veuillez entrer une URL', 'warning');
                return;
            }

            // Validation URL basique
            if (!this.isValidUrl(url)) {
                this.widget.modules.notifications.show('URL invalide', 'error');
                return;
            }

            // Afficher loading
            this.showLoading();

            // Récupérer les métadonnées
            this.fetchLinkMetadata(url)
                .then(metadata => {
                    this.hideLoading();
                    this.links.push(metadata);
                    this.renderLinks();
                    this.updateHiddenField();
                    el.linkUrlInput.value = '';
                })
                .catch(error => {
                    this.hideLoading();
                    console.error('Erreur fetch metadata:', error);
                    // Ajouter quand même avec données minimales
                    const metadata = {
                        url: url,
                        title: this.extractDomain(url),
                        description: '',
                        image: null
                    };
                    this.links.push(metadata);
                    this.renderLinks();
                    this.updateHiddenField();
                    el.linkUrlInput.value = '';
                });
        },

        isValidUrl: function(string) {
            try {
                const url = new URL(string);
                return url.protocol === 'http:' || url.protocol === 'https:';
            } catch (_) {
                return false;
            }
        },

        extractDomain: function(url) {
            try {
                const urlObj = new URL(url);
                return urlObj.hostname;
            } catch (_) {
                return url;
            }
        },

        fetchLinkMetadata: function(url) {
            return new Promise((resolve, reject) => {
                // Utiliser l'API du plugin pour récupérer les métadonnées
                const formData = new FormData();
                formData.append('action', 'wpvfh_fetch_link_metadata');
                formData.append('url', url);
                formData.append('nonce', this.widget.config.nonce);

                fetch(this.widget.config.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        resolve({
                            url: url,
                            title: data.data.title || this.extractDomain(url),
                            description: data.data.description || '',
                            image: data.data.image || null
                        });
                    } else {
                        // Fallback avec données minimales
                        resolve({
                            url: url,
                            title: this.extractDomain(url),
                            description: '',
                            image: null
                        });
                    }
                })
                .catch(() => {
                    // Fallback avec données minimales
                    resolve({
                        url: url,
                        title: this.extractDomain(url),
                        description: '',
                        image: null
                    });
                });
            });
        },

        showLoading: function() {
            const el = this.widget.elements;
            if (!el.linksList) return;

            const loading = document.createElement('div');
            loading.className = 'wpvfh-link-loading';
            loading.id = 'wpvfh-link-loading';
            loading.textContent = 'Chargement...';
            el.linksList.appendChild(loading);
        },

        hideLoading: function() {
            const loading = document.getElementById('wpvfh-link-loading');
            if (loading) loading.remove();
        },

        renderLinks: function() {
            const el = this.widget.elements;
            if (!el.linksList) return;

            el.linksList.innerHTML = '';

            this.links.forEach((link, index) => {
                const item = document.createElement('div');
                item.className = 'wpvfh-link-item';
                item.innerHTML = `
                    ${link.image
                        ? `<img src="${this.escapeHtml(link.image)}" alt="" class="wpvfh-link-thumbnail" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                           <div class="wpvfh-link-thumbnail-placeholder" style="display:none;">🔗</div>`
                        : `<div class="wpvfh-link-thumbnail-placeholder">🔗</div>`
                    }
                    <div class="wpvfh-link-info">
                        <div class="wpvfh-link-title">${this.escapeHtml(link.title)}</div>
                        ${link.description ? `<div class="wpvfh-link-description">${this.escapeHtml(link.description)}</div>` : ''}
                        <div class="wpvfh-link-url">${this.escapeHtml(link.url)}</div>
                    </div>
                    <button type="button" class="wpvfh-link-remove" data-index="${index}" title="Supprimer">&times;</button>
                `;

                // Event pour supprimer
                item.querySelector('.wpvfh-link-remove').addEventListener('click', () => {
                    this.removeLink(index);
                });

                el.linksList.appendChild(item);
            });
        },

        removeLink: function(index) {
            this.links.splice(index, 1);
            this.renderLinks();
            this.updateHiddenField();
        },

        updateHiddenField: function() {
            const el = this.widget.elements;
            if (el.linksData) {
                el.linksData.value = JSON.stringify(this.links);
            }
        },

        getLinks: function() {
            return this.links;
        },

        reset: function() {
            this.links = [];
            this.renderLinks();
            this.updateHiddenField();
        },

        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Exposer le module
    window.BlazingLinks = Links;

    // Enregistrer dans FeedbackWidget.modules
    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.links = Links;

})(window, document);
