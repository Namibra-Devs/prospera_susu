<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
	// if (!admin_is_logged_in()) {
	// 	admin_login_redirect();
	// }
    if (!admin_is_logged_in() && !collector_is_logged_in()) {
        redirect(PROOT . 'auth/sign-in');
    }
    $view = 0;


    $body_class = '';
    $title = 'Collections | ';
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
            <?php if (isset($_GET['view'])): 
                $view = sanitize($_GET['view']);

                // fetch customer data
                $query = "
                    SELECT * FROM customers 
                    WHERE customer_id = ? 
                    AND customers.customer_status = 'active'
                    LIMIT 1
                ";
                if (collector_is_logged_in()) {
                    $query = "
                        SELECT * FROM customers 
                        WHERE customer_id = ? 
                        AND customer_added_by = 'collector' 
                        AND customer_collector_id = '$collector_id'
                        AND customers.customer_status = 'active'
                        LIMIT 1
                    ";
                }
                $statement = $dbConnection->prepare($query);
                $statement->execute([$view]);
                if ($statement->rowCount() < 1) {
                    $_SESSION['flash_error'] = 'Customer not found!';
                   redirect(PROOT . 'app/customers');
                } else {
                    $customer_data = $statement->fetch(PDO::FETCH_ASSOC);

                    // get total saves by customer with status type
                    $total_approved_saves = sum_customer_saves($customer_data['customer_id'], 'Approved');

                    // get total withdrawals by customer with status type
                    $total_approved_withdrawals = sum_customer_withdrawals($customer_data['customer_id'], 'Approved');

                    // get total saved amount (subtract withdrawals from saves)
                    $total_saved_amount = $total_approved_saves - $total_approved_withdrawals;
                }
            ?>

            <!-- Stats -->
            <div class="row mb-8">
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Start date</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= (($customer_data['customer_start_date'] == null || $customer_data['customer_start_date'] == '0000-00-00') ? 'N/A' : pretty_date_notime($customer_data['customer_start_date'])); ?></div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Total saved</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money($total_saved_amount); ?></div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Deposits</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money($total_approved_saves); ?></div>
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
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-md-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Withdrawals</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money($total_approved_withdrawals); ?></div>
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

            <style>
                .calendar {
                    display: grid;
                    grid-template-columns: repeat(7, 1fr);
                    gap: 10px;
                }
                .calendar .day {
                    height: 80px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 8px;
                    font-weight: bold;
                    font-size: 1.1rem;
                    cursor: pointer;
                }
                .saved {
                    background-color: #28a745; /* green */
                    color: white;
                }
                .not-saved {
                    background-color: #e9ecef; /* light gray */
                    color: #6c757d; /* gray */
                }
                .commission {
                    background-color: #dc3545; /* red */
                    color: white;
                }
            </style>

            <div class="mb-8">
                <h2 class="mb-3">Savings Calendar</h2>
                <div class="d-flex justify-content-between mb-3">
                    <button class="btn btn-link" id="prevCycle">← Previous</button>
                    <h5 id="cycleLabel" class="mb-0"></h5>
                    <button class="btn btn-link" id="nextCycle">Next →</button>
                </div>

                <div id="calendar" class="calendar"></div>
            </div>




            <!-- Page content -->
            <div class="row">
                <div class="col-12 col-xxl-4">
                    <div class="position-sticky mb-8" style="top: 40px">
                        <!-- Card -->
                        <div class="card bg-body mb-3">
                            <!-- Body -->
                            <div class="card-body text-center">
                            <!-- Heading -->
                                <h1 class="card-title fs-5"><?= ucwords($customer_data["customer_name"]); ?></h1>

                                <!-- Text -->
                                <p class="text-body-secondary mb-6">Account number: <?= $customer_data["customer_account_number"]; ?></p>

                                <!-- List -->
                                <ul class="list-group list-group-flush mb-0">
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Address</span>
                                        <span><?= ucwords($customer_data["customer_address"]); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Phone</span>
                                        <a class="text-body" href="tel:<?= $customer_data["customer_phone"]; ?>"><?= $customer_data["customer_phone"]; ?></a>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Location</span>
                                        <span><?= ucwords($customer_data["customer_region"] . ', ' . $customer_data["customer_city"]); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">ID card name</span>
                                        <span><?= $customer_data["customer_id_type"] ?? 'N/A'; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">ID card number</span>
                                        <span><?= $customer_data["customer_id_number"] ?? 'N/A'; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Added by</span>
                                        <span><?= get_customer_added_by($customer_data['customer_added_by'], $customer_data['customer_collector_id']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Joined at</span>
                                        <span><?= pretty_date_notime($customer_data["created_at"]); ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="row gx-3">
                            <div class="col">
                                <a class="btn btn-light w-100" href="<?= PROOT; ?>app/customers/edit=<?= $customer_data['customer_id']; ?>">Update</a>
                            </div>
                            <div class="col">
                                <button class="btn btn-danger w-100" type="button">Deactivate</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xxl">
                    <section class="mb-8">
                        <!-- Header -->
                        <div class="d-flex align-items-center justify-content-between mb-5">
                            <h2 class="fs-5 mb-0">Transaction history</h2>
                            <div class="d-flex">
                                <div class="dropdown">
                                    <button class="btn btn-light px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <span class="material-symbols-outlined">filter_list</span>
                                    </button>
                                    <div class="dropdown-menu rounded-3 p-6">
                                        <h4 class="fs-lg mb-4">Filter</h4>
                                     
                                    </div>
                                </div>
                                <div class="dropdown ms-1">
                                    <button class="btn btn-light px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <span class="material-symbols-outlined">sort_by_alpha</span>
                                    </button>
                                    <div class="dropdown-menu rounded-3 p-6">
                                    <h4 class="fs-lg mb-4">Sort</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-round mb-0">
                        <thead>
                            <th>ID</th>
                            <th>Collector</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </thead>
                        <tbody>
                            <?php 
                                $all_saves = fetchAllTransaction($customer_data['customer_id']);
                                if (count($all_saves) > 0):
                                    $i = 1;
                                    foreach ($all_saves as $save): 
                                        $type = ($save['type'] == 'saving') ? '<span class="fs-sm text-primary">Deposit</span>' : '<span class="fs-sm text-danger">Withdrawal</span>';
                                        
                                        $collector = findCollectorById($save['collector_id'])->collector_name;
                                        if (!$collector) {
                                            $collector = 'Admin';
                                        }

                                        $status_badge = '';
                                        if ($save['status'] == 'Approved') {
                                            $status_badge = '<span class="badge bg-success-subtle text-success">Approved</span>';
                                        } elseif ($save['status'] == 'Pending') {
                                            $status_badge = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
                                        } elseif ($save['status'] == 'Rejected') {
                                            $status_badge = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
                                        } elseif ($save['status'] == 'Paid') {
                                            $status_badge = '<span class="badge bg-primary-subtle text-primary">Paid</span>';
                                        } else {
                                            $status_badge = '<span class="badge bg-secondary-subtle text-secondary">Unknown</span>';
                                        }
                            ?>
                            <tr>
                                <td class="text-body-secondary"><?= $i; ?></td>
                                <td><?= ucwords($collector); ?></td>
                                <td><?= pretty_date_notime($save['transaction_date']); ?></td>
                                <td><?= $type; ?></td>
                                <td><?= $status_badge; ?></td>
                                <td><?= money($save["amount"]); ?></td>
                            </tr>
                            <?php 
                                        $i++;
                                    endforeach;
                                else:
                                    echo '
                                        <tr>
                                            <td colspan="5">
                                                <div class="alert alert-info">No saves found!</div>
                                            </td>
                                        </tr>
                                    ';
                                endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <section>
                <!-- Header -->
                <div class="row align-items-center justify-content-between mb-5">
                    <div class="col">
                        <h2 class="fs-5 mb-0">Documents</h2>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-light" type="button"><span class="material-symbols-outlined text-body-secondary me-1">upload</span>Upload</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <tbody>
                            <?php if ($customer_data['customer_id_photo_front'] != ''): 
                                // get the file name
                                $file_name = basename($customer_data['customer_id_photo_front']);
                                
                                // get file size
                                $file_size = filesize($customer_data['customer_id_photo_front']);

                                // get file extension
                                $file_ext = pathinfo($customer_data['customer_id_photo_front'], PATHINFO_EXTENSION);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar rounded text-primary">
                                            <img class="img-fluid" src="../<?= $customer_data["customer_id_photo_front"]; ?>" />
                                        </div>
                                        <div class="ms-4">
                                            <div class="fw-normal"><a class="" href="../<?= $customer_data['customer_id_photo_front']; ?>" target="_blank"><?= $file_name; ?></a></div>
                                            <div class="fs-sm text-body-secondary"><?= $file_size; ?>kb · <?= strtoupper($file_ext); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-body-secondary">Uploaded on <?= pretty_date_notime($customer_data['created_at']); ?></td>
                                <td style="width: 0">
                                    <button class="btn btn-sm btn-light" type="button">Download</button>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($customer_data['customer_id_photo_back'] != ''): 
                                // get the file name
                                $file_name = basename($customer_data['customer_id_photo_back']);
                                
                                // get file size
                                $file_size = filesize($customer_data['customer_id_photo_back']);

                                // get file extension
                                $file_ext = pathinfo($customer_data['customer_id_photo_back'], PATHINFO_EXTENSION);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar rounded text-primary">
                                            <img class="img-fluid" src="../<?= $customer_data["customer_id_photo_back"]; ?>" />
                                        </div>
                                        <div class="ms-4">
                                            <div class="fw-normal"><a class="" href="../<?= $customer_data['customer_id_photo_back']; ?>" target="_blank"><?= $file_name; ?></a></div>
                                            <div class="fs-sm text-body-secondary"><?= $file_size; ?>kb · <?= strtoupper($file_ext); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-body-secondary">Updated on <?= pretty_date_notime($customer_data['created_at']); ?></td>
                                <td style="width: 0">
                                    <button class="btn btn-sm btn-light" type="button">Download</button>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <?php else: ?>

            <!-- Page header -->
            <div class="row align-items-center mb-7">
                <div class="col-auto">
                    <!-- Avatar -->
                    <div class="avatar avatar-xl rounded text-primary">
                        <i class="fs-2" data-duoicon="user"></i>
                    </div>
                </div>
                <div class="col">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Collections</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Collections</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">Collections</h1>
                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                    <!-- Action -->
                    <button class="btn btn-secondary d-block" href="javascript:;" type="button" data-bs-toggle="modal" data-bs-target="#todayUploadModal">
                        <span class="material-symbols-outlined me-1">add_a_photo</span> Upload new collection file
                    </button>
                </div>
            </div>


            <!-- Page content -->
            <div class="row">
                <div class="col-12">
                    <!-- Filters -->
                    <div class="card card-line bg-body-tertiary border-transparent mb-7">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="text-body-secondary">No collection selected</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg">
                                    <div class="row gx-3  ">
                                        <div class="col col-lg-auto ms-auto">
                                            <div class="input-group bg-body">
                                                <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" id="search" />
                                                <span class="input-group-text">
                                                    <span class="material-symbols-outlined">search</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="load-content"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

<?php include ('../system/inc/footer.php'); ?>

<script>

    $(document).ready(function() {

        // SEARCH AND PAGINATION FOR LIST
        function load_data(page, query = '') {
            $.ajax({
                url : "<?= PROOT; ?>app/controller/list.collections.php",
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