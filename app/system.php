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

$title = 'Admins | ';
$body_class = '';
include ('../system/inc/head.php');
include ('../system/inc/modals.php');
include ('../system/inc/sidebar.php');
include ('../system/inc/topnav-base.php');
include ('../system/inc/topnav.php');

// Fetch settings
function getSystemSettings($dbConnection) {
    $sql = "SELECT * FROM system_settings LIMIT 1";
    $stmt = $dbConnection->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


// function updateSystemSettings($dbConnection, $data) {
//     $sql = "UPDATE system_settings SET 
//                 app_name = :app_name,
//                 app_logo = :app_logo,
//                 default_saving_amount = :default_saving_amount,
//                 company_email = :company_email,
//                 company_phone = :company_phone,
//                 company_address = :company_address,
//                 currency_symbol = :currency_symbol,
//                 updated_by = :updated_by
//             WHERE id = 1";
    
//     $stmt = $dbConnection->prepare($sql);
//     return $stmt->execute([
//         ':app_name' => $data['app_name'],
//         ':app_logo' => $data['app_logo'],
//         ':default_saving_amount' => $data['default_saving_amount'],
//         ':company_email' => $data['company_email'],
//         ':company_phone' => $data['company_phone'],
//         ':company_address' => $data['company_address'],
//         ':currency_symbol' => $data['currency_symbol'],
//         ':updated_by' => $data['updated_by'] ?? null
//     ]);
// }

// Update settings
function updateSystemSettings($dbConnection, $data) {
    $sql = "UPDATE system_settings SET 
                app_name = :app_name,
                app_logo = :app_logo,
                default_saving_amount = :default_saving_amount,
                company_email = :company_email,
                company_phone = :company_phone,
                company_address = :company_address,
                currency_symbol = :currency_symbol,
                updated_by = :updated_by
            WHERE id = 1";
    
    $stmt = $dbConnection->prepare($sql);
    return $stmt->execute($data);
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_logo = $settings['app_logo'];

    // Handle file upload
    if (!empty($_FILES['app_logo']['name'])) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["app_logo"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["app_logo"]["tmp_name"], $targetFilePath)) {
            $app_logo = $targetFilePath;
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
        ':updated_by' => 1 // replace with logged-in admin id
    ];

    if (updateSystemSettings($dbConnection, $updateData)) {
        $message = '<div class="alert alert-success">Settings updated successfully!</div>';
        $settings = getSystemSettings($dbConnection); // Refresh data
    } else {
        $message = '<div class="alert alert-danger">Error updating settings.</div>';
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
                            <?php if (!isset($_GET['add']) || !$_GET['add']): ?>
                            <a class="btn btn-secondary d-block" href="<?= PROOT; ?>app/admins?add=1"> <span class="material-symbols-outlined me-1">add</span> New admin </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <a class="btn btn-light d-block" href="<?= goBack(); ?>"> Go back </a>
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
                                            <a class="nav-link bg-dark active" aria-current="page" href="<?= PROOT; ?>app/admins">All admins <?= $admin_count; ?></a>
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
                                <form method="POST" action="<?= PROOT; ?>app/admins.php?add=1">
                                    <div class="text-danger"><?= $errors; ?></div>
                                    <div class="mb-3">
                                        <label for="admin_fullname" class="form-label">Full name</label>
                                        <input type="text" class="form-control" name="admin_fullname" id="admin_fullname" value="<?= $admin_fullname; ?>" required>
                                        <div class="text-sm text-muted">Enter full name in this field</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" name="admin_email" id="admin_email" value="<?= $admin_email; ?>" required>
                                        <div class="text-sm text-muted">Enter email in this field</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin_phone" class="form-label">Phone number</label>
                                        <input type="number" class="form-control" name="admin_phone" id="admin_phone" value="<?= $admin_phone; ?>" placeholder="(___)___-____" data-inputmask="'mask': '(999)999-9999'" required>
                                        <div class="text-sm text-muted">Enter phone numner in this field</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin_password" class="form-label">Password</label>
                                        <input type="password" class="form-control" name="admin_password" id="admin_password" value="<?= $admin_password; ?>" required>
                                        <div class="text-sm text-muted">Enter password in this field</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm" id="confirm" value="<?= $confirm; ?>" required>
                                        <div class="text-sm text-muted">Enter confirm new password in this field</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin_permissions" class="form-label">Permission</label>
                                        <select class="form-control" name="admin_permissions" id="admin_permissions" required>
                                            <option value=""<?= (($admin_permissions == '')?' selected' : '') ?>></option>
                                            <option value="approver"<?= (($admin_permissions == 'approver')?' selected' : '') ?>>Approver</option>
                                            <option value="admin,approver"<?= (($admin_permissions == 'admin,approver')?' selected' : '') ?>>Admin,  Approver</option>
                                        </select>
                                        <div class="text-sm text-muted">Select type of admin permission in this field</div>
                                    </div>
                                    <button type="submit" class="btn btn-dark" name="submit_admin" id="submit_admin">Add admin</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
    </div>

<?php include ('../system/inc/footer.php'); ?>
