<?php
    
    require ('../system/DatabaseConnector.php');
    if (user_is_logged_in()) {
        redirect(PROOT . 'app/');
    }
    
    $title = 'Account - Sign In | ';
    $body_class = "";
    require ('../system/inc/head.php');

    $email = ((isset($_POST['email'])) ? sanitize($_POST['email']) : '');
    $email = trim($email);
    $password = ((isset($_POST['password'])) ? sanitize($_POST['password']) :'');
    $password = trim($password);
    $errors = '';
    if (isset($_POST['submit_login'])) {

        // Storing google recaptcha response
        // in $recaptcha variable
        $recaptcha = (isset($_POST['g-recaptcha-response'])) ? $_POST['g-recaptcha-response'] : '';
    
        // Hitting request to the URL, Google will
        // respond with success or error scenario
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . RECAPTCHA_SITE_KEY_SECRETE . '&response=' . $recaptcha;
    
        // Making request to verify captcha
        $response = file_get_contents($url);
    
        // Response return by google is in
        // JSON format, so we have to parse
        // that json
        $response = json_decode($response);
    
        // Checking, if response is true or not
        if ($response->success == true) {
        
            if (empty($_POST['email']) || empty($_POST['password'])) {
                $errors = '<div class="alert alert-danger" role="alert">You must provide email and password</div>';
            }
    
            // 
            $query = "
                SELECT * FROM levina_users 
                WHERE user_email = :user_email 
                LIMIT 1
            ";
            $statement = $dbConnection->prepare($query);
            $statement->execute(
                array(
                    ':user_email' => $email
                )
            );
            if ($statement->rowCount() < 1) {
                $errors = '<div class="alert alert-danger" role="alert">That email does\'nt exist in our database!</div>';
            } else {
                foreach ($statement->fetchAll() as $row) {
                    if ($row['user_verified'] != 1) {
                        redirect(PROOT . 'auth/resend-vericode');
                    } else {
                        $code = rand(100000, 999999); // Or use a more secure generator
                        $_SESSION['LVNLC'] = $code;
                        $_SESSION['LVE'] = $email;

                        $name = ucwords($row['user_fullname']);
                        $to = $email;
                        $subject = "Sign in code 🤞.";
                        $body = "
                            <h3>
                                {$name},</h3>
                                <p>
                                    Your sign in code is: <strong>{$code}</strong>
                                    <br><br>
                                    Best regards,
                                    <br>
                                    - Leviana, Namibra Inc. 🤞
                                </p>
                        ";
                        $mail_result = send_email($name, $to, $subject, $body);
                        if ($mail_result) {

                            $query = "
                                INSERT INTO levina_user_login_details (login_detail_id, login_detail_code, login_detail_user_id) 
                                VALUES (?, ?, ?)
                            ";
                            $statement = $dbConnection->prepare($query);
                            $statement->execute([guidv4(), $code, $row['user_id']]);

                            $_SESSION['flash_success'] = 'Sign in code sent to your email 👨‍💻.';
                            redirect(PROOT . 'auth/signin-vericode');
                        } else {
                            $errors = '<div class="alert alert-danger" role="alert">Error sending email!</div>';
                        }
                    }
                }
            }
    
        } else {
            $errors = '<div class="alert alert-danger" role="alert">Error in Google reCAPTACHA!</div>';
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
