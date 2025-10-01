<?php 
    require ('../system/DatabaseConnector.php');
        
        // Check if the admin or collector is logged in
    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    $title = 'Transactions | ';
    $body_class = '';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');

    // function to get total amount of transactions
    function getTotalTransactionAmount($type = 'Approved') {
        global $dbConnection;
        if (admin_is_logged_in()) {
            $stmt = $dbConnection->prepare("SELECT SUM(saving_amount) AS total_amount FROM savings WHERE saving_status = ?");
            $stmt->execute([$type]);
        } elseif (admin_has_permission('collector') && !admin_has_permission('admin')) {
            global $admin_id;
            $stmt = $dbConnection->prepare("SELECT SUM(saving_amount) AS total_amount FROM savings WHERE saving_collector_id = ? AND saving_status = ?");
            $stmt->execute([$admin_id, $type]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return money($row['total_amount'] ? $row['total_amount'] : 0);
    }

    // function to get total amount of withdrawals
    function getTotalWithdrawalAmount($type = 'Approved', $or = 'Paid') {
        global $dbConnection;
        // if (admin_is_logged_in()) {
            $stmt = $dbConnection->prepare("SELECT SUM(withdrawal_amount_requested) AS total_amount FROM withdrawals WHERE (withdrawal_status = ? OR withdrawal_status = ?)");
            $stmt->execute([$type, $or]);
        // }
        // elseif (admin_has_permission('collector') && !admin_has_permission('admin')) {
        //     global $collector_id;
        //     $stmt = $dbConnection->prepare("SELECT SUM(withdrawal_amount_requested) AS total_amount FROM withdrawals WHERE withdrawal_approver_id = ? AND (withdrawal_status = ? OR withdrawal_status = ?)");
        //     $stmt->execute([$collector_id, $type, $or]);
        // }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return money($row['total_amount'] ? $row['total_amount'] : 0);
    }

    // function to get total number of customers and to display for admin and collector
    function getTotalCustomers() {
        global $dbConnection;
        if (admin_has_permission()) {
            $stmt = $dbConnection->prepare("SELECT COUNT(*) AS total_customers FROM customers WHERE customer_status = ?");
            $stmt->execute(['active']);
        } elseif (admin_has_permission('collector') && !admin_has_permission('admin')) {
            global $admin_id;
            $stmt = $dbConnection->prepare("SELECT COUNT(*) AS total_customers FROM customers WHERE customer_added_by = 'collector' AND customer_collector_id = ? AND customer_status = 'active'");
            $stmt->execute([$admin_id]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_customers'] ?? 0;
    }

    //
    // Deposite status
    if (isset($_GET['d']) && !empty($_GET['d'])) {
        if (isset($_GET['approved']) && !empty($_GET['approved'])) {
            $approved_id = sanitize($_GET['approved']);

            $sql = $dbConnection->query("UPDATE savings SET saving_status = 'Approved' WHERE saving_id = '".$approved_id."'")->execute();
            if ($sql) {
                $log_message =  'Admin [' . $admin_id . '] has set deposit [' . $approved_id . '] status to Approved!';
                add_to_log($log_message, $admin_id, 'admin');
                $_SESSION['flash_success'] = $log_message;
                redirect(PROOT . 'app/transactions');
            } else {
                $_SESSION['flash_success'] = 'Could\'nt update deposit status to Approved!';
                redirect(PROOT . 'app/transactions');
            }
        }
    }


    //
    // withdrawal paid
    if (isset($_GET['w']) && !empty($_GET['w'])) {

        // set withdrawal status to paid
        if (isset($_GET['paid']) && !empty($_GET['paid'])) {
            $paid_id = sanitize($_GET['paid']);

            $sql = $dbConnection->query("UPDATE withdrawals SET withdrawal_status = 'Paid' WHERE withdrawal_id = '".$paid_id."'")->execute();
            if ($sql) {
                $log_message =  'Admin [' . $admin_id . '] set withdrawal [' . $paid_id . '] status to Paid!';
                add_to_log($log_message, $admin_id, 'admin');
                $_SESSION['flash_success'] = $log_message;
                redirect(PROOT . 'app/transactions');
            } else {
                $_SESSION['flash_success'] = 'Could\'nt update withdrawal status to Paid!';
                redirect(PROOT . 'app/transactions');
            }
        }

        // set withdrawal status to approved
        if (isset($_GET['approved']) && !empty($_GET['approved'])) {
            $approved_id = sanitize($_GET['approved']);

            $sql = $dbConnection->query("UPDATE withdrawals SET withdrawal_status = 'Approved' WHERE withdrawal_id = '".$approved_id."'")->execute();
            if ($sql) {
                $log_message =  'Admin [' . $admin_id . '] set withdrawal [' . $approved_id . '] status to Approved!';
                add_to_log($log_message, $admin_id, 'admin');
                $_SESSION['flash_success'] = $log_message;
                redirect(PROOT . 'app/transactions');
            } else {
                $_SESSION['flash_success'] = 'Could\'nt update withdrawal status to Approved!';
                redirect(PROOT . 'app/transactions');
            }
        }
        
        // set withdrawal status to reject
        if (isset($_GET['reject']) && !empty($_GET['reject'])) {
            $reject_id = sanitize($_GET['reject']);

            $sql = $dbConnection->query("UPDATE withdrawals SET withdrawal_status = 'Rejected' WHERE withdrawal_id = '".$reject_id."'")->execute();
            if ($sql) {
                $log_message =  'Admin [' . $admin_id . '] set withdrawal [' . $reject_id . '] status to Rejected!';
                add_to_log($log_message, $admin_id, 'admin');
                $_SESSION['flash_success'] = $log_message;
                //redirect(PROOT . 'app/transactions');
            } else {
                $_SESSION['flash_success'] = 'Could\'nt update withdrawal status to Rejected!';
                //redirect(PROOT . 'app/transactions');
            }
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
                    <div class="row gx-2">
                        <div class="col-6 col-sm-auto">
                            <?php if (admin_has_permission('collector') && !admin_has_permission('admin')): ?>
                            <button class="btn btn-secondary w-100" type="button" data-bs-toggle="modal" data-bs-target="#transactionModal">
                                <span class="material-symbols-outlined me-1">add</span> New transaction
                            </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" type="button" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                                <span class="material-symbols-outlined me-1">payment_arrow_down</span> Make new withdrawal
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <a class="btn btn-light d-block" href="<?= goBack(); ?>"><span class="material-symbols-outlined me-1">arrow_back_ios</span> Go back </a>
                        </div>
                    </div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Deposits</h4>

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
                                    <div class="fs-4 fw-semibold"><?= getTotalCustomers(); ?></div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Pending deposits</h4>

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
                                            <?php if ( admin_has_permission('collector') && !admin_has_permission('admin')): ?>
                                            <div class="col-auto">
                                                <div class="">
                                                    <!-- upload today collection file -->
                                                    <button class="btn btn-dark px-3" type="button" data-bs-toggle="modal" data-bs-target="#todayUploadModal">
                                                        <span class="material-symbols-outlined">add_a_photo</span> Today collection
                                                    </button>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <!-- <div class="col-auto ms-n2">
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
                                            </div> -->
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
                            <!-- <div class="col-auto">
                                <a class="btn btn-link my-n2" href="javascript:;">
                                    Browse all
                                    <span class="material-symbols-outlined">arrow_right_alt</span>
                                </a>
                            </div> -->
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
            
    });
</script>
    