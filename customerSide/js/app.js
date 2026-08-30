const hamburger = document.querySelector('.header .nav-bar .nav-list .hamburger');
const mobile_menu = document.querySelector('.header .nav-bar .nav-list ul');
const menu_item = document.querySelectorAll('.header .nav-bar .nav-list ul li a');
const header = document.querySelector('.header.container');

/* Below 1200px the nav is a slide-in panel; at or above it the horizontal bar
 * is showing and none of the touch behaviour below should run. This is the
 * same breakpoint css/style.css uses to hide the hamburger, so the two can
 * never disagree about which layout is on screen. */
const small_screen = window.matchMedia('(max-width: 1199px)');

hamburger.addEventListener('click', () => {
	hamburger.classList.toggle('active');
	mobile_menu.classList.toggle('active');
	sync_nav_state();
});

document.addEventListener('scroll', () => {
	// Don't repaint the bar while the panel is open — the panel is the surface
	// the viewer is looking at, and the scroll position behind it is frozen.
	if (document.body.classList.contains('nav-open')) return;

	var scroll_position = window.scrollY;
	if (scroll_position > 250) {
		header.style.backgroundColor = '#29323c';
	} else {
		header.style.backgroundColor = 'transparent';
	}
});

menu_item.forEach((item) => {
	item.addEventListener('click', () => {
		hamburger.classList.toggle('active');
		mobile_menu.classList.toggle('active');
		sync_nav_state();
	});
});

/* ---------------------------------------------------------------------
 * Touch behaviour for the slide-in panel
 *
 * Everything below is new and everything below is gated on small_screen, so
 * the desktop nav — which has no hamburger, no panel and a working :hover
 * dropdown — is left exactly as it was.
 * ------------------------------------------------------------------ */

/* Locks the page behind the open panel. Without this the panel stays put
 * while the site scrolls around underneath it, which reads as the menu being
 * broken. */
function sync_nav_state() {
	var open = mobile_menu.classList.contains('active');
	document.body.classList.toggle('nav-open', open);

	if (!open) close_dropdown();

	// The scroll listener stops repainting while the panel is open, so put the
	// bar back to whatever the scroll position calls for on the way out.
	if (!open && header) {
		header.style.backgroundColor = window.scrollY > 250 ? '#29323c' : 'transparent';
	}
}

const dropdown = document.querySelector('.header .nav-bar .nav-list .dropdown');
const dropbtn = dropdown && dropdown.querySelector('.dropbtn');

function close_dropdown() {
	if (dropdown) dropdown.classList.remove('is-open');
}

if (dropdown && dropbtn) {
	/* The account menu is opened by :hover in components/footer.php, which a
	 * finger cannot do — so on a phone Member Login, Staff Login, Sign Up,
	 * Profile and Logout were all unreachable. On a small screen the same
	 * markup becomes a tap-to-open accordion (see css/mobile.css); on a wide
	 * screen this handler does nothing and hover still runs the show. */
	dropbtn.addEventListener('click', (e) => {
		if (!small_screen.matches) return;
		e.preventDefault();
		e.stopPropagation();
		dropdown.classList.toggle('is-open');
	});

	// Tapping a link inside it navigates away; closing first stops the panel
	// from flashing open again if the browser restores the page from cache.
	dropdown.querySelectorAll('a').forEach((link) => {
		link.addEventListener('click', () => {
			if (!small_screen.matches) return;
			close_dropdown();
		});
	});
}

// Escape closes the panel, for anyone on a small window with a keyboard.
document.addEventListener('keydown', (e) => {
	if (e.key !== 'Escape') return;
	if (!mobile_menu.classList.contains('active')) return;
	hamburger.classList.remove('active');
	mobile_menu.classList.remove('active');
	sync_nav_state();
});

/* Rotating a phone, or dragging a desktop window across the breakpoint, must
 * not leave the page stuck with a locked body or a half-open accordion from
 * the layout that is no longer on screen. */
function on_breakpoint_change() {
	if (small_screen.matches) return;
	hamburger.classList.remove('active');
	mobile_menu.classList.remove('active');
	close_dropdown();
	document.body.classList.remove('nav-open');
}

if (small_screen.addEventListener) {
	small_screen.addEventListener('change', on_breakpoint_change);
} else if (small_screen.addListener) {
	small_screen.addListener(on_breakpoint_change); // Safari < 14
}
