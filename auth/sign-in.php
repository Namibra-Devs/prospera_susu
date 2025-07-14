<?php
    
    require ('../system/DatabaseConnector.php');

    if (admin_is_logged_in()) {
        redirect(PROOT);
    }
    $title = 'Account - Sign In | ';

    $error = '';

    if (isset($_POST['submit_login'])) {
        if (empty($_POST['admin_email']) || empty($_POST['admin_password'])) {
            $error = 'You must provide email and password.';
        }
        $query = "
            SELECT * FROM giltmarket_admin 
            WHERE admin_email = ? 
            AND admin_status = ?
            LIMIT 1 
        ";
        $statement = $conn->prepare($query);
        $statement->execute([sanitize($_POST['admin_email']), 0]);
        $count_row = $statement->rowCount();
        $row = $statement->fetchAll();

        if ($count_row < 1) {
            $error = 'Unkown admin!';
        } else {
            if (!password_verify($_POST['admin_password'], $row[0]['admin_password'])) {
                $error = 'Unkown admin!';
            }
        }

        if (!empty($error)) {
            $_SESSION['flash_error'] = $error;
            redirect(PROOT . 'auth/login');
        } else {
            $admin_id = $row[0]['admin_id'];
            adminLogin($admin_id);
        }
        
    }
?>


    <!-- Page wrapper -->
    <main class="page-wrapper">
        <div class="d-lg-flex position-relative h-100">

        <!-- Home button -->
        <a class="text-nav btn btn-icon bg-light border rounded-circle position-absolute top-0 end-0 p-0 mt-3 me-3 mt-sm-4 me-sm-4" href="<?= PROOT; ?>index" data-bs-toggle="tooltip" data-bs-placement="left" title="Back to home" aria-label="Back to home">
            <i class="ai-home"></i>
        </a>

        <!-- Sign in form -->
        <div class="d-flex flex-column align-items-center w-lg-50 h-100 px-3 px-lg-5 pt-5">
            <div class="w-100 mt-auto" style="max-width: 526px;">
                <h1>Sign in to Levina</h1>
                <p class="pb-3 mb-3 mb-lg-4">Don't have an account yet?&nbsp;&nbsp;<a href="<?= PROOT; ?>auth/signup">Register here!</a></p>
                <form class="needs-validation" method="POST" novalidate>
                    <?= $errors; ?>
                    <div class="pb-3 mb-3">
                        <div class="position-relative">
                            <i class="ai-mail fs-lg position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                            <input class="form-control form-control-lg ps-5" type="email" name="email" id="email" placeholder="Email address" value="<?= $email; ?>" autofocus="on" required>
                        </div>
                    </div>
                    <!-- div to show reCAPTCHA -->
                    <div class="g-recaptcha mb-3" data-sitekey="<?= RECAPTCHA_KEY; ?>"></div>
                    <button class="btn btn-lg btn-primary w-100 mb-4" name="submit_login" id="submit" type="submit">Sign in</button>
                </form>
            </div>

            <!-- Copyright -->
            <p class="nav w-100 fs-sm pt-5 mt-auto mb-5" style="max-width: 526px;"><span class="text-body-secondary">&copy; All rights reserved. Made by</span><a class="nav-link d-inline-block p-0 ms-1" href="https://namibra.io/" target="_blank" rel="noopener">Namibra Inc.</a></p>
            </div>

            <!-- Cover image -->
            <div class="w-50 bg-size-cover bg-repeat-0 bg-position-center" style="background-image: url(<?= PROOT; ?>assets/media/cover.jpg);"></div>
        </div>

    <!-- Google reCAPTCHA CDN -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php require('../system/inc/footer.php'); ?>
