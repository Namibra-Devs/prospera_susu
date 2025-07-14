<?php 

    // expenditure
    require_once ("../db_connection/conn.php");

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    //
	if (is_array(capital_mover($admin_id)) && capital_mover($admin_id)["msg"] == "touched") {
		redirect(PROOT . 'auth/end-trade-checker');
	}

    $today = date("Y-m-d");
    $where = '';
    if (!admin_has_permission()) {
        $where = ' AND (push_to = "' . $admin_id . '" OR push_from IN (SELECT push_from FROM giltmarket_pushes WHERE push_from = "' . $admin_id . '")) AND push_date = "' . $today . '" ';
    }
    $total_push = $conn->query("SELECT * FROM giltmarket_pushes INNER JOIN giltmarket_admin ON (giltmarket_admin.admin_id = giltmarket_pushes.push_from OR giltmarket_admin.admin_id = giltmarket_pushes.push_to) WHERE giltmarket_pushes.push_status = 0 $where GROUP BY push_id")->rowCount();
    $count_push = '';
    if ($total_push > 0) {
        $count_push = ' (' . $total_push . ')';
    }

    include ("../includes/header.inc.php");
    include ("../includes/aside.inc.php");
    include ("../includes/left.nav.inc.php");
    include ("../includes/top.nav.inc.php");
    
?>

    <div class="container-lg">
        <!-- Page header -->
        <div class="row align-items-center mb-7">
            <div class="col-auto">
                <!-- Avatar -->
                <div class="avatar avatar-xl rounded text-warning">
                <i class="fs-2" data-duoicon="menu"></i>
                </div>
            </div>
            <div class="col">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a class="text-body-secondary" href="javascript:;">Transactions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">All</li>
                    </ol>
                </nav>

                <!-- Heading -->
                <h1 class="fs-4 mb-0">Transactions</h1>
            </div>
            <?php if ($admin_permission == 'supervisor'): ?>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <a class="btn btn-warning d-block" href="javascript:;" data-bs-target="#modalCapital" data-bs-toggle="modal"> Fund coffers</a>
            </div>
            <?php endif; ?>
            <?php if (admin_has_permission()) : ?>
            <div class="col-6 col-sm-auto">
                <a class="btn btn-light d-block" href="#clearCoffersModal" data-bs-toggle="modal" > <span class="material-symbols-outlined me-1">clear_all</span> Clear coffers </a>
            </div>
            <?php endif; ?>
        </div>

        <?php if (admin_has_permission('supervisor')): ?>
        <div class="row mb-8">
            <div class="col-12 col-md-6 col-xxl-6 mb-4 mb-xxl-0">
                <div class="card bg-warning-subtle border-transparent">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <!-- Heading -->
                                <h4 class="fs-sm fw-normal text-body-secondary mb-1">Total amount in coffers</h4>

                                <!-- Text -->
                                <div class="fs-4 fw-semibold"><?= money(get_admin_coffers($conn, $admin_id)); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xxl-6 mb-4 mb-xxl-0">
                <div class="card bg-danger-subtle border-transparent">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <!-- Heading -->
                                <h4 class="fs-sm fw-normal text-body-secondary mb-1">Cash-out of coffers</h4>

                                <!-- Text -->
                                <div class="fs-4 fw-semibold"><?= money(get_admin_coffers($conn, $admin_id, 'send')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    
        <!-- Page content -->
        <div class="row">
            <div class="col-12">
                <!-- Filters -->
                <div class="card card-line bg-body-tertiary border-transparent mb-7">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a class="nav-link <?= ((!isset($_GET['data']) || !$_GET['data']) ? 'bg-dark active' : ''); ?>" aria-current="page" href="<?= PROOT; ?>account/pushes">All data<?= $count_push; ?></a>
                                    </li>
                                    <?php if (admin_has_permission('supervisor')): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= ((isset($_GET['data']) && $_GET['data'] == 'salesperson') ? 'bg-dark active' : ''); ?>" aria-current="page" href="<?= PROOT; ?>account/pushes/salesperson">Money to salepersonnels</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= ((isset($_GET['data']) && $_GET['data'] == 'gold-receive') ? 'bg-dark active' : ''); ?>" aria-current="page" href="<?= PROOT; ?>account/pushes/gold-receive"><?= ((admin_has_permission()) ? 'Gold to supervisors' : 'Gold received'); ?></a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <?php if ((!isset($_GET['data']) || !$_GET['data']) ? 'bg-dark active' : ''): ?>
                            <div class="col-12 col-lg">
                                <div class="row gx-3">
                                    <div class="col col-lg-auto ms-auto">
                                        <div class="input-group bg-body">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" id="search" />
                                            <span class="input-group-text" id="search">
                                                <span class="material-symbols-outlined">search</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-auto ms-n2">
                                        <div class="dropdown">
                                            <button class="btn btn-dark px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                <span class="material-symbols-outlined">export_notes</span>
                                            </button>
                                            <div class="dropdown-menu rounded-3 p-6">
                                                <h4 class="fs-lg mb-4">Export push</h4>
                                                <form style="width: 350px" id="exportForm" action="<?= PROOT; ?>auth/export.pushes.php">
                                                    <div class="row gx-3">
                                                        <div class="col-sm-12 mb-3">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input export_type" type="radio" name="export-status" id="inlineRadioStatus1" required value="zero" checked>
                                                            <label class="form-check-label" for="inlineRadioStatus1">All</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 mb-3">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio1" required value="date">
                                                            <label class="form-check-label" for="inlineRadio1">Date</label>
                                                        </div>
                                                        <div class="form-check form-check-inline <?= ((admin_has_permission()) ? '' : 'd-none'); ?>">
                                                            <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio2" required value="month">
                                                            <label class="form-check-label" for="inlineRadio2">Month</label>
                                                        </div>
                                                        <div class="form-check form-check-inline <?= ((admin_has_permission()) ? '' : 'd-none'); ?>">
                                                            <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio3" required value="year">
                                                            <label class="form-check-label" for="inlineRadio3">Year</label>
                                                        </div>
                                                        <div class="form-check form-check-inline <?= ((admin_has_permission()) ? '' : 'd-none'); ?>">
                                                            <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio4" required value="all">
                                                            <label class="form-check-label" for="inlineRadio4">All</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 mb-3 d-none" id="check-date">
                                                        <input type="date" class="form-control form-control-sm" id="export-date" name="export-date" <?= ((admin_has_permission()) ? '' : 'readonly'); ?> value="<?= date("Y-m-d"); ?>">
                                                    </div>
                                                    <div class="col-sm-12 mb-3 d-none" id="check-month">
                                                        <select id='export-month' name="export-month" class="form-control form-control-sm">
                                                            <option value=''>Select Month</option>
                                                            <option value="1"<?= ((date('m') == '1') ? ' selected' : ''); ?>>January</option>
                                                            <option value='2'<?= ((date('m') == '2') ? ' selected' : ''); ?>>February</option>
                                                            <option value='3'<?= ((date('m') == '3') ? ' selected' : ''); ?>>March</option>
                                                            <option value='4'<?= ((date('m') == '4') ? ' selected' : ''); ?>>April</option>
                                                            <option value='5'<?= ((date('m') == '5') ? ' selected' : ''); ?>>May</option>
                                                            <option value='6'<?= ((date('m') == '6') ? ' selected' : ''); ?>>June</option>
                                                            <option value='7'<?= ((date('m') == '7') ? ' selected' : ''); ?>>July</option>
                                                            <option value='8'<?= ((date('m') == '8') ? ' selected' : ''); ?>>August</option>
                                                            <option value='9'<?= ((date('m') == '9') ? ' selected' : ''); ?>>September</option>
                                                            <option value='10<?= ((date('m') == '10') ? ' selected' : ''); ?>'>October</option>
                                                            <option value='11<?= ((date('m') == '11') ? ' selected' : ''); ?>'>November</option>
                                                            <option value='12<?= ((date('m') == '12') ? ' selected' : ''); ?>'>December</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-12 mb-3 d-none" id="check-year">
                                                        <select id='export-year' name="export-year" class="form-control form-control-sm">
                                                            <option value=''>Select Year</option>
                                                            <?php for ($i = date('Y'); $i >= 2020; $i--) : ?>
                                                                <option value='<?= $i; ?>' <?= ((date('Y') == $i)? 'selected' : ''); ?>><?= $i; ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-12 mb-3">
                                                        <div class="btn-group w-100" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" name="export-type" id="export_xlsx" autocomplete="off" checked value="xlsx" required />
                                                            <label class="btn btn-light" for="export_xlsx" data-bs-toggle="tooltip" data-bs-title="XLSX">
                                                            <img src="<?= PROOT; ?>assets/media/XLSX.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                                            </label>
                                                            <input type="radio" class="btn-check" name="export-type" id="export_csv" autocomplete="off" value="csv" required />
                                                            <label class="btn btn-light" for="export_csv" data-bs-toggle="tooltip" data-bs-title="CSV">
                                                            <img src="<?= PROOT; ?>assets/media/CSV.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                                            </label>
                                                            <input type="radio" class="btn-check" name="export-type" id="export_pdf" autocomplete="off" value="pdf" required />
                                                            <label class="btn btn-light" for="export_pdf" data-bs-toggle="tooltip" data-bs-title="PDF">
                                                            <img src="<?= PROOT; ?>assets/media/PDF.png" class="w-rem-6 rh-rem-6 ounded-circle" alt="...">
                                                            </label>
                                                            <input type="radio" class="btn-check" name="export-type" id="export_xls" autocomplete="off" value="xls" required />
                                                            <label class="btn btn-light" for="export_xls" data-bs-toggle="tooltip" data-bs-title="XLS">
                                                            <img src="<?= PROOT; ?>assets/media/XLS.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <button id="submit-export" type="button" class="btn btn-warning">Export</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        
            <?php 
                if ((isset($_GET['data']) && $_GET['data'] == 'salesperson') && admin_has_permission('supervisor')): 
                    // all salepersonnles that received a push from supervisor
                    $sp_query = "
                        SELECT * FROM giltmarket_daily 
                        INNER JOIN giltmarket_admin 
                        ON giltmarket_admin.admin_id = giltmarket_daily.daily_to 
                        INNER JOIN giltmarket_pushes 
                        ON giltmarket_pushes.push_to = giltmarket_daily.daily_to
                        WHERE giltmarket_admin.admin_permissions = 'salesperson' 
                    ";
                    if (!admin_has_permission()) {
                        $sp_query .= " AND giltmarket_pushes.push_from = '" . $admin_id . "' AND giltmarket_daily.daily_date = '" . date("Y-m-d") . "' ";
                    }
                    $sp_query .= " GROUP BY giltmarket_pushes.push_to";
                    $statement = $conn->prepare($sp_query);
                    $statement->execute();
                    $sp_count = $statement->rowCount();
                    $sps = $statement->fetchAll();
            ?>

                <!-- Page content -->
                <div class="row">
                    <?php if ($sp_count > 0): ?>
                        <?php 
                        
                            foreach ($sps as $sp): 
                                $QUERY = "
                                    SELECT * FROM giltmarket_pushes 
                                    WHERE push_to = ? 
                                ";
                                if (!admin_has_permission()) {
                                    $QUERY .= " AND push_date = ' " . date("Y-m-d") . "' ";
                                }
                                $statement = $conn->prepare($QUERY);
                                $statement->execute([$sp['push_to']]);
                                $pss_count = $statement->rowCount();
                                $pss = $statement->fetchAll();
                        
                        ?>
                            <div class="col-12 col-xxl-4">
                                <div class="position-sticky mb-8" style="top: 40px">
                                    <!-- Card -->
                                    <div class="card bg-body mb-3">
                                        <!-- Image -->
                                        <div
                                        class="card-img-top pb-13"
                                        style="background: no-repeat url(<?= PROOT; ?>assets/media/background-2.jpg) center center / cover"
                                        ></div>

                                        <!-- Avatar -->
                                        <div class="avatar avatar-xl rounded-circle mt-n7 mx-auto">
                                            <img class="avatar-img border border-white border-3" src="<?= PROOT .  (($sp["admin_profile"] == NULL) ? 'assets/media/avatar.png' : $sp["admin_profile"]); ?>" alt="..." />
                                        </div>

                                        <!-- Body -->
                                        <div class="card-body text-center">
                                            <!-- Heading -->
                                            <h1 class="card-title fs-5"><?= ucwords($sp["admin_fullname"]); ?></h1>

                                            <!-- Text -->
                                            <!-- <p class="text-body-secondary mb-6">James is a long-standing customer with a passion for technology.</p> -->

                                            <!-- List -->
                                            <ul class="list-group list-group-flush mb-0">
                                                <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                                <span class="text-body-secondary">Title / Role</span>
                                                <span><?= ucwords(_admin_position($sp["admin_permissions"])); ?></span>
                                                </li>
                                                <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                                <span class="text-body-secondary">Phone</span>
                                                <a class="text-body" href="tel:<?= $sp["admin_phone"]; ?>"><?= $sp["admin_phone"]; ?></a>
                                                </li>
                                                <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                                <span class="text-body-secondary">Login datetime</span>
                                                <span><?= pretty_date($sp["admin_last_login"]); ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="row gx-3">
                                        <div class="col">
                                            <button class="btn btn-light w-100">Money given:<br /> <?= money($sp["daily_capital"]); ?></button>
                                        </div>
                                        <div class="col">
                                            <button class="btn btn-light w-100">Balance:<br /> <?= money($sp["daily_balance"]); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xxl">
                                <section class="mb-8">
                                    <!-- Header -->
                                    <div class="d-flex align-items-center justify-content-between mb-5">
                                        <h2 class="fs-5 mb-0">Recent pushes</h2>
                                    </div>

                                    <!-- Table -->
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                                <th>ID</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th></th>
                                            </thead>
                                            <tbody>
                                                <?php if ($pss_count > 0): ?>
                                                <?php foreach ($pss as $ps): ?>
                                                <tr>
                                                    <td class="text-body-secondary"><?= $ps["push_id"]; ?></td>
                                                    <td><?= money($ps["push_amount"]); ?></td>
                                                    <td><?= pretty_date($ps["createdAt"]); ?></td>
                                                    <!-- <td>reverse</td> -->
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td  colspan="4"class="bg-info-subtle">No data found!</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No data found!</div>
                    <?php endif; ?>
                </div>

            <?php elseif ((isset($_GET['data']) && $_GET['data'] == 'gold-receive') && admin_has_permission('supervisor')): 
                $q = "
                    SELECT * FROM giltmarket_pushes 
                    WHERE push_type = ? 
                ";
                if (!admin_has_permission()) {
                    // $q .= " AND push_to = '" . $admin_id . "' AND push_date = '" . date("Y-m-d") . "' ";
                    $q .= " AND push_to = '" . $admin_id . "'";
                }
                $q .= " ORDER BY createdAt DESC";
                
                $statement = $conn->prepare($q);
                $statement->execute(['gold']);
                $rows = $statement->fetchAll();
                $rows_count = $statement->rowCount();
                
            ?>
            <div class="card mb-7">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Push ID</th>
                                <th>Amount</th>
                                <th>From</th>
                                <th>Data</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($rows_count > 0): ?>
                            <?php 
                                $i = 1; 
                                foreach ($rows as $row): 
                                    $ad = find_admin_with_id($row["push_from"]); 
                            ?>
                                <tr>
                                    <td><?= $i; ?></td>
                                    <td><?= $row["push_id"]; ?></td>
                                    <td><?= money($row["push_amount"]); ?></td>
                                    <td><?= ucwords($ad["admin_fullname"]); ?></td>
                                    <td>
                                        <?php $p_data = json_decode($row["push_data"]); ?>
                                        Gram: <?= $p_data->gram; ?> <br>
                                        Volume: <?= $p_data->volume; ?> <br>
                                        Density: <?= $p_data->density; ?> <br>
                                        Pounds: <?= $p_data->pounds; ?> <br>
                                        Carat: <?= $p_data->carat; ?>
                                    </td>
                                    <td><?= pretty_date($row["createdAt"]); ?>
                                    <td>
                                        <a href="#viewModal_<?= $i; ?>" data-bs-toggle="modal" class="btn btn-sm bg-light">View</a>
                                    </td>
                                </tr>

                                <!-- view recieve gold details -->
                                <div class="modal fade" id="viewModal_<?= $i; ?>" tabindex="-1" aria-labelledby="viewModalLabel_<?= $i; ?>" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" style="backdrop-filter: blur(5px);">
                                    <div class="modal-dialog modal-dialog-centered" id="printableArea_<?= $i; ?>">
                                        <div class="modal-content overflow-hidden">
                                            <div class="modal-header pb-0 border-0">
                                                <h1 class="modal-title h4" id="viewModalLabel_<?= $i; ?>">Recieved gold details!</h1>
                                                <button type="button" class="btn-close view-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-5">
                                                <ul class="list-group list-group-flush">
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Push ID,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= $row["push_id"]; ?></time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Total amount,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= money($row["push_amount"]); ?></time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Gram,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= $p_data->gram; ?> </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Volume,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= $p_data->volume; ?> </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Density,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= $p_data->density; ?> </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Pounds,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= $p_data->pounds; ?> </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Carat,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= $p_data->carat; ?> </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">From,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= ucwords($ad["admin_fullname"]); ?> </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Note,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= $row["push_note"]; ?></time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col ms-n2">
                                                                <h6 class="fs-base fw-normal mb-1">Date,</h6>
                                                            </div>
                                                            <div class="col-auto">
                                                                <time class="text-body-secondary" datetime="01/01/2025"><?= $row["push_date"]; ?> </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </ul>
                                                <div class="px-6 py-5 d-flex justify-content-center">
                                                    <a href="javascript:;" class="btn btn-sm btn-dark" onclick="printPageArea('printableArea_<?= $i; ?>')">Print</a>&nbsp;&nbsp;
                                                    <!-- <a href="javascript:;" class="btn btn-sm btn-danger"><i class="bi bi-trash me-2"></i>Reverse</a>&nbsp;&nbsp; -->
                                                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php $i++; endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="bg-info-subtle">No data found!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div id="load-content"></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CLEAR COFFERS -->
    <div class="modal fade" id="clearCoffersModal" tabindex="-1" aria-labelledby="clearCoffersModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" style="backdrop-filter: blur(5px);">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content overflow-hidden">
                <div class="modal-header pb-0 border-0">
                    <h1 class="modal-title h4" id="clearCoffersModalLabel">Clear coffers!</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="px-6 py-5 border-bottom">                       
                        <p>
                            Are you sure you want to proceed by clearing the COFFERS?
                        </p>
                    </div>
                    <div class="px-6 py-5 d-flex justify-content-center">
                        <a href="<?= PROOT; ?>auth/clear.coffers?clear=1" class="btn btn-sm btn-danger"><i class="bi bi-trash me-2"></i>Yes, Proceed</a>&nbsp;&nbsp;
                        <button type="button" class="btn btn-sm btn-dark" data-bs-dismiss="modal">No, cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ("../includes/footer.inc.php"); ?>

<script>
    function printPageArea(areaID) {
        var printContent = document.getElementById(areaID).innerHTML;
        var originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        $('head').append(`
            <style>
                @page {
                    size: landscape;
                }

                @media print {
                    @page {
                        margin: 0 !important;
                    }

                    body {
                        padding: 75px; /* This will act as your margin. Originally, the margin will hide the header and footer text. */
                    }
                }
            </style>
        `);
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }

    $(document).ready(function() {

        $(".export_class").change(function(e) {
            event.preventDefault()
            var select_for = $(".export_class:checked").val();

            if (select_for == 'date') {
                $('#check-date').removeClass('d-none');

                // display none
                $('#check-month').addClass('d-none');
                $('#check-year').addClass('d-none');
            } else if (select_for == 'month') {
                $('#check-month').removeClass('d-none');

                // display none
                $('#check-date').addClass('d-none');
                $('#check-year').addClass('d-none');
            } else if (select_for == 'year') {
                $('#check-year').removeClass('d-none');

                // display none
                $('#check-month').addClass('d-none');
                $('#check-date').addClass('d-none');
            } else {
                // display none
                $('#check-date').addClass('d-none');
                $('#check-month').addClass('d-none');
                $('#check-year').addClass('d-none');
            }
        });


        $('#submit-export').on('click', function() {

            if ($(".export_class:checked").val()) {
                var select_for = $(".export_class:checked").val();

                if (select_for == 'date' && $("#export-date").val() == '') {
                    alert("You will have to select date!");
                    $("#export-date").focus();
                    return false;
                } else if (select_for == 'month' && $("#export-month").val() == '') {
                    alert("You will have to select month!");
                    $("#export-month").focus();
                    return false;
                } else if (select_for == 'year' && $("#export-year").val() == '') {
                    alert("You will have to select year!");
                    $("#export-year").focus();
                    return false;
                }

                $('#submit-export').attr('disabled', true);
                $('#submit-export').text('Exporting ...');
                setTimeout(function () {
                    $('#exportForm').submit();

                    $('#submit-export').attr('disabled', false);
                    $('#submit-export').text('Export');
                    // location.reload();
                }, 2000)
            } else {
                return false;
            }
        });


    });
    
    // SEARCH AND PAGINATION FOR LIST
    function load_data(page, query = '') {
        $.ajax({
            url : "<?= PROOT; ?>auth/push.list.php",
            method : "POST",
            data : {
                page : page, 
                query : query
            },
            success : function(data) {
                $("#load-content").html(data);
            }
        });
    }

    load_data(1);
    $('#search').keyup(function() {
        var query = $('#search').val();
        load_data(1, query);
    });

    $(document).on('click', '.page-link-go', function() {
        var page = $(this).data('page_number');
        var query = $('#search').val();
        load_data(page, query);
    });
</script>
