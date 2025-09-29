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
                                        <h4 class="fs-sm fw-normal text-body-secondary mb-1">Loans</h4>

                                        <!-- Text -->
                                        <div class="fs-4 fw-semibold">14.5%</div>
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
                <div class="row align-items-center mb-2">
                    <div class="col">
                        <!-- Heading -->
                        <h2 class="fs-3 mb-0">Performance</h2>
                    </div>
                    <div class="col-auto my-n2">
                        <button id="toggleChartType" class="btn btn-primary">Switch to Bar</button>
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
                            <a class="btn btn-link my-n2" href="../ecommerce/orders.html">
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
                                    <th class="fs-sm">Subscription Plan</th>
                                    <th class="fs-sm">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                            <td>#001</td>
                            <td>
                                <div class="d-flex align-items-center text-nowrap">
                                <div class="avatar avatar-xs me-2">
                                    <img class="avatar-img" src="../assets/img/photos/photo-2.jpg" alt="..." />
                                </div>
                                Michael Johnson
                                </div>
                            </td>
                            <td>$499.00</td>
                            <td>Enterprise</td>
                            <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                            </tr>
                            <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#002</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-1.jpg" alt="..." />
                          </div>
                          Emily Thompson
                        </div>
                      </td>
                      <td>$99.00</td>
                      <td>Pro</td>
                      <td><span class="badge bg-warning-subtle text-warning">Pending</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#003</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-2.jpg" alt="..." />
                          </div>
                          Michael Johnson
                        </div>
                      </td>
                      <td>$999.00</td>
                      <td>Enterprise</td>
                      <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#004</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-5.jpg" alt="..." />
                          </div>
                          Jessica Miller
                        </div>
                      </td>
                      <td>$49.00</td>
                      <td>Basic</td>
                      <td><span class="badge bg-danger-subtle text-danger">Failed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#005</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-4.jpg" alt="..." />
                          </div>
                          Olivia Davis
                        </div>
                      </td>
                      <td>$199.00</td>
                      <td>Pro</td>
                      <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#006</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-2.jpg" alt="..." />
                          </div>
                          Michael Johnson
                        </div>
                      </td>
                      <td>$49.00</td>
                      <td>Basic</td>
                      <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#007</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-1.jpg" alt="..." />
                          </div>
                          Emily Thompson
                        </div>
                      </td>
                      <td>$499.00</td>
                      <td>Enterprise</td>
                      <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#008</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-2.jpg" alt="..." />
                          </div>
                          Michael Johnson
                        </div>
                      </td>
                      <td>$199.00</td>
                      <td>Pro</td>
                      <td><span class="badge bg-warning-subtle text-warning">Pending</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#009</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-5.jpg" alt="..." />
                          </div>
                          Jessica Miller
                        </div>
                      </td>
                      <td>$49.00</td>
                      <td>Basic</td>
                      <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#010</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-4.jpg" alt="..." />
                          </div>
                          Olivia Davis
                        </div>
                      </td>
                      <td>$199.00</td>
                      <td>Pro</td>
                      <td><span class="badge bg-danger-subtle text-danger">Failed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#011</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-6.jpg" alt="..." />
                          </div>
                          Ethan Parker
                        </div>
                      </td>
                      <td>$499.00</td>
                      <td>Enterprise</td>
                      <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#012</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-1.jpg" alt="..." />
                          </div>
                          Sophia Lee
                        </div>
                      </td>
                      <td>$99.00</td>
                      <td>Pro</td>
                      <td><span class="badge bg-warning-subtle text-warning">Pending</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#013</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-2.jpg" alt="..." />
                          </div>
                          Jack Miller
                        </div>
                      </td>
                      <td>$49.00</td>
                      <td>Basic</td>
                      <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#014</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-4.jpg" alt="..." />
                          </div>
                          Emily Johnson
                        </div>
                      </td>
                      <td>$199.00</td>
                      <td>Pro</td>
                      <td><span class="badge bg-danger-subtle text-danger">Failed</span></td>
                    </tr>
                    <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                      <td>#015</td>
                      <td>
                        <div class="d-flex align-items-center text-nowrap">
                          <div class="avatar avatar-xs me-2">
                            <img class="avatar-img" src="../assets/img/photos/photo-6.jpg" alt="..." />
                          </div>
                          Noah Brown
                        </div>
                      </td>
                      <td>$99.00</td>
                      <td>Pro</td>
                      <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                    </tr>
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

            <div class="card mb-7">
              <div class="card-body">
                <!-- List -->
                <div class="vstack gap-2 mb-6">
                  <div class="row align-items-center gx-3">
                    <div class="col-auto"><span class="material-symbols-outlined text-primary me-1">circle</span> Google</div>
                    <div class="col"><hr style="border-style: dashed" /></div>
                    <div class="col-auto fs-sm text-body-secondary">52%</div>
                  </div>
                  <div class="row align-items-center gx-3">
                    <div class="col-auto"><span class="material-symbols-outlined text-success me-1">circle</span> Facebook</div>
                    <div class="col"><hr style="border-style: dashed" /></div>
                    <div class="col-auto fs-sm text-body-secondary">18%</div>
                  </div>
                  <div class="row align-items-center gx-3">
                    <div class="col-auto"><span class="material-symbols-outlined text-warning me-1">circle</span> LinkedIn</div>
                    <div class="col"><hr style="border-style: dashed" /></div>
                    <div class="col-auto fs-sm text-body-secondary">12%</div>
                  </div>
                  <div class="row align-items-center gx-3">
                    <div class="col-auto"><span class="material-symbols-outlined text-dark me-1">circle</span> Direct</div>
                    <div class="col"><hr style="border-style: dashed" /></div>
                    <div class="col-auto fs-sm text-body-secondary">18%</div>
                  </div>
                </div>

                <!-- Progress -->
                <div class="progress-stacked gap-1" style="--bs-progress-height: 1.5rem; --bs-progress-bg: var(--body-bg)">
                  <div
                    class="progress"
                    role="progressbar"
                    aria-label="Google"
                    aria-valuenow="52"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: 52%; --bs-progress-height: inherit"
                  >
                    <div class="progress-bar rounded bg-primary" data-bs-toggle="tooltip" data-bs-title="Google">52%</div>
                  </div>
                  <div
                    class="progress"
                    role="progressbar"
                    aria-label="Facebook"
                    aria-valuenow="18"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: 18%; --bs-progress-height: inherit"
                  >
                    <div class="progress-bar rounded bg-success" data-bs-toggle="tooltip" data-bs-title="Facebook">18%</div>
                  </div>
                  <div
                    class="progress"
                    role="progressbar"
                    aria-label="LinkedIn"
                    aria-valuenow="12"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: 12%; --bs-progress-height: inherit"
                  >
                    <div class="progress-bar rounded bg-warning" data-bs-toggle="tooltip" data-bs-title="LinkedIn">12%</div>
                  </div>
                  <div
                    class="progress"
                    role="progressbar"
                    aria-label="Direct"
                    aria-valuenow="18"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: 18%; --bs-progress-height: inherit"
                  >
                    <div class="progress-bar rounded bg-dark" data-bs-toggle="tooltip" data-bs-title="Direct">18%</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Header -->
            <div class="row align-items-center mb-5">
              <div class="col">
                <!-- Heading -->
                <h2 class="fs-5 mb-0">Support tickets</h2>
              </div>
              <div class="col-auto my-n2">
                <a class="btn btn-link" href="#!">
                  Browse all
                  <span class="material-symbols-outlined">arrow_right_alt</span>
                </a>
              </div>
            </div>

            <!-- Cards -->
            <div class="vstack gap-2 mb-7">
              <div class="card">
                <a class="card-body p-4" data-bs-toggle="collapse" href="#supportTicketOne" role="button" aria-expanded="true" aria-controls="supportTicketOne">
                  <div class="row align-items-center">
                    <div class="col-auto">
                      <div class="avatar avatar-sm fs-lg text-primary"><i data-duoicon="smartphone"></i></div>
                    </div>
                    <div class="col ms-n3">
                      <h5 class="fs-sm fw-normal text-body-secondary mb-1">#10245</h5>
                      <h3 class="fs-base mb-0">Login issues on mobile</h3>
                    </div>
                    <div class="col-auto">
                      <span class="fs-sm text-body-secondary">Oct 01</span>
                    </div>
                  </div>
                </a>
                <div class="collapse show" id="supportTicketOne">
                  <div class="card-body border-top line-clamp-2 text-body-secondary py-4 px-0 mx-4">
                    A user reports being unable to log in on the mobile app. They've tried resetting their password but continue to receive an error message.
                    Further investigation needed.
                  </div>
                </div>
              </div>

              <div class="card">
                <a
                  class="card-body p-4"
                  data-bs-toggle="collapse"
                  href="#supportTicketTwo"
                  role="button"
                  aria-expanded="false"
                  aria-controls="supportTicketTwo"
                >
                  <div class="row align-items-center">
                    <div class="col-auto">
                      <div class="avatar avatar-sm fs-lg text-primary"><i data-duoicon="credit-card"></i></div>
                    </div>
                    <div class="col ms-n3">
                      <h5 class="fs-sm fw-normal text-body-secondary mb-1">#10245</h5>
                      <h3 class="fs-base mb-0">Payment not processing</h3>
                    </div>
                    <div class="col-auto">
                      <span class="fs-sm text-body-secondary">Sep 29</span>
                    </div>
                  </div>
                </a>
                <div class="collapse" id="supportTicketTwo">
                  <div class="card-body border-top line-clamp-2 text-body-secondary py-4 px-0 mx-4">
                    Customer is experiencing issues with the payment gateway. Transaction attempts are failing without a clear error message. This affects both
                    credit card and PayPal payments.
                  </div>
                </div>
              </div>

              <div class="card">
                <a
                  class="card-body p-4"
                  data-bs-toggle="collapse"
                  href="#supportTicketThree"
                  role="button"
                  aria-expanded="false"
                  aria-controls="supportTicketThree"
                >
                  <div class="row align-items-center">
                    <div class="col-auto">
                      <div class="avatar avatar-sm fs-lg text-primary"><i data-duoicon="moon-2"></i></div>
                    </div>
                    <div class="col ms-n3">
                      <h5 class="fs-sm fw-normal text-body-secondary mb-1">#10245</h5>
                      <h3 class="fs-base mb-0">Feature request: dark mode toggle</h3>
                    </div>
                    <div class="col-auto">
                      <span class="fs-sm text-body-secondary">Sep 28</span>
                    </div>
                  </div>
                </a>
                <div class="collapse" id="supportTicketThree">
                  <div class="card-body border-top line-clamp-2 text-body-secondary py-4 px-0 mx-4">
                    A user has requested a toggle for dark mode in the app settings. They would like a way to switch themes without having to rely on the
                    system’s default settings.
                  </div>
                </div>
              </div>
            </div>

            <!-- Header -->
            <div class="row align-items-center mb-5">
              <div class="col">
                <!-- Heading -->
                <h2 class="fs-5 mb-0">API usage limits</h2>
              </div>
            </div>

            <!-- Card -->
            <div class="card border-transparent bg-light">
              <span class="badge position-absolute bg-warning top-0 end-0 translate-middle-y me-n2">75% Used</span>
              <div class="card-body">
                <h2 class="fs-base mb-1">ChatGPT</h2>
                <p class="text-body-secondary">You're approaching your monthly limit.</p>
                <hr />
                <div class="row align-items-center">
                  <div class="col-auto">
                    <div class="avatar avatar-sm bg-white fs-lg text-primary"><i data-duoicon="alert-octagon"></i></div>
                  </div>
                  <div class="col ms-n3">25,000 / 30,000 calls used</div>
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

<script>
    const ctx = document.getElementById('financeChart').getContext('2d');
    let financeChart;
    let chartType = 'line'; // default

    document.getElementById("toggleChartType").addEventListener("click", () => {
        chartType = (chartType === 'line') ? 'bar' : 'line';
        document.getElementById("toggleChartType").innerText =
            (chartType === 'line') ? "Switch to Bar" : "Switch to Line";
        loadChart(currentYear); // re-render chart with new type
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