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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Today</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(0); ?></div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">This week</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(0); ?></div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">This month</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(0); ?></div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Total</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(0); ?></div>
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
                                                    <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" />
                                                    <span class="input-group-text" id="search">
                                                        <span class="material-symbols-outlined">search</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="dropdown">
                                                    <button class="btn btn-dark px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="material-symbols-outlined">filter_list</span>
                                                    </button>
                                                    <div class="dropdown-menu rounded-3 p-6">
                                                        <h4 class="fs-lg mb-4">Filter</h4>
                                                        
                                                    </div>
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
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 0px">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="tableCheckAll" />
                                                    <label class="form-check-label" for="tableCheckAll"></label>
                                                </div>
                                            </th>
                                            <th class="fs-sm"></th>
                                            <th class="fs-sm">Client</th>
                                            <th class="fs-sm">Amount</th>
                                            <th class="fs-sm">Collector</th>
                                            <th class="fs-sm">Status</th>
                                            <th class="fs-sm"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="width: 0px">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="tableCheckOne" />
                                                    <label class="form-check-label" for="tableCheckOne"></label>
                                                </div>
                                            </td>
                                            <td>1</td>
                                            <td>Michael Johnson</td>
                                            <td>$499.00</td>
                                            <td>Enterprise</td>
                                            <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-light">Approve</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    
<?php include ('../system/inc/footer.php'); ?>

<script>

    $(document).ready(function() {
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
                        setTimeout(function() {
                            $('#transactionModal').modal('hide');
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
        
    });
</script>