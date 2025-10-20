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

    // create downoadable file
    if (isset($_GET['download']) && !empty($_GET['download'])) {
        $file = BASEURL . 'assets/media/uploads/customers-media/' . $_GET['download'];

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            
            //redirect(PROOT . 'app/customers/' . $_GET['id']);
            exit;
        } else {
            $_SESSION['flash_error'] = "File not found.";
            redirect(PROOT . 'app/customers/' . $_GET['id']);
        }
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

    //
    // deactivate status
    if (isset($_GET['c']) && !empty($_GET['c'])) {
        if (isset($_GET['deactivate']) && !empty($_GET['deactivate'])) {
            $deactivate_id = sanitize($_GET['deactivate']);

            $sql = $dbConnection->query("UPDATE customers SET customer_status = 'inactive' WHERE customer_id = '" . $deactivate_id . "'")->execute();
            if ($sql) {
                $log_message =  'Admin [' . $admin_id . '] has set customer [' . $deactivate_id . '] status to Inactive!';
                add_to_log($log_message, $admin_id, 'admin');
                $_SESSION['flash_success'] = $log_message;
                redirect(PROOT . 'app/customers');
            } else {
                $_SESSION['flash_success'] = 'Could\'nt update customer status to Inactive!';
                redirect(PROOT . 'app/customers');
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

                    $balanceData = getCustomerBalance($customer_data['customer_id'], 0);
                    $balance = $balanceData['balance'];
                    // $balanceData['total_saves']
                    // $balanceData['total_withdrawals']
                    // $balanceData['total_commissions']
                    // $total_saved_amount = $balanceData['balance'];
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Balance</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money($balance); ?></div>
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
                    background: #FFC107; 
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
                .withdrawn { 
                    background: #007bff; 
                    color:#fff 
                }

                .rejected {
                    background-color: #6c757d; /* gray */
                    color: #fff;
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
                        <span class="badge bg-success">Approved Deopsit</span>
                        <span class="badge bg-primary">Withdrawn</span>
                        <span class="badge bg-warning text-dark">Pending Deposit</span>
                        <span class="badge text-dark" style="background-color: #6c757d;">Rejected Deposit</span>
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
                                        <span class="text-body-secondary">Email</span>
                                        <a class="text-body" href="mailto:<?= $customer_data["customer_phone"]; ?>"><?= $customer_data["customer_email"] ?? 'N/A'; ?></a>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Location</span>
                                        <span><?= ucwords($customer_data["customer_region"] . ', ' . $customer_data["customer_city"]); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">ID card name</span>
                                        <span><?= (($customer_data["customer_id_type"] != '') ? $customer_data["customer_id_type"] : 'N/A'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">ID card number</span>
                                        <span><?= (($customer_data["customer_id_number"] != '') ? $customer_data["customer_id_number"] : 'N/A'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Added by</span>
                                        <span><?= get_customer_added_by($customer_data['customer_added_by'], $customer_data['customer_collector_id']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Dialy default deposit</span>
                                        <span class="text-body"><?= money($customer_data["customer_default_daily_amount"]); ?></span>
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
                                <a class="btn btn-light w-100" href="<?= PROOT; ?>app/customer-new?edit=<?= $customer_data['customer_id']; ?>">Update</a>
                            </div>
                            <?php if (admin_has_permission()): ?>
                            <div class="col">
                                <a href="<?= PROOT; ?>app/customers?c=1&deactivate=<?= $customer_data["customer_id"]; ?>" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to DEACTIVATE this customer ?');">Deactivate</a>
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
                                        <form style="width: 350px" id="filterForm">
                                            <div class="row align-items-center mb-3">
                                                <div class="col-4">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_class" type="radio" name="transaction_type" id="inlineRadio1" required value="deposit">
                                                        <label class="form-check-label" for="inlineRadio1">Deposits</label>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_class" type="radio" name="transaction_type" id="inlineRadio2" required value="withdrawal">
                                                        <label class="form-check-label" for="inlineRadio2">Withdrawals</label>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_type" type="radio" name="transaction_type" id="inlineRadio3" required value="all" checked>
                                                        <label class="form-check-label" for="inlineRadio3">All</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="row align-items-center mb-3">
                                                    <div class="col-3">
                                                        <label class="form-label mb-0" for="filterFromDate">From</label>
                                                    </div>
                                                    <div class="col-9">
                                                        <input type="date" class="form-control" id="filterFromDate">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-3">
                                                <div class="col-3">
                                                    <label class="form-label mb-0" for="filterToDate">To</label>
                                                </div>
                                                <div class="col-9">
                                                    <input type="date" class="form-control" id="filterToDate">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-3">
                                                <div class="col-3">
                                                    <label class="form-label mb-0" for="filterCollectors">Collectors</label>
                                                </div>
                                                <div class="col-9">
                                                    <select class="form-select" id="filterCollectors" data-choices>
                                                        <option value=""></option>
                                                        <?= $options; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">filter</button>
                                            <br><br>
                                            <a href="javascript:;" id="clearFilter" class="text-sm">clear filter</a>
                                        </form>
                                    </div>
                                </div>
                                <div class="ms-1">
                                    <input class="form-control" id="search" name="search" placeholder="search" />
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div id="load-customer-transaction-data"></div>

                </div>
            </section>
            <section>
                <!-- Header -->
                <div class="row align-items-center justify-content-between mb-5">
                    <div class="col">
                        <h2 class="fs-5 mb-0">Documents</h2>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-light" id="#customerUploadModal" type="button" data-bs-toggle="modal" data-bs-target="#customerUploadModal"><span class="material-symbols-outlined text-body-secondary me-1">upload</span>Upload</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <tbody>
                            <?php if ($customer_data['customer_id_photo_front'] != ''): 
                                $front_file = PROOT . 'assets/media/uploads/customers-media/' . $customer_data['customer_id_photo_front'];
                                // get the file name
                                $file_name = basename($customer_data['customer_id_photo_front']);
                                
                                // get file size
                                $file_size = (file_exists($front_file) && is_file($front_file)) ? filesize($front_file) : 0;

                                // get file extension
                                $file_ext = pathinfo($front_file, PATHINFO_EXTENSION);
                                
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar rounded text-primary">
                                            <img class="img-fluid" src="<?= $front_file; ?>" />
                                        </div>
                                        <div class="ms-4">
                                            <div class="fw-normal"><a class="" href="<?= $front_file; ?>" target="_blank"><?= $file_name; ?></a></div>
                                            <div class="fs-sm text-body-secondary"><?= $file_size; ?>kb · <?= strtoupper($file_ext); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-body-secondary">Uploaded on <?= pretty_date_notime($customer_data['created_at']); ?></td>
                                <td style="width: 0">
                                    <a href="<?= PROOT; ?>app/customers?id=<?= $customer_data['customer_id']; ?>&download=<?= $customer_data['customer_id_photo_front']; ?>" class="btn btn-sm btn-light">Download</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php 
                                if ($customer_data['customer_id_photo_back'] != ''): 
                                    $back_file = PROOT . 'assets/media/uploads/customers-media/' . $customer_data['customer_id_photo_back'];

                                    // get the file name
                                    $file_name = basename($back_file);
                                    
                                    // get file size
                                    $file_size = (file_exists($back_file) && is_file($back_file)) ? filesize($back_file) : 0;

                                    // get file extension
                                    $file_ext = pathinfo($back_file, PATHINFO_EXTENSION);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar rounded text-primary">
                                            <img class="img-fluid" src="<?= $back_file; ?>" />
                                        </div>
                                        <div class="ms-4">
                                            <div class="fw-normal"><a class="" href="<?= $back_file; ?>" target="_blank"><?= $file_name; ?></a></div>
                                            <div class="fs-sm text-body-secondary"><?= $file_size; ?>kb · <?= strtoupper($file_ext); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-body-secondary">Updated on <?= pretty_date_notime($customer_data['created_at']); ?></td>
                                <td style="width: 0">
                                    <a class="btn btn-sm btn-light" href="<?= PROOT; ?>app/customers?id=<?= $customer_data['customer_id']; ?>&download=<?= $customer_data['customer_id_photo_back']; ?>">Download</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- UPLOAD CUSTOMER/SAVER DOCUMENTS -->
            <div class="modal fade" id="customerUploadModal" tabindex="-1" aria-labelledby="customerUploadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="backdrop-filter: blur(5px);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-bottom-0 pb-0">
                            <h1 class="modal-title fs-5" id="customerUploadModalLabel">Upload customer decuments</h1>
                            <button class="btn-close" id="closeUploadModal" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="controller/upload.customer.documents.php" method="post" enctype="multipart/form-data" id="customer-upload-form" class="dropzone">
                                <div class="mb-4">
                                    <label class="form-label" for="idnumber">Customer Id Code</label>
                                    <input class="form-control" id="customerid" name="customerid" type="text" readonly disabled value="<?= $customer_data['customer_id']; ?>" />
                                </div>                      
                                <div class="mb-4 mt-2">
                                    <label class="form-label" for="idcard">ID</label>
                                    <select class="form-control" id="idcard" name="idcard" type="text">
                                        <option value=""></option>
                                        <option value="ghana-card"<?= (($customer_data['customer_id_type'] == 'ghana-card') ? 'selected' : ''); ?>>Ghana Card</option>
                                        <option value="driver-licence"<?= (($customer_data['customer_id_type'] == 'driver-licence') ? 'selected' : ''); ?>>Driver Licence</option>
                                        <option value="voters-id-card"<?= (($customer_data['customer_id_type'] == 'voters-id-card') ? 'selected' : ''); ?>>Voters ID card</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="idnumber">ID Number</label>
                                    <input class="form-control" id="idnumber" name="idnumber" type="text" value="<?= $customer_data['customer_id_number']; ?>" />
                                </div>
                                <div class="mb-4">
                                    <label for="dropzone">Front card</label>
                                    <div class="form-text mt-0 mb-3">Attach files to this customer.</div>
                                    <div class="dropzone dz-clickable" id="dropzone-front"><div class="dz-default dz-message"><button class="dz-button" type="button">Drop files here to upload</button></div></div>
                                </div>
                                <div class="mb-4">
                                    <label for="dropzone">Back Card</label>
                                    <div class="form-text mt-0 mb-3">Attach files to this customer.</div>
                                    <div class="dropzone dz-clickable" id="dropzone-back"><div class="dz-default dz-message"><button class="dz-button" type="button">Drop files here to upload</button></div></div>
                                </div>
                                <button type="button" id="customer-upload-button" class="btn btn-secondary w-100">Upload</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

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
                                        <!-- <div class="col-auto">
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
                                        </div> -->
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
                }

                // else if (savedDaysGlobal[d]) {
                //     const entries = savedDaysGlobal[d].entries;
                //     const statuses = entries.map(en => en.status.toLowerCase());

                //     // Determine box color by priority
                //     if (statuses.includes('rejected')) {
                //         cls = 'rejected'; // pink
                //     } else if (statuses.includes('pending')) {
                //         cls = 'pending'; // orange
                //     } else {
                //         cls = 'saved'; // green
                //     }

                //     const amt = parseFloat(savedDaysGlobal[d].amount).toFixed(2);
                //     innerHTML = `<div>${d}<span class="muted-small">GHS ${amt}</span></div>`;

                //     // Withdrawn override (blue)
                //     if (data.withdrawn_days && data.withdrawn_days.includes(d)) {
                //         cls = 'withdrawn';
                //         const totalWithdrawn = data.withdrawals
                //             .filter(w => ['approved', 'completed'].includes(w.status.toLowerCase()))
                //             .reduce((sum, w) => sum + parseFloat(w.amount), 0);

                //         innerHTML = `<div>${d}<span class="muted-small text-white">GHS ${totalWithdrawn.toFixed(2)} <br>(Withdrawn)</span></div>`;
                //     }
                // }


                else if (savedDaysGlobal[d]) {
                    const entries = savedDaysGlobal[d].entries;
                    const statuses = entries.map(en => en.status.toLowerCase());

                    // Determine base class by deposit status
                    if (statuses.includes('rejected')) {
                        cls = 'rejected'; // pink
                    } else if (statuses.includes('pending')) {
                        cls = 'pending'; // orange
                    } else {
                        cls = 'saved'; // green (approved)
                    }

                    // Default label and amount
                    const amt = parseFloat(savedDaysGlobal[d].amount).toFixed(2);
                    innerHTML = `<div>${d}<span class="muted-small">GHS ${amt}</span></div>`;

                    // 🔵 Handle withdrawals (per-day amount)
                    if (data.withdrawn_days && data.withdrawn_days.includes(d)) {
                        cls = 'withdrawn';
                        const perDayWithdrawAmount = parseFloat(data.daily_withdraw_amount || 0).toFixed(2);
                        innerHTML = `<div>${d}<span class="muted-small text-white">GHS ${perDayWithdrawAmount} (Withdrawn)</span></div>`;
                    }
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
                openDayModal(day, data);
            });
        })
        .fail(function (xhr, status, err) {
            let msg = 'Failed to load calendar.';
            try { msg = xhr.responseText || msg; } catch(e) {}
            alert(msg);
        });
    }

    function openDayModal(day, data) {
        const modal = new bootstrap.Modal(document.getElementById('dayModal'));
        let html = `
            <div class="vstack gap-3 card bg-body">
				<div class="card-body py-3">
                    <div class="row align-items-center gx-4 mb-2">
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
        const savedDay = savedDaysGlobal[day];
        const dayWithdrawals = data.withdrawals.filter(w => 
            ['approved', 'completed'].includes(w.status.toLowerCase())
        )

        const isCommissionDay = data.commission_day && parseInt(day) === parseInt(data.commission_day);
        const isWithdrawnDay = data.withdrawn_days && data.withdrawn_days.includes(day);
        const isRejected = savedDay && savedDay.entries.some(e => e.status.toLowerCase() === 'rejected');
        const isPending = savedDay && savedDay.entries.some(e => e.status.toLowerCase() === 'pending');
        
        // 🧩 Prevent blank cells (no savings, no commission, no withdrawal)
        if (!savedDay && !isCommissionDay && !isWithdrawnDay) return;

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
                <div class="p-2 mb-3 rounded bg-success-subtle border-start border-4 border-success shadow-sm">
                    <h6 class="fw-bold text-success mb-2">💰 Deposit Details</h6>
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
                    <div class="mb-2 ps-2 border-start border-3 ${en.status.toLowerCase() === 'rejected' ? 'border-danger' : en.status.toLowerCase() === 'pending' ? 'border-warning' : 'border-success'}">
                        <div class="row align-items-center gx-4">
                            <div class="col-auto">
                                <span class="text-body-secondary">Satus</span>
                            </div>
                            <div class="col">
                                <hr class="my-0 border-style-dotted" />
                            </div>
                            <div class="col-auto">
                                <span class="badge ${en.status.toLowerCase() === 'rejected' ? 'bg-danger' : en.status.toLowerCase() === 'pending' ? 'bg-warning text-dark' : 'bg-success'}">${en.status}</span>'
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

        // Show withdrawal details below deposits (only for blue boxes)
        if (!isCommissionDay && !isRejected && isWithdrawnDay && dayWithdrawals.length > 0) {
            const thisWithdrawal = data.withdrawal_map && data.withdrawal_map[day];
            if (thisWithdrawal) {
                html += `
                    <div class="p-2 mt-3 rounded bg-primary-subtle border-start border-4 border-primary shadow-sm">
                        <h6 class="fw-bold text-primary mb-2">🔵 Withdrawal Details</h6>
                        <div class="small text-muted">Withdrawal ID: ${thisWithdrawal.id}</div>
                            <div class="small text-muted">Amount: GHS ${parseFloat(thisWithdrawal.amount).toFixed(2)}</div>
                        <div class="small text-muted">Status: ${thisWithdrawal.status}</div>
                        <div class="small text-muted">Date: ${thisWithdrawal.date}</div>
                    </div>
                `;
            }

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

  <!-- UPLOAD COLLECT FILE SCRIPT  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script>
    Dropzone.autoDiscover = false;

    const frontDropzone  = new Dropzone("#dropzone-front", {
        url: "<?= PROOT; ?>app/controller/upload.customer.document.php",
        paramName: "front_card", // file name
        dictDefaultMessage: "Drag and drop file here or click to upload", // default message
        dictFallbackMessage: "Your browser does not support drag and drop file uploads.",
        autoProcessQueue: false,
        maxFiles: 1,
        maxFilesize: 10, // MB
        acceptedFiles: "image/*,application/pdf",
        addRemoveLinks: true
    });
    
    
    const backDropzone  = new Dropzone("#dropzone-back", {
        url: "<?= PROOT; ?>app/controller/upload.customer.document.php",
        paramName: "back_card", // file name
        dictDefaultMessage: "Drag and drop file here or click to upload", // default message
        dictFallbackMessage: "Your browser does not support drag and drop file uploads.",
        autoProcessQueue: false,
        maxFiles: 1,
        maxFilesize: 10, // MB
        acceptedFiles: "image/*,application/pdf",
        addRemoveLinks: true
    });

    document.getElementById("customer-upload-button").addEventListener("click", function () { 
        const formData = new FormData();

        // Add regular form fields
        formData.append("idcard", document.getElementById("idcard").value);
        formData.append("idnumber", document.getElementById("idnumber").value);
        formData.append("customerid", document.getElementById("customerid").value);

        // validate if front file is selected
        if (frontDropzone.getAcceptedFiles().length === 0) {
            $('.toast-body').html('Please upload a front page of your document.');
            $('.toast').toast('show');
            $('.toast').removeClass('bg-success').addClass('bg-danger');

            return false;
        }
        
        // validate if back file is selected
        if (backDropzone.getAcceptedFiles().length === 0) {
            $('.toast-body').html('Please upload a back page of your document.');
            $('.toast').toast('show');
            $('.toast').removeClass('bg-success').addClass('bg-danger');

            return false;
        }

        // validate
        var idcard = document.getElementById("idcard").value;
        if (idcard === '') {
            $('.toast-body').html('Please select ID card type.');
            $('.toast').toast('show');
            $('.toast').removeClass('bg-success').addClass('bg-danger');

            return false;
        }

        // validate
        var idnumber = document.getElementById("idnumber").value;
        if (idnumber === '') {
            $('.toast-body').html('Please enter ID card number.');
            $('.toast').toast('show');
            $('.toast').removeClass('bg-success').addClass('bg-danger');

            return false;
        }

        // Add files
        formData.append("front_card", frontDropzone.files[0]);
        formData.append("back_card", backDropzone.files[0]);

        // show loading on button
        $('#customer-upload-button').attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Uploading ...</span>');
        // disable close button
        $('#closeUploadModal').attr('disabled', true);

        // Submit via AJAX
        fetch("<?= PROOT; ?>app/controller/upload.customer.documents.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert("Upload successful!");
            console.log(data);
            frontDropzone.removeAllFiles(true);
            backDropzone.removeAllFiles(true);
            window.location.reload();
        })
        .catch(error => {
            alert("Upload failed.");
            console.error(error);
        });
    });

    // reset form if upload modal is closed
    $('#customerUploadModal').on('hidden.bs.modal', function () {
        // reset form
        $('#customer-upload-form')[0].reset();
        frontDropzone.removeAllFiles(true);
        backDropzone.removeAllFiles(true);
        // enable button
        $('#uploadButton').attr('disabled', false).html('Upload');
        // enable close button
        $('#closeUploadModal').attr('disabled', false);
    });

</script>

<script>

    $(document).ready(function() {

        // SEARCH AND PAGINATION FOR LIST
        function load_customer_data(page, query = ''<?= admin_has_permission() ? ', filters = {}' : ''; ?>) {
            $.ajax({
                url : "<?= PROOT; ?>app/controller/list.customer.transactions.php",
                method : "POST",
                data : {
                    id : "<?= $customer_data["customer_id"] ?? null; ?>",
                    page : page, 
                    query : query<?= admin_has_permission() ? ',' : ''; ?>
                    <?php if (admin_has_permission()): ?>
                    type: filters.type || '',
                    date_from: filters.date_from || '',
                    date_to: filters.date_to || '',
                    collector: filters.collector || ''
                    <?php endif; ?>
                },
                success : function(data) {
                    $("#load-customer-transaction-data").html(data);
                }, 
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function getFilters() {
            return {
                type: $('input[name="transaction_type"]:checked').val() || '',
                date_from: $('input[type="date"]').eq(0).val(),
                date_to: $('input[type="date"]').eq(1).val(),
                collector: $('#filterCollectors').val()
            }
        }

        load_customer_data(1);

        $('#search').keyup(function() {
            var query = $('#search').val();
            load_customer_data(1, query<?= admin_has_permission() ? ', getFilters()' : ''; ?>);
        });

         // Filter change
        $('#filterForm input, #filterForm select').on('change', function() {
            load_customer_data(1, $('#search').val(), getFilters());
        });

        // Optional: Add a submit button for filters
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            load_customer_data(1, $('#search').val()<?= admin_has_permission() ? ', getFilters()' : ''; ?>);
        });

        // Clear filter functionality
        $('#clearFilter').on('click', function() {
            // Reset radio buttons to "All"
            $('#inlineRadio3').prop('checked', true);
            $('#inlineRadio1, #inlineRadio2').prop('checked', false);

            // Clear date inputs
            $('#filterFromDate').val('');
            $('#filterToDate').val('');

            // Clear collector select
            $('#filterCollectors').val('').trigger('change');

            // Clear search input
            $('#search').val('');

            // Reload data with cleared filters
            load_customer_data(1, '', {
                type: 'all'
                <?php if (admin_has_permission()): ?>,
                date_from: '',
                date_to: '',
                collector: ''
                <?php endif; ?>
            });
        });

        $(document).on('click', '.page-link-go', function() {
            var page = $(this).data('page_number');
            var query = $('#search').val();
            load_customer_data(page, query<?= admin_has_permission() ? ', getFilters()' : ''; ?>);
        });
            
    });
</script>