<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
    if (!admin_is_logged_in()) {
        redirect(PROOT . 'auth/sign-in');
    }
    $view = 0;

    // functions to fetch all saves and withdrawals by cutomer 
    function fetchAllTransaction($customer_id) {
        global $dbConnection;
        $sql = "
            SELECT * FROM (
                SELECT 
                    saving_id AS transaction_id, 
                    saving_customer_id AS customer_id, 
                    saving_customer_account_number AS account_number,
                    saving_collector_id AS collector_id, 
                    saving_amount AS amount, 
                    saving_date_collected AS transaction_date, 
                    saving_status AS status, 
                    'saving' AS type, 
                    created_at 
                FROM savings 
                WHERE saving_customer_id = ?
                UNION ALL
                SELECT 
                    withdrawal_id AS transaction_id, 
                    withdrawal_customer_id AS customer_id, 
                    withdrawal_customer_account_number AS account_number, 
                    withdrawal_approver_id AS collector_id, 
                    withdrawal_amount_requested AS amount, 
                    withdrawal_date_requested AS transaction_date, 
                    withdrawal_status AS status, 
                    'withdrawal' AS type, 
                    created_at 
                FROM withdrawals 
                WHERE withdrawal_customer_id = ?
            ) AS transactions
            ORDER BY created_at DESC
        ";
        $stmt = $dbConnection->prepare($sql);
        $stmt->execute([$customer_id, $customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    //
    function get_all_saves($customer_id) {
        global $dbConnection;
        $query = "
            SELECT * FROM savings 
            WHERE savings.saving_customer_id = ? 
            -- AND savings.save_status = 'active' 
            ORDER BY savings.created_at DESC
        ";
        $statement = $dbConnection->prepare($query);
        $statement->execute([$customer_id]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // get customer added by
    function get_customer_added_by($by, $id) {
        global $dbConnection;
        if ($by == 'collector') {
            $query = "
                SELECT * FROM susu_admins 
                WHERE susu_admins.admin_id = ?
                LIMIT 1
            ";
            $statement = $dbConnection->prepare($query);
            $statement->execute([$id]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return (($row['admin_name']) ? ucwords($row['admin_name']) . ' <span class="badge bg-warning-subtle text-warning">Collector</span>': 'Collector');
        }
        return 'Admin';
    }

    $body_class = '';
    $title = 'Customers | ';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');

    // submit collector form
    $error = '';
    $post = cleanPost($_POST);

    // Collect and sanitize input
    $name     = $post['name'] ?? '';
    $email    = $post['email'] ?? '';
    $phone    = $post['phone'] ?? '';
    $address  = $post['address'] ?? '';
    $region   = $post['region'] ?? '';
    $city     = $post['city'] ?? '';
    $password = $post['password'] ?? '';
    $confirm  = $post['confirm'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Validate required fields
        if (!$name || !$email || !$phone || !$address || !$region || !$city || !$password || !$confirm) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Handle file upload if exists
            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $upload_dir = '../assets/media/uploads/collectors-media/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $filename = uniqid('collector_', true) . '.' . $ext;
                    $photo_path = $upload_dir . $filename;
                    move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
                } else {
                    $error = "Invalid photo file type.";
                }
            }

            if (!$error) {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:m:s"));
                $conn = $dbConnection;
                // Insert into database
                $stmt = $conn->prepare("
                    INSERT INTO collectors (collector_id, collector_name, collector_phone, collector_email, collector_address, collector_state, collector_city, collector_photo, collector_password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $unique_id, $name, $phone, $email, $address, $region, $city, $photo_path, $password_hash
                ]);
                if ($result) {
                    $_SESSION['success_flash'] = "Collector added successfully!";
                    redirect(PROOT . 'app/collectors');
                } else {
                    $error = "Failed to add collector. Please try again.";
                }
            }
        }
    }




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
                if (admin_has_permission('collector') && !admin_has_permission('admin')) {
                    $query = "
                        SELECT * FROM customers 
                        WHERE customer_id = ? 
                        AND customer_added_by = 'collector' 
                        AND customer_collector_id = '$admin_id'
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
                    user-select: none;
                }
                .saved {
                    background-color: #28a745; /* green */
                    color: white;
                }
                .pending { 
                    background: #fd7e14; 
                    color: #fff; 
                } /* orange pending */

                .not-saved { 
                    background: #f8f9fa; 
                    border: 1px solid #dee2e6; 
                }

                .commission {
                    background-color: #dc3545; /* red */
                    color: white;
                }
                .muted-small { 
                    font-weight: 400; 
                    font-size: 0.8rem; 
                    display: block;
                }

            </style>

            <div class="mb-8">
                <h2 class="mb-3">Savings Calendar</h2>

                <div class="mb-3">
                    <h5>Legend:</h5>
                    <div class="d-flex flex-wrap gap-3">
                        <span class="badge bg-success">Approved Savings</span>
                        <span class="badge bg-warning text-dark">Pending Savings</span>
                        <span class="badge bg-light text-dark border">Not Saved</span>
                        <span class="badge bg-danger">Commission</span>
                    </div>
                </div>

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
                            <?php if (admin_is_logged_in() && admin_has_permission()): ?>
                            <div class="col">
                                <button class="btn btn-danger w-100" type="button">Deactivate</button>
                            </div>
                            <?php endif; ?>
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
                                        
                                        $collector = findAdminById($save['collector_id'])->admin_name;
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
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Customers</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Customers</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">Customers</h1>
                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                    <!-- Action -->
                    <div class="row gx-2">
                        <div class="col-6 col-sm-auto">
                            <a class="btn btn-secondary d-block" href="<?= PROOT; ?>app/customer-new">
                                <span class="material-symbols-outlined me-1">add</span> New customer
                            </a>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <a class="btn btn-light d-block" href="<?= goBack(); ?>"><span class="material-symbols-outlined me-1">arrow_back_ios</span> Go back </a>
                        </div>
                    </div>
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
                                            <div class="text-body-secondary">No customers selected</div>
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
                                        <div class="col-auto">
                                            <div class="dropdown">
                                                <button class="btn btn-dark px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                    <span class="material-symbols-outlined">filter_list</span>
                                                </button>
                                                <div class="dropdown-menu rounded-3 p-6">
                                                    <h4 class="fs-lg mb-4">Filter</h4>
                                                    <form style="width: 350px" id="filterForm">
                                                        <div class="row align-items-center mb-3">
                                                            <div class="col-3">
                                                                <label class="form-label mb-0" for="filterUser">User</label>
                                                            </div>
                                                            <div class="col-9">
                                                                <select
                                                                    class="form-select"
                                                                    id="filterUser"
                                                                    data-choices='{"searchEnabled": false, "choices": [
                                                                        {
                                                                        "value": "Emily Thompson",
                                                                        "label": "Emily Thompson",
                                                                        "customProperties": {
                                                                        "avatarSrc": "../assets/img/photos/photo-1.jpg"
                                                                        }
                                                                    },
                                                                    {
                                                                        "value": "Michael Johnson",
                                                                        "label": "Michael Johnson",
                                                                        "customProperties": {
                                                                        "avatarSrc": "../assets/img/photos/photo-2.jpg"
                                                                        }
                                                                    },
                                                                    {
                                                                        "value": "Robert Garcia",
                                                                        "label": "Robert Garcia",
                                                                        "customProperties": {
                                                                        "avatarSrc": "../assets/img/photos/photo-3.jpg"
                                                                        }
                                                                    },
                                                                    {
                                                                        "value": "Jessica Miller",
                                                                        "label": "Jessica Miller",
                                                                        "customProperties": {
                                                                        "avatarSrc": "../assets/img/photos/photo-4.jpg"
                                                                        }
                                                                    }
                                                                    ]}'
                                                                ></select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-3">
                                                            <div class="col-3">
                                                                <label class="form-label mb-0" for="filterCompany">Company</label>
                                                            </div>
                                                            <div class="col-9">
                                                                <select class="form-select" id="filterCompany" data-choices='{"placeholder": "some"}'>
                                                                    <option value="TechPinnacle Solutions">TechPinnacle Solutions</option>
                                                                    <option value="Quantum Dynamics">Quantum Dynamics</option>
                                                                    <option value="Pinnacle Technologies">Pinnacle Technologies</option>
                                                                    <option value="Apex Innovations">Apex Innovations</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center">
                                                            <div class="col-3">
                                                                <label class="form-label mb-0" for="filterLocation">Location</label>
                                                            </div>
                                                            <div class="col-9">
                                                                <select class="form-select" id="filterLocation" data-choices>
                                                                    <option value="San Francisco, CA">San Francisco, CA</option>
                                                                    <option value="Austin, TX">Austin, TX</option>
                                                                    <option value="Miami, FL">Miami, FL</option>
                                                                    <option value="Seattle, WA">Seattle, WA</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto ms-n2">
                                            <div class="dropdown">
                                                <button class="btn btn-dark px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                    <span class="material-symbols-outlined">sort_by_alpha</span>
                                                </button>
                                                <div class="dropdown-menu rounded-3 p-6">
                                                    <h4 class="fs-lg mb-4">Sort</h4>
                                                    <form style="width: 350px" id="filterForm">
                                                        <div class="row gx-3">
                                                            <div class="col">
                                                                <select class="form-select" id="sort" data-choices='{"searchEnabled": false}'>
                                                                    <option value="user">User</option>
                                                                    <option value="company">Company</option>
                                                                    <option value="phone">Phone</option>
                                                                    <option value="location">Location</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-auto">
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
                                                    </form>
                                                </div>
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
<?php if (isset($_GET['view']) && !empty($_GET['view'])): ?>
<script>
    
    let currentCycle = null; // track current shown cycle
    const customerId = "<?= $view; ?>"; // set the customer id here

    // savedDays global for modal access: { dayNumber: { date, amount, entries: [...] } ... }
    let savedDaysGlobal = {};
    let commissionDayGlobal = null;
    let commissionAmountGlobal = null;
    let withdrawalsGlobal = [];

    function loadCalendar(cycle = undefined) {
        // build params
        const params = { customer_id: customerId };
        if (typeof cycle !== 'undefined') params.cycle = cycle;

        $.getJSON('<?= PROOT; ?>app/controller/customer.calendar.php', params)
            .done(function (data) {
            // sanity check
            if (!data || !data.cycle_start) {
                $('#cycleLabel').text('No savings yet');
                $('#calendar').empty();
                savedDaysGlobal = {};
                commissionDayGlobal = null;
                return;
            }

            // determine currentCycle (API tells us the cycle it used)
            currentCycle = data.current_cycle;
            savedDaysGlobal = data.saved_days || {};
            withdrawalsGlobal = data.withdrawals || [];
            commissionDayGlobal = data.commission_day || null;
            commissionAmountGlobal = data.commission_amount || null;

            // render label
            $('#cycleLabel').text(`Cycle ${currentCycle + 1}: ${data.cycle_start} → ${data.cycle_end}`);

            // render 31 cells
            const $cal = $('#calendar').empty();
            for (let d = 1; d <= 31; d++) {
                let cls = 'not-saved';
                let innerHTML = `<div>${d}</div>`;

                if (commissionDayGlobal === d) {
                    cls = 'commission';
                    innerHTML = `<div>${d}<span class="muted-small">(Fee)</span></div>`;
                } else if (savedDaysGlobal[d]) {
                    // check if any entry pending
                    const hasPending = savedDaysGlobal[d].entries.some(en => en.status === 'Pending');
                    cls = hasPending ? 'pending' : 'saved';
                    const amt = parseFloat(savedDaysGlobal[d].amount).toFixed(2);
                    innerHTML = `<div>${d}<span class="muted-small">GHS ${amt}</span></div>`;
                }

                // attach day element
                const $cell = $(`<div class="day ${cls}" data-day="${d}">${innerHTML}</div>`);
                $cal.append($cell);
            }

            // enable/disable prev button
            $('#prevCycle').prop('disabled', currentCycle <= 0);

            // attach click handlers
            $('.day').off('click').on('click', function () {
                const day = parseInt($(this).data('day'), 10);
                openDayModal(day);
            });
        })
        .fail(function (xhr, status, err) {
            let msg = 'Failed to load calendar.';
            try { msg = xhr.responseText || msg; } catch(e) {}
            alert(msg);
        });
    }				

    function openDayModal(day) {
        const modal = new bootstrap.Modal(document.getElementById('dayModal'));
        let html = `
            <div class="vstack gap-3 card bg-body">
				<div class="card-body py-3">
                    <div class="row align-items-center gx-4">
                        <div class="col-auto">
                            <span class="text-body-secondary">Day</span>
                        </div>
                        <div class="col">
                            <hr class="my-0 border-style-dotted" />
                        </div>
                        <div class="col-auto">
                            <span class="material-symbols-outlined text-body-tertiary me-1">event</span> ${day}
                        </div>
                    </div>
        `;

        // if commission day
        if (commissionDayGlobal === day) {
            html += `
                <div class="row align-items-center gx-4">
                    <div class="col-auto">
                        <span class="text-body-secondary">Commission deducted</span>
                    </div>
                    <div class="col">
                        <hr class="my-0 border-style-dotted" />
                    </div>
                    <div class="col-auto">
                        <span class="material-symbols-outlined text-body-tertiary me-1">mintmark</span> GHS ${parseFloat(commissionAmountGlobal).toFixed(2)}
                    </div>
                </div>
            `;
        }

        // saved entries
        const dayData = savedDaysGlobal[day];
        if (!dayData) {
            html += `<p>No savings recorded for this day.</p>`;
        } else {
            html += `
                <div class="row align-items-center gx-4">
                    <div class="col-auto">
                        <span class="text-body-secondary">Date</span>
                    </div>
                    <div class="col">
                        <hr class="my-0 border-style-dotted" />
                    </div>
                    <div class="col-auto">
                        <span class="material-symbols-outlined text-body-tertiary me-1">date_range</span> ${dayData.date}
                    </div>
                </div>
            `;
            html += `
                <div class="row align-items-center gx-4">
                    <div class="col-auto">
                        <span class="text-body-secondary">Total saved</span>
                    </div>
                    <div class="col">
                        <hr class="my-0 border-style-dotted" />
                    </div>
                    <div class="col-auto">
                        <span class="material-symbols-outlined text-body-tertiary me-1">savings</span> GHS ${parseFloat(dayData.amount).toFixed(2)}
                    </div>
                </div>
            `;

            html += `<hr/><div><strong>Entries:</strong></div>`;
            // html += `<ul>`;
            dayData.entries.forEach(en => {
                html += `
                    <div class="row align-items-center gx-4">
                    <div class="col-auto">
                        <span class="text-body-secondary">Satus</span>
                    </div>
                    <div class="col">
                        <hr class="my-0 border-style-dotted" />
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-secondary-subtle text-secondary">${en.status}</span>'
                    </div>
                </div>
                `;
                html += `
                    <div class="row align-items-center gx-4">
                        <div class="col-auto">
                            <span class="text-body-secondary">Collector Name</span>
                        </div>
                        <div class="col">
                            <hr class="my-0 border-style-dotted" />
                        </div>
                        <div class="col-auto">
                            <span class="material-symbols-outlined text-body-tertiary me-1">barcode_reader</span> ${en.admin_name.toUpperCase()}
                        </div>
                    </div>
                `;
                html += `
                    <div class="row align-items-center gx-4">
                        <div class="col-auto">
                            <span class="text-body-secondary">Saving ID</span>
                        </div>
                        <div class="col">
                            <hr class="my-0 border-style-dotted" />
                        </div>
                        <div class="col-auto">
                            <span class="material-symbols-outlined text-body-tertiary me-1">barcode</span> ${en.saving_id} — GHS ${parseFloat(en.amount).toFixed(2)}
                        </div>
                    </div>
                `;
            });
            
        }
        html += `
                </div>
            </div>
        `;

        const dayWithdrawals = withdrawalsGlobal.filter(w => w.day === day);
        if (dayWithdrawals.length) {
            html += `<hr><strong>Withdrawals:</strong><ul>`;
            dayWithdrawals.forEach(w => {
            html += `<li>Withdrawal ID ${w.id} — GHS ${w.amount.toFixed(2)} — <em>${w.status}</em></li>`;
            });
            html += `</ul>`;
        }


        $('#modalTitle').text(`Day ${day} details`);
        $('#modalBody').html(html);
        modal.show();
    }

    // navigation
    $('#prevCycle').on('click', function () {
        if (currentCycle > 0) {
            loadCalendar(currentCycle - 1);
        }
    });
    $('#nextCycle').on('click', function () {
        loadCalendar(currentCycle + 1);
    });

    // initial load (auto-detect)
    loadCalendar();
</script>
<?php endif; ?>

<script>

    $(document).ready(function() {

        // SEARCH AND PAGINATION FOR LIST
        function load_data(page, query = '') {
            $.ajax({
                url : "<?= PROOT; ?>app/controller/list.customers.php",
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