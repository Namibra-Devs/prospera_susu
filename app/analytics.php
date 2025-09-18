<?php 

    // expenditure
    require_once ("../db_connection/conn.php");

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    if (!admin_has_permission()) {
        redirect(PROOT . 'index');
    }
    include ("../includes/header.inc.php");
    include ("../includes/aside.inc.php");
    include ("../includes/left.nav.inc.php");
    include ("../includes/top.nav.inc.php");

    $thisYr = date("Y");
	$lastYr = $thisYr - 1;

    //
    $thisYrQ = "
        SELECT SUM(daily_capital) AS dc, SUM(daily_balance) AS db, SUM(daily_profit) AS dp, giltmarket_daily.daily_date
        FROM `giltmarket_daily` 
        INNER JOIN giltmarket_admin
        ON admin_id = giltmarket_daily.daily_to
        WHERE giltmarket_admin.admin_permissions = ?
        AND YEAR(giltmarket_daily.daily_date) = ? 
        AND giltmarket_daily.status = ? 
    ";
    $statement = $conn->prepare($thisYrQ);
    $statement->execute(['supervisor', $thisYr, 0]);
    $thisYr_result = $statement->fetchAll();
    
    //
    $lastYrQ = "
        SELECT SUM(daily_capital) AS dc, SUM(daily_balance) AS db, SUM(daily_profit) AS dp, giltmarket_daily.daily_date
        FROM `giltmarket_daily` 
        INNER JOIN giltmarket_admin
        ON admin_id = giltmarket_daily.daily_to
        WHERE giltmarket_admin.admin_permissions = ?
        AND YEAR(giltmarket_daily.daily_date) = ? 
        AND giltmarket_daily.status = ? 
    ";
    $statement = $conn->prepare($lastYrQ);
    $statement->execute(['supervisor', $lastYr, 0]);
    $lastYr_result = $statement->fetchAll();

    $current = array();
    $last = array();

    $currentTotal = 0;
    $lastTotal = 0;

    foreach ($thisYr_result as $thisYr_row) {
        // $this_year_profit_amount = (float)($thisYr_row['db'] - $thisYr_row['dc']);
        $this_year_profit_amount = $thisYr_row['dp'];
        if ($thisYr_row['db'] == null) {
            $this_year_profit_amount = 0;
        }
        $month = date("m", strtotime($thisYr_row['daily_date'] ?? ""));
        if (!array_key_exists((int)$month, $current)) {
            $current[(int)$month] = $this_year_profit_amount;
        } else {
            $current[(int)$month] += $this_year_profit_amount;
        }
        $currentTotal += $this_year_profit_amount;
    }

    foreach ($lastYr_result as $lastYr_row) {
        // $last_year_profit_amount = (float)($lastYr_row['db'] - $lastYr_row['dc']);
        $last_year_profit_amount = $lastYr_row['dp'];
        if ($lastYr_row['db'] == null) {
            $last_year_profit_amount = 0;
        }
        $month = date("m", strtotime($lastYr_row['daily_date'] ?? ""));
        if (!array_key_exists((int)$month, $last)) {
            $last[(int)$month] = $last_year_profit_amount;
        } else {
            $last[(int)$month] += $last_year_profit_amount;
        }
        $currentTotal += $last_year_profit_amount;
    }

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
                        <li class="breadcrumb-item active" aria-current="page">Analytics</li>
                    </ol>
                </nav>

                <!-- Heading -->
                <h1 class="fs-4 mb-0">Analytics</h1>
            </div>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <a class="btn btn-light d-block" href="<?= PROOT; ?>"> <span class="material-symbols-outlined me-1">dashboard</span> Go to dashboard </a>
            </div>
        </div>

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
                                        <a class="nav-link bg-dark active" aria-current="page" href="<?= PROOT; ?>account/analytics">Data</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-12 col-lg">
                                <div class="row gx-3">
                                    <div class="col col-lg-auto ms-auto">
                                        <input type="date" id="dater" class="form-control bg-body">
                                    </div>
                                    <div class="col-auto">
                                        <select id='use-month' class="form-control bg-body">
                                            <option value=''>Select Month</option>
                                            <option value="1">January</option>
                                            <option value='2'>February</option>
                                            <option value='3'>March</option>
                                            <option value='4'>April</option>
                                            <option value='5'>May</option>
                                            <option value='6'>June</option>
                                            <option value='7'>July</option>
                                            <option value='8'>August</option>
                                            <option value='9'>September</option>
                                            <option value='10'>October</option>
                                            <option value='11'>November</option>
                                            <option value='12'>December</option>
                                        </select>
                                    </div>
                                    <div class="col-auto ms-n2">
                                        <a href="<?= PROOT . 'account/analytics' ?>" class="btn btn-dark px-3">
                                            <span class="material-symbols-outlined">reset_wrench</span>
                                        </a>
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
                            <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <!-- Heading -->
                                                <h4 class="fs-base fw-normal text-body-secondary mb-1 des">Supervisor</h4>
                                                <div class="hide">Total amount of gold given to supervisor and total amount of gold sold.</div>
                                                <!-- Text -->
                                                <div class="fs-5 fw-semibold" id="sup-capital"></div>
                                                <p class="mt-1">
                                                    <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i>Gold: </span>
                                                    <span class="text-muted text-xs text-opacity-75" id="sup-balance">0.00</span>
                                                </p>
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

                            <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <!-- Heading -->
                                                <h4 class="fs-base fw-normal text-body-secondary mb-1 des">Salespersonnel</h4>
                                                <div class="hide">Total amount of cash given to salespersonnels and total balance remaining.</div>
                                                <!-- Text -->
                                                <div class="fs-5 fw-semibold" id="sal-capital"></div>
                                                <p class="mt-1">
                                                    <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i>Money: </span>
                                                    <span class="text-muted text-xs text-opacity-75" id="sal-balance">0.00</span>
                                                </p>
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

                            <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <!-- Heading -->
                                                <h4 class="fs-base fw-normal text-body-secondary mb-1">Expenses</h4>

                                                <!-- Text -->
                                                <div class="fs-5 fw-semibold" id="expenses"></div>
                                                <p class="mt-1">
                                                    <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i> </span>
                                                    <span class="text-muted text-xs text-opacity-75" id=""></span>
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

                            <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <!-- Heading -->
                                                <h4 class="fs-base fw-normal text-body-secondary mb-1">Total trades</h4>

                                                <!-- Text -->
                                                <div class="fs-5 fw-semibold" id="total-trades"></div>
                                                <p class="mt-1">
                                                    <span class="text-success text-xs"><i class="fas fa-arrow-up me-1"></i> </span>
                                                    <span class="text-muted text-xs text-opacity-75" id=""></span>
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
                    

                            <div class="col-12 mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <!-- Heading -->
                                                <h4 class="fs-base fw-normal text-body-secondary mb-1">Earnings</h4>

                                                <!-- Text -->
                                                <div class="fs-1 fw-semibold" id="profit-loss">0.00</div>
                                            </div>
                                            <div class="col-auto">
                                                <!-- Avatar -->
                                                <div class="avatar avatar-lg bg-body text-warning">
                                                    <i class="fs-4" data-duoicon="credit-card"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-8">
                                            <div class="">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <span class="text-success text-xs">Money accumulated (supervisor)</span>
                                                </div>
                                                <div class="text-muted text-xs text-opacity-75 mt-3" id="incoming">0.00 GHS</div>
                                            </div>
                                            <div class="">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <span class="text-warning text-xs">Gold accumulated (salesperson)</span>
                                                </div>
                                                <div class="text-muted text-xs text-opacity-75 mt-3" id="outgoing">0.00 GHS</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-xxl-4 mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <!-- Heading -->
                                                <h4 class="fs-sm fw-normal text-body-secondary mb-1">Total pushes</h4>

                                                <!-- Text -->
                                                <div class="fs-4 fw-semibold" id="total-pushes">0.00</div>
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
                            <div class="col-6 col-md-4 col-xxl-4 mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <!-- Heading -->
                                                <h4 class="fs-sm fw-normal text-body-secondary mb-1">Money</h4>

                                                <!-- Text -->
                                                <div class="fs-4 fw-semibold" id="push-money">0.00</div>
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
                            <div class="col-6 col-md-4 col-xxl-4 mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                    <!-- Heading -->
                                                <h4 class="fs-sm fw-normal text-body-secondary mb-1">Gold</h4>

                                                <!-- Text -->
                                                <div class="fs-4 fw-semibold" id="push-gold">0.00</div>
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

                        <div class="row mb-8">
                            <div class="col-12 col-md-6 col-xxl mb-4 mb-xxl-0">
                                <div class="card bg-body-tertiary border-transparent">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <!-- Heading -->
                                                <h4 class="fs-sm fw-normal text-body-secondary mb-1">Gram</h4>

                                                <!-- Text -->
                                                <div class="fs-4 fw-semibold" id="total-gram">0</div>
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
                                                <div class="fs-4 fw-semibold" id="total-volume">0</div>
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
                                                <div class="fs-4 fw-semibold" id="total-density">0</div>
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
                                                <div class="fs-4 fw-semibold" id="total-pounds">0</div>
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
                                                <div class="fs-4 fw-semibold" id="total-carat">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5>Earnings chart</h5>
                                    </div>
                                    <div class="hstack align-items-center">
                                        <a href="<?= PROOT; ?>account/analytics" class="text-muted"><i class="bi bi-arrow-repeat"></i></a>
                                    </div>
                                </div>
                                <div class="mx-n4">
                                    <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-5">
                                    <div>
                                        <h5>Accumulated earnings by months and years</h5>
                                    </div>
                                    <div class="hstack align-items-center">
                                        <a href="<?= PROOT; ?>account/trades" class="text-muted">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="vstack gap-6">
                                    <table class="table table-bordered table-lg">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col" style="font-family: Roboto Mono, monospace;"><?= $lastYr; ?></th>
                                                <th scope="col" style="font-family: Roboto Mono, monospace;"><?= $thisYr; ?></th>
                                            </tr>
                                        </thead>
                                            <tbody>
                                            <?php for ($i = 1; $i <= 12; $i++):
                                                $dt = dateTime::createFromFormat('!m',$i);
                                            ?>
                                                <tr>
                                                    <td <?= (date('m') == $i) ? ' class="bg-danger"' : ''; ?>><?= $dt->format("F"); ?></td>
                                                    <td <?= (date('m') == $i) ? ' class="bg-danger"' : ''; ?> style="font-family: Roboto Mono, monospace;"><?= ((array_key_exists($i, $last)) ? money($last[$i]) : money(0)); ?></td>
                                                    <td <?= (date('m') == $i) ? ' class="bg-danger"' : ''; ?> style="font-family: Roboto Mono, monospace;"><?=  ((array_key_exists($i, $current)) ? money($current[$i]) : money(0)); ?></td>
                                                </tr>
                                            <?php endfor; ?>
                                            <tr>
                                                <td>Total</td>
                                                <td style="font-family: Roboto Mono, monospace;"><?= money($lastTotal); ?></td>
                                                <td style="font-family: Roboto Mono, monospace;"><?= money($currentTotal); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
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
        $('#submitExpenditure').on('click', function() {
            if (confirm("By clicking on ok, this expenditure will be recorded!")) {
                expenditureForm.submit()
            }
            return false
        });

        var dater = null;
        var action = '';
        
        $('#dater').on('change', function() {
            dater = this.value;
            action = 'with_date'
            $('#use-month').val("");
            analytics(dater, action)
        });

        $('#use-month').change(function(){
            dater = $(this).val();
            action = "with_month"
            $('#dater').val("");
            analytics(dater, action)
        });

        function analytics(dater, action) {
            $.ajax({
                method: "POST",
                url: "<?= PROOT; ?>auth/analytics.info.php",
                data: {
                    dater : dater,
                    action : action
                },
                beforeSend: function() {
                    $('#sup-capital').text('loading ...');
                    $('#sup-balance').text('loading ...');
                    $('#sal-capital').text('loading ...');
                    $('#sal-balance').text('loading ...');
                    $('#expenses').text('loading ...');
                    $('#total-trades').text('loading ...');
                    $('#profit-loss').text('loading ...');
                    $('#incoming').text('loading ...');
                    $('#outgoing').text('loading ...');
                    $('#total-pushes').text('loading ...');
                    $('#total-gram').text('loading ...');
                    $('#total-volume').text('loading ...');
                    $('#total-density').text('loading ...');
                    $('#total-pounds').text('loading ...');
                    $('#total-carat').text('loading ...');
                    $('#push-send').text('loading ...');
                    $('#push-receive').text('loading ...');
                },
                success: function(data) {
                    const response = JSON.parse(data);

                    $('#sup-capital').text(response["supervisor_capital"]);
                    $('#sup-balance').text(response["supervisor_balance"]);
                    $('#sal-capital').text(response["sales_capital"]);
                    $('#sal-balance').text(response["sales_balance"]);
                    $('#expenses').text(response["expenses"]);
                    $('#total-trades').text(response["trades"]);
                    $('#profit-loss').text(response["gained_or_loss"]);
                    $('#incoming').text(response["in"]);
                    $('#outgoing').text(response["out"]);
                    $('#total-pushes').text(response["pushes"]);
                    $('#total-gram').text(response["gram"]);
                    $('#total-volume').text(response["volume"]);
                    $('#total-density').text(response["density"]);
                    $('#total-pounds').text(response["pounds"]);
                    $('#total-carat').text(response["carat"]);
                    $('#push-money').text(response["push_money"]);
                    $('#push-gold').text(response["push_gold"]);

                    console.log(data);
                },
                error: function() {

                }
            })
        }
        //
        analytics(dater);

    });
</script>

<script type="text/javascript">
    /* globals Chart:false, feather:false */

	(function () {
	    'use strict'

	      // Graphs
	    var ctx = document.getElementById('myChart')
	      // eslint-disable-next-line no-unused-vars
	    var myChart = new Chart(ctx, {
	        type: 'line',
	        data: {
	            labels: [
	                <?php 
	                    for ($i = 1; $i <= 12; $i++) {
	                        $dt = dateTime::createFromFormat('!m',$i);
	                        $m = $dt->format("F");
	                        echo json_encode($m).',';
	                    }
	                ?>
	            ],
	            datasets: [{
	                label: '<?= $thisYr; ?>, Amount ₵',
	                data: [
	                    <?php 
	                        for ($i = 1; $i <= 12; $i++) {
	                            $mn = (array_key_exists($i, $current)) ? $current[$i] : 0;
	                            echo json_encode($mn).',';
	                        }
	                    ?>
	                ],
	                lineTension: 0,
	                backgroundColor: 'rgba(225, 0.1, 0.3, 0.1)',
	                borderColor: 'tomato',
	                borderWidth: 3,
	                pointBackgroundColor: 'red'
	            },{
	                label: '<?= $lastYr; ?>, Amount ₵',
	                data : [
	                    <?php 
	                        for ($i = 1; $i <= 12; $i++) {
	                            $mn = (array_key_exists($i, $last)) ? $last[$i] : 0;
	                            echo json_encode($mn).',';
	                        }
	                    ?>
	                ],
	                backgroundColor: 'rgba(0, 225, 0, 0.1)',
	                borderColor: 'gold',
	                pointBackgroundColor: 'brown',
	                borderWidth: 3
	            }]
	        },
	        options: {
	            responsive: true,
	            scales: {
	                yAxes: [{
	                    ticks: {
	                        beginAtZero: false
	                    }
	                }]
	            },
	            legend: {
	                display: true,
	                position: 'top',
	            },
	            title: {
	                display: true,
	                text: 'Earnings By Month - Giltmarket LTD.'
	            }
	        }
	    })
	})()
</script>

