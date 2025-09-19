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
            
            // check if email or phone number already exist

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
                    $_SESSION['flash_success'] = "Collector added successfully!";
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
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Customer</a></li>
                            <li class="breadcrumb-item active" aria-current="page">New customer</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">New customer</h1>

                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">

                    <!-- Action -->
                    <button class="btn btn-light w-100" href="<?= goBack(); ?>">
                        Go back
                    </button>

                </div>
            </div>

            <!-- Page content -->
            <div class="row">
                <div class="col">

                    <!-- Form -->
                    <form class="" id="new-customer-form" method="POST" enctype="multipart/form-data">
                        <?php if ($error): ?>
                        <div class="alert alert-danger" id="temporary"><?= $error ?></div>
                        <?php endif; ?>
                        <div class="mb-4">
                            <label class="form-label" for="name">Full name</label>
                            <input class="form-control" id="name" name="name" type="text" value="<?= $name; ?>" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" id="email" name="email" type="email" value="<?= $email; ?>" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="text" class="form-control mb-3" id="phone" name="phone" placeholder="(___)___-____" data-inputmask="'mask': '(999)999-9999'" required value="<?= $phone; ?>" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="address">Address</label>
                            <input class="form-control" id="address" name="address" type="text" value="<?= $address; ?>" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="region">Region</label>
                            <input class="form-control" id="region" name="region" value="<?= $region; ?>" type="text" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="city">City</label>
                            <input class="form-control" id="city" name="city" value="<?= $city; ?>" type="text" required />
                        </div>
                        <!-- <div class="mb-4">
                            <label class="form-label mb-0" for="tiptapExample">About</label>
                            <div class="form-text mt-0 mb-3">
                                A brief description of the customer.
                            </div>
                            <di class="form-control" id="tiptapExample"></di>
                        </div> -->
                        <div class="mb-4">
                            <label class="form-label" for="city">ID type</label>
                            <input class="form-control" id="city" name="city" value="<?= $city; ?>" type="text" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="city">ID number</label>
                            <input class="form-control" id="city" name="city" value="<?= $city; ?>" type="text" required />
                        </div>
                        <div class="mb-7">
                            <label for="dropzone">ID photo</label>
                            <input class="form-control" id="photo" name="photo" type="file" />
                        </div>
                        <!-- <div class="mb-7">
                            <label for="dropzone">Photo</label>
                            <div class="form-text mt-0 mb-3">
                                Attach photo to this collector.
                            </div>
                            <div class="dropzone" id="dropzone" name="dropzone"></div>
                        </div> -->
                        <div class="mb-4">
                            <label class="form-label" for="city">Default daily amount</label>
                            <input class="form-control" id="city" name="city" value="<?= $city; ?>" type="number" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password">Start date</label>
                            <input class="form-control" id="password" name="password" type="date" required />
                        </div>
                        <button type="submit" id="submit-customer" class="btn btn-secondary w-100">
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