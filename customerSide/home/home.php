<?php include_once('../components/header.php')?>
<!-- Hero Section -->
<section id="hero">
    <div class="hero container">
        <?php $markVariant = 'hero'; include '../components/brandMark.php'; ?>
    </div>
</section>
<!-- End Hero Section -->

<!-- The full menu now lives on its own page: customerSide/menu/menu.php -->

<!-- Contact Section -->
<section id="contact">
  <div class="contact container">
    <div class="contact-inner">
      <h1 class="section-title">Contact</h1>
      <div class="contact-items">
        <div class="contact-item">
          <span class="contact-label">Phone</span>
          <p class="contact-value">7777777777</p>
        </div>
        <div class="contact-item">
          <span class="contact-label">Email</span>
          <p class="contact-value">noreplyaryan.ayush33@gmail.com</p>
        </div>
        <div class="contact-item">
          <span class="contact-label">Address</span>
          <p class="contact-value">Area 51<br>
            Vellore<br>
            Tamil Nadu</p>
        </div>
      </div>
      <p class="contact-hours">Open seven days &middot; 11:00 AM &ndash; 10:00 PM</p>
    </div>
  </div>
</section>
<!-- End Contact Section -->

<!-- About now has its own page: customerSide/about/about.php -->

<?php
include_once('../components/footer.php');
?>
