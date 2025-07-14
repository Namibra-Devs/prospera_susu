<?php
    
    require ('../system/DatabaseConnector.php');

    if (admin_is_logged_in()) {
        redirect(PROOT);
    }
    $title = 'Account - Sign In | ';
    $body_class = 'd-flex align-items-center';
    include ('../system/inc/head.php');

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

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12" style="max-width: 25rem">
                <!-- Heading -->
                <h1 class="fs-1 text-center">Sign in</h1>

                <!-- Subheading -->
                <p class="lead text-center text-body-secondary">Access our dashboard and start tracking your tasks.</p>

                <!-- Form -->
                <form class="mb-5">
                    <div class="mb-4">
                        <label class="visually-hidden" for="email">Email Address</label>
                        <input class="form-control" id="email" type="email" placeholder="Enter your email address..." autocomplete="off" autofocus/>
                    </div>
                    <button class="btn btn-secondary w-100" type="submit">Sign in</button>
                </form>

                <!-- Text -->
                <p class="text-center text-body-secondary mb-0">Don't have an account yet? <a href="./sign-up.html">Sign up</a>.</p>
            </div>
        </div>
    </div>

<?php include ('../system/inc/footer.php'); ?>
