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

$total_admins = $dbConnection->query("SELECT * FROM susu_admins WHERE admin_status = 'inactive' AND admin_permissions != 'collector'")->rowCount();
$admin_count = '';
if ($total_admins > 0) {
    $admin_count = '(' . $total_admins . ')';
}


// delete admin
if (isset($_GET['restore'])) {
    $admin_id = sanitize($_GET['restore']);

    $query = "
        UPDATE susu_admins 
        SET admin_status = ? 
        WHERE admin_id = ?
    ";
    $statement = $dbConnection->prepare($query);
    $result = $statement->execute(['active', $admin_id]);
    if (isset($result)) {

        $message = "admin with id [" . $admin_id . "] has been actived!";
        add_to_log($message, $admin_data['admin_id'], 'admin');

        $_SESSION['flash_success'] = 'Admin has been inactivated!';
        redirect(PROOT . "app/admins");
    } else {
        $_SESSION['flash_success'] = "Something went wrong!";
        redirect(PROOT . "app/admins");
    }
}

// add an admin
if (isset($_GET['add'])) {
    $errors = '';
    $admin_fullname = ((isset($_POST['admin_fullname'])) ? sanitize($_POST['admin_fullname']) : '');
    $admin_email = ((isset($_POST['admin_email'])) ? sanitize($_POST['admin_email']) : '');
    $admin_phone = ((isset($_POST['admin_phone'])) ? sanitize($_POST['admin_phone']) : '');
    $admin_password = ((isset($_POST['admin_password'])) ? sanitize($_POST['admin_password']) : '');
    $confirm = ((isset($_POST['confirm']))? sanitize($_POST['confirm']) : '');
    $admin_permissions = ((isset($_POST['admin_permissions']))? sanitize($_POST['admin_permissions']) : '');
    $admin_id = guidv4();

    if ($_POST) {
        $required = array('admin_fullname', 'admin_email', 'admin_phone', 'admin_password', 'confirm', 'admin_permissions');
        foreach ($required as $f) {
            if (empty($f)) {
                $errors = 'You must fill out all fields!';
                break;
            }
        }

        if (strlen($admin_password) < 6) {
            $errors = 'The password must be at least 6 characters!';
        }

        if ($admin_password != $confirm) {
            $errors = 'The passwords do not match!';
        }

        if (!empty($errors)) {
            $errors;
        } else {
            $data = array($admin_id, $admin_fullname, $admin_email, $admin_phone, password_hash($admin_password, PASSWORD_BCRYPT), $admin_permissions);
            $query = "
                INSERT INTO `susu_admins`(`admin_id`, `admin_name`, `admin_email`, `admin_phone`, `admin_password`, `admin_permissions`) 
                VALUES (?, ?, ?, ?, ?, ?)
            ";
            $statement = $dbConnection->prepare($query);
            $result = $statement->execute($data);
            if (isset($result)) {

                $message = "added new admin ".ucwords($admin_fullname)." as a ".strtoupper($admin_permissions)."";
                add_to_log($message, $admin_data['admin_id'], 'admin');

                $_SESSION['flash_success'] = 'Admin has been Added!';
                redirect(PROOT . "app/admins");
            } else {
                $_SESSION['flash_success'] = "Something went wrong!";
                redirect(PROOT . "app/admins?add=1");
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
                    <div class="avatar avatar-xl rounded text-warning">
                        <i class="fs-2" data-duoicon="user"></i>
                    </div>
                </div>
                <div class="col">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Admins</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0 text-danger">Archive Admins</h1>
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
    
                <div class="card mb-6">
                    <div class="table-responsive">
                        <table class="table table-selectable align-middle mb-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Admin</th>
                                    <th>Permission</th>
                                    <th>Phone</th>
                                    <th>Joined Date</th>
                                    <th>Last Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?= get_all_admins('inactive'); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
    </div>

<?php include ('../system/inc/footer.php'); ?>
