<?php
    
    require ('../system/DatabaseConnector.php');

    if (admin_is_logged_in()) {
        redirect(PROOT);
    }
    $title = 'Account - Sign In | ';
    $body_class = 'd-flex align-items-center';
    include ('../system/inc/head.php');

    $error = '';
    if ($_POST) {
        if (empty($_POST['admin_email']) || empty($_POST['admin_password'])) {
            $error = 'You must provide email and password.';
        }
        //
        $row = findAdminByEmail(sanitize($_POST['admin_email']));

        if (!$row) {
            $error = 'Unkown admin!';
        } else {
            if (!password_verify(sanitize($_POST['admin_password']), $row->admin_password)) {
                $error = 'Unkown admin!';
            }
        }

        if (!empty($error) || $error != '') {
            $_SESSION['flash_error'] = $error;
            redirect(PROOT . 'auth/sign-in');
        } else {
            $admin_id = $row->admin_id;
            adminLogin($admin_id);
        }
        
    }
?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12" style="max-width: 25rem">
                <!-- Heading -->
                <h1 class="fs-1 text-center">Sign in</h1>

                <!-- Subheading -->
                <p class="lead text-center text-body-warning">Access our dashboard and start tracking your tasks.</p>

                <!-- Form -->
                <form class="mb-5" id="sigin-form" method="POST">
                    <div class="mb-4">
                        <label class="visually-hidden" for="email">Email Address</label>
                        <input class="form-control" id="email" type="email" name="email" placeholder="Enter your email address..." autocomplete="off" autofocus/>
                    </div>
                    <div class="mb-4">
                        <label class="visually-hidden" for="email">Password</label>
                        <input class="form-control" id="password" name="password" type="password" placeholder="******" autocomplete="off" />
                    </div>
                    <button class="btn btn-warning w-100" id="signin-button" type="button">Sign in</button>
                </form>

                <!-- Text -->
                <p class="text-center text-body-warning mb-0">Don't remember account details? <a href="<?= PROOT; ?>auth/forget-password">Forget password</a>.</p>
            </div>
        </div>
    </div>
    
<?php include ('../system/inc/footer.php'); ?>

<script>
    $('#signin-button').on('click', function() {
        if ($('#email').val() != '') {
            if ($('#password').val() != '') {
                $('#signin-button').attr('disabled', true);
                $('#signin-button').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Processing ...</span>');

                setTimeout(function () {
                    $('#sigin-form').submit()
                }, 2000)
            } else {
                $('#password').addClass('input-field-error')
                $('#email').removeClass('input-field-error')
                alert("Password is required!");
                $('#password').val('');
                $('#password').focus()
                return false;
            }
        } else {
            $('#email').addClass('input-field-error')
            alert("Email is required!");
            $('#email').val('');
            $('#email').focus()
            return false;
        }
        return false
    });
</script>
