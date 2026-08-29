<?php
/*
 * Ember loading screen and page-to-page fade.
 *
 * Included by components/background.php, so every page that has the video
 * backdrop also gets the transition. Plain CSS and JS -- no libraries.
 */
?>
<div id="ember-loader" class="ember-loader is-active">
    <?php $markVariant = 'loader'; include __DIR__ . '/brandMark.php'; ?>
</div>

<style>
    .ember-loader {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #14100f;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.45s ease;
    }
    .ember-loader.is-active {
        opacity: 1;
        pointer-events: auto;
    }
    /* The mark itself is styled in css/style.css, so the curtain and the hero
       cannot drift apart. Only the waiting motion lives here: a single ember
       travelling along the rule the hero is about to inherit. */
    .brand-mark--loader .brand-rule {
        overflow: hidden;
    }
    .brand-mark--loader .brand-rule::after {
        content: '';
        position: absolute;
        top: 0;
        left: -40%;
        width: 40%;
        height: 100%;
        background-color: #b1462f;
        animation: ember-sweep 1.5s ease-in-out infinite;
    }
    @keyframes ember-sweep {
        0%   { left: -40%; }
        100% { left: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .brand-mark--loader .brand-rule::after { animation: none; }
        .ember-loader { transition: none; }
    }
</style>

<script>
    (function () {
        var loader = document.getElementById('ember-loader');
        if (!loader) return;

        // The hero mark waits for this class before it assembles, so the
        // animation is never spent behind the curtain.
        function hide() {
            loader.classList.remove('is-active');
            document.body.classList.add('ember-revealed');
        }
        function show() { loader.classList.add('is-active'); }

        // Reveal the page once it has painted.
        if (document.readyState === 'complete') {
            setTimeout(hide, 150);
        } else {
            window.addEventListener('load', function () { setTimeout(hide, 150); });
        }
        // Never let the curtain stick if something stalls.
        setTimeout(hide, 4000);

        // Coming back via the Back button restores the page from cache with the
        // loader still showing, so clear it again.
        window.addEventListener('pageshow', hide);

        document.addEventListener('click', function (e) {
            var link = e.target.closest ? e.target.closest('a') : null;
            if (!link) return;

            var href = link.getAttribute('href');
            if (!href) return;

            // Leave alone: anchors, new tabs, downloads, non-http schemes and
            // anything that leaves the site. bill.php streams a PDF without
            // navigating, so a curtain there would never lift; cart.php is a
            // quick add/remove that should feel instant, not staged.
            if (href.charAt(0) === '#'
                || link.target === '_blank'
                || link.hasAttribute('download')
                || link.hasAttribute('data-no-transition')
                || /^(mailto:|tel:|javascript:)/i.test(href)
                || /bill\.php/.test(href)
                || /cart\.php/.test(href)) {
                return;
            }
            if (link.host && link.host !== window.location.host) return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;

            // A link to the page we are already on -- '/home.php#hero' from
            // home.php, say. The browser only moves the scroll position, so no
            // load event ever fires and the curtain would hang there for good.
            if (link.pathname === window.location.pathname
                && link.search === window.location.search) {
                return;
            }

            e.preventDefault();
            show();
            setTimeout(function () { window.location.href = link.href; }, 380);
        });
    })();
</script>
