/**
 * DataBuilder Admin Index - JavaScript
 * 
 * Handles interactive behavior for the admin dashboard
 * This file can be completely overridden in:
 * - themes/custom/js/databuilder.js
 * - themes/custom/js/admin/index.js
 */

(function() {
    'use strict';

    /**
     * Initialize the DataBuilder admin index page
     */
    function init() {
        initStatCards();
        initResponsive();
        observeTrafficBar();
    }

    /**
     * Initialize stat card interactions
     */
    function initStatCards() {
        const cards = document.querySelectorAll('[data-stat]');
        
        if (cards.length === 0) {
            console.warn('DataBuilder: No stat cards found');
            return;
        }

        cards.forEach((card) => {
            // Hover effects with transform
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });

            // Optional: Click to navigate if data-link is provided
            const link = card.getAttribute('data-link');
            if (link) {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function() {
                    window.location.href = link;
                });
            }
        });
    }

    /**
     * Handle responsive layout adjustments
     */
    function initResponsive() {
        const grid = document.querySelector('.statistics-grid');
        if (!grid) return;

        function adjustGrid() {
            const width = window.innerWidth;
            if (width < 768) {
                grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(140px, 1fr))';
            } else if (width < 1024) {
                grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(180px, 1fr))';
            } else {
                grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(200px, 1fr))';
            }
        }

        adjustGrid();
        window.addEventListener('resize', adjustGrid);
    }

    /**
     * Animate traffic progress bar
     */
    function observeTrafficBar() {
        const progressBar = document.querySelector('.traffic-progress');
        if (!progressBar) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Trigger animation when visible
                    progressBar.style.width = progressBar.getAttribute('style');
                    observer.unobserve(entry.target);
                }
            });
        });

        observer.observe(progressBar);
    }

    /**
     * Add debugging utilities (only in development/debug mode)
     */
    function initDebug() {
        // Make DataBuilder API globally accessible for debugging
        window.DataBuilderDebug = {
            getStats: function() {
                const cards = document.querySelectorAll('[data-stat]');
                const stats = {};
                cards.forEach(card => {
                    const key = card.getAttribute('data-stat');
                    const value = card.querySelector('.stat-card__value')?.textContent || 'N/A';
                    stats[key] = value;
                });
                return stats;
            },
            getTraffic: function() {
                const progress = document.querySelector('.traffic-progress');
                const percentage = document.querySelector('.traffic-percentage');
                return {
                    width: progress ? progress.style.width : 'N/A',
                    percentage: percentage ? percentage.textContent : 'N/A'
                };
            },
            getColors: function() {
                const style = getComputedStyle(document.documentElement);
                return {
                    primary: style.getPropertyValue('--db-primary'),
                    secondary: style.getPropertyValue('--db-secondary'),
                    bgLight: style.getPropertyValue('--db-bg-light'),
                    bgDark: style.getPropertyValue('--db-bg-dark-800')
                };
            },
            log: function(msg) {
                console.log('[DataBuilder] ' + msg);
            }
        };

        console.log('[DataBuilder] Admin Index loaded. Use window.DataBuilderDebug for debugging.');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            init();
            initDebug();
        });
    } else {
        init();
        initDebug();
    }

})();
