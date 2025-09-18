<?php 
    // deleted trades
    require_once ("../db_connection/conn.php");

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    //
	if (is_array(capital_mover($admin_id)) && capital_mover($admin_id)["msg"] == "touched") {
		redirect(PROOT . 'auth/end-trade-checker');
	}

    include ("../includes/header.inc.php");
    include ("../includes/aside.inc.php");
    include ("../includes/left.nav.inc.php");
    include ("../includes/top.nav.inc.php");

?>

    <!-- Content -->
    <div class="container-lg">
        <!-- Page header -->
        <div class="row align-items-center mb-7">
            <div class="col-auto">
                <!-- Avatar -->
                <div class="avatar avatar-xl rounded text-warning">
                    <i class="fs-2" data-duoicon="credit-card"></i>
                </div>
            </div>
            <div class="col">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Market</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Trades</li>
                    </ol>
                </nav>

                <!-- Heading -->
                <h1 class="fs-4 mb-0">Delete trade request(s)</h1>
            </div>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <a class="btn btn-secondary d-block" href="<?= goBack(); ?>" data-bs-target="#buyModal" data-bs-toggle="modal"> <span class="material-symbols-outlined me-1">arrow_back</span> Go back </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Filters -->
                <div class="card card-line bg-body-tertiary border-transparent mb-7">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= PROOT; ?>account/trades">All trades</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link bg-dark active" aria-current="page" href="<?= PROOT; ?>account/trades.archive">Archive trades</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-12 col-lg">
                            </div>
                            <div class="col-auto ms-n2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mb-7">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <?php if (admin_has_permission()): ?>
                            <th scope="col">Handler</th>
                        <?php endif; ?>
                        <th>Customer</th>
                        <th>Gram</th>
                        <th>Volume</th>
                        <th>Price</th>
                        <th>Amount</th>
                        <th></th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?= fetch_all_sales(1, $admin_id, 'no_exp'); ?>
                </tbody>
            </table>
        </div>
    </div>

<?php include ("../includes/footer.inc.php"); ?>
