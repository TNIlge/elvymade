// Fonctions utilitaires pour ElvyMade
class ElvyMade {
    constructor() {
        this.init();
    }

    init() {
        this.setupAnimations();
        this.setupLazyLoading();
        this.setupSmoothScroll();
        this.setupNotifications();
        this.setupModals();
        this.setupTooltips();
        this.setupCounters();
        this.setupTabs();
        this.setupAccordions();
        this.setupLiveSearch();
    }

    // Configuration des animations au scroll
    setupAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observer tous les éléments avec animation
        document.querySelectorAll('.fade-in-up, .slide-in, .fade-in').forEach(el => {
            observer.observe(el);
        });
    }

    // Configuration du lazy loading pour les images
    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('loading-shimmer');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                img.classList.add('loading-shimmer');
                imageObserver.observe(img);
            });
        }
    }

    // Configuration du smooth scroll
    setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Système de notifications
    setupNotifications() {
        this.notificationContainer = document.createElement('div');
        this.notificationContainer.className = 'notification-container';
        this.notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1500;
            pointer-events: none;
        `;
        document.body.appendChild(this.notificationContainer);
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            pointer-events: auto;
            max-width: 350px;
        `;

        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#8b5cf6'
        };

        notification.style.borderLeftColor = colors[type] || colors.info;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-${this.getNotificationIcon(type)}" style="color: ${colors[type] || colors.info};"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="
                    background: none;
                    border: none;
                    cursor: pointer;
                    color: #64748b;
                    margin-left: auto;
                ">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        this.notificationContainer.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Suppression automatique
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }

    // Configuration des modales
    setupModals() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-modal]')) {
                e.preventDefault();
                this.openModal(e.target.dataset.modal);
            }

            if (e.target.matches('.modal-close') || e.target.matches('.modal')) {
                this.closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal() {
        document.querySelectorAll('.modal.active').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    // Configuration des tooltips
    setupTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.classList.add('tooltip');
        });
    }

    // Configuration des compteurs animés
    setupCounters() {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        });

        document.querySelectorAll('.counter-number').forEach(counter => {
            counterObserver.observe(counter);
        });
    }

    animateCounter(element) {
        const target = parseInt(element.dataset.count || element.textContent);
        const duration = 2000;
        const start = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(progress * target);

            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    // Configuration des onglets
    setupTabs() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.tab-button')) {
                e.preventDefault();
                this.switchTab(e.target);
            }
        });
    }

    switchTab(button) {
        const tabGroup = button.closest('.tabs');
        const targetId = button.dataset.tab;

        // Désactiver tous les onglets
        tabGroup.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });

        // Activer l'onglet cliqué
        button.classList.add('active');

        // Masquer tous les contenus
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });

        // Afficher le contenu correspondant
        const targetContent = document.getElementById(targetId);
        if (targetContent) {
            targetContent.classList.add('active');
        }
    }

    // Configuration des accordéons
    setupAccordions() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.accordion-header') || e.target.closest('.accordion-header')) {
                const header = e.target.closest('.accordion-header');
                const item = header.closest('.accordion-item');
                this.toggleAccordion(item);
            }
        });
    }

    toggleAccordion(item) {
        const isActive = item.classList.contains('active');
        
        // Fermer tous les autres accordéons du même groupe
        const group = item.closest('.accordion-group');
        if (group) {
            group.querySelectorAll('.accordion-item.active').forEach(activeItem => {
                if (activeItem !== item) {
                    activeItem.classList.remove('active');
                }
            });
        }

        // Basculer l'accordéon actuel
        item.classList.toggle('active', !isActive);
    }

    // Utilitaires pour les favoris
    toggleFavorite(productId, element) {
        const isFavorite = element.classList.contains('active');
        
        if (isFavorite) {
            this.removeFavorite(productId);
            element.classList.remove('active');
            this.showNotification('Produit retiré des favoris', 'info');
        } else {
            this.addFavorite(productId);
            element.classList.add('active');
            this.showNotification('Produit ajouté aux favoris', 'success');
        }

        // Animation
        element.style.transform = 'scale(1.2)';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 200);
    }

    addFavorite(productId) {
        let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        if (!favorites.includes(productId)) {
            favorites.push(productId);
            localStorage.setItem('favorites', JSON.stringify(favorites));
        }
        this.updateFavoriteCount();
    }

    removeFavorite(productId) {
        let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        favorites = favorites.filter(id => id !== productId);
        localStorage.setItem('favorites', JSON.stringify(favorites));
        this.updateFavoriteCount();
    }

    updateFavoriteCount() {
        if (window.isLoggedIn) return; // NE PAS écraser le compteur si connecté
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        const countElements = document.querySelectorAll('.favorite-count');
        countElements.forEach(element => {
            element.textContent = favorites.length;
            element.style.display = 'inline'; // Toujours visible
        });
    }

    // Fonction pour contacter via WhatsApp
    contactWhatsApp(productId, productName = '') {
        const message = `Bonjour, je suis intéressé(e) par ${productName ? `le produit "${productName}"` : `le produit ID: ${productId}`} sur ElvyMade.`;
        const whatsappUrl = `https://wa.me/237696095805?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    }

    // Fonction de recherche en temps réel
    setupLiveSearch() {
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            let searchTimeout;
            
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        }
    }

    async performSearch(query) {
        if (query.length < 2) return;

        try {
            const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
            const results = await response.json();
            this.displaySearchResults(results);
        } catch (error) {
            console.error('Erreur de recherche:', error);
        }
    }

    displaySearchResults(results) {
        // Logique pour afficher les résultats de recherche
        console.log('Résultats de recherche:', results);
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    window.elvyMade = new ElvyMade();
    
    // Charger les favoris au démarrage
    window.elvyMade.updateFavoriteCount();
    
    // Marquer les favoris existants
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    favorites.forEach(productId => {
        const favoriteBtn = document.querySelector(`[onclick*="${productId}"]`);
        if (favoriteBtn) {
            favoriteBtn.classList.add('active');
        }
    });
});

// Fonctions globales pour la compatibilité
function toggleFavorite(productId) {
    const element = event.target.closest('.favorite-btn');
    window.elvyMade.toggleFavorite(productId, element);
}

function contactWhatsApp(productId, productName = '') {
    window.elvyMade.contactWhatsApp(productId, productName);
}

function showNotification(message, type = 'info') {
    window.elvyMade.showNotification(message, type);
}

function updateFavoriteCount() {
    if (window.isLoggedIn) return; // NE PAS écraser le compteur si connecté
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const countElements = document.querySelectorAll('.favorite-count');
    countElements.forEach(element => {
        element.textContent = favorites.length;
        element.style.display = 'inline'; // Toujours visible
    });
}