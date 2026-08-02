/* KACHI Foodstuff Supplies & Logistics — progressive enhancement only.
   Every feature below has a working no-JS fallback. */
(function () {
    'use strict';

    /* Mobile navigation ---------------------------------------------------- */
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');

    if (toggle && nav) {
        var openIcon = toggle.querySelector('[data-nav-icon="open"]');
        var closeIcon = toggle.querySelector('[data-nav-icon="close"]');

        toggle.addEventListener('click', function () {
            var open = nav.hasAttribute('hidden');
            if (open) { nav.removeAttribute('hidden'); } else { nav.setAttribute('hidden', ''); }

            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            if (openIcon) openIcon.classList.toggle('hidden', open);
            if (closeIcon) closeIcon.classList.toggle('hidden', !open);
        });

        // Close on Escape so keyboard users are never trapped.
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !nav.hasAttribute('hidden')) {
                toggle.click();
                toggle.focus();
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
