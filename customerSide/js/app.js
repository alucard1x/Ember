// app.js - all the JavaScript for the customer website
// (header hide on scroll, phone menu, sign in dropdown, dimmer, fade-in, menu filter)
// it is loaded at the bottom of every page by footer.php

var lessMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;


// ---------- 1. header hides when you scroll down, comes back when you scroll up ----------

function setupHeader() {
    var header = document.querySelector('.e-masthead');
    if (!header) {
        return;
    }
    var lastScroll = window.scrollY;
    var waiting = false;

    function updateHeader() {
        var scroll = window.scrollY;
        var goingDown = scroll > lastScroll;

        var menuIsOpen = document.body.classList.contains('e-menu-open');
        if (scroll > 220 && goingDown && !menuIsOpen) {
            header.classList.add('is-tucked');       // hide
        } else if (!goingDown || scroll <= 220) {
            header.classList.remove('is-tucked');    // show
        }

        if (scroll > 0) {
            lastScroll = scroll;
        } else {
            lastScroll = 0;
        }
        waiting = false;
    }

    // run updateHeader at most once per frame
    window.addEventListener('scroll', function () {
        if (waiting) {
            return;
        }
        waiting = true;
        window.requestAnimationFrame(updateHeader);
    }, { passive: true });
}


// ---------- 2. full screen menu on phones (the 3 lines button) ----------

function setupPhoneMenu() {
    var button = document.querySelector('.e-burger');
    var menu = document.querySelector('.e-overlay');
    if (!button || !menu) {
        return;
    }
    var lastFocused = null;   // so we can go back to it after closing

    function openMenu() {
        lastFocused = document.activeElement;
        menu.classList.add('is-open');
        menu.removeAttribute('aria-hidden');
        button.setAttribute('aria-expanded', 'true');
        document.body.classList.add('e-menu-open');   // stops the page scrolling
        var firstLink = menu.querySelector('a, button');
        if (firstLink) {
            firstLink.focus({ preventScroll: true });
        }
    }

    function closeMenu() {
        menu.classList.remove('is-open');
        menu.setAttribute('aria-hidden', 'true');
        button.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('e-menu-open');
        if (lastFocused && lastFocused.focus) {
            lastFocused.focus({ preventScroll: true });
        }
    }

    button.addEventListener('click', function () {
        if (menu.classList.contains('is-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // clicking a link closes the menu
    menu.addEventListener('click', function (e) {
        if (e.target.closest('a')) {
            closeMenu();
        }
    });

    // Escape key closes the menu
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) {
            closeMenu();
        }
    });

    // Tab key stays inside the menu while it is open
    menu.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab' || !menu.classList.contains('is-open')) {
            return;
        }
        var items = menu.querySelectorAll('a[href], button:not([disabled])');
        if (items.length == 0) {
            return;
        }
        var first = items[0];
        var last = items[items.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });

    // if the window becomes wide again, close the phone menu
    window.addEventListener('resize', function () {
        if (window.innerWidth > 900 && menu.classList.contains('is-open')) {
            closeMenu();
        }
    });
}


// ---------- 3. Sign In / Account dropdown ----------
// mouse hover opens it (CSS), a click keeps it open until you click somewhere else

function setupAccountMenu() {
    var box = document.querySelector('.e-account');
    if (!box) {
        return;
    }
    var button = box.querySelector('.e-account__button');
    if (!button) {
        return;
    }

    function closeAccountMenu() {
        box.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
    }

    button.addEventListener('click', function (e) {
        e.preventDefault();
        var isOpen = box.classList.toggle('is-open');
        if (isOpen) {
            button.setAttribute('aria-expanded', 'true');
        } else {
            button.setAttribute('aria-expanded', 'false');
        }
    });

    // click outside closes it
    document.addEventListener('click', function (e) {
        if (!box.contains(e.target)) {
            closeAccountMenu();
        }
    });

    // Escape closes it
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && box.classList.contains('is-open')) {
            closeAccountMenu();
            button.focus();
        }
    });
}


// ---------- 4. dimmer: video is bright on the homepage hero, darker after you scroll past it ----------

function setupDimmer() {
    var hero = document.querySelector('.e-hero');
    if (!hero || !document.body.classList.contains('has-hero')) {
        return;
    }
    if (!('IntersectionObserver' in window)) {
        document.body.classList.add('is-past-hero');
        return;
    }
    // IntersectionObserver tells us when the hero is on the screen or not
    var heroWatcher = new IntersectionObserver(function (entries) {
        var heroOnScreen = entries[0].isIntersecting;
        document.body.classList.toggle('is-past-hero', !heroOnScreen);
    }, { threshold: 0 });
    heroWatcher.observe(hero);
}


// ---------- 5. fade in when you scroll ----------
// elements with data-reveal get the class "is-in" when they come on the screen

function setupReveal() {
    var items = document.querySelectorAll('[data-reveal]');
    if (items.length == 0) {
        return;
    }

    // items in a group appear one after another (small delay each, max 6)
    var groups = document.querySelectorAll('[data-reveal-group]');
    for (var g = 0; g < groups.length; g++) {
        var children = groups[g].querySelectorAll(':scope > [data-reveal]');
        for (var c = 0; c < children.length; c++) {
            children[c].style.setProperty('--i', Math.min(c, 6));
        }
    }

    // no animation wanted (or old browser) -> show everything now
    if (lessMotion || !('IntersectionObserver' in window)) {
        for (var i = 0; i < items.length; i++) {
            items[i].classList.add('is-in');
        }
        return;
    }

    // photos (data-reveal="image") are hidden with clip-path, and a hidden element
    // is never "visible" to the observer, so for photos we watch the parent instead
    var watched = new Map();   // box we watch => elements to show
    for (var j = 0; j < items.length; j++) {
        var item = items[j];
        var box = item;
        if (item.getAttribute('data-reveal') === 'image' && item.parentElement) {
            box = item.parentElement;
        }
        if (!watched.has(box)) {
            watched.set(box, []);
        }
        watched.get(box).push(item);
    }

    var revealWatcher = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }
            var toShow = watched.get(entry.target) || [];
            toShow.forEach(function (el) {
                el.classList.add('is-in');
            });
            revealWatcher.unobserve(entry.target);   // only needs to happen once
        });
    }, {
        rootMargin: '0px 0px -12% 0px',   // start a little before it reaches the screen
        threshold: 0.05
    });

    watched.forEach(function (elements, box) {
        revealWatcher.observe(box);
    });
}


// ---------- 6. menu page: filter buttons and search box ----------

function setupMenuFilter() {
    var menuBox = document.querySelector('[data-menu]');
    if (!menuBox) {
        return;
    }
    var buttons = menuBox.querySelectorAll('[data-course]');
    var searchBox = menuBox.querySelector('[data-menu-search]');
    var courses = menuBox.querySelectorAll('.e-course');
    var dishes = menuBox.querySelectorAll('.e-dish');
    var nothingFound = menuBox.querySelector('[data-menu-empty]');

    var chosenCategory = 'all';
    var searchText = '';

    // hide the dishes that don't match the button and the search text
    function updateMenu() {
        var shownCount = 0;

        for (var i = 0; i < dishes.length; i++) {
            var dish = dishes[i];
            var rightCategory = chosenCategory === 'all' || dish.getAttribute('data-category') === chosenCategory;
            var name = (dish.getAttribute('data-name') || '').toLowerCase();
            var matchesSearch = searchText == '' || name.indexOf(searchText) !== -1;

            var show = rightCategory && matchesSearch;
            dish.hidden = !show;
            if (show) {
                shownCount++;
            }
        }

        // hide a course heading if all its dishes are hidden
        for (var c = 0; c < courses.length; c++) {
            var visibleDish = courses[c].querySelector('.e-dish:not([hidden])');
            courses[c].hidden = !visibleDish;
        }

        if (nothingFound) {
            nothingFound.hidden = shownCount !== 0;
        }
    }

    // filter buttons
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            chosenCategory = button.getAttribute('data-course');
            buttons.forEach(function (other) {
                if (other === button) {
                    other.setAttribute('aria-pressed', 'true');
                } else {
                    other.setAttribute('aria-pressed', 'false');
                }
            });
            updateMenu();
        });
    });

    // search box (waits 120ms after typing stops)
    if (searchBox) {
        var typingTimer;
        searchBox.addEventListener('input', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function () {
                searchText = searchBox.value.trim().toLowerCase();
                updateMenu();
            }, 120);
        });
        // pressing Enter should not reload the page
        searchBox.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }
}


// ---------- 7. underline the navigation link of the page we are on ----------

function markCurrentPage() {
    var thisPage = window.location.pathname.split('/').pop();
    var links = document.querySelectorAll('.e-nav a[href], .e-overlay__list a[href]');
    for (var i = 0; i < links.length; i++) {
        var linkPage = links[i].getAttribute('href').split('/').pop().split('?')[0];
        if (linkPage && linkPage === thisPage) {
            links[i].setAttribute('aria-current', 'page');
        }
    }
}


// ---------- start everything ----------
setupHeader();
setupPhoneMenu();
setupAccountMenu();
setupDimmer();
setupReveal();
setupMenuFilter();
markCurrentPage();
