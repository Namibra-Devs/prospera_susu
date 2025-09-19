
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

        // check user iddleness
        function is_idle() {
            var type = 'idle';

            $.ajax ({
                method : "POST",
                url : "<?= PROOT; ?>auth/idle.checker.php",
                data : { type : type},
                success : function (data) {
                    console.log(data);
                    if (data != '') {
                        window.location.href = "<?= PROOT; ?>auth/sign-in"
                    }
                }
            })
        }
        // setInterval(updateTime, 1000);

        setInterval(() => {
            // is_idle()
        }, 300000); // referesh after every 30sec

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
            $('.nav-dashboards').addClass('active');
            $('.nav-dashboards').attr('aria-expanded', true);
            $('#dashboards').addClass('show');
        }
        
        if (window.location.href.indexOf("live") > -1) {
            $('.nav-dashboards').addClass('active');
            $('.nav-dashboards').attr('aria-expanded', true);
            $('#dashboards').addClass('show');
        }

        if ((window.location.href.indexOf("collectors") > -1) || (window.location.href.indexOf("collector-new") > -1)) {
            $('.nav-collectors').addClass('active');
            $('.nav-collectors').attr('aria-expanded', true);
            $('#collectors').addClass('show');
        }

        if (window.location.href.indexOf("customers") > -1 || (window.location.href.indexOf("customer-new") > -1)) {
            $('.nav-customers').addClass('active');
            $('.nav-customers').attr('aria-expanded', true);
            $('#customers').addClass('show');
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
