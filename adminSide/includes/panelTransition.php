<?php
// panelTransition.php - background video, loading line and page change animation for the staff panel
// (included by dashHeader.php, so every staff page has it)

$emberAssets    = '../../customerSide/image';    // the video and photo of the customer site
$emberVideoFile = __DIR__ . '/../../customerSide/image/background.mp4';
$emberHasVideo  = file_exists($emberVideoFile);
?>
<div class="site-bg" aria-hidden="true">
<?php if ($emberHasVideo) { ?>
    <video id="site-bg-video" autoplay muted loop playsinline preload="auto"
           poster="<?= $emberAssets ?>/hero-background.jpg"></video>
<?php } else { ?>
    <div class="site-bg-fallback" style="background-image: url('<?= $emberAssets ?>/hero-background.jpg');"></div>
<?php } ?>
    <div class="site-bg-scrim"></div>
    <div class="site-bg-ambient"></div>
</div>

<div class="ember-filament" aria-hidden="true"><i></i></div>

<style>
    /* ===== 1. BACKGROUND (the same video as the customer site, but very dark) ===== */

    /* the page itself must be see-through, the colour is on .site-bg
       (if html had a colour, the video would blink during every page change) */
    html,
    body,
    body.sb-nav-fixed,
    #layoutSidenav,
    #layoutSidenav_content,
    main {
        background-color: transparent;
        background-image: none;
    }

    .site-bg {
        position: fixed;
        inset: 0;
        z-index: -10;
        overflow: hidden;
        pointer-events: none;
        /* dark colour, also shown while the video is loading */
        background-color: var(--ink-900, #0f0c0b);
    }
    .site-bg video,
    .site-bg-fallback {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        min-width: 100%;
        min-height: 100%;
        width: auto;
        height: auto;
        object-fit: cover;
        background-size: cover;
        background-position: center;
        /* very faint so the tables are still easy to read */
        opacity: 0.22;
    }
    .site-bg-fallback {
        width: 100%;
        height: 100%;
        opacity: 0.18;
    }
    .site-bg-scrim {
        position: absolute;
        inset: 0;
        background-color: rgba(12, 9, 8, 0.62);
    }
    /* soft warm glow that moves very slowly */
    .site-bg-ambient {
        position: absolute;
        inset: -20%;
        background-image:
            radial-gradient(38% 46% at 18% 24%, rgba(177, 70, 47, 0.16) 0%, rgba(177, 70, 47, 0) 70%),
            radial-gradient(42% 40% at 82% 74%, rgba(201, 162, 39, 0.09) 0%, rgba(201, 162, 39, 0) 72%);
        animation: ember-drift 46s ease-in-out infinite alternate;
    }
    @keyframes ember-drift {
        from { transform: translate3d(-2%, -1.5%, 0) scale(1); }
        to   { transform: translate3d(2.5%, 2%, 0) scale(1.08); }
    }

    /* ===== 2. THIN RED LINE AT THE TOP while the next page is loading ===== */

    .ember-filament {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 2px;
        z-index: 10000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    .ember-filament.is-running { opacity: 1; }
    .ember-filament i {
        display: block;
        width: 0;
        height: 100%;
        background-image: linear-gradient(90deg,
            rgba(177, 70, 47, 0) 0%,
            #b1462f 22%,
            #e0784f 78%,
            #f3efec 100%);
        box-shadow: 0 0 12px rgba(224, 120, 79, 0.55);
    }
    .ember-filament.is-running i {
        animation: ember-filament 9s cubic-bezier(0.05, 0.8, 0.2, 1) forwards;
    }
    @keyframes ember-filament {
        0%   { width: 0; }
        18%  { width: 42%; }
        45%  { width: 68%; }
        100% { width: 94%; }
    }

    /* ===== 3. PAGE CHANGE ANIMATION (Chrome and Edge do this by themselves) ===== */

    @view-transition {
        navigation: auto;
    }

    /* animation speed: 1 = normal. Set it to 8 in the browser console to watch it slowly */
    :root { --ember-vt: 1; }

    /* the top bar and the left menu do not move, only the page content changes */
    .sb-topnav        { view-transition-name: ember-topnav; }
    #layoutSidenav_nav { view-transition-name: ember-sidenav; }

    ::view-transition-group(ember-topnav),
    ::view-transition-group(ember-sidenav) {
        animation-duration: 0s;
        z-index: 10;
    }
    ::view-transition-image-pair(ember-topnav),
    ::view-transition-image-pair(ember-sidenav) {
        isolation: auto;
    }
    ::view-transition-old(ember-topnav),
    ::view-transition-old(ember-sidenav) {
        display: none;
    }
    ::view-transition-new(ember-topnav),
    ::view-transition-new(ember-sidenav) {
        animation: none;
        opacity: 1;
        mix-blend-mode: normal;
    }

    /* coming from the login page or going back to the website:
       the top bar slides down and the left menu slides in */
    @supports selector(:active-view-transition-type(ember-crossing)) {
        html:active-view-transition-type(ember-crossing)::view-transition-group(ember-topnav),
        html:active-view-transition-type(ember-crossing)::view-transition-group(ember-sidenav) {
            animation-duration: calc(520ms * var(--ember-vt));
        }
        html:active-view-transition-type(ember-crossing)::view-transition-new(ember-topnav) {
            animation: ember-topnav-in calc(460ms * var(--ember-vt)) cubic-bezier(0.16, 1, 0.3, 1) calc(160ms * var(--ember-vt)) both;
        }
        html:active-view-transition-type(ember-crossing)::view-transition-new(ember-sidenav) {
            animation: ember-sidenav-in calc(520ms * var(--ember-vt)) cubic-bezier(0.16, 1, 0.3, 1) calc(220ms * var(--ember-vt)) both;
        }
        html:active-view-transition-type(ember-crossing)::view-transition-old(ember-topnav) {
            display: block;
            animation: ember-topnav-in calc(300ms * var(--ember-vt)) cubic-bezier(0.4, 0, 1, 1) reverse both;
        }
        html:active-view-transition-type(ember-crossing)::view-transition-old(ember-sidenav) {
            display: block;
            animation: ember-sidenav-in calc(300ms * var(--ember-vt)) cubic-bezier(0.4, 0, 1, 1) reverse both;
        }
    }
    @keyframes ember-topnav-in {
        from { opacity: 0; transform: translateY(-100%); }
        to   { opacity: 1; transform: none; }
    }
    @keyframes ember-sidenav-in {
        from { opacity: 0; transform: translateX(-28px); }
        to   { opacity: 1; transform: none; }
    }

    /* the background video stays still (same name as on the customer site) */
    .site-bg { view-transition-name: ember-backdrop; }
    ::view-transition-group(ember-backdrop) {
        animation-duration: calc(560ms * var(--ember-vt));
        animation-timing-function: ease;
        z-index: 1;
    }
    ::view-transition-old(ember-backdrop),
    ::view-transition-new(ember-backdrop) {
        animation-duration: calc(560ms * var(--ember-vt));
        animation-timing-function: ease;
    }
    ::view-transition-group(root) { z-index: 2; }

    /* the page content: old page fades out going up, new page fades in (faster than the customer site) */
    ::view-transition-old(root) {
        animation: ember-panel-out calc(200ms * var(--ember-vt)) cubic-bezier(0.4, 0, 1, 1) both;
    }
    ::view-transition-new(root) {
        animation: ember-panel-in calc(400ms * var(--ember-vt)) cubic-bezier(0.16, 1, 0.3, 1) calc(70ms * var(--ember-vt)) both;
    }
    @keyframes ember-panel-out {
        to { opacity: 0; transform: translateY(-8px); }
    }
    @keyframes ember-panel-in {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: none; }
    }

    /* Back button: the content moves the other way */
    @supports selector(:active-view-transition-type(ember-back)) {
        html:active-view-transition-type(ember-back)::view-transition-old(root) {
            animation-name: ember-panel-out-back;
        }
        html:active-view-transition-type(ember-back)::view-transition-new(root) {
            animation-name: ember-panel-in-back;
        }
    }
    @keyframes ember-panel-out-back {
        to { opacity: 0; transform: translateY(10px); }
    }
    @keyframes ember-panel-in-back {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: none; }
    }

    /* the word "Ember" flies from the login page into the top bar (same name as the customer site logo) */
    .ember-brand-word { view-transition-name: ember-word; }

    ::view-transition-group(ember-word) {
        animation-duration: calc(620ms * var(--ember-vt));
        animation-timing-function: cubic-bezier(0.62, 0.04, 0.2, 1);
        z-index: 20;
    }
    ::view-transition-old(ember-word),
    ::view-transition-new(ember-word) {
        animation-duration: calc(620ms * var(--ember-vt));
        animation-timing-function: cubic-bezier(0.62, 0.04, 0.2, 1);
        height: 100%;
        object-fit: contain;
    }

    /* ===== 4. THE MENU LINK OF THE PAGE WE ARE ON (dashHeader.php adds "active") ===== */

    .sb-sidenav-dark .sb-sidenav-menu .nav-link.active {
        position: relative;
        font-weight: 400;
        color: var(--text);
    }
    /* small red dot on the right */
    .sb-sidenav-dark .sb-sidenav-menu .nav-link.active::after {
        content: '';
        position: absolute;
        right: 16px;
        top: 50%;
        width: 5px;
        height: 5px;
        margin-top: -2.5px;
        border-radius: 50%;
        background-color: var(--brand-accent);
        box-shadow: 0 0 8px rgba(177, 70, 47, 0.9);
    }

    /* "Ember" bright, "Staff Panel" a bit darker */
    .sb-topnav .navbar-brand .ember-brand-word {
        color: var(--text);
    }
    .sb-topnav .navbar-brand .ember-brand-suffix {
        margin-left: 0.6rem;
        font-family: var(--sans, 'Jost', 'Futura', 'Avenir Next', sans-serif);
        font-size: 0.68rem;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        vertical-align: middle;
        color: var(--text-dim);
    }

    /* ===== 5. SIMPLE FADE for Safari and Firefox (the script adds the class ember-fallback) ===== */

    html.ember-fallback main {
        animation: ember-panel-in 400ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    html.ember-fallback.is-leaving main {
        animation: ember-panel-out 220ms cubic-bezier(0.4, 0, 1, 1) both;
    }

    /* ===== 6. NO ANIMATION if the user turned off motion on their computer ===== */

    @media (prefers-reduced-motion: reduce) {
        .site-bg-ambient,
        .ember-filament.is-running i { animation: none; }
        .ember-filament.is-running i { width: 60%; }
        .site-bg video { display: none; }

        ::view-transition-group(*),
        ::view-transition-old(*),
        ::view-transition-new(*) { animation: none !important; }
        html.ember-fallback main,
        html.ember-fallback.is-leaving main { animation: none; }
    }
</style>

<script>
    // ---------- setup ----------

    var panelTopLine = null;        // the thin red line (found later, the first time we need it)
    var panelTopLineTimer = null;

    // Chrome and Edge can animate between pages by themselves ("view transitions")
    var panelBrowserDoesTransitions = ('onpageswap' in window) && typeof document.startViewTransition === 'function';

    // other browsers (Safari, Firefox) get a simple fade that we do ourselves
    if (!panelBrowserDoesTransitions) {
        document.documentElement.classList.add('ember-fallback');
    }

    // when the browser cancels a page animation it shows a harmless "AbortError" in the console - hide only that one
    window.addEventListener('unhandledrejection', function (e) {
        var reason = e.reason;
        if (reason && reason.name === 'AbortError' && /transition/i.test(reason.message || '')) {
            e.preventDefault();
        }
    });


    // ---------- keep the left menu closed if the staff member closed it ----------
    // the template (scripts.js) saves this in localStorage but never reads it back, so we do it here

    try {
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.documentElement.classList.add('sb-sidenav-toggled-preload');
        }
    } catch (e) {}

    function applySidebarState() {
        var html = document.documentElement;
        if (html.classList.contains('sb-sidenav-toggled-preload')) {
            document.body.classList.add('sb-sidenav-toggled');
            html.classList.remove('sb-sidenav-toggled-preload');
        }
    }

    if (document.body) {
        applySidebarState();
    } else {
        document.addEventListener('DOMContentLoaded', applySidebarState);
    }


    // ---------- thin red line at the top while the next page loads ----------

    function startPanelTopLine() {
        if (!panelTopLine) {
            panelTopLine = document.querySelector('.ember-filament');
        }
        if (!panelTopLine) {
            return;
        }
        panelTopLine.classList.remove('is-running');
        void panelTopLine.offsetWidth;            // this restarts the CSS animation
        panelTopLine.classList.add('is-running');

        // remove it after 12 seconds (for example if a form did not leave the page)
        clearTimeout(panelTopLineTimer);
        panelTopLineTimer = setTimeout(function () {
            panelTopLine.classList.remove('is-running');
        }, 12000);
    }

    // coming back with the Back button: reset everything
    window.addEventListener('pageshow', function () {
        document.documentElement.classList.remove('is-leaving');
        if (!panelTopLine) {
            panelTopLine = document.querySelector('.ember-filament');
        }
        if (panelTopLine) {
            panelTopLine.classList.remove('is-running');
        }
    });


    // ---------- only ONE element with the same animation name per page ----------
    // if two elements had it, the browser would cancel the animation, so we keep only the first

    function panelKeepOnlyOne(selector) {
        var elements = document.querySelectorAll(selector);
        var visibleFound = false;
        for (var i = 0; i < elements.length; i++) {
            var isVisible = elements[i].getClientRects().length > 0;
            if (isVisible && !visibleFound) {
                visibleFound = true;            // keep this one
            } else {
                elements[i].style.viewTransitionName = 'none';
            }
        }
    }

    function panelCheckTransitionNames() {
        panelKeepOnlyOne('.ember-brand-word');
        panelKeepOnlyOne('.site-bg');
        panelKeepOnlyOne('.sb-topnav');
        panelKeepOnlyOne('#layoutSidenav_nav');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', panelCheckTransitionNames);
    } else {
        panelCheckTransitionNames();
    }


    // ---------- is this a normal link to another page of our site? ----------

    function panelIsPageLink(link, e) {
        if (!link) {
            return false;
        }
        var href = link.getAttribute('href');
        if (!href || href === '#!' || href.charAt(0) === '#') {
            return false;
        }
        // not these: new tabs, downloads, email/phone links, and the pages that make a PDF
        if (link.target === '_blank'
            || link.hasAttribute('download')
            || link.hasAttribute('data-no-transition')
            || /^(mailto:|tel:|javascript:)/i.test(href)
            || /receipt\.php|generate_report\.php|reservationReceipt\.php/.test(href)) {
            return false;
        }
        // not a link to another website
        if (link.host && link.host !== window.location.host) {
            return false;
        }
        // not when Cmd / Ctrl / Shift is held, or not the left mouse button
        if (e && (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0)) {
            return false;
        }
        // not a link to the same page
        if (link.pathname === window.location.pathname && link.search === window.location.search) {
            return false;
        }
        return true;
    }

    document.addEventListener('click', function (e) {
        var link = null;
        if (e.target.closest) {
            link = e.target.closest('a');
        }
        if (!panelIsPageLink(link, e)) {
            return;
        }

        startPanelTopLine();

        // Chrome / Edge do the animation themselves
        if (panelBrowserDoesTransitions) {
            return;
        }

        // other browsers: fade the page out first, then go to the new page
        e.preventDefault();
        document.documentElement.classList.add('is-leaving');
        setTimeout(function () {
            window.location.href = link.href;
        }, 200);
    });

    // also show the red line when a form is sent
    document.addEventListener('submit', startPanelTopLine, true);


    // ---------- tell the CSS which kind of page change it is ----------
    // works the same as customerSide/components/pageTransition.php

    // which kind of page is this url?
    function panelGetPageKind(pathname) {
        if (/\/adminSide\//i.test(pathname) && !/\/StaffLogin\//i.test(pathname)) {
            return 'console';     // staff panel
        }
        if (/\/(customerLogin|CustomerReservation|StaffLogin)\//i.test(pathname)) {
            return 'plain';       // form page without the header
        }
        return 'site';            // normal customer page
    }

    function panelIsDifferentKind(fromUrl, toUrl) {
        if (!fromUrl || !toUrl) {
            return false;
        }
        try {
            var fromKind = panelGetPageKind(new URL(fromUrl, location.href).pathname);
            var toKind = panelGetPageKind(new URL(toUrl, location.href).pathname);
            return fromKind !== toKind;
        } catch (err) {
            return false;
        }
    }

    function panelAddTransitionTypes(transition, isBack, isCrossing) {
        if (!transition) {
            return;
        }
        // stop the harmless "AbortError" messages if the browser cancels the animation
        var promises = ['ready', 'finished', 'updateCallbackDone'];
        for (var i = 0; i < promises.length; i++) {
            var p = transition[promises[i]];
            if (p && typeof p.catch === 'function') {
                p.catch(function () {});
            }
        }
        // older browsers don't have "types"
        if (!transition.types || !transition.types.add) {
            return;
        }
        try {
            if (isBack) {
                transition.types.add('ember-back');
            } else {
                transition.types.add('ember-forward');
            }
            if (isCrossing) {
                transition.types.add('ember-crossing');
            }
        } catch (err) {}
    }

    // runs on the OLD page just before it is replaced
    window.addEventListener('pageswap', function (e) {
        if (!e.viewTransition) {
            return;
        }
        var activation = e.activation || {};
        var toUrl = activation.entry && activation.entry.url;
        panelAddTransitionTypes(e.viewTransition, activation.navigationType === 'traverse', panelIsDifferentKind(location.href, toUrl));
    });

    // runs on the NEW page when it is shown
    window.addEventListener('pagereveal', function (e) {
        if (!e.viewTransition) {
            return;
        }
        var activation = window.navigation && window.navigation.activation;
        var fromUrl = activation && activation.from && activation.from.url;
        panelAddTransitionTypes(e.viewTransition, activation && activation.navigationType === 'traverse', panelIsDifferentKind(fromUrl, location.href));
    });


    // ---------- background video ----------
    // it continues from the same second as on the customer site (saved in sessionStorage as "emberBgTime")

    var panelVideo = document.getElementById('site-bg-video');

    // slow internet or "less motion" turned on -> only show the photo, don't load the video
    function panelUseLightMode() {
        var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn && conn.saveData) {
            return true;
        }
        if (conn && /^(slow-)?2g$/.test(conn.effectiveType || '')) {
            return true;
        }
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return true;
        }
        return false;
    }

    // start playing (if the browser blocks it, just ignore it)
    function panelPlayVideo() {
        var attempt = panelVideo.play();
        if (attempt && attempt.catch) {
            attempt.catch(function () {});
        }
    }

    // save the current second of the video
    function panelSaveVideoTime() {
        try {
            sessionStorage.setItem('emberBgTime', panelVideo.currentTime);
        } catch (e) {}
    }

    if (panelVideo) {
        if (panelUseLightMode()) {
            panelVideo.style.backgroundImage = "url('" + panelVideo.poster + "')";
            panelVideo.style.backgroundSize = 'cover';
            panelVideo.style.backgroundPosition = 'center';
        } else {
            // add the video file now (not in the HTML, so slow connections never download it)
            var panelSource = document.createElement('source');
            panelSource.src = '<?= $emberAssets ?>/background.mp4';
            panelSource.type = 'video/mp4';
            panelVideo.appendChild(panelSource);
            panelVideo.load();

            // jump to the saved second once the video is ready
            try {
                var panelSavedTime = parseFloat(sessionStorage.getItem('emberBgTime'));
                if (!isNaN(panelSavedTime) && panelSavedTime > 0) {
                    var panelJumpToSavedTime = function () {
                        panelVideo.removeEventListener('canplay', panelJumpToSavedTime);
                        if (panelSavedTime < panelVideo.duration - 1) {
                            panelVideo.currentTime = panelSavedTime;
                        }
                    };
                    panelVideo.addEventListener('canplay', panelJumpToSavedTime);
                }
            } catch (e) {}

            // save the time when leaving the page, and every second
            window.addEventListener('pagehide', panelSaveVideoTime);
            window.addEventListener('beforeunload', panelSaveVideoTime);
            setInterval(panelSaveVideoTime, 1000);

            panelVideo.muted = true;
            panelPlayVideo();
            window.addEventListener('pageshow', panelPlayVideo);
            panelVideo.addEventListener('stalled', panelPlayVideo);
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    panelPlayVideo();
                }
            });
        }
    }
</script>
