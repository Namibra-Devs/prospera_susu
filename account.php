<?php 

    require ('system/DatabaseConnector.php');
    
	// Check if the admin or collector is logged in
    if (!admin_is_logged_in() && !collector_is_logged_in()) {
        redirect(PROOT . 'auth/sign-in');
    }

    $body_class = '';
    $title = 'Account | ';
    include ('system/inc/head.php');
    include ('system/inc/modals.php');
    include ('system/inc/sidebar.php');
    include ('system/inc/topnav-base.php');
    include ('system/inc/topnav.php');

?>
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
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Account</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Account</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-5 mb-0">Account</h1>
                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                    <!-- Action -->
                    <div class="row gx-2">
                        <div class="col-6 col-sm-auto">
                            <a class="btn btn-danger d-block" href="<?= PROOT; ?>account/settings"> Edit </a>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <a class="btn btn-light d-block" href="<?= goBack(); ?>"> Go back </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <div class="row">
                <div class="col-12 col-lg-3">
                    <!-- Nav -->
                    <nav class="nav nav-pills position-sticky flex-column mb-8" id="accountNav" style="top: 2rem">
                        <a class="nav-link" href="<?= PROOT; ?>account">General</a>
                        <a class="nav-link" href="<?= PROOT; ?>account-settings">Update account</a>
                        <a class="nav-link" href="#security">Security</a>
                        <a class="nav-link" href="<?= PROOT; ?>account-change-password">Change password</a>
                        <a class="nav-link text-danger" href="<?= PROOT; ?>auth/logout">Logout</a>
                    </nav>
                </div>
                <div class="col-12 col-lg-9" data-bs-spy="scroll" data-bs-target="#accountNav" data-bs-smooth-scroll="true" tabindex="0">

                    <!-- General -->
                    <section class="card bg-body-tertiary border-transparent mb-5" id="general">
                        <div class="card-body">

                            <?php 
                                if (admin_is_logged_in()) {
                                    echo get_admin_profile($collector_id); 
                                } elseif (collector_is_logged_in()) {
                                    echo get_collector_profile($collector_id); 
                                }
                            
                            ?>

                        </div>
                    </section>

                    <!-- Security -->
                    <section class="card bg-body-tertiary border-transparent" id="security">
                        <div class="card-body">
                            <h2 class="fs-5 mb-1">Security</h2>
                            <p class="text-body-secondary">Secure your account with a strong password and two-factor authentication.</p>
                            <div class="card border-transparent mb-4">
                                <div class="card-body py-0">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="material-symbols-outlined text-body-tertiary">bring_your_own_ip</span>
                                                </div>
                                                <div class="col"><?= ((isset($admin_data['admin_ip'])) ? $admin_data['admin_ip'] : ''); ?> <small class="text-body-secondary ms-1">I.P</small></div>
                                            </div>
                                        </li>
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="material-symbols-outlined text-body-tertiary">important_devices</span>
                                                </div>
                                                <div class="col"><?= ((isset($admin_data['admin_device'])) ? $admin_data["admin_device"] : ''); ?> <small class="text-body-secondary ms-1">Device</small></div>
                                            </div>
                                        </li>
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="material-symbols-outlined text-body-tertiary">device_hub</span>
                                                </div>
                                                <div class="col"><?= ((isset($admin_data['admin_os'])) ?  $admin_data["admin_os"] : ''); ?> <small class="text-body-secondary ms-1">O.S</small></div>
                                            </div>
                                        </li>
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="material-symbols-outlined text-body-tertiary">web</span>
                                                </div>
                                                <div class="col"><?= ((isset($admin_data['admin_browser'])) ? $admin_data["admin_browser"] : ''); ?> <small class="text-body-secondary ms-1">Browser</small></div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <a href="<?= PROOT . 'auth/sign-out'; ?>" class="btn btn-dark">Sign out from this devices</a>
                        </div>
                    </section>

                </div>
            </div>
        </div>


<?php include ('system/inc/footer.php'); ?>
