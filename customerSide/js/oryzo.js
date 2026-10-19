// oryzo.js - behaviour for home.php (the Ember homepage).
// Everything is plain JavaScript. Sections are in page order.

(function () {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isDesktop = window.matchMedia('(min-width: 768px)');

    function clamp(n, min, max) { return Math.min(max, Math.max(min, n)); }


    // ---------- SMOOTH SCROLLING (Lenis) ----------
    // Lenis eases the page towards where you scrolled, which gives the soft "floaty" feel.
    // All the scroll effects below run inside the same frame as Lenis, so they never lag behind it.
    var lenis = null;
    if (window.Lenis && !reduceMotion) {
        lenis = new Lenis({ lerp: 0.1, wheelMultiplier: 0.9 });

        // links to a section on this page (#kitchen etc.) glide there too
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[href^="#"]');
            if (!link) { return; }
            var target = document.querySelector(link.getAttribute('href'));
            if (target) {
                e.preventDefault();
                lenis.scrollTo(target, { duration: 1.4 });
            }
        });
    }


    // ---------- MEASURE ONCE, NOT EVERY FRAME ----------
    // Reading sizes from the page while also moving things is what makes scrolling stutter,
    // so every section's top and height is measured once (and again after a resize).
    var header = document.querySelector('.oz-header');
    var hero = document.querySelector('.oz-hero');
    var navLinks = document.querySelectorAll('[data-nav]');
    var navNow = document.querySelector('[data-nav-now]');
    var sections = Array.prototype.slice.call(document.querySelectorAll('[data-section]'));
    var lights = Array.prototype.slice.call(document.querySelectorAll('.oz-light'));
    var statement = document.querySelector('.oz-statement');
    var spinPlate = document.querySelector('[data-spin]');
    var sharpen = document.querySelector('.oz-sharpen');
    var scenes = document.querySelector('.oz-scenes');
    var strip = document.querySelector('.oz-strip');
    var stripTrack = strip ? strip.querySelector('.oz-strip__track') : null;
    var view = window.innerHeight;
    var box = new Map();            // element -> { top, height }
    var stripExtra = 0;

    function measure() {
        view = window.innerHeight;
        var y = window.scrollY;
        // the sideways gallery is as tall as its extra width, so the sideways move feels 1:1
        if (strip) {
            stripExtra = Math.max(0, stripTrack.scrollWidth - window.innerWidth);
            strip.style.height = isDesktop.matches ? (view + stripExtra) + 'px' : '';
        }
        [hero, statement, sharpen, scenes, strip].concat(sections, lights).forEach(function (el) {
            if (!el) { return; }
            var r = el.getBoundingClientRect();
            box.set(el, { top: r.top + y, height: r.height });
        });
    }

    // 0 at the top of a tall sticky section, 1 at its end
    function sticky(el, y) {
        var b = box.get(el);
        var room = b.height - view;
        return room > 0 ? clamp((y - b.top) / room, 0, 1) : 0;
    }
    // 0 when a section's top enters at the bottom of the screen, 1 when its bottom leaves at the top
    function passing(el, y) {
        var b = box.get(el);
        return clamp((y + view - b.top) / (b.height + view), 0, 1);
    }


    // ---------- SHARPEN: split the big line into words ----------
    var line = document.querySelector('[data-words]');
    var words = [];
    if (line) {
        var parts = line.textContent.trim().split(/\s+/);
        line.textContent = '';
        parts.forEach(function (word, i) {
            var span = document.createElement('span');     // the slot
            span.className = 'w';
            var inner = document.createElement('span');    // the word that rises inside it
            inner.textContent = word;
            span.appendChild(inner);
            line.appendChild(span);
            if (i < parts.length - 1) { line.appendChild(document.createTextNode(' ')); }
            words.push(span);
        });
    }


    // ---------- SCENES ----------
    var scenePhotos = scenes ? scenes.querySelectorAll('.oz-scenes__photo') : [];
    var sceneSteps = scenes ? scenes.querySelectorAll('.oz-scene') : [];
    var sceneBar = scenes ? scenes.querySelector('.oz-scenes__bar') : null;
    var sceneNow = -1;

    // on phones the scene list is stacked, so only the first photo is shown
    function resetScenesForPhone() {
        if (!scenes) { return; }
        if (!isDesktop.matches) {
            sceneSteps.forEach(function (el) { el.classList.add('is-on'); });
            scenePhotos.forEach(function (el, i) { el.classList.toggle('is-on', i === 0); el.style.opacity = i === 0 ? '1' : ''; });
        } else {
            scenePhotos.forEach(function (el) { el.style.opacity = ''; });
            sceneNow = -1;
        }
    }


    // ---------- EVERYTHING THAT MOVES WITH THE SCROLL (one function, called once per frame) ----------
    var lastY = -1;
    var current = '';
    var onLight = false;

    function update(y) {
        if (y === lastY) { return; }
        lastY = y;

        // header: small logo after the hero, dark text over the light section, underline the current link
        header.classList.toggle('is-past-hero', y > box.get(hero).height * 0.6);
        // the page turns cream while the light section fills the middle of the screen
        var light = false;
        lights.forEach(function (el) {
            var b = box.get(el);
            if (y + view / 2 >= b.top && y + view / 2 <= b.top + b.height) { light = true; }
        });
        if (light !== onLight) {
            onLight = light;
            document.documentElement.classList.toggle('is-light', light);
            header.classList.toggle('on-light', light);
        }
        var middle = y + view / 2, now = '';
        sections.forEach(function (el) {
            var b = box.get(el);
            if (middle >= b.top && middle <= b.top + b.height) { now = el.getAttribute('data-section'); }
        });
        if (now !== current) {
            current = now;
            navLinks.forEach(function (a) {
                var on = a.getAttribute('data-nav') === now;
                a.classList.toggle('is-on', on);
                if (on && navNow) { navNow.textContent = a.getAttribute('aria-label'); }
            });
        }

        if (reduceMotion) { return; }

        // statement: the plate turns from edge-on to flat
        if (spinPlate) {
            spinPlate.style.setProperty('--spin', (75 - passing(statement, y) * 110).toFixed(1) + 'deg');
        }

        // sharpen: each word goes from blurred to sharp in turn
        if (sharpen) {
            var p = sticky(sharpen, y);
            words.forEach(function (w, i) {
                var share = (i + 1) / words.length * 0.7;
                var blur = clamp((share - p) / (0.7 / words.length), 0, 1);
                w.style.setProperty('--b', blur.toFixed(3));
            });
        }

        // scenes: three heats, one at a time
        if (scenes && isDesktop.matches) {
            var sp = sticky(scenes, y);
            sceneBar.style.setProperty('--p', sp.toFixed(3));
            var step = Math.min(sceneSteps.length - 1, Math.floor(sp * sceneSteps.length));
            if (step !== sceneNow) {
                sceneNow = step;
                sceneSteps.forEach(function (el, i) { el.classList.toggle('is-on', i === step); });
                scenePhotos.forEach(function (el, i) { el.classList.toggle('is-on', i === step); });
            }
        }

        // sideways gallery
        if (strip && isDesktop.matches) {
            stripTrack.style.setProperty('--x', (-stripExtra * sticky(strip, y)).toFixed(1) + 'px');
        }
    }

    // run update() in step with Lenis (or with normal scrolling when Lenis is off)
    if (lenis) {
        var frame = function (time) {
            lenis.raf(time);
            update(Math.round(lenis.scroll * 10) / 10);
            requestAnimationFrame(frame);
        };
        requestAnimationFrame(frame);
    } else {
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (ticking) { return; }
            ticking = true;
            requestAnimationFrame(function () { update(window.scrollY); ticking = false; });
        }, { passive: true });
    }

    function remeasure() {
        resetScenesForPhone();
        measure();
        if (lenis) { lenis.resize(); }
        lastY = -1;
        update(window.scrollY);
    }
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(remeasure, 120);
    });
    window.addEventListener('load', remeasure);        // photos have loaded, sizes are final
    remeasure();


    // ---------- PHONE MENU ----------
    var menuButton = document.querySelector('.oz-menu-btn');
    var overlay = document.getElementById('oz-overlay');

    function setMenu(open) {
        menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        overlay.hidden = !open;
        document.body.style.overflow = open ? 'hidden' : '';
        document.body.classList.toggle('oz-menu-open', open);
        if (lenis) {
            if (open) { lenis.stop(); } else { lenis.start(); }
        }
    }
    menuButton.addEventListener('click', function () {
        setMenu(menuButton.getAttribute('aria-expanded') !== 'true');
    });
    overlay.addEventListener('click', function (e) {
        if (e.target.closest('a')) { setMenu(false); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !overlay.hidden) { setMenu(false); menuButton.focus(); }
    });


    // ---------- SIGN IN DROP-DOWN ----------
    var signBtn = document.querySelector('.oz-signin__btn');
    var signMenu = document.getElementById('oz-signin-menu');
    function setSignMenu(open) {
        signBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        signMenu.hidden = !open;
    }
    if (signBtn) {
        signBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            setSignMenu(signMenu.hidden);
        });
        document.addEventListener('click', function (e) {
            if (!signMenu.hidden && !e.target.closest('.oz-signin')) { setSignMenu(false); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !signMenu.hidden) { setSignMenu(false); signBtn.focus(); }
        });
    }


    // ---------- HERO: the flat photo (shown when 3D is off) follows the mouse a little ----------
    var tiltPlate = document.querySelector('[data-tilt]');
    if (tiltPlate && !reduceMotion) {
        hero.addEventListener('mousemove', function (e) {
            var x = (e.clientX / window.innerWidth - 0.5) * 24;
            var y = (e.clientY / window.innerHeight - 0.5) * 24;
            tiltPlate.style.setProperty('--mx', x.toFixed(1) + 'px');
            tiltPlate.style.setProperty('--my', y.toFixed(1) + 'px');
        });
    }


    // ---------- HEADINGS FADE UP ONCE WHEN THEY COME INTO VIEW ----------
    var fadeWatcher = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-in');
                fadeWatcher.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' });
    document.querySelectorAll('[data-fade]').forEach(function (el) { fadeWatcher.observe(el); });


    // ---------- QR CODE ON THE TABLE CARD ----------
    if (window.qrcode) {
        document.querySelectorAll('[data-qr]').forEach(function (box) {
            var qr = qrcode(0, 'M');                 // 0 = choose the size automatically, M = medium error correction
            qr.addData(box.getAttribute('data-qr'));
            qr.make();
            box.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0, scalable: true });
        });
    }


    // ---------- WARM UP THE PHOTOS ----------
    // Photos further down normally load at the moment you scroll to them, and preparing a big photo
    // can make that one moment stutter. So once the first screen is ready, the rest are fetched and
    // prepared quietly in the background, one at a time, before you get there.
    window.addEventListener('load', function () {
        var later = Array.prototype.slice.call(document.querySelectorAll('img[loading="lazy"]'));
        var idle = window.requestIdleCallback || function (fn) { return setTimeout(fn, 200); };
        function next() {
            var img = later.shift();
            if (!img) { return; }
            img.loading = 'eager';
            var ready = img.decode ? img.decode() : Promise.resolve();
            ready.catch(function () {}).then(function () { idle(next); });
        }
        setTimeout(function () { idle(next); }, 800);
    });


    // ---------- FOOTER VIDEO: only load and play it when the footer is on screen ----------
    var lazyVideos = document.querySelectorAll('[data-lazy-video]');
    if (lazyVideos.length) {
        var videoWatcher = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                var v = entry.target;
                if (entry.isIntersecting) {
                    if (reduceMotion) { return; }           // keep the still poster for people who prefer less motion
                    v.preload = 'auto';
                    var p = v.play();
                    if (p && p.catch) { p.catch(function () {}); }
                } else {
                    v.pause();
                }
            });
        }, { rootMargin: '200px 0px' });
        lazyVideos.forEach(function (v) { videoWatcher.observe(v); });
    }


    // ---------- ORDER DEMO: pill buttons switch the phone screen ----------
    var orderPills = document.querySelectorAll('[data-show]');
    var phoneScreens = document.querySelectorAll('[data-phone]');
    orderPills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            var show = pill.getAttribute('data-show');
            orderPills.forEach(function (p) { p.classList.toggle('is-on', p === pill); });
            phoneScreens.forEach(function (s) { s.hidden = s.getAttribute('data-phone') !== show; });
        });
    });


    // ---------- PICKER: tabs for the signature dishes (arrow keys move between tabs) ----------
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.oz-picker__tabs [role="tab"]'));

    function selectTab(tab) {
        tabs.forEach(function (t) {
            var on = t === tab;
            t.classList.toggle('is-on', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.tabIndex = on ? 0 : -1;
            var panel = document.getElementById(t.getAttribute('aria-controls'));
            panel.hidden = !on;
            if (on) { panel.classList.add('is-in'); }    // already faded in, show it straight away
        });
    }
    tabs.forEach(function (tab, i) {
        tab.tabIndex = i === 0 ? 0 : -1;
        tab.addEventListener('click', function () { selectTab(tab); });
        tab.addEventListener('keydown', function (e) {
            var next = null;
            if (e.key === 'ArrowRight') { next = tabs[(i + 1) % tabs.length]; }
            if (e.key === 'ArrowLeft') { next = tabs[(i - 1 + tabs.length) % tabs.length]; }
            if (next) { e.preventDefault(); selectTab(next); next.focus(); }
        });
    });


    // ---------- FOOTER: copy the page link ----------
    var copyButton = document.querySelector('[data-copy-url]');
    if (copyButton) {
        copyButton.addEventListener('click', function () {
            var label = copyButton.querySelector('[data-label]');
            function done(text) {
                label.textContent = text;
                label.setAttribute('data-label', text);
                setTimeout(function () { label.textContent = 'Copy link'; label.setAttribute('data-label', 'Copy link'); }, 1800);
            }
            if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href).then(function () { done('Copied'); }, function () { done('Copy failed'); });
            } else {
                done('Copy failed');
            }
        });
    }
})();
