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
                SELECT * FROM collectors 
                WHERE collectors.collector_id = ?
                LIMIT 1
            ";
            $statement = $dbConnection->prepare($query);
            $statement->execute([$id]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return (($row['collector_name']) ? ucwords($row['collector_name']) . ' <span class="badge bg-warning-subtle text-warning">Collector</span>': 'Collector');
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



                //
                function getCustomerSavingsByWindow($customer_id, $window = 1) {
                    global $dbConnection;

                    // each window = 31 days
                    $limit = 31;
                    $offset = ($window - 1) * $limit;

                    // fetch savings ordered by saving_date_collected
                    $sql = "SELECT saving_date_collected, saving_amount
                            FROM savings 
                            WHERE saving_customer_id = ?
                            ORDER BY saving_date_collected ASC
                            LIMIT $limit OFFSET $offset";
                    $stmt = $dbConnection->prepare($sql);
                    $stmt->execute([$customer_id]);

                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }



                $window = isset($_GET['window']) ? (int)$_GET['window'] : 1;
                $savings = getCustomerSavingsByWindow($view, $window);

                // put savings dates in array for quick lookup
                $savedDays = [];
                foreach ($savings as $row) {
                    $day = date('j', strtotime($row['saving_date_collected'])); // day number
                    $savedDays[$day] = true;
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
                                    <div class="fs-4 fw-semibold"><?= pretty_date_notime($customer_data['customer_start_date']); ?></div>
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
                <h2 class="mb-3">Savings Calendar (Window <?= $window ?>)</h2>
                <div class="d-flex justify-content-between mb-3">

                    <?php if ($window > 1): ?>
                        <a href="?window=<?= $window - 1 ?>" class="btn btn-outline-link">Previous 31 Days</a>
                    <?php endif; ?>
                    <a href="?window=<?= $window + 1 ?>" class="btn btn-outline-link">Next 31 Days</a>

                    <div class="row row-cols-7 g-2">
                        <?php for ($day = 1; $day <= 31; $day++): ?>
                            <?php
                                $classes = "p-3 border text-center";
                                $label = $day;

                                // Commission = first day of window
                                if ($day == 1) {
                                    $classes .= " bg-warning text-dark fw-bold"; 
                                    $label .= "<br><small>(Commission)</small>";
                                } 
                                // Saved = green
                                elseif (isset($savedDays[$day])) {
                                    $classes .= " bg-success text-white";
                                } 
                                // Not saved yet = gray
                                else {
                                    $classes .= " bg-light";
                                }
                            ?>
                            <div class="col">
                                <div class="<?= $classes ?>" style="border-radius:8px;">
                                    <?= $label ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                </div>




                    <!-- <button class="btn btn-link" id="prevCycle">← Previous</button>
                    <h5 id="cycleLabel" class="mb-0"></h5>
                    <button class="btn btn-link" id="nextCycle">Next →</button> -->
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
                                <td><?= pretty_date_notime($save['created_at']); ?></td>
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
                        <a class="btn btn-secondary d-block" href="<?= PROOT; ?>app/customer-new">
                        <span class="material-symbols-outlined me-1">add</span> New customer
                    </a>
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

<script>
    let currentCycle = 0;
    const customerId = "<?= $view; ?>";
    let lastData = null;

    function loadCalendar(cycle) {
        $.getJSON("<?= PROOT; ?>app/controller/customer.calendar.php", { customer_id: customerId, cycle: cycle }, function (data) {
            $("#calendar").empty(); // Clear existing calendar
            lastData = data; // Store the last fetched data

            if (!data.cycle_start) {
                $("#cycleLabel").text("No savings yet");
                return;
            }

            $("#cycleLabel").text(
                "Cycle " + (cycle + 1) + ": " + data.cycle_start + " → " + data.cycle_end
            );

            for (let day = 1; day <= 31; day++) {
                let cellClass = "not-saved";
                let text = day;

                if (data.saved_days[day]) {
                    cellClass = "saved";
                    text = day + " ✓ <br>";
                    // Show amount saved if any
                    text += `\nGHS ${data.saved_days[day]}`;
                }

                if (data.commission_day === day) {
                    cellClass = "commission";
                    text = day + " (Fee) <br>";
                    // If there's also a saving on this day, show both
                    if (data.saved_days[day]) {
                        text += `\nGHS ${data.saved_days[day]}`;
                    }
                }

                $("#calendar").append(
                    `<div class="day ${cellClass}" data-day="${day}">${text}</div>`
                );
            }
            // Add click handler to each cell
            $(".day").click(function () {
                let day = $(this).data("day");
                console.log(day);
                showDayDetails(day);
            });

        }).fail(function (xhr) {
            alert("Error: " + xhr.responseText);
        });
    }

    //
    function showDayDetails(day) {
        if (!lastData) return;
        console.log(day);
        let body = "";
        if (lastData.commission_day === day) {
            body = `<p><strong>Day ${day}</strong> is the <span class="text-danger">Commission Fee</span> day for the company.</p>`;
        } else if (lastData.saved_days[day]) {
            body = `
            <p><strong>Day:</strong> ${day}</p>
            <p><strong>Amount Saved:</strong> GHS ${lastData.saved_days[day]}</p>
            <p><strong>Date:</strong> ${calculateDate(lastData.cycle_start, day)}</p>
            `;
        } else {
            body = `<p>No savings recorded for <strong>Day ${day}</strong>.</p>`;
        }

        $("#modalBody").html(body);
        new bootstrap.Modal(document.getElementById("dayModal")).show();
    }

    //
    // helper: calculate actual date of a given day in the cycle
    function calculateDate(cycleStart, day) {
        let start = new Date(cycleStart);
        start.setDate(start.getDate() + (day - 1));
        return start.toISOString().split("T")[0];
    }

    $("#prevCycle").click(function () {
        if (currentCycle > 0) {
            currentCycle--;
            loadCalendar(currentCycle);
        }
    });

    $("#nextCycle").click(function () {
        currentCycle++;
        loadCalendar(currentCycle);
    });

    // Load first cycle
    loadCalendar(currentCycle);
</script>


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