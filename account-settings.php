<?php

  // CONNECTION TO DATABASE
    require_once("../connection/conn.php");
    if (!cadminIsLoggedIn()) {
        cadminLoginErrorRedirect();
    }

    // REQUIREMENT OF EXTERNAL FILES
    include ('includes/header.inc.php');
    include ('includes/top-nav.inc.php');
    include ('includes/left-nav.inc.php');

    $errors = '';
    if (isset($_POST["submit_settings"])) {
        if (empty($_POST['email']) || empty($_POST['fname']) || empty($_POST['lname'])) {
            $errors = 'Fill out all empty fileds';
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors = 'The email you provided is not valid';
        }

        if (!empty($errors)) {
            $errors;
        } else {
            $data = [
                ':cfname' => sanitize($_POST['fname']),
                ':clname' => sanitize($_POST['lname']),
                ':cemail' => sanitize($_POST['email']),
                ':c_aid' => $admin_id
            ];
            $query = "
                UPDATE puubu_admin 
                SET cfname = :cfname, clname = :clname, cemail = :cemail
                WHERE admin_id = :c_aid
            ";
            $statement = $conn->prepare($query);
            $result = $statement->execute($data);
            if (isset($result)) {
                $log_message = "admin ['" . $admin_id . "'], profile updated!";
                add_to_log($log_message, $admin_id, 'admin');

                $_SESSION['flash_success'] = 'Admin\'s profile has been <span class="bg-info">Updated</span></div>';
                redirect(ADROOT . 'settings');
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
                        <i class="fs-2" data-duoicon="app"></i>
                    </div>
                </div>
                <div class="col">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="javascript:;">Profile</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Profile</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">Profile</h1>
                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                    <!-- Action -->
                <!-- <a class="btn btn-secondary d-block" href="javascript:;">
                    <span class="material-symbols-outlined me-1">export_notes</span> Export
                </a> -->
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
                                        <div class="text-body-secondary">Profile details.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-lg">
                                <div class="row gx-3  ">
                                    <div class="col col-lg-auto ms-auto">
                                    </div>

                                    <div class="col-auto">
                                        <a class="btn btn-dark px-3" href="<?= ADROOT; ?>">
                                            Dashboard
                                        </a>
                                    </div>

                                    <div class="col-auto ms-n2">
                                        <a class="btn btn-dark px-3" href="<?= goBack(); ?>">
                                            Go back
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div>
<?php
 
    if (isset($_GET['cp']) && $_GET['cp'] == 1) {

        $errors = '';
        $hashed = $admin_data['ckey'];
        $old_password = ((isset($_POST['old_password']))?sanitize($_POST['old_password']):'');
        $old_password = trim($old_password);
        $password = ((isset($_POST['password']))?sanitize($_POST['password']):'');
        $password = trim($password);
        $confirm = ((isset($_POST['confirm']))?sanitize($_POST['confirm']):'');
        $confirm = trim($confirm);
        $new_hashed = password_hash($password, PASSWORD_BCRYPT);

        if (isset($_POST['edit_pasword'])) {
            if (empty($_POST['old_password']) || empty($_POST['password']) || empty($_POST['confirm'])) {
                $errors = 'You must fill out all fields';
            } else {

                if (strlen($password) < 6) {
                    $errors = 'Password must be at least 6 characters';
                }

                if ($password != $confirm) {
                    $errors = 'The new password and confirm new password does not match.';
                }

                if (!password_verify($old_password, $hashed)) {
                    $errors = 'Your old password does not our records.';
                }
            }

            if (!empty($errors)) {
                $errors;
            } else {
                $query = '
                    UPDATE puubu_admin 
                    SET ckey = :ckey 
                    WHERE admin_id = :c_aid
                ';
                $satement = $conn->prepare($query);
                $result = $satement->execute(
                    array(
                        ':ckey' => $new_hashed,
                        ':c_aid' => $admin_id
                    )
                );
                if (isset($result)) {
                    $log_message = "admin ['" . $admin_id . "'], password changed!";
                    add_to_log($log_message, $admin_id, 'admin');

                    $_SESSION['flash_success'] = 'Password successfully <span class="bg-info">UPDATED</span></div>';
                    redirect(ADROOT . 'details');
                }
            }
        }

?>


 <!-- Industry news -->
    <div class="card mb-6 mb-xxl-0">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="fs-6 mb-0">Change password</h3>
                </div>
                <div class="col-auto my-n3 me-n3">
                    <a class="btn btn-link" href="<?= ADROOT; ?>details">
                        Profile
                        <span class="material-symbols-outlined">arrow_right_alt</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body py-3">
            <form method="POST" action="settings.php?cp=1" id="edit_passwordForm">
                <span class="text-danger lead"><?= $errors; ?></span>
                <div class="mb-3">
                    <label for="old_password" class="form-label">Old password</label>
                    <input type="password" class="form-control" name="old_password" id="old_password" value="<?= $old_password; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New password</label>
                    <input type="password" class="form-control" name="password" id="password" value="<?= $password; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="confirm" class="form-label">Confirm new password</label>
                    <input type="password" class="form-control" name="confirm" id="confirm" value="<?= $confirm; ?>" required>
                </div>
                <button type="submit" class="btn btn-outline-warning" name="edit_pasword" id="edit_pasword">Change Password</button>&nbsp;
                <a href="details" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>

<?php } else { ?>

    <!-- Industry news -->
    <div class="card mb-6 mb-xxl-0">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="fs-6 mb-0">Update profile</h3>
                </div>
                <div class="col-auto my-n3 me-n3">
                    <a class="btn btn-link" href="<?= ADROOT; ?>settings?cp=1">
                        Change password
                        <span class="material-symbols-outlined">arrow_right_alt</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body py-3">
            <form method="POST" action="" id="settingsForm">
                <span class="text-danger lead"><?= $errors; ?></span>
                <div class="mb-3">
                    <label for="fname" class="form-label">First Name</label>
                    <input type="text" class="form-control-sm form-control form-control-dark" name="fname" id="fname" value="<?= ((isset($_POST["fname"]))?sanitize($_POST["fname"]):$admin_data["cfname"]); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="lname" class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="lname" id="lname" value="<?= ((isset($_POST["lname"]))?sanitize($_POST["lname"]):$admin_data["clname"]); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="email" value="<?= ((isset($_POST["email"]))?sanitize($_POST["email"]):$admin_data["cemail"]); ?>" required>
                </div>
                <button type="submit" class="btn btn-dark" name="submit_settings" id="submit_settings">Update</button>&nbsp;
                <a href="details" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>

<?php } include ('includes/footer.inc.php');?>
