  </main>

  <!-- footer.php - bottom part of every customer page (footer, scripts) -->
  <footer class="e-footer">
    <div class="e-shell">
      <div class="e-footer__grid">

        <div class="e-footer__col">
          <a class="e-footer__mark" href="../home/home.php">Ember</a>
          <p>Modern Indian, cooked over coals.</p>
        </div>

        <div class="e-footer__col">
          <span class="e-eyebrow">Visit</span>
          <p>Area 51<br>Vellore<br>Tamil Nadu</p>
        </div>

        <div class="e-footer__col">
          <span class="e-eyebrow">Contact</span>
          <a href="tel:7777777777">7777777777</a>
          <a href="mailto:noreplyaryan.ayush33@gmail.com">Email us</a>
        </div>

        <div class="e-footer__col">
          <span class="e-eyebrow">Hours</span>
          <p>Open seven days<br>11:00 &ndash; 22:00</p>
        </div>

        <div class="e-footer__col">
          <span class="e-eyebrow">Navigate</span>
          <a href="../menu/menu.php">Menu</a>
          <a href="../about/about.php">Our Story</a>
          <a href="../CustomerReservation/reservePage.php">Reservations</a>
        </div>

      </div>

      <div class="e-footer__base">
        <!-- my signature -->
        <p class="footer-signature">
          <span class="footer-signature-text">aryan made it</span>
          <span class="footer-signature-pixels" aria-hidden="true"></span>
        </p>
        <p class="e-footer__legal">
          &copy; 26MCA0009<br>
          DBMS Project
        </p>
      </div>
    </div>
  </footer>

  <!-- smooth scrolling, the same as on the homepage (Lenis, free, MIT) -->
  <script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script>
  <script>
  (function () {
      if (!window.Lenis || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
      var lenis = new Lenis({ lerp: 0.1, wheelMultiplier: 0.9 });
      function frame(time) { lenis.raf(time); requestAnimationFrame(frame); }
      requestAnimationFrame(frame);
      // links to a part of the same page glide there
      document.addEventListener('click', function (e) {
          var link = e.target.closest('a[href^="#"]');
          if (!link || link.getAttribute('href').length < 2) { return; }
          var target = document.querySelector(link.getAttribute('href'));
          if (target) { e.preventDefault(); lenis.scrollTo(target, { offset: -80, duration: 1.4 }); }
      });
      // stop the page moving while the phone menu is open
      new MutationObserver(function () {
          if (document.body.classList.contains('e-menu-open')) { lenis.stop(); } else { lenis.start(); }
      }).observe(document.body, { attributes: true, attributeFilter: ['class'] });
  })();
  </script>

  <!-- main JavaScript file (?v= makes the browser load the newest version) -->
  <script src="../js/app.js?v=<?= @filemtime(__DIR__ . '/../js/app.js') ?>" defer></script>

  <script>
  // signature animation: small blocks cover the text and then disappear one by one
  var signature = document.querySelector('.footer-signature');
  var signaturePixels = signature.querySelector('.footer-signature-pixels');
  var signatureTiles = [];
  var signatureNoMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // cover the text with all the blocks
  function coverSignature() {
      signature.classList.add('is-revealing');
      for (var i = 0; i < signatureTiles.length; i++) {
          signatureTiles[i].style.transitionDelay = '0s';
          signatureTiles[i].style.opacity = '1';
          signatureTiles[i].style.transform = 'scale(1)';
      }
  }

  // remove the blocks, each one after a random small delay
  function revealSignature() {
      for (var i = 0; i < signatureTiles.length; i++) {
          signatureTiles[i].style.transitionDelay = (Math.random() * 0.55).toFixed(3) + 's';
          signatureTiles[i].style.opacity = '0';
          signatureTiles[i].style.transform = 'scale(0.4)';
      }
      setTimeout(function () {
          signature.classList.remove('is-revealing');
      }, 120);
  }

  if (!signatureNoMotion) {
      // make a grid of 22 x 5 blocks
      signaturePixels.style.gridTemplateColumns = 'repeat(22, 1fr)';
      signaturePixels.style.gridTemplateRows = 'repeat(5, 1fr)';
      for (var t = 0; t < 22 * 5; t++) {
          var tile = document.createElement('i');
          signaturePixels.appendChild(tile);
          signatureTiles.push(tile);
      }

      coverSignature();

      // play the animation every time the footer comes into view
      var signatureWatcher = new IntersectionObserver(function (entries) {
          if (entries[0].isIntersecting) {
              revealSignature();
          } else {
              coverSignature();
          }
      }, { threshold: 0.6 });
      signatureWatcher.observe(signature);
  }
  </script>
</body>
</html>
