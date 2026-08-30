<?php
/*
 * Site-wide video background.
 *
 * Drop your clip in at customerSide/image/background.mp4 and every page picks
 * it up automatically. Until that file exists the still hero photo is used, so
 * the site never renders on a blank backdrop.
 *
 * Before including this file a page must set $bgAssets to the path of
 * customerSide/image relative to that page, e.g.
 *     $bgAssets = '../image';                     // customerSide/<folder>/page.php
 *     $bgAssets = '../../customerSide/image';     // adminSide/<folder>/page.php
 */

$bgAssets = $bgAssets ?? '../image';
$bgVideoFile = __DIR__ . '/../image/background.mp4';
$bgHasVideo  = file_exists($bgVideoFile);

// customerSide/css, worked out from $bgAssets so it resolves from whichever
// folder included this file -- including adminSide/StaffLogin.
$bgCssDir = rtrim(dirname($bgAssets), '/') . '/css';
?>
<div class="site-bg">
<?php if ($bgHasVideo) { ?>
    <video id="site-bg-video" autoplay muted loop playsinline preload="auto"
           poster="<?= $bgAssets ?>/hero-background.jpg">
        <source src="<?= $bgAssets ?>/background.mp4" type="video/mp4">
    </video>
<?php } else { ?>
    <div class="site-bg-fallback" style="background-image: url('<?= $bgAssets ?>/hero-background.jpg');"></div>
<?php } ?>
    <div class="site-bg-scrim"></div>
</div>

<?php include __DIR__ . '/pageTransition.php'; ?>

<style>
    /* The backdrop sits behind everything and never scrolls. */
    .site-bg {
        position: fixed;
        inset: 0;
        z-index: -10;
        overflow: hidden;
        background-color: #14100f;
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
    }
    .site-bg-fallback {
        width: 100%;
        height: 100%;
    }
    /* Keeps text legible whatever is happening in the footage. */
    .site-bg-scrim {
        position: absolute;
        inset: 0;
        background-color: rgba(12, 9, 8, 0.45);
    }
    /* Let the backdrop show through the page itself. Pages that set their own
       body photo (login, register, reservation, staff login) are cleared here
       so the video is what shows. */
    html, body {
        background-color: transparent;
        background-image: none;
    }

    /* Every page that sits straight on the video gets the same readable card,
       so login, register, reservation, profile and staff login all match. */
    .login-container,
    .register-container,
    .reserve-container,
    .site-panel {
        background-color: rgba(18, 14, 13, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.10);
        border-radius: 4px;
        padding: 40px;
        color: #f3efec;
    }
    .login-container,
    .register-container,
    .site-panel {
        margin: 40px auto;
    }
    .reserve-container {
        margin: 120px auto 40px;
    }
    /* These pages centre the body with flex; that fights a scrolling card. */
    body.has-site-panel {
        display: block;
        height: auto;
        min-height: 100vh;
        padding: 40px 20px;
    }

    /* One set of form styles for every panelled page, so login, register,
       reservation and staff login all look like the same site. */
    .login-container .panel-title,
    .register-container .panel-title,
    .site-panel .panel-title {
        font-size: 2.2rem;
        font-weight: 300;
        letter-spacing: 0.32rem;
        text-transform: uppercase;
        text-align: center;
        margin-bottom: 6px;
    }
    .login-container .panel-title::after,
    .register-container .panel-title::after,
    .site-panel .panel-title::after {
        content: '';
        display: block;
        width: 46px;
        height: 1px;
        margin: 18px auto 32px;
        background-color: #b1462f;
    }
    .login-container label,
    .register-container label,
    .site-panel label {
        display: block;
        font-size: 11px;
        letter-spacing: 0.18rem;
        text-transform: uppercase;
        color: #b9aea8;
        margin-bottom: 8px;
    }
    .login-container .form-control,
    .register-container .form-control,
    .site-panel .form-control {
        width: 100%;
        padding: 11px 14px;
        margin-bottom: 18px;
        font-size: 14px;
        color: #f3efec;
        background-color: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 0;
        box-shadow: none;
    }
    .login-container .form-control:focus,
    .register-container .form-control:focus,
    .site-panel .form-control:focus {
        outline: none;
        color: #f3efec;
        background-color: rgba(255, 255, 255, 0.09);
        border-color: #b1462f;
        box-shadow: none;
    }
    .login-container option,
    .register-container option,
    .site-panel option {
        color: #14100f;
    }
    .login-container .btn,
    .register-container .btn,
    .site-panel .btn {
        display: inline-block;
        padding: 10px 26px;
        font-size: 12px;
        letter-spacing: 0.22rem;
        text-transform: uppercase;
        color: #f3efec;
        background-color: transparent;
        border: 1px solid #b1462f;
        border-radius: 0;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .login-container .btn:hover,
    .register-container .btn:hover,
    .site-panel .btn:hover {
        background-color: #b1462f;
        color: #ffffff;
    }
</style>

<?php
/*
 * Cinzel: the wordmark's fallback face.
 *
 * The Ember mark asks for Copperplate first and only reaches Cinzel on a
 * device that has none of the Copperplate/Optima/Palatino system fonts --
 * in practice, Android. It is linked here rather than in css/style.css
 * because login, register, profile and the reservation page draw the mark
 * (via brandLink.php and pageTransition.php) without linking style.css at
 * all, and those were the pages where it fell through to a plain serif.
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500&display=swap">

<?php
/*
 * The mobile layer. Loaded last on purpose: every rule in it is wrapped in a
 * max-width media query, so it can only ever act below the desktop
 * breakpoint, and being last is what lets it override the per-page styles
 * above without needing !important on everything.
 */
?>
<link rel="stylesheet" href="<?= $bgCssDir ?>/mobile.css?v=<?= @filemtime(__DIR__ . '/../css/mobile.css') ?>">

<?php if ($bgHasVideo) { ?>
<script>
    (function () {
        var video = document.getElementById('site-bg-video');
        if (!video) return;

        // Carry the playback position across page loads so navigating the site
        // looks like one continuous clip rather than a restart each time.
        // Seeking deep into the file forces a re-buffer, which looks like the
        // video is stuck on the poster. Only resume once enough is buffered.
        var KEY = 'emberBgTime';
        try {
            var saved = parseFloat(sessionStorage.getItem(KEY));
            if (!isNaN(saved) && saved > 0) {
                video.addEventListener('canplay', function seek() {
                    video.removeEventListener('canplay', seek);
                    if (saved < video.duration - 1) video.currentTime = saved;
                });
            }
        } catch (e) { /* private mode: just play from the start */ }

        function remember() {
            try { sessionStorage.setItem(KEY, video.currentTime); } catch (e) {}
        }
        window.addEventListener('pagehide', remember);
        window.addEventListener('beforeunload', remember);
        setInterval(remember, 1000);

        // Autoplay is only permitted while muted; this makes that explicit.
        video.muted = true;
        function resume() {
            var attempt = video.play();
            if (attempt && attempt.catch) attempt.catch(function () {});
        }
        resume();

        // Pressing Back restores the page from the browser's back/forward
        // cache with the video paused and this script never re-run, which is
        // what left it frozen on a single frame. Nudge it again on every one
        // of the events that can hand the page back to the viewer.
        window.addEventListener('pageshow', resume);
        video.addEventListener('seeked', resume);
        video.addEventListener('stalled', resume);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) resume();
        });
    })();
</script>
<?php } ?>
