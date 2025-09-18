
    </main>
    <?= $flash_message; ?>


    <!-- JAVASCRIPT -->
    <script src="<?= PROOT; ?>assets/js/jquery-3.7.1.min.js"></script>

    <!-- Vendor JS -->
    <script src="<?= PROOT; ?>assets/js/vendor.bundle.js"></script>
    
    <!-- Theme JS -->
    <script src="<?= PROOT; ?>assets/js/theme.bundle.js"></script>
    <script>
		// Fade out messages 
		$("#temporary").fadeOut(10000);

        // Get the current URL
        var currentUrl = window.location.href;

        // Get all the links in the sidebar
        var sidebarLinks = document.querySelectorAll('#sidebarAccount .nav-link');

        // Loop through the links and add the 'active' class to the one that matches the current URL
        sidebarLinks.forEach(function(link) {
            if (link.href === currentUrl) {
                link.classList.add('active');
            } 
        })





        

        // check if browser is online or offline
        if (!navigator.onLine) {
            alert('Your are offline, make sure you are connected internet!')
        }
        // var x = "Is the browser online? " + navigator.onLine
        // alert(x);

        // activate left nav link upon url

        if (window.location.href.indexOf("index") > -1) {
            $('.nav-dashboard').addClass('active');
            $('.nav-dashboard').attr('aria-expanded', true);
            $('#dashboard').addClass('show');
        }
        
        if (window.location.href.indexOf("analytics") > -1) {
            $('.nav-dashboard').addClass('active');
            $('.nav-dashboard').attr('aria-expanded', true);
            $('#dashboard').addClass('show');
        }

        if ((window.location.href.indexOf("trades") > -1) || (window.location.href.indexOf("end-trade") > -1)) {
            $('.nav-market').addClass('active');
            $('.nav-market').attr('aria-expanded', true);
            $('#market').addClass('show');
        }

        if (window.location.href.indexOf("expenditure") > -1) {
            $('.nav-expenditure').addClass('active');
            $('.nav-expenditure').attr('aria-expanded', true);
            $('#expenditure').addClass('show');
        }
        
        if (window.location.href.indexOf("pushes") > -1) {
            $('.nav-pushes').addClass('active');
            $('.nav-pushes').attr('aria-expanded', true);
            $('#pushes').addClass('show');
        }

        if (window.location.href.indexOf("admins") > -1) {
            $('.nav-admins').addClass('active');
            $('.nav-admins').attr('aria-expanded', true);
            $('#admins').addClass('show');
        }

        if ((window.location.href.indexOf("profile") > -1) || window.location.href.indexOf("settings") > -1) {
            $('.nav-account').addClass('active');
            $('.nav-account').attr('aria-expanded', true);
            $('#account').addClass('show');
        }

        if (window.location.href.indexOf("logs") > -1) {
            $('.nav-logs').addClass('active');
            $('.nav-logs').attr('aria-expanded', true);
            $('#logs').addClass('show');
        }

        var childpath = window.location.href;
        $('nav a.nav-child').each(function() {
            if (this.href === childpath) {
                $(this).addClass('active');
            }
        });
    </script>
</body>
</html>
