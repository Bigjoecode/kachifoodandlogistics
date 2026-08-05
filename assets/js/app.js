/* KACHI Foodstuff Supplies & Logistics — progressive enhancement only.
   Every feature below has a working no-JS fallback. */
(function () {
    'use strict';

    /* Mobile navigation ---------------------------------------------------- */
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    var backdrop = document.querySelector('[data-nav-backdrop]');

    if (toggle && nav) {
        var openIcon = toggle.querySelector('[data-nav-icon="open"]');
        var closeIcon = toggle.querySelector('[data-nav-icon="close"]');
        var label = toggle.querySelector('[data-nav-label]');

        function setMenu(open, returnFocus) {
            nav.toggleAttribute('hidden', !open);
            if (backdrop) backdrop.toggleAttribute('hidden', !open);

            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            if (openIcon) openIcon.classList.toggle('hidden', open);
            if (closeIcon) closeIcon.classList.toggle('hidden', !open);
            if (label) label.textContent = open ? 'Close' : 'Menu';
            document.body.classList.toggle('overflow-hidden', open);

            if (open) {
                var firstLink = nav.querySelector('a');
                if (firstLink) firstLink.focus({ preventScroll: true });
            } else if (returnFocus) {
                toggle.focus({ preventScroll: true });
            }
        }

        toggle.addEventListener('click', function () {
            setMenu(nav.hasAttribute('hidden'), false);
        });

        if (backdrop) {
            backdrop.addEventListener('click', function () { setMenu(false, true); });
        }

        nav.addEventListener('click', function (event) {
            if (event.target.closest('a')) setMenu(false, false);
        });

        // Close on Escape so keyboard users are never trapped.
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !nav.hasAttribute('hidden')) {
                setMenu(false, true);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024 && !nav.hasAttribute('hidden')) {
                setMenu(false, false);
            }
        });
    }

    /* Quantity steppers ---------------------------------------------------- */
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-qty]');
        if (!button) return;

        var input = button.parentElement.querySelector('input');
        if (!input) return;

        var step = button.dataset.qty === 'up' ? 1 : -1;
        var min = parseInt(input.min || '1', 10);
        var value = parseInt(input.value || '1', 10) + step;

        input.value = Math.max(isNaN(min) ? 1 : min, isNaN(value) ? min : value);
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    /* Confirm destructive submits ------------------------------------------ */
    document.addEventListener('submit', function (event) {
        var message = event.target.dataset.confirm;
        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });

    /* Auto-submitting filter controls -------------------------------------- */
    document.querySelectorAll('[data-autosubmit]').forEach(function (control) {
        control.addEventListener('change', function () {
            if (control.form) control.form.submit();
        });
    });

    /* Cart quantity: submit the row form when the value settles ------------- */
    document.querySelectorAll('[data-cart-qty]').forEach(function (input) {
        var timer;
        input.addEventListener('change', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { input.form.submit(); }, 400);
        });
    });

    /* Flash messages: dismiss on click, auto-dismiss after a read ----------- */
    document.querySelectorAll('[data-flash]').forEach(function (flash) {
        function dismiss() {
            flash.style.transition = 'opacity .35s, transform .35s';
            flash.style.opacity = '0';
            flash.style.transform = 'translateX(1rem)';
            setTimeout(function () { flash.remove(); }, 350);
        }

        var closer = flash.querySelector('[data-flash-close]');
        if (closer) closer.addEventListener('click', dismiss);
        setTimeout(dismiss, 7000);
    });

    /* Copy-to-clipboard (order references, bank details) -------------------- */
    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-copy]');
        if (!trigger || !navigator.clipboard) return;

        navigator.clipboard.writeText(trigger.dataset.copy).then(function () {
            var label = trigger.querySelector('[data-copy-label]') || trigger;
            var original = label.textContent;
            label.textContent = 'Copied';
            setTimeout(function () { label.textContent = original; }, 1500);
        });
    });

    /* Delivery date: never allow a past date ------------------------------- */
    document.querySelectorAll('input[type="date"][data-min-today]').forEach(function (input) {
        if (!input.min) input.min = new Date().toISOString().slice(0, 10);
    });

    /* Reveal-on-scroll, skipped entirely when reduced motion is requested --- */
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var revealables = document.querySelectorAll('[data-reveal]');

    if (!reduced && 'IntersectionObserver' in window && revealables.length) {
        revealables.forEach(function (el) { el.style.opacity = '0'; });

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry, i) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                setTimeout(function () {
                    el.style.opacity = '';
                    el.classList.add('animate-rise');
                }, i * 40);
                observer.unobserve(el);
            });
        }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });

        revealables.forEach(function (el) { observer.observe(el); });
    }
})();

/* Back-office navigation drawer.
   Only active below 1024px, where the sidebar is off-canvas. Desktop keeps a
   permanent sidebar and none of this runs. */
(function () {
    'use strict';

    var drawer = document.querySelector('[data-drawer]');
    var toggle = document.querySelector('[data-drawer-toggle]');
    var scrim = document.querySelector('[data-drawer-scrim]');
    if (!drawer || !toggle || !scrim) return;

    var isMobile = function () { return window.matchMedia('(max-width: 1023px)').matches; };

    function open() {
        drawer.classList.add('is-open');
        scrim.hidden = false;
        // Next frame, so the scrim transitions in rather than snapping.
        requestAnimationFrame(function () { scrim.classList.add('is-open'); });
        document.body.classList.add('drawer-open');
        toggle.setAttribute('aria-expanded', 'true');

        var firstLink = drawer.querySelector('a, button');
        if (firstLink) firstLink.focus();
    }

    function close(returnFocus) {
        drawer.classList.remove('is-open');
        scrim.classList.remove('is-open');
        document.body.classList.remove('drawer-open');
        toggle.setAttribute('aria-expanded', 'false');

        // Keep the scrim out of the accessibility tree once it has faded.
        setTimeout(function () {
            if (!drawer.classList.contains('is-open')) scrim.hidden = true;
        }, 300);

        if (returnFocus) toggle.focus();
    }

    toggle.addEventListener('click', function () {
        drawer.classList.contains('is-open') ? close(true) : open();
    });

    scrim.addEventListener('click', function () { close(false); });

    var closer = drawer.querySelector('[data-drawer-close]');
    if (closer) closer.addEventListener('click', function () { close(true); });

    // Following a link should not leave the drawer covering the new page.
    drawer.addEventListener('click', function (event) {
        if (event.target.closest('a[href]') && isMobile()) close(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && drawer.classList.contains('is-open')) close(true);
    });

    // Keep focus inside the drawer while it is covering the page.
    drawer.addEventListener('keydown', function (event) {
        if (event.key !== 'Tab' || !drawer.classList.contains('is-open')) return;

        var focusable = drawer.querySelectorAll('a[href], button:not([disabled]), input, select, textarea');
        if (!focusable.length) return;

        var first = focusable[0];
        var last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    // Resizing up to desktop must not leave the body scroll-locked.
    window.addEventListener('resize', function () {
        if (!isMobile() && drawer.classList.contains('is-open')) close(false);
    });
})();

/* Data tables on phones.
   Below 768px each row becomes a stacked card. Labels are copied from the
   table's own <th> cells, so this works for every admin table without any
   per-page markup and cannot fall out of sync with the headers. */
(function () {
    'use strict';

    document.querySelectorAll('.admin-content .table').forEach(function (table) {
        var headers = [].map.call(table.querySelectorAll('thead th'), function (th) {
            return th.textContent.trim();
        });
        if (!headers.length) return;

        table.querySelectorAll('tbody tr').forEach(function (row) {
            [].forEach.call(row.cells, function (cell, index) {
                if (headers[index]) cell.setAttribute('data-label', headers[index]);
            });
        });

        table.classList.add('table-stacked');
    });
})();
