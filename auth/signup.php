<?php
    
    require ('../system/DatabaseConnector.php');
    if (user_is_logged_in()) {
        redirect(PROOT . 'app/');
    }
    $title = 'Account - Sign Up | ';
    $body_class = "";
    require ('../system/inc/head.php');

    $fullname = ((isset($_POST['fullname']) && !empty($_POST['fullname'])) ? sanitize($_POST['fullname']) : '');
    $email = ((isset($_POST['email']) && !empty($_POST['email'])) ? sanitize($_POST['email']) : '');
    $phone = ((isset($_POST['phone']) && !empty($_POST['phone'])) ? sanitize($_POST['phone']) : '');
    $address = ((isset($_POST['address']) && !empty($_POST['address'])) ? sanitize($_POST['address']) : '');
    $terms = ((isset($_POST['terms']) && !empty($_POST['terms'])) ? sanitize($_POST['terms']) : '');
    $output = '';

    if ($_POST) {
        $cleanedPhone = sanitizeGhanaPhone($phone);

        $sql = "
            SELECT * FROM levina_users 
            WHERE (user_email = ? OR user_phone = ?) 
            AND user_trash = ?
        ";
		$statement = $dbConnection->prepare($sql);
		$statement->execute([$email, $cleanedPhone, 0]);

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

			if ($statement->rowCount() > 0) {
				$output = '<div class="alert alert-danger" role="alert">User email or phone already exist.<div>';
			} else {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $output =  '<div class="alert alert-danger" role="alert">Invalid email address.<div>';
                }

                if (!filter_var($cleanedPhone, FILTER_VALIDATE_INT)) {
                    $output =  '<div class="alert alert-danger" role="alert">Invalid phone number.<div>';
                }

				$vericode = md5(time());

				$fn = ucwords($fullname);
				$to = $email;
				$subject = "Please Verify Your Account 👨‍💻.";
				$body = "
					<h3>
						{$fn},</h3>
					<p>
						Thank you for registering. Please verify your account by clicking 
						<a href=\"https://sites.local/levina/auth/verified/{$vericode}\" target=\"_blank\">here</a>, or copy and paste the link below into your browser.
						<br>
						https://sites.local/levina/auth/verified/{$vericode}
						<br><br>
                        Best regards,
                        <br>
                        - Leviana, Namibra Inc. 🤞
					</p>
				";

				$mail_result = send_email($fn, $to, $subject, $body);
				if ($mail_result) {

					$data = [guidv4(), $fullname, $email, $cleanedPhone, $address, $terms];
					$query = "
						INSERT INTO levina_users (user_id, user_fullname, user_email, user_phone, user_address, user_terms) 
						VALUES (?, ?, ?, ?, ?, ?); 
					";
					$statement = $dbConnection->prepare($query);
					$result = $statement->execute($data);
					$user_id = $dbConnection->lastInsertId();

					if (isset($result)) {
						$sql = "
							UPDATE levina_users 
							SET user_vericode = :user_vericode 
							WHERE id = :user_id
						";
						$statement = $dbConnection->prepare($sql);
						$statement->execute([
							':user_vericode' => $vericode,
							':user_id' => $user_id
						]);

                        send_sms('Namibra welcome you to Levina 🤞.', $cleanedPhone);

                        redirect(PROOT . 'auth/verify');
					}
				} else {
					//$output =  "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
					$output =  '<div class="alert alert-danger" role="alert">Message could not be sent, please try again</div>';
				}
			}
		} else {
			$output = '<div class="alert alert-danger" role="alert">Error in Google reCAPTACHA!</div>';

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
                <h1>No account? Sign up</h1>
                <p class="pb-3 mb-3 mb-lg-4">Have an account already?&nbsp;&nbsp;<a href="<?= PROOT; ?>auth/signin">Sign in here!</a></p>
                <?= $output; ?>
                <form class="needs-validation" method="POST" novalidate>
                    <div class="row row-cols-1 row-cols-sm-2">
                        <div class="col mb-4">
                            <input class="form-control form-control-lg" type="text" name="fullname" id="fullname" placeholder="Your name" <?= $fullname; ?> required>
                        </div>
                        <div class="col mb-4">
                            <input class="form-control form-control-lg" type="email" id="email" name="email" placeholder="Email address" <?= $email; ?> required>
                        </div>
                    </div>
                    <div class="pb-3 mb-3">
                        <div class="position-relative">
                            <i class="ai-phone fs-lg position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                            <input class="form-control form-control-lg ps-5" type="tel" placeholder="Phone number" name="phone" id="phone" <?= $phone; ?> required>
                        </div>
                    </div>
                    <div class="pb-3 mb-3">
                        <div class="position-relative">
                            <i class="ai-globe fs-lg position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                            <input class="form-control form-control-lg ps-5" type="text" placeholder="Address" name="address" id="address" value="<?= $address; ?>" required>
                        </div>
                    </div>
                    <div class="pb-4">
                        <div class="form-check my-2">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" required>
                            <label class="form-check-label ms-1" for="terms">I agree to <a href="javascript:;">Terms &amp; Conditions</a></label>
                        </div>
                    </div>
                    <div class="g-recaptcha mb-3" data-sitekey="<?= RECAPTCHA_KEY; ?>"></div>
                    <button class="btn btn-lg btn-primary w-100 mb-4" type="submit">Sign up</button>
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
