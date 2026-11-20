/* Shared behaviour for MAN'S CHOICE public pages */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Smooth scrolling for in-page links (unless the browser/CSS already does it).
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (event) {
            var id = this.getAttribute('href');
            if (!id || id === '#') {
                return;
            }

            var target = document.querySelector(id);
            if (!target) {
                return;
            }

            event.preventDefault();
            target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
            history.replaceState(null, '', id);
        });
    });

    // Highlight the section currently in view in the privacy policy TOC.
    var sections = Array.prototype.slice.call(document.querySelectorAll('.section[id]'));
    var tocLinks = Array.prototype.slice.call(document.querySelectorAll('.toc a[href^="#"]'));

    if (sections.length && tocLinks.length) {
        var updateActiveLink = function () {
            var current = '';
            sections.forEach(function (section) {
                if (window.scrollY >= section.offsetTop - 110) {
                    current = section.id;
                }
            });

            tocLinks.forEach(function (link) {
                link.classList.toggle('active', link.getAttribute('href') === '#' + current);
            });
        };

        window.addEventListener('scroll', updateActiveLink, { passive: true });
        updateActiveLink();
    }

    // Reveal cards as they enter the viewport. Elements are only hidden when
    // JavaScript runs, so content stays visible without it.
    var cards = document.querySelectorAll('.feature-card, .stat-card, .product-card, .timeline-item');
    if (cards.length && !reduceMotion && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        cards.forEach(function (card) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(16px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });
    }
})();
