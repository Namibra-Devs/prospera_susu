
    </main>
    <?= $flash_message; ?>

    <!-- TOAST FOR LIVE MESSAGES -->
    <div aria-live="polite" aria-atomic="true" class="d-flex justify-content-center align-items-center w-100">
        <div id="live-toast" class="toast fade hide position-fixed rounded" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: #6e46cc; right: 6px; bottom: 0; z-index: 99999;">
            <div class="toast-header small p-1 border-bottom">
                <img src="<?= PROOT; ?>assets/media/logo/logo.png" style="width: 35px; height: 35px;" class="rounded me-2" alt="J-Spence Logo">
                <strong class="me-auto small">Susu</strong>
                <small>notification . just now</small>
                <button type="button" class="btn-close small" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body p-1 small"></div>
        </div>
    </div>


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

        // customers
        if (window.location.href.indexOf("customers") > -1) {
            $('.nav-customers').addClass('active');
            $('.sub-nav-customers').addClass('active');
            $('.nav-customers').attr('aria-expanded', true);
            $('#customers').addClass('show');
        }
        if (window.location.href.indexOf("customer-new") > -1) {
            $('.nav-customers').addClass('active');
            $('.sub-nav-new-customers').addClass('active');
            $('.nav-customers').attr('aria-expanded', true);
            $('#customers').addClass('show');
        }

        // collections
        if (window.location.href.indexOf("collections") > -1) {
            $('.nav-collections').addClass('active');
            $('.sub-nav-collections').addClass('active');
            $('.nav-collections').attr('aria-expanded', true);
            $('#collections').addClass('show');
        }
        if (window.location.href.indexOf("collection-archive") > -1) {
            $('.nav-collections').addClass('active');
            $('.sub-nav-archived-collections').addClass('active');
            $('.nav-collections').attr('aria-expanded', true);
            $('#collections').addClass('show');
        }
        
        // admins
        if (window.location.href.indexOf("admins") > -1 || (window.location.href.indexOf("admin-new") > -1)) {
            $('.nav-admins').addClass('active');
            $('.nav-admins').attr('aria-expanded', true);
            $('#admins').addClass('show');
        }
        
        // transactions
        if (window.location.href.indexOf("transactions") > -1 || (window.location.href.indexOf("transactions-approve") > -1)) {
            $('.nav-transactions').addClass('active');
            $('.sub-nav-transactions').addClass('active');
            $('.nav-transactions').attr('aria-expanded', true);
            $('#transactions').addClass('show');
        }
        if (window.location.href.indexOf("transactions-approved") > -1) {
            $('.nav-transactions').addClass('active');
            $('.sub-nav-approved-transactions').addClass('active');
            $('.nav-transactions').attr('aria-expanded', true);
            $('#transactions').addClass('show');
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

    <!-- UPLOAD COLLECT FILE SCRIPT  -->
    <script>
        Dropzone.autoDiscover = false;

        const myDropzone = new Dropzone("#upload-collection-form", {
            url: "controller/upload.collection.file.php",
            paramName: "collection_file", // file name
            dictDefaultMessage: "Drag and drop file here or click to upload", // default message
            dictFallbackMessage: "Your browser does not support drag and drop file uploads.",
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 6, // MB
            acceptedFiles: "image/png,image/jpeg",
            addRemoveLinks: true
        });

        document.getElementById("uploadButton").addEventListener("click", function () {
            // validate if file is selected
            if (myDropzone.getAcceptedFiles().length === 0) {
                $('.toast-body').html('Please select a file to upload.');
                $('.toast').toast('show');
                $('.toast').removeClass('bg-success').addClass('bg-danger');

                return false;
            }

            // validate if upload date is selected
            var uploadDate = document.getElementById("upload_date").value;
            if (uploadDate === '') {
                $('.toast-body').html('Please select a date to upload.');
                $('.toast').toast('show');
                $('.toast').removeClass('bg-success').addClass('bg-danger');

                return false;
            }

            // validate if total collected is entered
            var totalCollected = document.getElementById("totalcollected").value;
            if (totalCollected === '' || isNaN(totalCollected) || Number(totalCollected) < 0) {
                $('.toast-body').html('Please enter a valid total amount collected.');
                $('.toast').toast('show');
                $('.toast').removeClass('bg-success').addClass('bg-danger');

                return false;
            } 

            // process the queue
            myDropzone.processQueue();

            // show loading on button
            $('#uploadButton').attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Uploading ...</span>');
            // disable close button
            $('#closeUploadModal').attr('disabled', true);
        
            // on error
            myDropzone.on("error", function(file, response) {
                // show error message
                $('.toast-body').html(response);
                $('.toast').toast('show');
                $('.toast').removeClass('bg-success').addClass('bg-danger'); // change toast color to red

                // enable button
                $('#uploadButton').attr('disabled', false).html('Upload');
                // enable close button
                $('#closeUploadModal').attr('disabled', false);

                // remove file
                myDropzone.removeAllFiles(true);
            });
            // check if there are any error messages from server
            myDropzone.on("success", function(file, response) {
                var data = JSON.parse(response);
                if (data.status === 'error') {
                    $('.toast-body').html(data.message);
                    $('.toast').toast('show');
                    $('.toast').removeClass('bg-success').addClass('bg-danger'); // change toast color to red
                    // remove file
                    myDropzone.removeAllFiles(true);

                    // enable button
                    $('#uploadButton').attr('disabled', false).html('Upload');
                    // enable close button
                    $('#closeUploadModal').attr('disabled', false);
                } else {
                    // success message will be shown on complete event
                }
            });
            // on complete
            myDropzone.on("complete", function(file) {
                var data = JSON.parse(file.xhr.response);
                if (data.status === 'success') {
                    $('.toast-body').html(data.message);
                    $('.toast').toast('show');
                    // reset form
                    $('#upload-collection-form')[0].reset();
                    // close modal after short delay
                    setTimeout(function() {
                        $('#todayUploadModal').modal('hide');
                    }, 2000);
                    // reload page after short delay
                    setTimeout(function() {
                        location.reload();
                    }, 2500);
                }
                // enable button
                $('#uploadButton').attr('disabled', false).html('Upload');
                // enable close button
                $('#closeUploadModal').attr('disabled', false);
                // remove file
                myDropzone.removeAllFiles(true);
            });

        });

        // reset form if upload modal is closed
        $('#todayUploadModal').on('hidden.bs.modal', function () {
            // reset form
            $('#upload-collection-form')[0].reset();
            myDropzone.removeAllFiles(true);
            // enable button
            $('#uploadButton').attr('disabled', false).html('Upload');
            // enable close button
            $('#closeUploadModal').attr('disabled', false);
        });
    </script>
</body>
</html>
