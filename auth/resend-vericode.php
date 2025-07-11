<?php
    
    require ('../system/DatabaseConnector.php');
    if (user_is_logged_in()) {
        redirect(PROOT . 'app/');
    }
    $title = 'Account - Resend Vericode | ';
    $body_class = "";
    require ('../system/inc/head.php');

    $errors = '';
	if ($_POST) {
		// Storing google recaptcha response
		// in $recaptcha variable
		$recaptcha = $_POST['g-recaptcha-response'];

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

			$email = sanitize($_POST['email']);
			//validation
			if(empty($email)) {
				$errors = '<div class="alert alert-danger" role="alert">Email is required.</div>';
			}

			if(empty($errors)) {
				$sql = "
					SELECT * FROM levina_users 
					WHERE user_email = :user_email 
					AND user_verified = :user_verified
					AND user_trash = :user_trash
				";
				$statement = $dbConnection->prepare($sql);
				$statement->execute([
					':user_email' => $email, 
					':user_trash' => 0, 
					':user_verified' => 0
				]);
                $sub_rows = $statement->fetchAll();
                $sub_row = $sub_rows[0] ?? $sub_rows;

				if($statement->rowCount() > 0) {
					$vericode = md5(time());

					$name = ucwords($sub_row['user_fullname']);
					$to = $sub_row['user_email'];
					$subject = "Please Verify Your Account 😒.";
					$body = "
						<h3>
							{$name},</h3>
							<p>
								Thank you for registering. Please verify your account by clicking 
								<a href=\"https://sites.local/levina/auth/verified/{$vericode}\" target=\"_blank\">here</a>.
								<br>
								or copy and paste this link in your url: https://sites.local/levina/auth/verified/{$vericode}
								<br><br>
								Best regards,
								<br>
								- Leviana, Namibra Inc. 🤞
							</p>
					";
					$mail_result = send_email($name, $to, $subject, $body);
					if ($mail_result) {
						$sql = "
							UPDATE levina_users 
							SET user_vericode = :user_vericode 
							WHERE user_email = :user_email
						";
						$statement = $dbConnection->prepare($sql);
						$result = $statement->execute([
							':user_vericode' => $vericode,
							':user_email' => $email
						]);
						$_SESSION['flash_success'] = 'Check your email for re-verification link 🤞.';
						redirect(PROOT . 'auth/signin');
					} else {
						//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
						$errors = '<div class="alert alert-danger" role="alert">Message could not be sent... check your internet or try again later.</div>';

					}

				} else {
					$errors = '<div class="alert alert-danger" role="alert">Can not find user account.</div>';
				}
			}
		} else {
			$errors = '<div class="alert alert-danger" role="alert">Error in Google reCAPTACHA!</div>';
		}
	}

?>

    <!-- Page wrapper -->
    <main class="page-wrapper">
        <div class="d-flex flex-column align-items-center position-relative h-100 px-3 pt-5">

            <!-- Home button -->
            <a class="text-nav btn btn-icon bg-light border rounded-circle position-absolute top-0 end-0 z-2 p-0 mt-3 me-3 mt-sm-4 me-sm-4" href="<?= PROOT; ?>app" data-bs-toggle="tooltip" data-bs-placement="left" title="Back to home" aria-label="Back to home">
                <i class="ai-home"></i>
            </a>

            <!-- Content -->
            <div class="mt-auto" style="max-width: 700px;">
                <h1 class="pt-3 pb-2 pb-lg-3">Resend Verification Email</h1>
                <p class="pb-2">You must verify your account before logging in. Verify your account in three easy steps. This helps to keep your new password secure.
                <small class="fs-6 fst-italic">If you did not receive the verification email you may request a new verification email below.</small></p>
                <ul class="list-unstyled pb-2 pb-lg-0 mb-4 mb-lg-5">
                    <li class="d-flex mb-2">
                        <span class="text-primary fw-semibold me-2">1.</span>
                        Fill in your email address below.
                    </li>
                    <li class="d-flex mb-2">
                        <span class="text-primary fw-semibold me-2">2.</span>
                        We'll email you a temporary code (Please check your inbox and spam folders).
                    </li>
                    <li class="d-flex mb-2">
                        <span class="text-primary fw-semibold me-2">3.</span>
                        Use the code to change your password on our secure website.
                    </li>
                </ul>
                <div class="card border-0 bg-primary" data-bs-theme="dark">
                    <form class="card-body" method="POST">
						<?= $errors; ?>
                        <div class="mb-4">
                            <div class="position-relative">
                                <i class="ai-mail fs-lg position-absolute top-50 start-0 translate-middle-y text-light opacity-80 ms-3"></i>
                                <input class="form-control form-control-lg ps-5" type="email" placeholder="Email address" id="email" name="email" required>
                                </div>
                            </div>
                        <!-- div to show reCAPTCHA -->
                        <div class="g-recaptcha mb-3" data-sitekey="<?= RECAPTCHA_KEY; ?>"></div>
                            <button class="btn btn-light" type="submit">Resend verification email</button>
                        </form>
                    </div>
                </div>

                <!-- Copyright -->
                <p class="nav w-100 fs-sm pt-5 mt-auto mb-5" style="max-width: 700px;">
                    <span class="text-body-secondary">&copy; All rights reserved. Made by</span>
                    <a class="nav-link d-inline-block p-0 ms-1" href="https://nambra.io/" target="_blank" rel="noopener">Namibra Inc.</a>
                </p>
            </div>
        </div>

<!-- Google reCAPTCHA CDN -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php require('../system/inc/footer.php'); ?>