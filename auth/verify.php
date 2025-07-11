<?php
    
    require ('../system/DatabaseConnector.php');
    if (user_is_logged_in()) {
        redirect(PROOT . 'app/');
    }
    $title = 'Account - Resend Vericode | ';
    $body_class = "";
    require ('../system/inc/head.php');

?>

    <!-- Page wrapper -->
    <main class="page-wrapper">
        <div class="d-flex flex-column align-items-center position-relative h-100 px-3 pt-5">

            <!-- Home button -->
            <a class="text-nav btn btn-icon bg-light border rounded-circle position-absolute top-0 end-0 z-2 p-0 mt-3 me-3 mt-sm-4 me-sm-4" href="<?= PROOT; ?>app" data-bs-toggle="tooltip" data-bs-placement="left" title="Back to home" aria-label="Back to home">
                <i class="ai-home"></i>
            </a>

            <!-- Content -->
            <div class="px-3 px-lg-5 pt-5" style="max-width: 700px;">
                <h1 class="pt-3 pb-2 pb-lg-3">Verify your email</h1>
                <p class="pb-2">
                    A verification link has been sent to your email account. Please check your <b>spam folder</b> if not found in your <b>inbox</b> to verify your Levina account.
                </p>
                <a href="<?= PROOT . 'auth/'?>">Go home.</a>

                <!-- Copyright -->
                <p class="nav w-100 fs-sm pt-5 mt-auto mb-5" style="max-width: 700px;">
                    <span class="text-body-secondary">&copy; All rights reserved. Made by</span>
                    <a class="nav-link d-inline-block p-0 ms-1" href="https://nambra.io/" target="_blank" rel="noopener">Namibra Inc.</a>
                </p>
            </div>
        </div>

<?php require('../system/inc/footer.php'); ?>