// ========================================
// SIDEBAR TOGGLE & AUTO SCROLL FIX
// ========================================

(function() {
    'use strict';
    
    // ===== SIDEBAR TOGGLE =====
    window.toggleSidebar = function() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
        if (overlay) {
            overlay.classList.toggle('active');
        }
    };

    window.closeSidebar = function() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if (sidebar) {
            sidebar.classList.remove('open');
        }
        if (overlay) {
            overlay.classList.remove('active');
        }
    };

    // ===== EVENT LISTENERS =====
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    document.addEventListener('click', function(e) {
        var sidebar = document.getElementById('sidebar');
        var hamburger = document.querySelector('.hamburger');
        if (window.innerWidth <= 992) {
            if (sidebar && !sidebar.contains(e.target) && hamburger && !hamburger.contains(e.target)) {
                closeSidebar();
            }
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            closeSidebar();
        }
    });

    // ===== CONFIRM LOGOUT =====
    window.confirmLogout = function() {
        return confirm('Are you sure you want to logout?');
    };

    // ===== AUTO SCROLL PREVENTION =====
    if (window.Element && Element.prototype.scrollIntoView) {
        Element.prototype.scrollIntoView = function() {
            return;
        };
    }
    
    var originalScrollTo = window.scrollTo;
    window.scrollTo = function(x, y) {
        if (x === 0 && y === 0) {
            originalScrollTo.call(this, 0, 0);
        }
        return;
    };
    
    window.scrollBy = function() {
        return;
    };
    
    window.scroll = function() {
        return;
    };
    
    // ===== PREVENT HASH CHANGE =====
    window.addEventListener('hashchange', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.location.hash) {
            history.replaceState(null, null, window.location.pathname);
        }
        return false;
    }, true);
    
    // ===== REMOVE HASH =====
    function removeHash() {
        if (window.location.hash) {
            window.scrollTo(0, 0);
            history.replaceState(null, null, window.location.pathname);
        }
    }
    
    // ===== HANDLE SIDEBAR LINK CLICKS =====
    function handleSidebarLinkClick(e) {
        var href = this.getAttribute('href');
        
        if (href && href !== '#' && href !== '' && !href.startsWith('javascript:')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            window.location.href = href;
            return false;
        }
    }
    
    function initSidebarLinks() {
        var sidebarLinks = document.querySelectorAll('.sidebar-menu a');
        
        sidebarLinks.forEach(function(link) {
            link.removeEventListener('click', handleSidebarLinkClick);
            link.addEventListener('click', handleSidebarLinkClick, true);
        });
    }
    
    // ===== BLOCK ALL SIDEBAR LINK CLICKS =====
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a');
        if (link && link.closest('.sidebar-menu')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            var href = link.getAttribute('href');
            if (href && href !== '#' && href !== '' && !href.startsWith('javascript:')) {
                window.location.href = href;
            }
            return false;
        }
    }, true);
    
    // ===== PREVENT KEYBOARD SCROLL =====
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            var target = e.target;
            if (target.tagName === 'A' && target.closest('.sidebar-menu')) {
                e.preventDefault();
                e.stopPropagation();
                var href = target.getAttribute('href');
                if (href && href !== '#' && href !== '' && !href.startsWith('javascript:')) {
                    window.location.href = href;
                }
                return false;
            }
        }
    }, true);
    
    // ===== OVERRIDE FOCUS =====
    var originalFocus = HTMLElement.prototype.focus;
    HTMLElement.prototype.focus = function(options) {
        originalFocus.call(this, { preventScroll: true });
    };
    
    // ===== DISABLE SCROLL RESTORATION =====
    if (window.history && window.history.scrollRestoration) {
        window.history.scrollRestoration = 'manual';
    }
    
    // ===== INIT =====
    function init() {
        removeHash();
        initSidebarLinks();
        if (window.scrollY > 0) {
            window.scrollTo(0, 0);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    window.addEventListener('load', function() {
        init();
        setTimeout(removeHash, 50);
        setTimeout(removeHash, 200);
        if (window.scrollY > 0) {
            window.scrollTo(0, 0);
        }
    });
    
    console.log('✅ Sidebar & Auto scroll fix activated!');
    
})();