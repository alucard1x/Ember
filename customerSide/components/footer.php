  <!-- Footer -->
  <section id="footer">
    <div class="footer container">
        <div class="brand">
          <h1 class="footer-signature"><span class="footer-signature-text">aryan made it</span><span class="footer-signature-pixels" aria-hidden="true"></span></h1>
      </div>
      <div class="social-icon">
        <div class="social-item">
          <a href="https://www.facebook.com"><img src="https://img.icons8.com/color/48/facebook.png" alt="facebook"/></a>
        </div>
        <div class="social-item">
          <a href="https://www.instagram.com"><img src="https://img.icons8.com/color/48/instagram-new.png" alt="instagram-new"/></a>
        </div>
          <div class="social-item">
          <a href="https://www.pinterest.com"><img src="https://img.icons8.com/color/48/pinterest.png" alt="pinterest-new"/></a>
        </div>
          <div class="social-item">
          <a href="https://www.tiktok.com"><img src="https://img.icons8.com/color/48/tiktok.png" alt="tiktok-new"/></a>
        </div>
          <div class="social-item">
          <a href="https://www.youtube.com"><img src="https://img.icons8.com/color/48/youtube-play.png" alt="youtube-new"/></a>
        </div>
          
        
      </div>
      <p>© 26MCA0009</p>
      <h6 class="footer-project-tag">dbms project</h6>

      
    </div>
  </section>
  <!-- End Footer -->
  <script src="../js/app.js"></script>
   <style type="text/css">
       
       .navbar-container {
  width: 100%;
  padding: 0;
  margin: 0;
}
      .msg {
        font-family: 'Montserrat', sans-serif;
        margin-top: 25px;
        display: none;
        color: #f3efec;
        width: 100%;
      }
      .yellow {
        background: transparent;
      }
      .green {
        background: transparent;
      }
      .red {
        background: transparent;
      }

      /* Styling the select button */
   .menu-category {
  font-size: 20px;
  padding: 12px 18px;
  border: 1px solid rgba(255, 255, 255, 0.35);
  outline: none;
  cursor: pointer;
  letter-spacing: 0.15rem;
  text-transform: uppercase;
  transition: border-color 0.3s ease, background-color 0.3s ease, color 0.3s ease;
  color: #f3efec;
  background-color: rgba(18, 14, 13, 0.72);
  border-radius: 0; /* Sharp corners, in keeping with the rest of the page */
}

/* Style the option text in the select dropdown */
.menu-category option {
  font-size: 20px;
}

/* Hover effect */
.menu-category:hover {
  background-color: var(--brand-accent);
  border-color: var(--brand-accent);
  color: white;
}

      /* Menu items lay themselves out; see .menu-items in style.css */

.user-profile {
    display: flex;
    align-items: center;
    color: white;
    margin-right: 20px;
}

.user-profile a {
    margin-left: 10px;
    color: white;
    text-decoration: none;
}

/* Style for the profile link */
.profile-link {
  border: 1px solid #fff; /* Smaller border style and color */
  padding: 3px 8px; /* Smaller padding inside the border */
  border-radius: 3px; /* Rounded corners for the border */
  text-decoration: none; /* Remove the default underline */
  color: #fff; /* Text color */
  margin-left: auto; /* Automatically push the link to the right */
  margin-right: 10px; /* Add a small right margin for spacing */
}



.navbar {
  overflow: hidden;
  
}

.navbar a {
  float: left;
  font-size: 16px;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

.dropdown {
  float: right;
  overflow: hidden;
}

.dropdown .dropbtn {
  font-size: 17px;  
  border: none;
  outline: none;
  color: white;
  padding: 13.9px 16px;
  background-color: inherit;
  font-family: inherit;
  margin: 0;
  margin-top: 6px;
}

 .dropdown:hover .dropbtn {
  color: var(--brand-accent);
}

.dropdown-content {
  display: none;
  position: absolute;
    background-color: rgba(12, 9, 8, 0.92);
  min-width: 180px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
  color: black;
}

.dropdown-content a {
  float: none;
  color: white;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
  text-align: left;
}

.dropdown-content a:hover {
  background-color: var(--brand-accent);
}
/* Style for the dropdown content text */
.dropdown-content a {
  float: none;
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
  text-align: left;
}

/* Hover effect for dropdown content text */
.dropdown-content a:hover {
  background-color: var(--brand-accent);
  color: #ffffff;
}

.dropdown:hover .dropdown-content {
  display: block;
}

 .tooltip {
    display: none;
    position: absolute;
    background-color: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 5px;
    border-radius: 3px;
    font-size: 0.9em;
    margin-top: 50px; /* Add margin to move the tooltip below the element */
    left: 0; /* Set left to 0 to align with the element */
    width: 100%; /* Make the tooltip span the width of the element */
    text-align: center; /* Center the text within the tooltip */
  }

.footer-project-tag {
  margin: 6px 0 0;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.72rem;
  font-weight: 400;
  letter-spacing: 0.22rem;
  text-transform: uppercase;
  color: rgba(243, 239, 236, 0.65);
  text-align: center;
}

/* Footer signature -- Alex Brush throughout; the reveal is a grid of blocks
   dissolving over the top, so the typeface never changes mid-animation. */
.brand h1.footer-signature {
  position: relative;
  display: inline-block;
  font-family: 'Alex Brush', 'Snell Roundhand', 'Brush Script MT', cursive;
  font-size: 4.2rem;
  font-weight: 400;
  line-height: 1.25;
  letter-spacing: 0.04rem;
  text-transform: lowercase;
  color: var(--brand-accent);
  padding: 0 6px;
}
.footer-signature-text {
  position: relative;
  opacity: 1;
  transition: opacity 0.35s ease;
}
.footer-signature.is-revealing .footer-signature-text {
  opacity: 0;
}
.footer-signature-pixels {
  position: absolute;
  inset: 0;
  display: grid;
  pointer-events: none;
}
.footer-signature-pixels i {
  background-color: var(--brand-accent);
  opacity: 0;
  transform: scale(1);
  transition: opacity 0.28s ease, transform 0.28s ease;
}
.footer-signature.is-revealing .footer-signature-pixels i {
  opacity: 1;
  transform: scale(1);
}
@media only screen and (max-width: 767px) {
  .brand h1.footer-signature { font-size: 3.2rem; }
}
@media (prefers-reduced-motion: reduce) {
  .footer-signature-text { opacity: 1 !important; }
  .footer-signature-pixels { display: none; }
}
    </style>

    <script>
    (function () {
        var sig = document.querySelector('.footer-signature');
        if (!sig) return;

        var pixels = sig.querySelector('.footer-signature-pixels');
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!pixels || reduce) return;

        var COLS = 22, ROWS = 5;
        pixels.style.gridTemplateColumns = 'repeat(' + COLS + ', 1fr)';
        pixels.style.gridTemplateRows = 'repeat(' + ROWS + ', 1fr)';

        var tiles = [];
        for (var n = 0; n < COLS * ROWS; n++) {
            var tile = document.createElement('i');
            pixels.appendChild(tile);
            tiles.push(tile);
        }

        var timers = [];
        function clearTimers() {
            timers.forEach(clearTimeout);
            timers = [];
        }

        function cover() {
            clearTimers();
            sig.classList.add('is-revealing');
            tiles.forEach(function (t) {
                t.style.transitionDelay = '0s';
                t.style.opacity = '1';
                t.style.transform = 'scale(1)';
            });
        }

        function reveal() {
            clearTimers();
            // Each block clears after its own delay, so the word emerges in a
            // ragged scatter rather than a clean wipe.
            tiles.forEach(function (t) {
                t.style.transitionDelay = (Math.random() * 0.55).toFixed(3) + 's';
                t.style.opacity = '0';
                t.style.transform = 'scale(0.4)';
            });
            timers.push(setTimeout(function () {
                sig.classList.remove('is-revealing');
            }, 120));
        }

        cover();

        if (!('IntersectionObserver' in window)) { reveal(); return; }

        // Replays every time the footer is scrolled into view.
        new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) reveal();
                else cover();
            });
        }, { threshold: 0.6 }).observe(sig);
    })();
    </script>

    <!-- jQuery, Popper and Bootstrap are already loaded once in components/header.php -->
    <script type="text/javascript">
    $(document).ready(function(){
        $("select").change(function(){
            $(this).find("option:selected").each(function(){
                var val = $(this).attr("value");
                if(val){
                    $(".msg").not("." + val).hide();
                    $("." + val).show();
                } else{
                    $(".msg").hide();
                }
            });
        }).change();
    });

  $(document).ready(function(){
    // Function to filter menu items based on search input
    function filterMenuItems(searchTerm) {
      $(".item-name").each(function() {
        var itemName = $(this).text().toLowerCase();
        if (itemName.includes(searchTerm)) {
          $(this).closest(".msg").show();
        } else {
          $(this).closest(".msg").hide();
        }
      });
    }
    
    // Search button click event
    $("#search-button").click(function() {
      var searchTerm = $("#search-input").val().toLowerCase();
      filterMenuItems(searchTerm);
    });
    
    // Search input keyup event
    $("#search-input").keyup(function() {
      var searchTerm = $(this).val().toLowerCase();
      filterMenuItems(searchTerm);
    });
  });

    </script>
<script>
  $(document).ready(function () {
    $('.logout-link').hover(function () {
      var $tooltip = $(this).find('.tooltip');
      var elementHeight = $(this).height();
      $tooltip.css('top', elementHeight + 10 + 'px'); // Position the tooltip below the element
      $tooltip.css('display', 'block');
    }, function () {
      $(this).find('.tooltip').css('display', 'none');
    });
  });
</script>
</body>
</html>

