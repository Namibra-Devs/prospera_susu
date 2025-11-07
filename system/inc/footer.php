
    </main>
    <?= $flash_message; ?>

    <!-- TOAST FOR LIVE MESSAGES -->
    <div aria-live="polite" aria-atomic="true" class="d-flex justify-content-center align-items-center w-100">
        <div id="live-toast" class="toast fade hide position-fixed rounded" role="alert" aria-live="assertive" aria-atomic="true" style="right: 6px; bottom: 0; z-index: 99999;">
            <div class="toast-header small p-1 border-bottom">
                <img src="<?= PROOT; ?>assets/media/logo/logo.png" style="width: 35px; height: 35px;" class="rounded me-2" alt="Logo">
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

        // activate left nav link upon url //

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

        // collectors
        if (window.location.href.indexOf("collectors") > -1) {
            $('.nav-collectors').addClass('active');
            $('.sub-nav-collectors').addClass('active');
            $('.nav-collectors').attr('aria-expanded', true);
            $('#collectors').addClass('show');
        }
        if (window.location.href.indexOf("archived-collectors") > -1) {
            $('.nav-collectors').addClass('active');
            $('.sub-nav-collectors').removeClass('active');
            $('.sub-nav-archived-collectors').addClass('active');
            $('.nav-collectors').attr('aria-expanded', true);
            $('#collectors').addClass('show');
        }
        if ((window.location.href.indexOf("collector-new") > -1)) {
            $('.nav-collectors').addClass('active');
            $('.sub-nav-new-collectors').addClass('active');
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
        if (window.location.href.indexOf("archived-customers") > -1) {
            $('.nav-customers').addClass('active');
            $('.sub-nav-archived-customers').addClass('active');
            $('.sub-nav-customers').removeClass('active');
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

        // logs
        if (window.location.href.indexOf("logs") > -1) {
            $('.nav-logs').addClass('active');
            $('.sub-nav-logs').addClass('active');
            $('.nav-logs').attr('aria-expanded', true);
            $('#logs').addClass('show');
        }
        // if (window.location.href.indexOf("collection-archive") > -1) {
        //     $('.nav-collections').addClass('active');
        //     $('.sub-nav-archived-collections').addClass('active');
        //     $('.nav-collections').attr('aria-expanded', true);
        //     $('#collections').addClass('show');
        // }
        
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

        // admins
        if (window.location.href.indexOf("admins") > -1) {
            $('.nav-admins').addClass('active');
            $('.sub-nav-admins').addClass('active');
            $('.nav-admins').attr('aria-expanded', true);
            $('#admins').addClass('show');
        }
        if (window.location.href.indexOf("archived-admins") > -1) {
            $('.nav-admins').addClass('active');
            $('.sub-nav-admins').removeClass('active');
            $('.sub-nav-archived-admins').addClass('active');
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

    <script>


        // get customer deafult amount on customer select
        $('#select_customer').on('change', function() {
            var selectedValue = $(this).val();
            var parts = selectedValue.split(',');
            if (parts.length === 2) {
                var customerName = parts[0];
                var accountNumber = parts[1];

                // Make an AJAX request to fetch the default amount
                $.ajax({
                    url: '<?= PROOT; ?>app/controller/get_customer_default_amount.php',
                    type: 'GET',
                    data: { customer_name: customerName, account_number: accountNumber },
                    success: function(response) {
                        // Assuming the response is a JSON object with a 'default_amount' property
                        var data = JSON.parse(response);
                        if (data.customer_default_daily_amount) {
                            $('#default_amount').val(data.customer_default_daily_amount);
                            $('#label-default-amount').html('(Default amount: ' + data.customer_default_daily_amount + ')');
                        } else {
                            $('#default_amount').val('');
                            $('#label-default-amount').html();
                        }
                    },
                    error: function() {
                        console.error('Error fetching default amount');
                        $('#default_amount').val('');
                        $('#label-default-amount').html();
                    }
                });
            } else {
                $('#default_amount').val('');
                $('#label-default-amount').html();
            }
        });

        // limit note characters to 500
        $('#note').on('input', function() {
            var maxLength = 500;
            var currentLength = $(this).val().length;
            if (currentLength > maxLength) {
                $(this).val($(this).val().substring(0, maxLength));
            }
        });

        // check if check box of advance payment is checked or not
        $('#is_advance_payment').on('change', function() {
            if ($(this).is(':checked')) {
                // if checked, show advance payment select
                $('#advance_payment_div').show();
                $('#advance_payment').prop('disabled', false);
            } else {
                // if not checked, hide advance payment select
                $('#advance_payment_div').hide();
                $('#advance_payment').prop('disabled', true);
            }
        });

        // next step button in add transaction modal
        $('#next_step').on('click', function() {
            // validate form
            var customer = $('#select_customer').val();
            var amount = $('#default_amount').val();
            var date = $('#today_date').val();
            var payment_mode = $('#payment_mode').val();

            if (customer === '' || amount === '' || date === '' || payment_mode === '') {
                $('.toast-body').html('Please fill all required fields.');
                $('.toast').toast('show');
                $('.toast').removeClass('bg-success').addClass('bg-danger');

                return false;
            } else {
                // hide first step
                $('#first_step').hide();
                // show preview step
                $('#preview_step').show();

                // set preview values
                var customerText = $('#select_customer option:selected').text();
                $('#preview_customer').html(customerText);

                var amountText = $('#default_amount').val();
                $('#preview_amount').html(amountText);

                var dateText = $('#today_date').val();
                $('#preview_date').html(dateText);

                var paymentModeText = $('#payment_mode option:selected').text();
                $('#preview_payment_mode').html(paymentModeText);

                var noteText = $('#note').val();
                if (noteText === '') {
                    noteText = 'N/A';
                }
                $('#preview_note').html(noteText);

                var isAdvancePaymentChecked = $('#is_advance_payment').is(':checked');
                if (isAdvancePaymentChecked) {
                    var advancePaymentDays = $('#advance_payment').val();
                    $('#preview_advance_payment').html('Yes, for ' + advancePaymentDays + ' days');
                } else {
                    $('#preview_advance_payment').html('No');
                }

                // change modal title
                $('#transactionModalLabel').html('Preview transaction');
                // change next step button to back button
                $('#next_step').hide();
                $('#back_step').show();
                // show submit button
                $('#submit-transaction').show();
            }
        });

        // back to first step button
        $('#back_step').on('click', function() {
            // hide preview step
            $('#preview_step').hide();
            // show first step
            $('#first_step').show();

            // change modal title
            $('#transactionModalLabel').html('Add new transaction');
            // change back button to next step button
            $('#back_step').hide();
            $('#next_step').show();
            // hide submit button
            $('#submit-transaction').hide();
        });

        // create an ajax request to submit the add transaction form
        var $this = $('#add-transaction-form');
        var $state = $('.toast-body');
        $('#add-transaction-form').on('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission
            var formData = $(this).serialize(); // Serialize the form data
            $.ajax({
                type: 'POST',
                url: '<?= PROOT; ?>app/controller/transaction.add.php',
                data: formData,
                beforeSend: function() {
                    $this.find('#submit-transaction').attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Processing ...</span>');
                },
                success: function(response) {
                    // Handle the response from the server
                    // Assuming the response is a JSON object with 'status' and 'message' properties
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        $state.html(data.message);
                        $('.toast').toast('show');

                        // Optionally, you can reset the form here
                        $('#add-transaction-form')[0].reset();
                        // Close the modal after a short delay
                        $('#transactionModal').modal('hide');

                        setTimeout(function() {
                            location.reload(); // Reload the page to reflect changes
                        }, 2000);
                    } else {
                        $state.html(data.message);
                        $('.toast').toast('show');
                        $('.toast').removeClass('bg-success').addClass('bg-danger');

                        return false;
                    }
                },
                error: function() {
                    $state.html('An error occurred. Please try again.');
                    $('.toast').toast('show');
                    $('.toast').removeClass('bg-success').addClass('bg-danger');

                    return false;
                },
                complete: function() {
                    $this.find('#submit-transaction').attr('disabled', false).html('Add transaction');
                }
            });
        });

        // reset form  if add transaction modal is closed
        $('#transactionModal').on('hidden.bs.modal', function () {
            // reset form
            $('#add-transaction-form')[0].reset();
            // show first step
            $('#first_step').show();
            // hide preview step
            $('#preview_step').hide();
            // change modal title
            $('#transactionModalLabel').html('Add new transaction');
            // change back button to next step button
            $('#back_step').hide();
            $('#next_step').show();
            // hide submit button
            $('#submit-transaction').hide();
            // hide advance payment select
            $('#advance_payment_div').hide();
            $('#advance_payment').prop('disabled', true);
        });







        // WITHDRWAL
        // get customer balance if customer is selectd
        $('#withdrawal_select_customer').on('change', function(e) {
            e.preventDefault()
            // alert('12');
            var selectedValue = $(this).val();
            var parts = selectedValue.split(',');
            if (parts.length === 2) {
               //  var customerName = parts[0];
                var accountNumber = parts[1];

                // Make an AJAX request to fetch the default amount
                $.ajax({
                    url: '<?= PROOT; ?>app/controller/get_customer_balance.php',
                    type: 'GET',
                    data: { account_number: accountNumber },
                    success: function(response) {
                        // Assuming the response is a JSON object with a 'balance_amount' property
                        var data = JSON.parse(response);
                        if (data.customer_balance_amount) {
                            $('#customer_balance').val(data.customer_balance_amount);
                        } else {
                            $('#customer_balance').val('');
                        }
                    },
                    error: function() {
                        console.error('Error fetching default amount');
                        $('#customer_balance').val('');
                    }
                });
            } else {
                $('#customer_balance').val('');
            }
        });

        // limit note characters to 500
        $('#withdrawal_note').on('input', function() {
            var maxLength = 500;
            var currentLength = $(this).val().length;
            if (currentLength > maxLength) {
                $(this).val($(this).val().substring(0, maxLength));
            }
        });

        // next step button in add transaction modal
        $('#w_next_step').on('click', function() {
            // validate form
            var customer = $('#withdrawal_select_customer').val();
            var balance = $('#customer_balance').val();
            var amount = $('#amount-to-withdraw').val();
            var date = $('#withdrawal_today_date').val();
            var payment_mode = $('#withdrawal_payment_mode').val();

            // check if withdrawal amount is greater than balance
            if (+balance <= 0) {
                $('.toast-body').html('Balance is insufficient.');
                $('.toast').toast('show');
                $('.toast').removeClass('bg-success').addClass('bg-danger');

                return false;
            }
            if (+amount > +balance) {
                $('.toast-body').html('Withdrawal amount cannot be greater than balance.');
                $('.toast').toast('show');
                $('.toast').removeClass('bg-success').addClass('bg-danger');

                return false;
            } else {
                if (customer === '' || amount === '' || date === '' || payment_mode === '') {
                    $('.toast-body').html('Please fill all required fields.');
                    $('.toast').toast('show');
                    $('.toast').removeClass('bg-success').addClass('bg-danger');

                    return false;
                } else {
                    // hide first step
                    $('#w_first_step').hide();
                    // show preview step
                    $('#w_preview_step').show();

                    // set preview values
                    var customerText = $('#withdrawal_select_customer option:selected').text();
                    $('#w_preview_customer').html(customerText);

                    var amountText = $('#amount-to-withdraw').val();
                    $('#w_preview_amount').html(amountText);

                    var balanceText = $('#customer_balance').val();
                    $('#w_balance').html(balanceText);
                    
                    var dateText = $('#withdrawal_today_date').val();
                    $('#w_preview_date').html(dateText);

                    var paymentModeText = $('#withdrawal_payment_mode option:selected').text();
                    $('#w_preview_payment_mode').html(paymentModeText);

                    var noteText = $('#withdrawal_note').val();
                    if (noteText === '') {
                        noteText = 'N/A';
                    }
                    $('#w_preview_note').html(noteText);

                    // change modal title
                    $('#withdrawalModalLabel').html('Preview withdrawal transaction');
                    // change next step button to back button
                    $('#w_next_step').hide();
                    $('#w_back_step').show();
                    // show submit button
                    $('#w-submit-transaction').show();
                }
            }
        });

        // back to first step button
        $('#w_back_step').on('click', function() {
            // hide preview step
            $('#w_preview_step').hide();
            // show first step
            $('#w_first_step').show();

            // change modal title
            $('#withdrawalModalLabel').html('Make new withdrawal');
            // change back button to next step button
            $('#back_step').hide();
            $('#w_next_step').show();
            // hide submit button
            $('#w-submit-transaction').hide();
        });

        // create an ajax request to submit the add transaction form
        var $this = $('#add-withdrawal-form');
        var $state = $('.toast-body');
        $('#add-withdrawal-form').on('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission
            var formData = $(this).serialize(); // Serialize the form data
            $.ajax({
                type: 'POST',
                url: '<?= PROOT; ?>app/controller/transaction.withdraw.php',
                data: formData,
                beforeSend: function() {
                    $this.find('#w-submit-transaction').attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Processing ...</span>');
                },
                success: function(response) {
                    // Handle the response from the server
                    // Assuming the response is a JSON object with 'status' and 'message' properties
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        $state.html(data.message);
                        $('.toast').toast('show');

                        // Optionally, you can reset the form here
                        $('#add-withdrawal-form')[0].reset();
                        // Close the modal after a short delay
                        $('#withdrawalModal').modal('hide');

                        setTimeout(function() {
                            location.reload(); // Reload the page to reflect changes
                        }, 2000);
                    } else {
                        $state.html(data.message);
                        $('.toast').toast('show');
                        $('.toast').removeClass('bg-success').addClass('bg-danger');

                        return false;
                    }
                },
                error: function() {
                    $state.html('An error occurred. Please try again.');
                    $('.toast').toast('show');
                    $('.toast').removeClass('bg-success').addClass('bg-danger');

                    return false;
                },
                complete: function() {
                    $this.find('#w-submit-transaction').attr('disabled', false).html('Withdraw now');
                }
            });
        });

        // reset form  if add transaction modal is closed
        $('#withdrawalModal').on('hidden.bs.modal', function () {
            // reset form
            $('#add-withdrawal-form')[0].reset();
            // show first step
            $('#w_first_step').show();
            // hide preview step
            $('#w_preview_step').hide();
            // change modal title
            $('#withdrawalModalLabel').html('Make new withdrawal');
            // change back button to next step button
            $('#w_back_step').hide();
            $('#w_next_step').show();
            
            $('#w-submit-transaction').attr('disabled', false).html('Withdraw now');
            $('#w-submit-transaction').hide(); // hide submit button
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

    <script>
        $(document).ready(function(){
            $('#transaction_file').on('change', function(e) {
                let file = e.target.files[0];
                if (!file) return;

                let formData = new FormData();
                formData.append('file', file);

                $('.progress').show();
                $('#uploadProgress').css('width', '0%').text('0%');
                $('#previewArea').html('<p class="text-muted">Processing file... Please wait.</p>');
                $('#uploadTransactionButton').hide();

                $.ajax({
                    xhr: function() {
                        let xhr = new XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                let percent = Math.round((e.loaded / e.total) * 100);
                                $('#uploadProgress').css('width', percent + '%').text(percent + '%');
                            }
                        });
                        return xhr;
                    },
                    url: '<?= PROOT;  ?>app/controller/transaction.preview.upload.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('.progress').hide();
                        $('#previewArea').html(response);
                        $('#uploadTransactionButton').show();
                    }
                });
            });

            $('#uploadTransactionButton').click(function() {
                $.post('<?= PROOT; ?>app/controller/transaction.upload.insert.php', function(response) {
                    $('#previewArea').html(response);
                    $('#uploadTransactionButton').hide();
                    $('#transaction_file').val('');
                });
            });

            // reset form if upload modal is closed
            $('#transactionUploadModal').on('hidden.bs.modal', function () {
                // reset form
                $('#transaction_file').val('');
                $('#previewArea').html('');
                $('#uploadTransactionButton').hide();
            });
        });
    </script>
</body>
</html>
