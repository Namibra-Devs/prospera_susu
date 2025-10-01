<?php 
    require ('system/DatabaseConnector.php');
    
	// Check if the admin is logged in
    if (!admin_is_logged_in()) {
        redirect(PROOT . 'auth/sign-in');
    }

    $body_class = '';
    $title = 'Dashboard | ';
    include ('system/inc/head.php');
    include ('system/inc/modals.php');
    include ('system/inc/sidebar.php');
    include ('system/inc/topnav-base.php');
    include ('system/inc/topnav.php');

?>

    <!-- Main -->
    <main class="main px-lg-6">
        <!-- Content -->
        <div class="container-lg">


            <!-- Page content -->
            <div class="row align-items-center">
                <div class="col-12 col-md-auto order-md-1 d-flex align-items-center justify-content-center mb-4 mb-md-0">
                    <div class="avatar text-info me-2">
                        <i class="fs-4" data-duoicon="world"></i>
                    </div>
                    Ghana, Gh –&nbsp;<span datetime="20:00" id="time_span"></span>
                </div>
                <div class="col-12 col-md order-md-0 text-center text-md-start">
                    <h1>Hello, 
                        <?php echo $admin_data['first']; ?>
                    </h1>
                    <p class="fs-lg text-body-secondary mb-0">Here's a summary of your account activity for this week.</p>
                </div>
            </div>

            <!-- Divider -->
            <hr class="my-8" />

            <!-- Stats -->
            <div class="row mb-8">
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Commisions</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(get_total_commission_amount()); ?></div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Deposits</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(get_total_deposits_amount()); ?></div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
                                    <i class="fs-4" data-duoicon="clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-md-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <!-- Heading -->
                                        <h4 class="fs-sm fw-normal text-body-secondary mb-1">Withdrawals</h4>

                                        <!-- Text -->
                                        <div class="fs-4 fw-semibold"><?= money(get_total_withdrawal_amount()); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <!-- Avatar -->
                                        <div class="avatar avatar-lg bg-body text-primary">
                                            <i class="fs-4" data-duoicon="slideshow"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xxl-3">
                        <div class="card bg-body-tertiary border-transparent">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <!-- Heading -->
                                        <h4 class="fs-sm fw-normal text-body-secondary mb-1">Customers</h4>

                                        <!-- Text -->
                                        <div class="fs-4 fw-semibold"><?= get_total_customer(); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <!-- Avatar -->
                                        <div class="avatar avatar-lg bg-body text-primary">
                                            <i class="fs-4" data-duoicon="discount"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page content -->
				<?php if (admin_has_permission()): ?>
                <div class="row align-items-center mb-2">
                    <div class="col">
                        <!-- Heading -->
                        <h2 class="fs-3 mb-0">Performance</h2>
                    </div>
                    <div class="col-auto my-n2">
                        <select id="chartTypeSelect" class="form-select w-auto">
                           <option value="line" selected>Line</option>
                           <option value="bar">Bar</option>
                           <option value="pie">Pie</option>
                           <option value="doughnut">Doughnut</option>
                        </select>


                    </div>
                    <div class="col-auto my-n2">
                        <!-- Select -->
                        <select class="form-select" id="yearSelect"></select>
                    </div>
                </div>
				
                <!-- Chart -->
                <div style="height:500px;">
                    <canvas id="financeChart"></canvas>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
				<?php endif; ?>

                <!-- Divider -->
                <hr class="my-8" />

                <div class="row">
                    <div class="col-12 col-xxl-8">
                        <!-- Header -->
                        <div class="row align-items-center mb-5">
							<div class="col">
								<!-- Heading -->
								<h2 class="fs-5 mb-0">Latest transactions</h2>
							</div>
							<div class="col-auto">
								<a class="btn btn-link my-n2" href="<?= PROOT; ?>app/transactions">
									Browse all
									<span class="material-symbols-outlined">arrow_right_alt</span>
								</a>
							</div>
                        </div>

                        <!-- Table -->
                        <div class="card mb-7 mb-xxl-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                    <th class="fs-sm">ID</th>
                                    <th class="fs-sm">Client</th>
                                    <th class="fs-sm">Amount</th>
                                    <th class="fs-sm">Handler</th>
                                    <th class="fs-sm">Type</th>
                                    <th class="fs-sm">Status</th>
								</tr>
							</thead>
							<tbody>
								<?php 
									// 
									$query = "
										SELECT * FROM (
											SELECT 
												saving_id AS transaction_id, 
												saving_customer_id AS customer_id, 
												saving_customer_account_number AS account_number,
												saving_collector_id AS collector_id, 
												saving_amount AS amount, 
												saving_date_collected AS transaction_date, 
												saving_status AS status, 
												'saving' AS type, 
												created_at FROM savings 
												UNION ALL 
													SELECT 
														withdrawal_id AS transaction_id, 
														withdrawal_customer_id AS customer_id, 
														withdrawal_customer_account_number AS account_number, 
														withdrawal_approver_id AS collector_id, 
														withdrawal_amount_requested AS amount, 
														withdrawal_date_requested AS transaction_date, 
														withdrawal_status AS status, 
														'withdrawal' AS type, 
														created_at FROM withdrawals
											) 
										AS transactions WHERE ";
									// check if a collector is logged in, then show only their transactions
									if (admin_has_permission('collector') && !admin_has_permission('admin')) {
										$query .= " collector_id = '". $admin_id . "' ";
									} else {
										$query .= " 1=1 ";
									}
									$query .= " ORDER BY created_at DESC LIMIT 20";
									$statement = $dbConnection->prepare($query);
									$statement->execute();
									$rows_count = $statement->rowCount();
									$trows = $statement->fetchAll();
								?>
								<?php if ($rows_count > 0): ?>
									<?php 
										$i = 1;
										foreach ($trows as $trow): 
											// get customer name
											$client_name = findCustomerByAccountNumber($trow['account_number'])->customer_name;
											if (!$client_name) {
												$client_name = 'Unknown';
											}

											// get handler name
											$handler = findAdminById($trow['collector_id'])->admin_name;
											if (!$handler) {
												$handler = 'Admin';
											}

											// get type of transaction
											$type = 'Unknown';
											if ($trow['type'] == 'saving') {
												$type = '<span class="fs-sm text-info">Deposit</span>';

												// check status of deposite transactions
												if ($trow['status'] == 'Pending') {
													$trow['status'] = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
												} elseif ($trow['status'] == 'Approved') {
													$trow['status'] = '<span class="badge bg-success-subtle text-success">Approved</span>';
												} elseif ($trow['status'] == 'Rejected') {
													$trow['status'] = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
												}

											} elseif ($trow['type'] == 'withdrawal') {
												$type = '<span class="fs-sm text-warning">Withdrawal</span>';

												// check status of withdrawal transactions
												if ($trow['status'] == 'Pending') {
													$trow['status'] = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
												} elseif ($trow['status'] == 'Approved') {
													$trow['status'] = '<span class="badge bg-success-subtle text-success">Approved</span>';
												} elseif ($trow['status'] == 'Paid') {
													$trow['status'] = '<span class="badge bg-primary-subtle text-primary">Paid</span>';
												} elseif ($trow['status'] == 'Rejected') {
													$trow['status'] = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
												}
											}
									?>
									<tr>
										<td style="width: 0px">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="tableCheckOne" />
												<label class="form-check-label" for="tableCheckOne"></label>
											</div>
										</td>
										<td><?= $i; ?></td>
										<td><?= ucwords($client_name) . ' (' . $trow['account_number'] . ')'; ?></td>
										<td><?= money($trow["amount"]); ?></td>
										<td><?= ucwords($handler); ?></td>
										<td><?= $type; ?></td>
										<td><?= $trow['status']; ?></td>
									</tr>
									<?php $i++; endforeach; ?>
								<?php else: ?>
									<tr class="text-warning">
										<td colspan="10"> 
											<div class="alert alert-info">No data found!</div>
										</td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
          	</div>

          	<div class="col-12 col-xxl-4">
				<!-- Header -->
				<div class="row align-items-center mb-5">
					<div class="col">
						<!-- Heading -->
						<h2 class="fs-5 mb-0">Top referrers</h2>
					</div>
					<div class="col-auto my-n2">
						<a class="btn btn-link" href="#!">
						Browse all
						<span class="material-symbols-outlined">arrow_right_alt</span>
						</a>
					</div>
				</div>

				<!-- Activity -->
				<div class="card mb-7">
					<div class="card-header">
						<h3 class="fs-6 mb-0">Recent activity</h3>
					</div>
					<div class="card-body">
						<ul class="activity">
							<?= get_logs($admin_data['admin_id']); ?>
						</ul>
					</div>
				</div>

				<!-- Header -->
				<div class="row align-items-center mb-5">
					<div class="col">
						<!-- Heading -->
						<h2 class="fs-5 mb-0">Your details</h2>
					</div>
				</div>

				<!-- Card -->
				<div class="card border-transparent bg-light">
					<span class="badge position-absolute bg-warning top-0 end-0 translate-middle-y me-n2"><?= $admin_data['login_details_device']; ?></span>
					<div class="card-body">
						<h2 class="fs-base mb-1">Location</h2>
						<p class="text-body-secondary"><?= $admin_data['login_details_device'] . ', ' . $admin_data['login_details_os']; ?></p>
						<hr />
						<div class="row align-items-center">
							<div class="col-auto">
								<div class="avatar avatar-sm bg-white fs-lg text-primary"><i data-duoicon="alert-octagon"></i></div>
							</div>
							<div class="col ms-n3">IP: <?= $admin_data['login_details_ip']; ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
    </div>
</main>

<?php include ('system/inc/footer.php'); ?>

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
	

        // check user iddleness
        function is_idle() {
            var type = 'idle';

            $.ajax ({
                method : "POST",
                url : "<?= PROOT; ?>auth/idle.checker.php",
                data : { type : type},
                success : function (data) {
                    console.log(data);
                    if (data != '') {
                        window.location.href = "<?= PROOT; ?>auth/sign-in"
                    }
                }
            })
        }
        // setInterval(updateTime, 1000);

        setInterval(() => {
            // is_idle()
        }, 300000); // referesh after every 30sec

    });
</script>

<?php if (admin_has_permission()): ?>
<script>
    const ctx = document.getElementById('financeChart').getContext('2d');
    let financeChart;
    let chartType = 'line'; // default

    document.getElementById("chartTypeSelect").addEventListener("change", (e) => {
        chartType = e.target.value;
        loadChart(currentYear); // redraw chart with selected type
    });

    // Months labels
    const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

    // Function to load data
    async function loadChart(year) {
        const res = await fetch(`app/controller/analytics.php?year=${year}`);
        const data = await res.json();

        const deposits = Array(12).fill(0);
        const withdrawals = Array(12).fill(0);
        const commissions = Array(12).fill(0);
        
        for (let m in data.deposits) deposits[m-1] = parseFloat(data.deposits[m]);
        for (let m in data.withdrawals) withdrawals[m-1] = parseFloat(data.withdrawals[m]);
        for (let m in data.commissions) commissions[m-1] = parseFloat(data.commissions[m]);

        console.log("Deposits:", deposits);
        console.log("Withdrawals:", withdrawals);
        console.log("Commissions:", commissions);

        if (financeChart) financeChart.destroy(); // Destroy old chart
        const ctx = document.getElementById('financeChart').getContext('2d');

        financeChart = new Chart(ctx, {
            type: chartType,
            data: {
            labels: months,
            datasets: [
                {
                    label: "Deposits",
                    data: deposits,
                    borderColor: "green",
                    backgroundColor: "rgba(0,128,0,0.2)",
                    fill: true,
                    tension: 0.3
                },
                {
                    label: "Withdrawals",
                    data: withdrawals,
                    borderColor: "red",
                    backgroundColor: "rgba(255,0,0,0.2)",
                    fill: true,
                    tension: 0.3
                },
                {
                    label: "Commissions",
                    data: commissions,
                    borderColor: "blue",
                    backgroundColor: "rgba(0,0,255,0.2)",
                    fill: true,
                    tension: 0.3
                }
            ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: `Financial Summary for ${year}`
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: "Amount (GHS)" }
                    }
                }
            }
        });
    }

    // Populate year selector
    const currentYear = new Date().getFullYear();
    const yearSelect = document.getElementById("yearSelect");
    for (let y = currentYear; y >= currentYear - 5; y--) {
        const opt = document.createElement("option");
        opt.value = y;
        opt.text = y;
        if (y === currentYear) opt.selected = true;
        yearSelect.appendChild(opt);
    }

    // Load initial chart
    loadChart(currentYear);

    // Reload on year change
    yearSelect.addEventListener("change", e => loadChart(e.target.value));
</script>
<?php endif; ?>