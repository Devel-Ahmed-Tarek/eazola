/**
 * Performance Optimizer
 * Handles lazy loading for images, sections, and deferred resources
 */
(function() {
    'use strict';

    const PerformanceOptimizer = {
        
        // Configuration
        config: {
            lazyImageSelector: 'img[data-src], img[loading="lazy"]',
            lazySectionSelector: '.lazy-section',
            lazyBackgroundSelector: '[data-bg]',
            rootMargin: '50px 0px',
            threshold: 0.1
        },

        // Initialize all optimizations
        init: function() {
            this.initLazyImages();
            this.initLazySections();
            this.initLazyBackgrounds();
            this.initDeferredStyles();
            this.addLoadingIndicators();
        },

        // =============================================
        // 1. LAZY LOADING IMAGES
        // =============================================
        initLazyImages: function() {
            // Use native lazy loading if supported
            if ('loading' in HTMLImageElement.prototype) {
                this.nativeLazyLoad();
            } else {
                // Fallback to Intersection Observer
                this.observerLazyLoad();
            }
        },

        nativeLazyLoad: function() {
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
        },

        observerLazyLoad: function() {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: this.config.rootMargin,
                threshold: this.config.threshold
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        },

        loadImage: function(img) {
            const src = img.dataset.src;
            if (!src) return;

            // Create a temporary image to preload
            const tempImg = new Image();
            tempImg.onload = function() {
                img.src = src;
                img.classList.add('loaded');
                img.removeAttribute('data-src');
            };
            tempImg.onerror = function() {
                img.classList.add('error');
            };
            tempImg.src = src;
        },

        // =============================================
        // 2. LAZY LOADING SECTIONS
        // =============================================
        initLazySections: function() {
            const sections = document.querySelectorAll(this.config.lazySectionSelector);
            if (sections.length === 0) return;

            const sectionObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadSection(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '100px 0px',
                threshold: 0.05
            });

            sections.forEach((section, index) => {
                // First section loads immediately
                if (index === 0) {
                    this.loadSection(section);
                } else {
                    section.classList.add('section-loading');
                    sectionObserver.observe(section);
                }
            });
        },

        loadSection: function(section) {
            // Remove loading state
            section.classList.remove('section-loading');
            section.classList.add('section-loaded');

            // Load any deferred content
            const deferredContent = section.querySelector('[data-deferred-content]');
            if (deferredContent) {
                const url = deferredContent.dataset.deferredContent;
                this.fetchDeferredContent(url, deferredContent);
            }

            // Load lazy images within section
            section.querySelectorAll('img[data-src]').forEach(img => {
                this.loadImage(img);
            });

            // Load lazy backgrounds within section
            section.querySelectorAll('[data-bg]').forEach(el => {
                this.loadBackground(el);
            });

            // Trigger custom event
            section.dispatchEvent(new CustomEvent('sectionLoaded', { bubbles: true }));
        },

        fetchDeferredContent: function(url, container) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                    container.removeAttribute('data-deferred-content');
                })
                .catch(err => console.warn('Failed to load deferred content:', err));
        },

        // =============================================
        // 3. LAZY LOADING BACKGROUNDS
        // =============================================
        initLazyBackgrounds: function() {
            const bgElements = document.querySelectorAll(this.config.lazyBackgroundSelector);
            if (bgElements.length === 0) return;

            const bgObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadBackground(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: this.config.rootMargin,
                threshold: this.config.threshold
            });

            bgElements.forEach(el => {
                bgObserver.observe(el);
            });
        },

        loadBackground: function(el) {
            const bg = el.dataset.bg;
            if (!bg) return;

            // Preload the image
            const img = new Image();
            img.onload = function() {
                el.style.backgroundImage = 'url(' + bg + ')';
                el.classList.add('bg-loaded');
                el.removeAttribute('data-bg');
            };
            img.src = bg;
        },

        // =============================================
        // 4. DEFERRED STYLES
        // =============================================
        initDeferredStyles: function() {
            const deferredStyles = document.querySelectorAll('link[data-defer]');
            
            if (deferredStyles.length === 0) return;

            // Load deferred styles after page load
            if (document.readyState === 'complete') {
                this.loadDeferredStyles(deferredStyles);
            } else {
                window.addEventListener('load', () => {
                    this.loadDeferredStyles(deferredStyles);
                });
            }
        },

        loadDeferredStyles: function(styles) {
            styles.forEach(link => {
                const href = link.dataset.href || link.getAttribute('href');
                if (href) {
                    link.setAttribute('href', href);
                    link.removeAttribute('data-defer');
                    link.removeAttribute('data-href');
                }
            });
        },

        // =============================================
        // 5. LOADING INDICATORS
        // =============================================
        addLoadingIndicators: function() {
            // Add skeleton loading for sections
            const style = document.createElement('style');
            style.textContent = `
                .section-loading {
                    position: relative;
                    min-height: 200px;
                    opacity: 0;
                    transform: translateY(20px);
                }
                .section-loading::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 40px;
                    height: 40px;
                    margin: -20px 0 0 -20px;
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid var(--main-color-one, #3498db);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                .section-loaded {
                    opacity: 1;
                    transform: translateY(0);
                    transition: opacity 0.5s ease, transform 0.5s ease;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                img[data-src] {
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                img.loaded {
                    opacity: 1;
                }
                [data-bg] {
                    background-color: #f5f5f5;
                    transition: background-image 0.3s ease;
                }
                .bg-loaded {
                    background-color: transparent;
                }
            `;
            document.head.appendChild(style);
        },

        // =============================================
        // UTILITY: Convert existing images to lazy
        // =============================================
        convertToLazy: function() {
            // Skip first viewport images (above the fold)
            const viewportHeight = window.innerHeight;
            const images = document.querySelectorAll('img:not([loading])');
            
            images.forEach(img => {
                const rect = img.getBoundingClientRect();
                // If image is below the fold, make it lazy
                if (rect.top > viewportHeight) {
                    if (img.src && !img.dataset.src) {
                        img.dataset.src = img.src;
                        img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E';
                        img.loading = 'lazy';
                    }
                }
            });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => PerformanceOptimizer.init());
    } else {
        PerformanceOptimizer.init();
    }

    // Expose globally for manual usage
    window.PerformanceOptimizer = PerformanceOptimizer;

})();
