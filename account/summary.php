<?php 

    // Summary page

    require_once ("../db_connection/conn.php");

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    if (admin_has_permission()) {
        redirect(PROOT . 'index');
    }

    //
	if (is_array(capital_mover($admin_id)) && capital_mover($admin_id)["msg"] == "touched") {
		redirect(PROOT . 'auth/end-trade-checker');
	}

    //
	$runningCapital = find_capital_given_to($admin_id);
    
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
                    <i class="fs-2" data-duoicon="slideshow"></i>
                </div>
            </div>
            <div class="col">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Market</li>
                    </ol>
                </nav>

                <!-- Heading -->
                <h1 class="fs-4 mb-0">Summary</h1>
            </div>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <a class="btn btn-light d-block" href="<?= PROOT; ?>"> <span class="material-symbols-outlined me-1">dashboard</span> Go to dashboard </a>
            </div>
            <div class="col-6 col-sm-auto">
                <a class="btn btn-light d-block" href="javascript:;" onclick="printPageArea('printableArea')"> <span class="material-symbols-outlined me-1">print</span> Print page </a>
            </div>
        </div>

        <!-- Page content -->
        <div class="row" id="printableArea">
            <div class="col-12">
                <!-- Filters -->
                <div class="card card-line bg-body-tertiary border-transparent mb-7">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a class="nav-link bg-dark active" aria-current="page" href="<?= PROOT; ?>account/summary">Data</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-12 col-lg">
                                <div class="row gx-3">
                                    <div class="col col-lg-auto ms-auto">
                                        <button class="btn"><?= date('l, F jS, Y', strtotime(date("Y-m-d"))); ?></button>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn">Ghana, GH –&nbsp;<time datetime="20:00" id="time_span"></time></button>
                                    </div>
                                    <div class="col-auto ms-n2">
                                        <a href="<?= PROOT . 'account/summary' ?>" class="btn btn-dark px-3">
                                            <span class="material-symbols-outlined">reset_wrench</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12 col-md-6 col-xxl mb-4 mb-xxl-0">
                        <div class="card bg-body-tertiary border-transparent">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <!-- Heading -->
                                        <h4 class="fs-sm fw-normal text-body-secondary mb-1">Gram</h4>

                                        <!-- Text -->
                                        <div class="fs-4 fw-semibold" id="total-gram"><?= sum_up_grams($conn, $admin_id); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xxl mb-4 mb-xxl-0">
                        <div class="card bg-body-tertiary border-transparent">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <!-- Heading -->
                                        <h4 class="fs-sm fw-normal text-body-secondary mb-1">Volume</h4>

                                        <!-- Text -->
                                        <div class="fs-4 fw-semibold" id="total-volume"><?= sum_up_volume($conn, $admin_id); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xxl mb-4 mb-xxl-0">
                        <div class="card bg-body-tertiary border-transparent">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <!-- Heading -->
                                        <h4 class="fs-sm fw-normal text-body-secondary mb-1">Density</h4>

                                        <!-- Text -->
                                        <div class="fs-4 fw-semibold" id="total-density"><?= sum_up_density($conn, $admin_id); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xxl mb-4 mb-xxl-0">
                        <div class="card bg-body-tertiary border-transparent">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <!-- Heading -->
                                        <h4 class="fs-sm fw-normal text-body-secondary mb-1">Pounds</h4>

                                        <!-- Text -->
                                        <div class="fs-4 fw-semibold" id="total-pounds"><?= sum_up_pounds($conn, $admin_id); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xxl mb-md-0">
                        <div class="card bg-body-tertiary border-transparent">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <!-- Heading -->
                                        <h4 class="fs-sm fw-normal text-body-secondary mb-1">Carat</h4>

                                        <!-- Text -->
                                        <div class="fs-4 fw-semibold" id="total-carat"><?= sum_up_carat($conn, $admin_id); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="row">
                    <div class="col">
                        <div class="vstack gap-3 gap-xl-6">
                            <div class="row g-3">

                                <div class="col-12 col-md-<?= ((admin_has_permission('supervisor')) ? '3' : '4'); ?> mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1"><?= ((admin_has_permission('supervisor')) ? 'Cash' : 'Gold'); ?> Acummulated</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="sal-capital"><?= money(total_amount_today($admin_id)); ?></div>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="credit-card"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-<?= ((admin_has_permission('supervisor')) ? '3' : '4'); ?> mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1"><?= ((admin_has_permission('supervisor')) ? 'Gold' : 'Cash'); ?> Balance</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="sal-capital"><?= ((admin_has_permission('supervisor')) ? money(remaining_gold_balance($admin_id)) : money(_capital($admin_id)['today_balance'])); ?></div>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="credit-card"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-<?= ((admin_has_permission('supervisor')) ? '3' : '4'); ?> mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Capital</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="sup-capital"><?= money(_capital($admin_id)['today_capital']); ?></div>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="briefcase"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (admin_has_permission('supervisor')): ?>
                                <div class="col-12 col-md-<?= ((admin_has_permission('supervisor')) ? '3' : '4'); ?> mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Earned</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="sup-capital">
                                                        <?php
                                                            $e = 0;
                                                            if (is_array($runningCapital)) {
                                                                $e = $runningCapital['daily_profit'];
                                                            }
                                                            echo money($e);
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="briefcase"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="col-12 col-md-6 mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Trades</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="expenses"><?php $tst = total_sale_amount_today($admin_id, null, 'exp'); echo money($tst["sum"]); ?></div>
                                                    <p class="mt-1">
                                                        <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i>Count: </span>
                                                        <span class="text-muted text-xs text-opacity-75" id="sup-balance"><?= $tst["count"]; ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="clipboard"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Deleted Trades</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="expenses"><?php $dtst = total_sale_amount_today($admin_id, 'delete', 'exp'); echo money($dtst["sum"]); ?></div>
                                                    <p class="mt-1">
                                                        <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i>Count: </span>
                                                        <span class="text-muted text-xs text-opacity-75" id="sup-balance"><?= $dtst["count"]; ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-danger">
                                                        <i class="fs-4" data-duoicon="clipboard"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if (admin_has_permission("salesperson")): ?>
                                <div class="col-12 col-md-6 mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Expenses</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold"><?php $exp = total_expenditure_today($admin_id); echo money($exp["sum"]); ?></div>
                                                    <p class="mt-1">
                                                        <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i>Count: </span>
                                                        <span class="text-muted text-xs text-opacity-75"><?= $exp["count"]; ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="clipboard"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Deleted Expenses</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="expenses"><?php $del_exp = total_expenditure_today($admin_id, $option = 'delete'); echo money($del_exp["sum"]); ?></div>
                                                    <p class="mt-1">
                                                        <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i>Count: </span>
                                                        <span class="text-muted text-xs text-opacity-75" id="sup-balance"><?= $del_exp["count"]; ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-danger">
                                                        <i class="fs-4" data-duoicon="clipboard"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="col-12 col-md-6 mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Send Pushes (<?= ((admin_has_permission("supervisor")) ? 'Money' : 'Gold'); ?>)</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="total-trades">
                                                        <?php 
                                                            $ps = get_total_send_push($conn, $admin_id, $admin_data["admin_permissions"]);
                                                            $ps_count = 0;
                                                            if (is_array($ps)) {
                                                                $ps_count = $ps["count"];
                                                                echo money($ps["sum"]);
                                                            } else {
                                                                echo money(0);
                                                            }
                                                        ?>
                                                    </div>
                                                    <p class="mt-1">
                                                        <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i>Count: </span>
                                                        <span class="text-muted text-xs text-opacity-75" id=""><?= $ps_count; ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="bell-badge"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Receive Pushes (<?= ((admin_has_permission("supervisor")) ? 'Gold' : 'Money'); ?>)</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="total-trades">
                                                        <?php 
                                                            $pr = get_total_receive_push($conn, $admin_id, date("Y-m-d"));
                                                            $pr_count = 0;
                                                            if (is_array($pr)) {
                                                                $pr_count = $pr["count"];
                                                                echo money($pr["sum"]);
                                                            } else {
                                                                echo money(0);
                                                            }
                                                        ?>
                                                        </div>
                                                    <p class="mt-1">
                                                        <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i>Count: </span>
                                                        <span class="text-muted text-xs text-opacity-75" id=""><?= $pr_count; ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="bell-badge"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6 col-md-6 mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Logs</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="profit-loss"><?= count_logs($admin_id); ?></div>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="align-bottom"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6 mb-4 mb-xxl-0">
                                    <div class="card bg-body-tertiary border-transparent">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <!-- Heading -->
                                                    <h4 class="fs-base fw-normal text-body-secondary mb-1">Last logged in</h4>

                                                    <!-- Text -->
                                                    <div class="fs-5 fw-semibold" id="total-pushes"><?= pretty_date($admin_data["admin_last_login"]); ?></div>
                                                </div>
                                                <div class="col-auto">
                                                    <!-- Avatar -->
                                                    <div class="avatar avatar-lg bg-body text-warning">
                                                        <i class="fs-4" data-duoicon="align-bottom"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php include ("../includes/footer.inc.php"); ?>
<script type="text/javascript" src="<?= PROOT; ?>assets/js/Chart.min.js"></script>
<script>
	$(document).ready(function() {

		// fetch current time.
		function updateTime() {
			var currentTime = new Date()
			var hours = currentTime.getHours()
			var seconds = currentTime.getSeconds();
			var minutes = currentTime.getMinutes()
			if (minutes < 10){
				minutes = "0" + minutes
			}
			if (seconds < 10){
				seconds = "0" + seconds
			}
			var t_str = hours + ":" + minutes + " " + seconds + " ";
			if(hours > 11){
				t_str += "PM";
			} else {
				t_str += "AM";
			}
			document.getElementById('time_span').innerHTML = t_str;
		}
		setInterval(updateTime, 1000);
	});

    function printPageArea(areaID){
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
    }
</script>
