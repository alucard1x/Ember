<?php // pageTransition.php - loading screen and smooth animation when going from one page to another ?>
<div class="ember-filament" aria-hidden="true"><i></i></div>

<div id="ember-loader" class="ember-loader">
    <div class="ember-loader__bloom" aria-hidden="true"></div>
    <?php $markVariant = 'loader'; include __DIR__ . '/brandMark.php'; ?>
</div>

<style>
    /* ===== 1. LOADING SCREEN (only shown on the first page of a visit) ===== */

    .ember-loader {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #14100f;
        overflow: hidden;      /* the glow is bigger than the screen */
        opacity: 0;
        pointer-events: none;
        visibility: hidden;
        transition: opacity 0.72s cubic-bezier(0.4, 0, 0.2, 1),
                    visibility 0s linear 0.72s;
    }
    .ember-loader.is-active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transition: opacity 0.45s ease, visibility 0s;
    }

    /* warm glow behind the logo that grows when the loading screen goes away */
    .ember-loader__bloom {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 120vmax;
        height: 120vmax;
        transform: translate(-50%, -50%) scale(0.34);
        background-image: radial-gradient(
            circle at center,
            rgba(177, 70, 47, 0.30) 0%,
            rgba(177, 70, 47, 0.10) 32%,
            rgba(177, 70, 47, 0) 62%
        );
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.9s ease, transform 1.1s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ember-loader.is-active .ember-loader__bloom {
        opacity: 0.75;
        transform: translate(-50%, -50%) scale(0.34);
    }
    .ember-loader.is-lifting .ember-loader__bloom {
        opacity: 0;
        transform: translate(-50%, -50%) scale(1);
    }

    /* logo on the loading screen.
       The login and reservation pages do not load style.css, so the logo styles are copied here.
       These sizes MUST stay the same as the homepage logo in style.css */
    .brand-mark--loader {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .brand-mark--loader .brand-word {
        display: block;
        /* Cinzel is the font for Android phones */
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
    @media only screen and (min-width: 1200px) {
        .brand-mark--loader .brand-word {
            font-size: 84px;
            letter-spacing: 11px;
            text-indent: 5.5px;
        }
        .brand-mark--loader .brand-rule {
            width: 190px;
            margin-top: 36px;
        }
        .brand-mark--loader .brand-tagline {
            margin-top: 28px;
            font-size: 16px;
            letter-spacing: 9.6px;
            text-indent: 4.8px;
        }
    }
    /* small red line moving under the logo while the page loads */
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
    .ember-filament.is-running {
        opacity: 1;
    }
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
        /* fast at first, then slow */
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

    /* the page content: old page fades out going up, new page fades in */

    ::view-transition-old(root) {
        animation: ember-content-out calc(260ms * var(--ember-vt)) cubic-bezier(0.4, 0, 1, 1) both;
    }
    ::view-transition-new(root) {
        animation: ember-content-in calc(520ms * var(--ember-vt)) cubic-bezier(0.16, 1, 0.3, 1) calc(90ms * var(--ember-vt)) both;
    }
    @keyframes ember-content-out {
        to {
            opacity: 0;
            transform: translateY(-12px) scale(0.992);
            filter: blur(3px);
        }
    }
    @keyframes ember-content-in {
        from {
            opacity: 0;
            transform: translateY(20px) scale(1.006);
            filter: blur(4px);
        }
        to {
            opacity: 1;
            transform: none;
            filter: blur(0);
        }
    }

    /* Back button: the content moves the other way */
    @supports selector(:active-view-transition-type(ember-back)) {
        html:active-view-transition-type(ember-back)::view-transition-old(root) {
            animation-name: ember-content-out-back;
        }
        html:active-view-transition-type(ember-back)::view-transition-new(root) {
            animation-name: ember-content-in-back;
        }
    }
    @keyframes ember-content-out-back {
        to {
            opacity: 0;
            transform: translateY(14px) scale(1.008);
            filter: blur(3px);
        }
    }
    @keyframes ember-content-in-back {
        from {
            opacity: 0;
            transform: translateY(-16px) scale(0.994);
            filter: blur(4px);
        }
        to {
            opacity: 1;
            transform: none;
            filter: blur(0);
        }
    }

    /* the logo flies from its place on the old page to its place on the new page.
       Only ONE element per page may have this name, otherwise the animation is cancelled */

    .brand-mark--hero .brand-word,
    .e-masthead__wordmark,
    .site-brand {
        view-transition-name: ember-word;
    }
    /* the loading screen logo must not have the name (on the homepage there would be two) */
    .ember-loader .brand-word {
        view-transition-name: none;
    }

    ::view-transition-group(ember-word) {
        /* the logo moves a bit slower than the page */
        animation-duration: calc(620ms * var(--ember-vt));
        animation-timing-function: cubic-bezier(0.62, 0.04, 0.2, 1);
        z-index: 20;
    }
    ::view-transition-old(ember-word),
    ::view-transition-new(ember-word) {
        /* fade between the big and small logo while it moves */
        animation-duration: calc(620ms * var(--ember-vt));
        animation-timing-function: cubic-bezier(0.62, 0.04, 0.2, 1);
        height: 100%;
        object-fit: contain;
    }

    /* the background video stays still while the page changes */

    .site-bg {
        view-transition-name: ember-backdrop;
    }
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
    ::view-transition-group(root) {
        z-index: 2;
    }

    /* the header does not fade, the new header appears straight away
       (so SIGN IN and ACCOUNT are never shown at the same time) */

    .e-masthead {
        view-transition-name: ember-nav;
    }
    ::view-transition-group(ember-nav) {
        animation-duration: 0s;
        z-index: 10;
    }
    ::view-transition-image-pair(ember-nav) {
        isolation: auto;
    }
    ::view-transition-old(ember-nav) {
        display: none;
    }
    ::view-transition-new(ember-nav) {
        animation: none;
        opacity: 1;
        mix-blend-mode: normal;
    }

    /* going to a page without the header (login, reservation, staff panel): the header slides */

    @supports selector(:active-view-transition-type(ember-crossing)) {
        html:active-view-transition-type(ember-crossing)::view-transition-group(ember-nav) {
            animation-duration: calc(480ms * var(--ember-vt));
        }
        html:active-view-transition-type(ember-crossing)::view-transition-new(ember-nav) {
            animation: ember-nav-in calc(460ms * var(--ember-vt)) cubic-bezier(0.16, 1, 0.3, 1) calc(160ms * var(--ember-vt)) both;
        }
        html:active-view-transition-type(ember-crossing)::view-transition-old(ember-nav) {
            display: block;
            animation: ember-nav-in calc(300ms * var(--ember-vt)) cubic-bezier(0.4, 0, 1, 1) reverse both;
        }
    }
    @keyframes ember-nav-in {
        from { opacity: 0; transform: translateY(-100%); }
        to   { opacity: 1; transform: none; }
    }

    /* ===== 4. SIMPLE FADE for Safari and Firefox (the script adds the class ember-fallback) =====
       the video, header, logo, loading screen and red line do not fade */

    html.ember-fallback body > *:not(.site-bg):not(#ember-loader):not(.ember-filament):not(.e-masthead):not(.e-overlay):not(.site-brand) {
        animation: ember-fb-in 520ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    html.ember-fallback.is-leaving body > *:not(.site-bg):not(#ember-loader):not(.ember-filament):not(.e-masthead):not(.e-overlay):not(.site-brand) {
        animation: ember-fb-out 280ms cubic-bezier(0.4, 0, 1, 1) both;
    }
    @keyframes ember-fb-in {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: none; }
    }
    @keyframes ember-fb-out {
        from { opacity: 1; transform: none; }
        to   { opacity: 0; transform: translateY(-12px); }
    }

    /* ===== 5. NO ANIMATION if the user turned off motion on their computer ===== */

    @media (prefers-reduced-motion: reduce) {
        .brand-mark--loader .brand-rule::after,
        .ember-filament.is-running i { animation: none; }
        .ember-loader,
        .ember-loader__bloom { transition: none; }
        .ember-filament.is-running i { width: 60%; }

        ::view-transition-group(*),
        ::view-transition-old(*),
        ::view-transition-new(*) {
            animation: none !important;
        }
        html.ember-fallback body > *,
        html.ember-fallback.is-leaving body > * {
            animation: none;
        }
    }
</style>

<script>
    // ---------- setup ----------

    var loadingScreen = document.getElementById('ember-loader');
    var topLine = document.querySelector('.ember-filament');
    var topLineTimer = null;

    // Chrome and Edge can animate between pages by themselves ("view transitions")
    var browserDoesTransitions = ('onpageswap' in window) && typeof document.startViewTransition === 'function';

    // other browsers (Safari, Firefox) get a simple fade that we do ourselves
    if (!browserDoesTransitions) {
        document.documentElement.classList.add('ember-fallback');
    }

    // when the browser cancels a page animation it shows a harmless "AbortError" in the console - hide only that one
    window.addEventListener('unhandledrejection', function (e) {
        var reason = e.reason;
        if (reason && reason.name === 'AbortError' && /transition/i.test(reason.message || '')) {
            e.preventDefault();
        }
    });


    // ---------- loading screen (only on the first page of a visit) ----------

    var firstVisit = true;
    try {
        if (sessionStorage.getItem('emberVisited')) {
            firstVisit = false;
        }
        sessionStorage.setItem('emberVisited', '1');
    } catch (e) {}

    // the sign-in pages (member login, create account, staff login) always open with the same
    // logo curtain you see when you first go from the homepage to the menu -
    // except when the page just reloads itself (for example after a wrong password)
    var isSignInPage = /\/(customerLogin\/(login|register)|StaffLogin\/login)\.php$/i.test(location.pathname);
    var cameFromSamePage = false;
    try {
        cameFromSamePage = document.referrer !== '' && new URL(document.referrer).pathname === location.pathname;
    } catch (e) {}
    if (isSignInPage && !cameFromSamePage) {
        firstVisit = true;
    }

    if (firstVisit && loadingScreen) {
        loadingScreen.classList.add('is-active');   // show it
    }

    // hide the loading screen and start the logo animation
    function hideLoadingScreen() {
        if (loadingScreen) {
            loadingScreen.classList.add('is-lifting');
            loadingScreen.classList.remove('is-active');
        }
        document.body.classList.add('ember-revealed');
    }

    if (firstVisit) {
        // wait until the page has loaded, then hide it
        if (document.readyState === 'complete') {
            setTimeout(hideLoadingScreen, 260);
        } else {
            window.addEventListener('load', function () {
                setTimeout(hideLoadingScreen, 260);
            });
        }
        setTimeout(hideLoadingScreen, 4000);   // hide it after 4 seconds anyway
    } else {
        hideLoadingScreen();
    }

    // coming back with the Back button: reset everything
    window.addEventListener('pageshow', function () {
        hideLoadingScreen();
        document.documentElement.classList.remove('is-leaving');
        if (topLine) {
            topLine.classList.remove('is-running');
        }
    });


    // ---------- the flying logo needs exactly ONE element with the same name per page ----------
    // if two elements had it, the browser would cancel the animation, so we keep only the first

    function keepOnlyOne(selector) {
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

    function checkTransitionNames() {
        keepOnlyOne('.brand-mark--hero .brand-word, .e-masthead__wordmark, .site-brand');
        keepOnlyOne('.site-bg');
        keepOnlyOne('.e-masthead');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkTransitionNames);
    } else {
        checkTransitionNames();
    }


    // ---------- thin red line at the top while the next page loads ----------

    function startTopLine() {
        if (!topLine) {
            return;
        }
        topLine.classList.remove('is-running');
        void topLine.offsetWidth;            // this restarts the CSS animation
        topLine.classList.add('is-running');

        // remove it after 12 seconds (for example if a form did not leave the page)
        clearTimeout(topLineTimer);
        topLineTimer = setTimeout(function () {
            topLine.classList.remove('is-running');
        }, 12000);
    }


    // ---------- is this a normal link to another page of our site? ----------

    function isPageLink(link, e) {
        if (!link) {
            return false;
        }
        var href = link.getAttribute('href');
        if (!href) {
            return false;
        }
        // not these: #anchors, new tabs, downloads, email/phone links, the PDF bill, the cart
        if (href.charAt(0) === '#'
            || link.target === '_blank'
            || link.hasAttribute('download')
            || link.hasAttribute('data-no-transition')
            || /^(mailto:|tel:|javascript:)/i.test(href)
            || /bill\.php/.test(href)
            || /cart\.php/.test(href)) {
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
        if (!isPageLink(link, e)) {
            return;
        }

        startTopLine();

        // Chrome / Edge do the animation themselves
        if (browserDoesTransitions) {
            return;
        }

        // other browsers: fade the page out first, then go to the new page
        e.preventDefault();
        document.documentElement.classList.add('is-leaving');
        setTimeout(function () {
            window.location.href = link.href;
        }, 260);
    });

    // also show the red line when a form is sent (login, register, reservation)
    document.addEventListener('submit', startTopLine, true);


    // ---------- tell the CSS which kind of page change it is ----------
    // "ember-back"     = the Back button was used
    // "ember-crossing" = going between pages that look different (normal page / form page / staff panel)

    // which kind of page is this url?
    function getPageKind(pathname) {
        if (/\/adminSide\//i.test(pathname) && !/\/StaffLogin\//i.test(pathname)) {
            return 'console';     // staff panel
        }
        if (/\/(customerLogin|CustomerReservation|StaffLogin)\//i.test(pathname)) {
            return 'plain';       // form page without the header
        }
        return 'site';            // normal page with the header
    }

    function isDifferentKind(fromUrl, toUrl) {
        if (!fromUrl || !toUrl) {
            return false;
        }
        try {
            var fromKind = getPageKind(new URL(fromUrl, location.href).pathname);
            var toKind = getPageKind(new URL(toUrl, location.href).pathname);
            return fromKind !== toKind;
        } catch (err) {
            return false;
        }
    }

    function addTransitionTypes(transition, isBack, isCrossing) {
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
        addTransitionTypes(e.viewTransition, activation.navigationType === 'traverse', isDifferentKind(location.href, toUrl));
    });

    // runs on the NEW page when it is shown
    window.addEventListener('pagereveal', function (e) {
        if (!e.viewTransition) {
            return;
        }
        var activation = window.navigation && window.navigation.activation;
        var fromUrl = activation && activation.from && activation.from.url;
        addTransitionTypes(e.viewTransition, activation && activation.navigationType === 'traverse', isDifferentKind(fromUrl, location.href));
    });
</script>
