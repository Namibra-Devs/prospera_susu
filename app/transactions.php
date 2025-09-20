<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
	if (!admin_is_logged_in()) {
		admin_login_redirect();
	}

    $body_class = '';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');




?>

    <!-- Main -->
    <main class="main px-lg-6">
        <!-- Content -->
        <div class="container-lg">

            <!-- Stats -->
            <div class="row mb-8">
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Today</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
                                        <i class="fs-4" data-duoicon="payment-card"></i>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">This week</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(0); ?></div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">This month</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(0); ?></div>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Total</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money(0); ?></div>
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
            </div>

            <!-- Page content -->
            

            <div class="row">
                <div class="col-12 col-xxl">
                    <section class="mb-8">

                    


                        <!-- Filters -->
            <div class="card card-line bg-body-tertiary border-transparent mb-7">
              <div class="card-body p-4">
                <div class="row align-items-center">
                  <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <div class="text-body-secondary">No customers selected</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-lg"><div class="row gx-3  ">
  <div class="col col-lg-auto ms-auto">
    <div class="input-group bg-body">
      <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" />
      <span class="input-group-text" id="search">
        <span class="material-symbols-outlined">search</span>
      </span>
    </div>
  </div>
  <div class="col-auto">
    <div class="dropdown">
      <button class="btn btn-dark px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <span class="material-symbols-outlined">filter_list</span>
      </button>
      <div class="dropdown-menu rounded-3 p-6">
        <h4 class="fs-lg mb-4">Filter</h4>
        <form style="width: 350px" id="filterForm">
          <div class="row align-items-center mb-3">
            <div class="col-3">
              <label class="form-label mb-0" for="filterUser">User</label>
            </div>
            <div class="col-9">
              <select
                class="form-select"
                id="filterUser"
                data-choices='{"searchEnabled": false, "choices": [
              {
                "value": "Emily Thompson",
                "label": "Emily Thompson",
                "customProperties": {
                  "avatarSrc": "../assets/img/photos/photo-1.jpg"
                }
              },
              {
                "value": "Michael Johnson",
                "label": "Michael Johnson",
                "customProperties": {
                  "avatarSrc": "../assets/img/photos/photo-2.jpg"
                }
              },
              {
                "value": "Robert Garcia",
                "label": "Robert Garcia",
                "customProperties": {
                  "avatarSrc": "../assets/img/photos/photo-3.jpg"
                }
              },
              {
                "value": "Jessica Miller",
                "label": "Jessica Miller",
                "customProperties": {
                  "avatarSrc": "../assets/img/photos/photo-4.jpg"
                }
              }
            ]}'
              ></select>
            </div>
          </div>
          <div class="row align-items-center mb-3">
            <div class="col-3">
              <label class="form-label mb-0" for="filterCompany">Company</label>
            </div>
            <div class="col-9">
              <select class="form-select" id="filterCompany" data-choices='{"placeholder": "some"}'>
                <option value="TechPinnacle Solutions">TechPinnacle Solutions</option>
                <option value="Quantum Dynamics">Quantum Dynamics</option>
                <option value="Pinnacle Technologies">Pinnacle Technologies</option>
                <option value="Apex Innovations">Apex Innovations</option>
              </select>
            </div>
          </div>
          <div class="row align-items-center">
            <div class="col-3">
              <label class="form-label mb-0" for="filterLocation">Location</label>
            </div>
            <div class="col-9">
              <select class="form-select" id="filterLocation" data-choices>
                <option value="San Francisco, CA">San Francisco, CA</option>
                <option value="Austin, TX">Austin, TX</option>
                <option value="Miami, FL">Miami, FL</option>
                <option value="Seattle, WA">Seattle, WA</option>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-auto ms-n2">
    <div class="dropdown">
      <button class="btn btn-dark px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <span class="material-symbols-outlined">sort_by_alpha</span>
      </button>
      <div class="dropdown-menu rounded-3 p-6">
        <h4 class="fs-lg mb-4">Sort</h4>
        <form style="width: 350px" id="filterForm">
          <div class="row gx-3">
            <div class="col">
              <select class="form-select" id="sort" data-choices='{"searchEnabled": false}'>
                <option value="user">User</option>
                <option value="company">Company</option>
                <option value="phone">Phone</option>
                <option value="location">Location</option>
              </select>
            </div>
            <div class="col-auto">
              <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="sortRadio" id="sortAsc" autocomplete="off" checked />
                <label class="btn btn-light" for="sortAsc" data-bs-toggle="tooltip" data-bs-title="Ascending">
                  <span class="material-symbols-outlined">arrow_upward</span>
                </label>
                <input type="radio" class="btn-check" name="sortRadio" id="sortDesc" autocomplete="off" />
                <label class="btn btn-light" for="sortDesc" data-bs-toggle="tooltip" data-bs-title="Descending">
                  <span class="material-symbols-outlined">arrow_downward</span>
                </label>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>
                </div>
              </div>
            </div>





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
                                </tbody>
                            </table>
                        </div>
                    </div>
            </section>
                </div>
            </div>
        </div>
    
<?php include ('../system/inc/footer.php'); ?>

<script>

    $(document).ready(function() {

    });
</script>