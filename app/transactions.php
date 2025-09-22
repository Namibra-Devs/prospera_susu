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

    // check if is posted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_customer'])) {
        // dnd($_POST);
        // get form data
        $customer_info = sanitize($_POST['select_customer']);
        list($customer_name, $customer_account_number) = explode(',', $customer_info);
        $transaction_amount = sanitize($_POST['defualt_amount']);
        $transaction_date = sanitize($_POST['today_date']);
        $transaction_note = sanitize($_POST['note']);
        $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:m:s"));

        $find_customer_row = findCustomerByAccountNumber($customer_account_number);

        // validate inputs
        if (empty($customer_name) || empty($customer_account_number) || empty($transaction_amount) || empty($transaction_date)) {
            $_SESSION['flash_error'] = 'Please fill in all required fields.';
            redirect(PROOT . 'app/transactions');
        }

        // insert into database
        $stmt = $dbConnection->prepare("INSERT INTO savings (saving_id, saving_customer_id, saving_customer_account_number, saving_collector_id, saving_amount, saving_date_collected, saving_note) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $added_by = null;
        $added_by_id = null;
        if (array_key_exists('PRSADMIN', $_SESSION)) {
            $added_by = 'admin';
            $added_by_id = $_SESSION['PRSADMIN'];
        } elseif (array_key_exists('PRSCOLLECTOR', $_SESSION)) {
            $added_by = 'collector';
            $added_by_id = $_SESSION['PRSCOLLECTOR'];
        }
        $stmt->execute([$unique_id, $find_customer_row->customer_id, $customer_account_number, $collector_id, $transaction_amount, $transaction_date, $transaction_note]);

        if ($stmt) {
            // 
            $log_message = ucwords($added_by) . ' [' . $added_by_id . '] added new transaction to ' . ucwords($customer_name) . ' (' . $customer_account_number . ') account';
            add_to_log($log_message, $added_by_id, $added_by);


            $_SESSION['flash_success'] = 'Transaction added successfully.';
            redirect(PROOT . 'app/transactions');
        } else {
            $_SESSION['flash_error'] = 'An error occurred. Please try again.';
            redirect(PROOT . 'app/transactions');
        }
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
                            $('#defualt_amount').val(data.customer_default_daily_amount);
                        } else {
                            $('#defualt_amount').val('');
                        }
                    },
                    error: function() {
                        console.error('Error fetching default amount');
                        $('#defualt_amount').val('');
                    }
                });
            } else {
                $('#defualt_amount').val('');
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

        // add new transaction
        $('#add-transaction-form').on('submit', function (e) {
            // e.preventDefault();

            $('#submit-transaction').attr('disabled', true);
            $('#submit-transaction').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Processing ...</span>');

            // Simulate a delay (e.g., AJAX call)
            setTimeout(function () {
                alert('Transaction submitted!');
                $('#submit-transaction').html('Add transaction');
                $('#submit-transaction').attr('disabled', false);
            }, 2000);
        });
    });
</script>