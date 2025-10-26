<?php 

// ADMINS

    require ('../system/DatabaseConnector.php');

// if not logged in
if (!admin_is_logged_in()) {
    admin_login_redirect();
}

// check for permissions
if (!admin_has_permission()) {
    admin_permission_redirect('index');
}

$title = 'System | ';
$body_class = '';
include ('../system/inc/head.php');
include ('../system/inc/modals.php');
include ('../system/inc/sidebar.php');
include ('../system/inc/topnav-base.php');
include ('../system/inc/topnav.php');

// Update settings
function updateSystemSettings($dbConnection, $data) {
    $sql = "
        UPDATE system_settings SET 
            app_name = :app_name,
            app_logo = :app_logo,
            default_saving_amount = :default_saving_amount,
            company_email = :company_email,
            company_phone = :company_phone,
            company_address = :company_address,
            currency_symbol = :currency_symbol,
            updated_by = :updated_by
        WHERE id = 1
    ";
    $stmt = $dbConnection->prepare($sql);
    return $stmt->execute($data);
}

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_logo = $settings['app_logo'];

    // Handle file upload
    if (!empty($_FILES['app_logo']['name'])) {
        $targetDir = "../assets/media/logo/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        // accept only certain file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $fileType = strtolower(pathinfo($_FILES["app_logo"]["name"], PATHINFO_EXTENSION));
        if (!in_array($fileType, $allowedTypes)) {
            $message = '<div class="alert alert-danger">Invalid file type for logo. Allowed types: ' . implode(', ', $allowedTypes) . '.</div>';
        }

        // file size limit (e.g., 10MB)
        if ($_FILES["app_logo"]["size"] > 10 * 1024 * 1024) {
            $message = '<div class="alert alert-danger">File size exceeds 2MB limit.</div>';
        }

        $fileName = time() . "_" . basename($_FILES["app_logo"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (($message == '' || $message == null || empty($message)) && move_uploaded_file($_FILES["app_logo"]["tmp_name"], $targetFilePath)) {

            // check if old logo exists and delete
            if ($settings['app_logo'] && file_exists('../' . $settings['app_logo'])) {
                unlink('../' . $settings['app_logo']);
            } 

            $app_logo = $targetFilePath;
            $app_logo = str_replace('../', '', $app_logo); // Store relative path
        }
    }

    $updateData = [
        ':app_name' => $_POST['app_name'], 
        ':app_logo' => $app_logo, 
        ':default_saving_amount' => $_POST['default_saving_amount'], 
        ':company_email' => $_POST['company_email'], 
        ':company_phone' => $_POST['company_phone'], 
        ':company_address' => $_POST['company_address'], 
        ':currency_symbol' => $_POST['currency_symbol'], 
        ':updated_by' => $admin_id
    ];

    if (($message == '' || $message == null || empty($message)) && updateSystemSettings($dbConnection, $updateData)) {
        // add to log 
        $message = "admin with id [" . $admin_id . "] has been updated system settings!";
        add_to_log($message, $admin_data['admin_id'], 'admin');

        $message = '<div class="alert alert-success">Settings updated successfully!</div>';
        $settings = getSystemSettings($dbConnection); // Refresh data
    } 
    // else {
    //     $message = '<div class="alert alert-danger">Error updating settings.</div>';
    // }
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
                    <div class="avatar avatar-xl rounded text-warning">
                        <i class="fs-2" data-duoicon="user"></i>
                    </div>
                </div>
                <div class="col">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">System</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">System</h1>
                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                    <!-- Action -->
                    <div class="row gx-2">
                        <div class="col-6 col-sm-auto">
                            <a class="btn btn-light d-block" href="<?= goBack(); ?>"> << Go back </a>
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
                                    <ul class="nav nav-pills">
                                        <li class="nav-item">
                                            <a class="nav-link bg-dark active" aria-current="page" href="<?= PROOT; ?>app/admins">App </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body d-flex flex-column">

                                <?= $message ?>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">App Name</label>
                                            <input type="text" name="app_name" class="form-control" value="<?= sanitize($settings['app_name'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Default Saving Amount (<?= sanitize($settings['currency_symbol']) ?>)</label>
                                            <input type="number" step="0.01" name="default_saving_amount" class="form-control" value="<?= sanitize($settings['default_saving_amount']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">App Logo</label>
                                            <input type="file" name="app_logo" class="form-control">
                                            <?php if ($settings['app_logo']): ?>
                                                <div class="mt-2">
                                                    <img src="<?= PROOT . sanitize($settings['app_logo']); ?>" alt="App Logo" height="60">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Currency Symbol</label>
                                            <input type="text" name="currency_symbol" class="form-control" value="<?= sanitize($settings['currency_symbol']); ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Company Email</label>
                                            <input type="email" name="company_email" class="form-control" value="<?= sanitize($settings['company_email'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Company Phone</label>
                                            <input type="text" name="company_phone" class="form-control" value="<?= sanitize($settings['company_phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company Address</label>
                                            <textarea name="company_address" class="form-control"><?= sanitize($settings['company_address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-warning px-4">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
    </div>

<?php include ('../system/inc/footer.php'); ?>
