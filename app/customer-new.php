<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
	if (!admin_is_logged_in()) {
		admin_login_redirect();
	}
    $body_class = '';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');

    //
    function email_exist($email) {
        global $dbConnection;
        $query = "SELECT customer_email FROM customers WHERE customer_email = ? LIMIT 1";
        $statement = $dbConnection->prepare($query);
        $statement->execute([$email]);
        return $statement->rowCount() > 0;
    }
    
    //
    function phone_exist($phone) {
        global $dbConnection;
        $query = "SELECT customer_phone FROM customers WHERE customer_phone = ? LIMIT 1";
        $statement = $dbConnection->prepare($query);
        $statement->execute([$phone]);
        return $statement->rowCount() > 0;
    }

    // fetch collectors
    // $collector_row = $dbConnection->query("SELECT * FROM collectors WHERE collector_status = 'active' ORDER BY collector_name ASC")->fetchAll();
    // $collector_options = '';
    // if ($collector_row) {
    //     $collector_options .= '
    //         <option value="' . $collector_row['collector_id'] . '">' . ucwords($collector_row["collector_name"]) . '</option>
    //     ';
    // }
    $error = '';
    $post = cleanPost($_POST);

    // Collect and sanitize input
    $name     = $post['name'] ?? '';
    $email    = $post['email'] ?? '';
    $phone    = $post['phone'] ?? '';
    $address  = $post['address'] ?? '';
    $region   = $post['region'] ?? '';
    $city     = $post['city'] ?? '';
    $amount   = $post['amount'] ?? '';
    $target   = $post['target'] ?? '';
    $duration = $post['duration'] ?? '';
    $startdate = $post['startdate'] ?? '';
    $idcard    = $post['idcard'] ?? '';
    $idnumber  = $post['idnumber'] ?? '';
    $collector = $post['collector'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Validate required fields
        if (!$name || !$phone || !$address || !$region || !$city || !$amount || !$startdate) {
            $error = "All fields are required.";
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        }
        
        // check if email or phone number already exist
        if (email_exist($email)) {
            $error = "Email address already exists.";
        }

        if (phone_exist($phone)) {
            $error = "Phone number already exists.";
        }

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

        if (!$error) {
            // Hash password
            $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:m:s"));
            $conn = $dbConnection;
            // Insert into database
            $stmt = $conn->prepare("
                INSERT INTO customers (customer_id, customer_account_number, customer_collector_id, customer_added_by, customer_name, customer_phone, customer_email, customer_address, customer_region, customer_city, customer_id_type, customer_id_number, customer_id_photo_front, customer_id_photo_back, customer_default_daily_amount, customer_target, customer_duration, customer_start_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $unique_id, generateAccountNumber($dbConnection), $added_by_id, $added_by, $name, $phone, $email, $address, $region, $city, $idcard, $idnumber, $front_photo_path, $back_photo_path, $amount, $target, $duration, $startdate
            ]);
            if ($result) {
                $log_message = ucwords($added_by) . ' [' . $added_by_id . '] added new customer ' . ucwords($name) . ' (' . $phone . ')';
                add_to_log($log_message, $added_by_id, $added_by);

                $_SESSION['flash_success'] = "Customer added successfully!";
                redirect(PROOT . 'app/customers');
            } else {
                $error = "Failed to add collector. Please try again.";
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
                        Go back
                    </a>

                </div>
            </div>

            <!-- Page content -->
            <div class="row">
                <div class="col">

                    <!-- Form -->
                    <form class="" id="new-customer-form" method="POST" enctype="multipart/form-data">

                        <section class="card card-line bg-body-tertiary border-transparent mb-5">
                            <div class="card-body">
                                <h3 class="fs-5 mb-1">General</h3>
                                <p class="text-body-secondary mb-5">General information about the project.</p>
                                <hr>
                                <div class="mb-4">
                                    <label class="form-label" for="name">Full name</label>
                                    <input class="form-control bg-body" id="name" name="name" type="text" value="<?= $name; ?>" required />
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" class="form-control bg-body mb-3" name="phone" id="phone" placeholder="(___)___-____"
                                    data-inputmask="'mask': '(999)999-9999'" value="<?= $phone; ?>" required>
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
                                    <input class="form-control bg-body" id="amount" name="amount" type="number" min="10" value="<?= $amount; ?>" required />
                                    <small class="form-text">Mininum GHS 10.00</small>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="target">Target</label>
                                    <input class="form-control bg-body" id="target" name="target" type="number" value="<?= $target; ?>" />
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="duration">Duration</label>
                                    <input class="form-control bg-body" id="duration" name="duration" type="date" value="<?= $duration; ?>" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="startdate">Start date</label>
                                    <input class="form-control bg-body bg-body flatpickr-input" id="startdate" name="startdate" type="text" data-flatpickr="" readonly="readonly" value="<?= $startdate; ?>" required>
                                </div>
                            </div>
                        </section>
                        
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