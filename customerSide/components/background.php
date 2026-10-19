<?php
// background.php - the restaurant video playing behind every page
// before including this file set $bgAssets to the path of the customerSide/image folder

if (!isset($bgAssets)) {
    $bgAssets = '../image';
}

// if the video file is missing we show a photo instead
$bgHasVideo = file_exists(__DIR__ . '/../image/background.mp4');

// path to the css folder (works from customerSide and from adminSide/StaffLogin)
$bgCssDir = rtrim(dirname($bgAssets), '/') . '/css';
?>
<script>
    // tells the CSS that JavaScript is on (the scroll animations need this)
    document.documentElement.classList.add('js');
</script>

<div class="site-bg" aria-hidden="true">
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

<!-- dark layer over the video so text is easy to read (lighter on the homepage hero) -->
<div class="e-veil" aria-hidden="true"></div>

<?php include __DIR__ . '/pageTransition.php'; ?>

<style>
    /* video box: fixed behind everything, does not scroll */
    .site-bg {
        position: fixed;
        inset: 0;
        z-index: -10;
        overflow: hidden;
        background-color: #14100f;
    }
    /* make the video (or photo) cover the whole screen */
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
    /* a little darker at the top and bottom where the header and footer are */
    .site-bg-scrim {
        position: absolute;
        inset: 0;
        background-color: rgba(12, 9, 8, 0.46);
        background-image: linear-gradient(
            to bottom,
            rgba(11, 9, 8, 0.34) 0%,
            rgba(11, 9, 8, 0) 26%,
            rgba(11, 9, 8, 0) 62%,
            rgba(11, 9, 8, 0.42) 100%
        );
    }

    /* the dark layer (dimmer) */
    .e-veil {
        position: fixed;
        inset: 0;
        z-index: -9;
        pointer-events: none;
        background-color: rgba(11, 9, 8, 0.86);
        opacity: 1;
        transition: opacity 1.1s cubic-bezier(0.16, 1, 0.3, 1);
    }
    /* homepage: no dimmer at the top, dimmer after you scroll past the hero */
    body.has-hero .e-veil { opacity: 0; }
    body.has-hero.is-past-hero .e-veil { opacity: 1; }

    @media (prefers-reduced-motion: reduce) {
        .e-veil { transition: none; }
    }

    /* page background is see-through so the video shows */
    html, body {
        background-color: transparent;
        background-image: none;
    }
</style>

<!-- phone styles, loaded last so they win -->
<link rel="stylesheet" href="<?= $bgCssDir ?>/mobile.css?v=<?= @filemtime(__DIR__ . '/../css/mobile.css') ?>">

<?php if ($bgHasVideo) { ?>
<script>
    var bgVideo = document.getElementById('site-bg-video');
    var savedTime = 0;   // the second the last page stopped at (read once, when the page opens)

    // start the video (browsers only allow autoplay when it is muted)
    function playBgVideo() {
        var attempt = bgVideo.play();
        if (attempt && attempt.catch) {
            attempt.catch(function () {});
        }
    }

    // save where the video is, so the next page continues from the same second
    function saveBgVideoTime() {
        try {
            sessionStorage.setItem('emberBgTime', bgVideo.currentTime);
        } catch (e) {}
    }

    // when the video is ready, jump to the saved time
    function jumpToSavedTime() {
        bgVideo.removeEventListener('canplay', jumpToSavedTime);
        if (savedTime < bgVideo.duration - 1) {
            bgVideo.currentTime = savedTime;
        }
    }

    if (bgVideo) {
        try {
            savedTime = parseFloat(sessionStorage.getItem('emberBgTime'));
            if (!isNaN(savedTime) && savedTime > 0) {
                bgVideo.addEventListener('canplay', jumpToSavedTime);
            }
        } catch (e) {}

        window.addEventListener('pagehide', saveBgVideoTime);
        window.addEventListener('beforeunload', saveBgVideoTime);
        setInterval(saveBgVideoTime, 1000);

        bgVideo.muted = true;
        playBgVideo();

        // start again when coming back with the Back button
        window.addEventListener('pageshow', playBgVideo);
        bgVideo.addEventListener('stalled', playBgVideo);

        // pause when the tab is hidden, play again when it is shown
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                bgVideo.pause();
            } else {
                playBgVideo();
            }
        });
    }
</script>
<?php } ?>
