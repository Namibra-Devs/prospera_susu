<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
	// if (!admin_is_logged_in()) {
	// 	admin_login_redirect();
	// }

    // Check if the admin or collector is logged in
    if (!admin_is_logged_in() && !collector_is_logged_in()) {
        redirect(PROOT . 'auth/sign-in');
    }

    $title = 'Transactions | ';
    $body_class = '';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');

    // function to get total amount of transac
    function getTotalTransactionAmount($type = 'Approved') {
        global $dbConnection;
        if (admin_is_logged_in()) {
            $stmt = $dbConnection->prepare("SELECT SUM(saving_amount) AS total_amount FROM savings WHERE saving_status = ?");
            $stmt->execute([$type]);
        } elseif (collector_is_logged_in()) {
            global $collector_id;
            $stmt = $dbConnection->prepare("SELECT SUM(saving_amount) AS total_amount FROM savings WHERE saving_collector_id = ? AND saving_status = ?");
            $stmt->execute([$collector_id, $type]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return money($row['total_amount'] ? $row['total_amount'] : 0);
    }

    // function to get total amount of withdrawals
    function getTotalWithdrawalAmount($type = 'Approved', $or = 'Paid') {
        global $dbConnection;
        if (admin_is_logged_in()) {
            $stmt = $dbConnection->prepare("SELECT SUM(withdrawal_amount_requested) AS total_amount FROM withdrawals WHERE (withdrawal_status = ? OR withdrawal_status = ?)");
            $stmt->execute([$type, $or]);
        } elseif (collector_is_logged_in()) {
            global $collector_id;
            $stmt = $dbConnection->prepare("SELECT SUM(withdrawal_amount_requested) AS total_amount FROM withdrawals WHERE withdrawal_approver_id = ? AND (withdrawal_status = ? OR withdrawal_status = ?)");
            $stmt->execute([$collector_id, $type, $or]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return money($row['total_amount'] ? $row['total_amount'] : 0);
    }

    // function to get total number of customers and to display for admin and collector
    function getTotalCustomers() {
        global $dbConnection;
        if (admin_is_logged_in()) {
            $stmt = $dbConnection->prepare("SELECT COUNT(*) AS total_customers FROM customers WHERE customer_status = ?");
            $stmt->execute(['active']);
        } elseif (collector_is_logged_in()) {
            global $collector_id;
            $stmt = $dbConnection->prepare("SELECT COUNT(*) AS total_customers FROM customers WHERE customer_added_by = 'collector' AND customer_collector_id = ? AND customer_status = 'active'");
            $stmt->execute([$collector_id]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_customers'] ? $row['total_customers'] : 0;
    }

?>

    <!-- Main -->
    <main class="main px-lg-6">
        <!-- Content -->
        <div class="container-lg">

            <!-- Page header -->
            <div class="row align-items-center mb-7">
                <div class="col-auto">
                    <!-- Avatar -->
                    <div class="avatar avatar-xl rounded text-primary">
                        <i class="fs-2" data-duoicon="calendar"></i>
                    </div>
                </div>
                <div class="col">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Other</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">Transactions</h1>
                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                    <!-- Action -->
                    <button class="btn btn-secondary w-100" type="button" data-bs-toggle="modal" data-bs-target="#transactionModal">
                        <span class="material-symbols-outlined me-1">add</span> New transaction
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="row mb-8">
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Deposites</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= getTotalTransactionAmount(); ?></div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
                                        <i class="fs-4" data-duoicon="clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Withdrawals</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= getTotalWithdrawalAmount(); ?></div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
                                        <i class="fs-4" data-duoicon="credit-card"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Customers</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= getTotalCustomers(0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
                                        <i class="fs-4" data-duoicon="calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-md-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Pending deposites</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= getTotalTransactionAmount('Pending'); ?></div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
                                        <i class="fs-4" data-duoicon="slideshow"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            

            <div class="row">
                <div class="col-12 col-xxl">
                    <section class="mb-8">
                        <!-- Filters -->
                        <div class="card card-line bg-body-tertiary border-transparent mb-7">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="text-body-secondary">
                                                    Today transactions
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="col-12 col-lg"><div class="row gx-3  ">
                                            <div class="col col-lg-auto ms-auto">
                                                <div class="input-group bg-body">
                                                    <input type="text" id="search" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" />
                                                    <span class="input-group-text" id="search">
                                                        <span class="material-symbols-outlined">search</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="">
                                                    <!-- upload today collection file -->
                                                    <button class="btn btn-dark px-3" type="button" data-bs-toggle="modal" data-bs-target="#todayUploadModal">
                                                        <span class="material-symbols-outlined">add_a_photo</span> Today collection
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-auto ms-n2">
                                                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" name="sortRadio" id="sortAsc" autocomplete="off" checked />
                                                    <label class="btn btn-light" for="sortAsc" data-bs-toggle="tooltip" data-bs-title="Ascending">
                                                        <span class="material-symbols-outlined">arrow_upward</span>
                                                    </label>
                                                    <input type="radio" class="btn-check" name="sortRadio" id="sortDesc" autocomplete="off" />
                                                    <label class="btn btn-light" for="sortDesc" data-bs-toggle="tooltip" data-bs-title="Descending">
                                                        <span class="material-symbols-outlined">arrow_downward</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Header -->
                        <div class="row align-items-center mb-5">
                            <div class="col">
                                <!-- Heading -->
                                <h2 class="fs-5 mb-0">
                                    <?php
                                        // today's date
                                        $today = date('Y-m-d');
                                        echo date('F j, Y', strtotime($today));
                                    ?>
                                </h2>
                            </div>
                            <div class="col-auto">
                                <a class="btn btn-link my-n2" href="../ecommerce/orders.html">
                                    Browse all
                                    <span class="material-symbols-outlined">arrow_right_alt</span>
                                </a>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="card mb-7 mb-xxl-0">

                            <div id="load-content"></div>

                        </div>
                    </section>
                </div>
            </div>
        </div>
    
<?php include ('../system/inc/footer.php'); ?>

<script>

    $(document).ready(function() {

        // SEARCH AND PAGINATION FOR LIST
        function load_data(page, query = '') {
            $.ajax({
                url : "<?= PROOT; ?>app/controller/list.transactions.php",
                method : "POST",
                data : {
                    page : page, 
                    query : query
                },
                success : function(data) {
                    $("#load-content").html(data);
                }
            });
        }

        load_data(1);
        $('#search').keyup(function() {
            var query = $('#search').val();
            load_data(1, query);
        });

        $(document).on('click', '.page-link-go', function() {
            var page = $(this).data('page_number');
            var query = $('#search').val();
            load_data(page, query);
        });


        // get customer deafult amount on customer select
        $('#select_customer').on('change', function() {
            var selectedValue = $(this).val();
            var parts = selectedValue.split(',');
            if (parts.length === 2) {
                var customerName = parts[0];
                var accountNumber = parts[1];

                // Make an AJAX request to fetch the default amount
                $.ajax({
                    url: 'controller/get_customer_default_amount.php',
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
                url: 'controller/transaction.add.php',
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
                        return false;
                    }
                },
                error: function() {
                    $state.html('An error occurred. Please try again.');
                    $('.toast').toast('show');
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
        
    });
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
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
            return false;
        }

        // validate if upload date is selected
        var uploadDate = document.getElementById("upload_date").value;
        if (uploadDate === '') {
            $('.toast-body').html('Please select a date to upload.');
            $('.toast').toast('show');
            return false;
        }

        // validate if total collected is entered
        var totalCollected = document.getElementById("total_collected").value;
        if (totalCollected === '' || isNaN(totalCollected) || Number(totalCollected) < 0) {
            $('.toast-body').html('Please enter a valid total amount collected.');
            $('.toast').toast('show');
            return false;
        }

        // process the queue
        myDropzone.processQueue();
        // show loading on button
        $('#uploadButton').attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Uploading ...</span>');
        // disable close button
        $('#closeUploadModal').attr('disabled', true);
    
        // after processing
        myDropzone.on("complete", function () {
            // show success message
            $('.toast-body').html('File uploaded successfully.');
            $('.toast').toast('show');

            // enable button
            $('#uploadButton').attr('disabled', false).html('Upload file');
            // enable close button
            $('#closeUploadModal').attr('disabled', false);
            // close modal
            $('#todayUploadModal').modal('hide');
            // reload page after 2 seconds
            setTimeout(function() {
                location.reload();
            }, 2000);
        });
        myDropzone.on("error", function(file, response) {
            // show error message
            $('.toast-body').html(response);
            $('.toast').toast('show');
            // enable button
            $('#uploadButton').attr('disabled', false).html('Upload file');
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
                // enable button
                $('#uploadButton').attr('disabled', false).html('Upload file');
                // enable close button
                $('#closeUploadModal').attr('disabled', false);
            } else {
                // success message will be shown on complete event
            }
        });
    });
</script>