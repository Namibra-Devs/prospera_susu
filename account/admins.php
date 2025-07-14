<?php 

// ADMINS

require_once ("../db_connection/conn.php");

// if not logged in
if (!admin_is_logged_in()) {
    admin_login_redirect();
}

// check for permissions
if (!admin_has_permission()) {
    admin_permission_redirect('index');
}
include ("../includes/header.inc.php");
include ("../includes/aside.inc.php");
include ("../includes/left.nav.inc.php");
include ("../includes/top.nav.inc.php");

$total_admins = $conn->query("SELECT * FROM giltmarket_admin WHERE admin_status = 0")->rowCount();
$admin_count = '';
if ($total_admins > 0) {
    $admin_count = '(' . $total_admins . ')';
}


// delete admin
if (isset($_GET['delete'])) {
    $admin_id = sanitize($_GET['delete']);

    $query = "
        UPDATE giltmarket_admin 
        SET admin_status = ? 
        WHERE admin_id = ?
    ";
    $statement = $conn->prepare($query);
    $result = $statement->execute([1, $admin_id]);
    if (isset($result)) {

        $message = "delete an admin with id " . $admin_id . "";
        add_to_log($message, $admin_data['admin_id']);

        $_SESSION['flash_success'] = 'Admin has been deleted!';
        redirect(PROOT . "account/admins");
    } else {
        echo js_alert("Something went wrong!");
        redirect(PROOT . "account/admins");
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
                INSERT INTO `giltmarket_admin`(`admin_id`, `admin_fullname`, `admin_email`, `admin_phone`, `admin_password`, `admin_permissions`) 
                VALUES (?, ?, ?, ?, ?, ?)
            ";
            $statement = $conn->prepare($query);
            $result = $statement->execute($data);
            if (isset($result)) {

                $message = "added new admin ".ucwords($admin_fullname)." as a ".strtoupper($admin_permissions)."";
                add_to_log($message, $admin_data['admin_id']);

                $_SESSION['flash_success'] = 'Admin has been Added!';
                redirect(PROOT . "account/admins");
            } else {
                echo js_alert("Something went wrong!");
                redirect(PROOT . "account/admins?add=1");
            }
        }
    }
}


?>


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
                <h1 class="fs-4 mb-0">Admins</h1>
            </div>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <div class="row gx-2">
                    <div class="col-6 col-sm-auto">
                        <?php if (!isset($_GET['add']) || !$_GET['add']): ?>
                        <a class="btn btn-secondary d-block" href="<?= PROOT; ?>account/admins?add=1"> <span class="material-symbols-outlined me-1">add</span> New admin </a>
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
                                        <a class="nav-link bg-dark active" aria-current="page" href="<?= PROOT; ?>account/admins">All admins <?= $admin_count; ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    

    <?php if (isset($_GET['add']) && !empty($_GET['add'])): ?>

        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <form method="POST" action="<?= PROOT; ?>account/admins.php?add=1">
                            <div class="text-danger"><?= $errors; ?></div>
                            <div class="mb-3">
                                <label for="admin_fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="admin_fullname" id="admin_fullname" value="<?= $admin_fullname; ?>" required>
                                <div class="text-sm text-muted">Enter full name in this field</div>
                            </div>
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="admin_email" id="admin_email" value="<?= $admin_email; ?>" required>
                                <div class="text-sm text-muted">Enter email in this field</div>
                            </div>
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Phone number</label>
                                <input type="number" class="form-control" name="admin_phone" id="admin_phone" value="<?= $admin_phone; ?>" required>
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
                                    <option value="salesperson"<?= (($admin_permissions == 'salesperson')?' selected' : '') ?>>Salesperson</option>
                                    <option value="supervisor"<?= (($admin_permissions == 'supervisor')?' selected' : '') ?>>Supervisor</option>
                                    <option value="admin,supervisor,salesperson"<?= (($admin_permissions == 'admin,supervisor,salesperson')?' selected' : '') ?>>Admin,  Supervisor, Salesperson</option>
                                </select>
                                <div class="text-sm text-muted">Select type of admin permission in this field</div>
                            </div>
                            <button type="submit" class="btn btn-dark" name="submit_admin" id="submit_admin">Add admin</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
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
                        <?= get_all_admins(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif ?>



</div>

<?php include ("../includes/footer.inc.php"); ?>
