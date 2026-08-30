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
    /* The curtain must be able to draw the mark on its own.
       css/style.css is only linked by pages that include components/header.php;
       the reservation, login and register pages do not, so the wordmark used to
       flash as unstyled body text -- and the sweep below, with nothing
       positioned to anchor to, escaped to the viewport as a red slab.
       Everything the curtain needs is therefore repeated here, in px rather
       than rem so it does not depend on style.css setting html { 10px }.
       The values match .brand-word / .brand-rule / .brand-tagline exactly, so
       on pages that do load style.css nothing moves at the handover. */
    .brand-mark--loader {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .brand-mark--loader .brand-word {
        display: block;
        /* Cinzel sits after the installed faces as the Android fallback --
           see --brand-font in css/style.css for the full reasoning. */
        font-family: Copperplate, 'Copperplate Gothic Light', 'Optima',
                     'Palatino Linotype', Palatino, 'Cinzel', Georgia, serif;
        font-size: 44px;
        font-weight: 400;
        line-height: 1.05;
        letter-spacing: 6px;
        text-indent: 3px;
        text-transform: none;
        color: #f3efec;
    }
    .brand-mark--loader .brand-rule {
        display: block;
        position: relative;
        width: 120px;
        height: 1px;
        margin-top: 28px;
        background-color: rgba(255, 255, 255, 0.12);
        overflow: hidden;
    }
    .brand-mark--loader .brand-tagline {
        display: block;
        margin-top: 22px;
        font-size: 13px;
        font-weight: 300;
        letter-spacing: 6.2px;
        text-indent: 3.1px;
        text-transform: uppercase;
        color: #cfc7c2;
        opacity: 0;
    }
    @media only screen and (min-width: 768px) {
        .brand-mark--loader .brand-word {
            font-size: 66px;
            letter-spacing: 8px;
            text-indent: 4px;
        }
        .brand-mark--loader .brand-rule {
            width: 150px;
        }
        .brand-mark--loader .brand-tagline {
            font-size: 15px;
            letter-spacing: 7.8px;
            text-indent: 3.9px;
        }
    }
    /* The waiting motion: a single ember travelling along the rule the hero is
       about to inherit. */
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
