<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
	// if (!admin_is_logged_in()) {
	// 	admin_login_redirect();
	// }

    if (!admin_is_logged_in()) {
        redirect(PROOT . 'auth/sign-in');
    }

    $title = 'New customer | ';
    $body_class = '';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');

    //
    function email_exist($email, $edit_id = null) {
        global $dbConnection;
        $query = "SELECT * FROM customers WHERE customer_email = ? AND customer_id != '" . $edit_id . "' LIMIT 1";
        $statement = $dbConnection->prepare($query);
        $statement->execute([$email]);
        return $statement->rowCount() > 0;
    }
    
    //
    function phone_exist($phone, $edit_id = null) {
        global $dbConnection;
        $query = "SELECT customer_phone FROM customers WHERE customer_phone = ? AND customer_id != '" . $edit_id . "' LIMIT 1";
        $statement = $dbConnection->prepare($query);
        $statement->execute([$phone]);
        return $statement->rowCount() > 0;
    }

    $error = '';
    $post = cleanPost($_POST);

    // Collect and sanitize input
    $account_number = $post['account_number'] ?? '';
    $name     = $post['name'] ?? '';
    $email    = $post['email'] ?? null;
    $phone    = $post['phone'] ?? null;
    $address  = $post['address'] ?? '';
    $region   = $post['region'] ?? '';
    $city     = $post['city'] ?? '';
    $amount   = $post['amount'] ?? $settings['default_saving_amount'] ?? 2.00;
    $target   = $post['target'] ?? '';
    $duration = $post['duration'] ?? null;
    $startdate = $post['startdate'] ?? null;
    $idcard    = $post['idcard'] ?? '';
    $idnumber  = $post['idnumber'] ?? '';
    // $collector = $post['collector'] ?? '';

    if (isset($_GET['edit']) && !empty($_GET['edit'])) {
        $edit_id = sanitize($_GET['edit']);

        $edit_row = findCustomerByID($edit_id);
        if ($edit_row) {
            $account_number = $post['account_number'] ?? $edit_row->customer_account_number;
            $name     = $post['name'] ?? $edit_row->customer_name;
            $email    = $post['email'] ?? $edit_row->customer_email;
            $phone    = $post['phone'] ?? $edit_row->customer_phone ;
            $address  = $post['address'] ?? $edit_row->customer_address;
            $region   = $post['region'] ?? $edit_row->customer_region;
            $city     = $post['city'] ?? $edit_row->customer_city;
            $amount   = $post['amount'] ?? $edit_row->customer_default_daily_amount;
            $target   = $post['target'] ?? $edit_row->customer_target;
            $duration = $post['duration'] ?? $edit_row->customer_duration;
            $startdate = $post['startdate'] ?? $edit_row->customer_start_date;
        } else {
            redirect(PROOT . 'app/customers');
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //dnd($post['startdate']);
        $required = array('name', 'phone', 'address', 'region', 'city', 'amount', 'startdate');
        foreach ($required as $f) {
            if (empty($f)) {
                $errors = ucfirst($f) . ' is required !';
                break;
            }
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        }
        
        // check if email or phone number already exist
        if (!empty($email) && email_exist($email, ((isset($_GET['edit']) ? $edit_id : null)))) {
            $error = "Email address already exists !";
        }

        if (!empty($phone) && phone_exist($phone, ((isset($_GET['edit']) ? $edit_id : null)))) {
            $error = "Phone number already exists !";
        }

        if (!$account_number) {
            $account_number = generateAccountNumber($dbConnection);
        } else {
            // check if entered account already exists or not.
            $a = $dbConnection->query("SELECT customer_account_number FROM customers WHERE customer_account_number = '" . $account_number . "' ORDER BY customer_account_number DESC LIMIT 1")->rowCount();
            if (isset($_GET['edit'])){
                $a = $dbConnection->query("SELECT customer_account_number FROM customers WHERE customer_account_number = '" . $account_number . "' AND customer_id != '" . $edit_id . "' ORDER BY customer_account_number DESC LIMIT 1")->rowCount();
            }
            if ($a > 0) {
                $error = "Entered account number already exist !";
            }
        }

        // check for minimum amount
        if ($amount < ($settings['default_saving_amount'] ?? 2.00)) {
            $error = "The minimum daily saving amount is GHS " . money($settings['default_saving_amount'] ?? 2.00) . " !";
        }

        if (isset($_GET['edit']) && $_GET['edit']) {

        } else {
            // Handle file upload if exists
            $front_photo_path = null;
            if (isset($_FILES['front_photo']) && $_FILES['front_photo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['front_photo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $upload_dir = '../assets/media/uploads/customers-media/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $filename = uniqid('customer_front_id_', true) . '.' . $ext;
                    $front_photo_path = $upload_dir . $filename;
                    move_uploaded_file($_FILES['front_photo']['tmp_name'], $front_photo_path);
                } else {
                    $error = "Invalid front photo id card file type.";
                }
            }
            
            // Handle file upload if exists
            $back_photo_path = null;
            if (isset($_FILES['back_photo']) && $_FILES['back_photo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['back_photo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $upload_dir = '../assets/media/uploads/customers-media/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $filename = uniqid('customer_back_id_', true) . '.' . $ext;
                    $back_photo_path = $upload_dir . $filename;
                    move_uploaded_file($_FILES['back_photo']['tmp_name'], $back_photo_path);
                } else {
                    $error = "Invalid back photo id card file type.";
                }
            }
        }

        if (!$error) {
            $conn = $dbConnection;
            if (isset($_GET['edit']) && !empty($_GET['edit'])) {
                $stmt = $conn->prepare("
                    UPDATE customers 
                    SET customer_account_number = ?, customer_name = ?, customer_phone = ?, customer_email = ?, customer_address = ?, customer_region = ?, customer_city = ?, customer_default_daily_amount = ?, customer_target = ?, customer_duration = ?, customer_start_date = ? 
                    WHERE customer_id = ?
                ");
                $result = $stmt->execute([
                    $account_number, $name, $phone, $email, $address, $region, $city, $amount, $target, $duration, $startdate, $edit_id
                ]);
                if ($result) {
                    $log_message = ucwords($added_by) . ' [' . $admin_id . '] updated customer ' . ucwords($name) . ' (' . $phone . ')';
                    add_to_log($log_message, $admin_id, $added_by);

                    $_SESSION['flash_success'] = "Customer updated successfully !";
                    redirect(PROOT . 'app/customers/' . $edit_id);
                } else {
                    $error = "Failed to update customer. Please try again !";
                }
            } else {
                $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:m:s"));
            
                // Insert into database
                $stmt = $conn->prepare("
                    INSERT INTO customers (customer_id, customer_account_number, customer_collector_id, customer_added_by, customer_name, customer_phone, customer_email, customer_address, customer_region, customer_city, customer_id_type, customer_id_number, customer_id_photo_front, customer_id_photo_back, customer_default_daily_amount, customer_target, customer_duration, customer_start_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $unique_id, $account_number, $admin_id, $added_by, $name, $phone, $email, $address, $region, $city, $idcard, $idnumber, $front_photo_path, $back_photo_path, $amount, $target, $duration, $startdate
                ]);
                if ($result) {
                    $log_message = ucwords($added_by) . ' [' . $admin_id . '] added new customer ' . ucwords($name) . ' (' . $phone . ')';
                    add_to_log($log_message, $admin_id, $added_by);

                    $_SESSION['flash_success'] = "Customer added successfully !";
                    redirect(PROOT . 'app/customers');
                } else {
                    $error = "Failed to add customer. Please try again !";
                }
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
                        <i class="fs-2" data-duoicon="user"></i>
                    </div>

                </div>
                <div class="col">

                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Customers</a></li>
                            <li class="breadcrumb-item active" aria-current="page">New customer</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">New customer</h1>

                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">

                    <!-- Action -->
                    <a class="btn btn-light w-100" href="<?= goBack(); ?>">
                        <span class="material-symbols-outlined me-1">arrow_back_ios</span> Go back
                    </a>

                </div>
            </div>

            <!-- Page content -->
            <div class="row">
                <div class="col">

                    <!-- Form -->
                    <form class="" id="new-customer-form" action="customer-new.php<?= ((isset($_GET['edit']) && !empty($_GET['edit'])) ? '?edit=' . $edit_id : ''); ?>" method="POST" enctype="multipart/form-data">
                        <p class="text-danger"><?= $error; ?></p>
                        <section class="card card-line bg-body-tertiary border-transparent mb-5">
                            <div class="card-body">
                                <h3 class="fs-5 mb-1">General</h3>
                                <p class="text-body-secondary mb-5">General information about the project.</p>
                                <hr>
                                <div class="mb-4">
                                    <label class="form-label" for="account_number">Account number</label>
                                    <input class="form-control bg-body" id="account_number" name="account_number" type="text" value="<?= $account_number; ?>" />
                                    <small class="form-text text-info">Only use this field if the customer is already having an acount number else leave it blank to generate one on its own.</small>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="name">Full name</label>
                                    <input class="form-control bg-body" id="name" name="name" type="text" value="<?= $name; ?>" required />
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" class="form-control bg-body mb-3" name="phone" id="phone" placeholder="(___)___-____" data-inputmask="'mask': '(999)999-9999'" value="<?= $phone; ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control bg-body" name="email" id="email" placeholder="name@company.com" value="<?= $email; ?>" />
                                </div>
                                <div class="row mb-0">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label" for="company">Address</label>
                                        <input class="form-control bg-body" id="address" name="address" type="text" value="<?= $address; ?>" required />
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label" for="company">Region</label>
                                        <input class="form-control bg-body" id="region" name="region" type="text" value="<?= $region; ?>" required />
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label" for="company">City</label>
                                        <input class="form-control bg-body" id="city" name="city" type="text" value="<?= $city; ?>" required />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="card card-line bg-body-tertiary border-transparent mb-5">
                            <div class="card-body">
                                <h3 class="fs-5 mb-1">Saving plan</h3>
                                <p class="text-body-secondary mb-5">General information about the project.</p>
                                <hr>
                                <div class="mb-4">
                                    <label class="form-label" for="amount">Daily amount</label>
                                    <input class="form-control bg-body" id="amount" name="amount" type="number" min="2" step="0.01" value="<?= $amount; ?>" required />
                                    <small class="form-text">Mininum GHS <?= money($settings['default_saving_amount'] ?? '2.00'); ?></small>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="target">Target</label>
                                    <input class="form-control bg-body" id="target" name="target" type="number" value="<?= $target; ?>" />
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="duration">Duration</label>
                                    <input class="form-control bg-body flatpickr-input" id="duration" name="duration" type="date" value="<?= $duration; ?>" data-flatpickr="" readonly="readonly" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="startdate">Start date</label>
                                    <input class="form-control bg-body bg-body flatpickr-input" id="startdate" name="startdate" type="text" data-flatpickr="" readonly="readonly" value="<?= $startdate; ?>" required>
                                </div>
                            </div>
                        </section>
                        <?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
                        <?php else: ?>
                        <section class="card bg-body-tertiary border-transparent mb-7">
                            <div class="card-body">
                                <h3 class="fs-5 mb-1">ID details</h3>
                                <p class="text-body-secondary mb-5">Starting files for the project.</p>
                                <hr>
                                <div class="mb-4">
                                    <label class="form-label" for="idcard">ID</label>
                                    <select class="form-control bg-body" id="idcard" name="idcard" type="text">
                                        <option value=""></option>
                                        <option value="ghana-card"<?= (($idcard == 'ghana-card') ? 'selected' : ''); ?>>Ghana Card</option>
                                        <option value="driver-licence"<?= (($idcard == 'driver-licence') ? 'selected' : ''); ?>>Driver Licence</option>
                                        <option value="voters-id-card"<?= (($idcard == 'voters-id-card') ? 'selected' : ''); ?>>Voters ID card</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="idnumber">ID Number</label>
                                    <input class="form-control bg-body" id="idnumber" name="idnumber" type="text" value="<?= $idnumber; ?>" />
                                </div>
                                <div class="row mb-4">
                                    <div class="col">
                                        <div class="mb-0">
                                            <label for="front_photo">Front card</label>
                                            <input class="form-control" id="front_photo" name="front_photo" type="file" />
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-0">
                                            <label for="back_photo">Back card</label>
                                            <input class="form-control" id="back_photo" name="back_photo" type="file" />
                                        </div>
                                    </div>
                                    <!-- <div class="col">
                                        <div class="mb-0">
                                            <label for="dropzone">Front card</label>
                                            <div class="form-text mt-0 mb-3">Attach files to this customer.</div>
                                            <div class="dropzone dz-clickable" id="dropzone"><div class="dz-default dz-message"><button class="dz-button" type="button">Drop files here to upload</button></div></div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-0">
                                            <label for="dropzone">Back Card</label>
                                            <div class="form-text mt-0 mb-3">Attach files to this customer.</div>
                                            <div class="dropzone dz-clickable" id="dropzone"><div class="dz-default dz-message"><button class="dz-button" type="button">Drop files here to upload</button></div></div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </section>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-secondary w-100" id="submit-customer">
                            Save customer
                        </button>
                        <button type="reset" class="btn btn-link w-100 mt-3">
                            Reset form
                        </button>
                    </form>

                </div>
            </div>
        </div>
    

<?php include ('../system/inc/footer.php'); ?>
<script>
    $(document).ready(function() {
        // 
        $('#new-customer-form').on('submit', function (e) {
            // e.preventDefault();

            $('#submit-customer').attr('disabled', true);
            $('#submit-customer').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Processing ...</span>');

            // Simulate a delay (e.g., AJAX call)
            setTimeout(function () {
                alert('Form submitted!');
                $('#submit-customer').html('Save customer');
                $('#submit-customer').attr('disabled', false);
            }, 2000);
        });
    });
</script>