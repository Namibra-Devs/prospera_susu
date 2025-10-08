<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
	if (!admin_is_logged_in()) {
		admin_login_redirect();
	}

    $title = 'Collectors | ';
    $body_class = '';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');

    // submit collector form
    $error = '';
    $post = cleanPost($_POST);

    // Collect and sanitize input
    $name     = $post['name'] ?? '';
    $email    = $post['email'] ?? '';
    $phone    = $post['phone'] ?? '';
    $address  = $post['address'] ?? '';
    $region   = $post['region'] ?? '';
    $city     = $post['city'] ?? '';
    $password = $post['password'] ?? '';
    $confirm  = $post['confirm'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Validate required fields
        if (!$name || !$email || !$phone || !$address || !$region || !$city || !$password || !$confirm) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Handle file upload if exists
            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $upload_dir = '../assets/media/uploads/collectors-media/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $filename = uniqid('collector_', true) . '.' . $ext;
                    $photo_path = $upload_dir . $filename;
                    move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
                } else {
                    $error = "Invalid photo file type.";
                }
            }

            if (!$error) {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:m:s"));
                $conn = $dbConnection;
                // Insert into database
                $stmt = $conn->prepare("
                    INSERT INTO collectors (collector_id, collector_name, collector_phone, collector_email, collector_address, collector_state, collector_city, collector_photo, collector_password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $unique_id, $name, $phone, $email, $address, $region, $city, $photo_path, $password_hash
                ]);
                if ($result) {
                    $_SESSION['success_flash'] = "Collector added successfully!";
                    redirect(PROOT . 'app/collectors');
                } else {
                    $error = "Failed to add collector. Please try again.";
                }
            }
        }
    }

    //
    // inactivate collector
    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $delete_id = sanitize($_GET['delete']);

        $sql = $dbConnection->query("UPDATE susu_admins SET admin_status = 'inactive' WHERE admin_id = '" . $delete_id . "'")->execute();
        if ($sql) {
            $log_message =  'Admin [' . $admin_id . '] has set collector [' . $delete_id . '] status to Inactive!';
            add_to_log($log_message, $admin_id, 'admin');
            $_SESSION['flash_success'] = $log_message;
            redirect(PROOT . 'app/collectors');
        } else {
            $_SESSION['flash_success'] = 'Could\'nt update collector status to Inactive!';
            redirect(PROOT . 'app/collectors');
        }
    }



?>

    <!-- Main -->
    <main class="main px-lg-6">
        <!-- Content -->
        <div class="container-lg">
            <?php if (isset($_GET['view'])):
                
                $view = sanitize($_GET['view']);

                // fetch collector data
                $query = "
                    SELECT * FROM susu_admins 
                    WHERE admin_id = ? 
                    AND susu_admins.admin_status = 'active'
                    LIMIT 1
                ";
                $statement = $dbConnection->prepare($query);
                $statement->execute([$view]);
                if ($statement->rowCount() < 1) {
                    $_SESSION['flash_error'] = 'Collector not found!';
                   redirect(PROOT . 'app/collectors');
                } else {
                    $collector_data = $statement->fetch(PDO::FETCH_ASSOC);

                    // get total deposit from a particular collector 
                    $count_customers = count_collector_customers($view);
                    $total_saves = sum_collector_saves($view, '');
                    $total_pending_saves = sum_collector_saves($view, 'Pending');
                    $total_approved_saves = sum_collector_saves($view, 'Approved');

                }
            
            ?>

            <!-- Stats -->
            <div class="row mb-8">
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Registered Customers</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= $count_customers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
                                        <i class="fs-4" data-duoicon="bell-badge"></i>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Total Deposit</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money($total_saves); ?></div>
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
                <div class="col-12 col-md-6 col-xxl-3 mb-4 mb-md-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Pending Deposits</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold">
                                        <?= money($total_pending_saves); ?>
                                        <small class="text-danger fs-sm"><?= money(sum_collector_saves($view, 'Rejected')); ?></small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <!-- Avatar -->
                                    <div class="avatar avatar-lg bg-body text-primary">
                                        <i class="fs-4" data-duoicon="chart-pie"></i>
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
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Approved Deposits</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold"><?= money($total_approved_saves); ?></div>
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
            <div class="row">
                <div class="col-12 col-xxl-4">
                    <div class="position-sticky mb-8" style="top: 40px">
                        <!-- Card -->
                        <div class="card bg-body mb-3">

                            <!-- Avatar -->
                            <div class="avatar avatar-xl rounded-circle mt-n7 mx-auto">
                                <img 
                                    class="avatar-img border border-white border-3" 
                                    src="<?= (($collector_data['admin_profile'] != NULL) ? $collector_data['admin_profile'] : PROOT . 'assets/media/avatar.png'); ?>" 
                                    alt="..." 
                                />
                            </div>

                            <!-- Body -->
                            <div class="card-body text-center">
                            <!-- Heading -->
                                <h1 class="card-title fs-5"><?= ucwords($collector_data['admin_name']); ?></h1>

                                <!-- Text -->
                                <p class="text-body-secondary mb-6"><?= strtoupper($collector_data['admin_permissions']); ?></p>

                                <!-- List -->
                                <ul class="list-group list-group-flush mb-0">
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Email</span>
                                        <span><?= ucwords($collector_data["admin_email"]); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Address</span>
                                        <span><?= ucwords($collector_data["admin_address"]); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Phone</span>
                                        <a class="text-body" href="tel:<?= $collector_data["admin_phone"]; ?>"><?= $collector_data["admin_phone"]; ?></a>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Location</span>
                                        <span><?= ucwords($collector_data["admin_state"] . ', ' . $collector_data["admin_city"]); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Added by</span>
                                        <span>SUPER ADMIN</span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Joined at</span>
                                        <span><?= pretty_date_notime($collector_data["created_at"]); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between bg-body px-0">
                                        <span class="text-body-secondary">Last login</span>
                                        <span class="text-info"><?= pretty_date(person_last_login($collector_data["admin_id"])); ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="row gx-3">
                            <div class="col">
                                <a href="<?= goBack(); ?>" class="btn btn-light w-100"><< Go back</a>
                            </div>
                            <div class="col">
                                <a href="<?= PROOT; ?>app/collectors?c=1&deactivate=<?= $collector_data["admin_id"]; ?>" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to DEACTIVATE this collector ?');">Deactivate</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xxl">
                    <section class="mb-8">
                    <!-- Header -->
                        <div class="d-flex align-items-center justify-content-between mb-5">
                            <h2 class="fs-5 mb-0">Recent activities</h2>
                            <div class="d-flex">
                                <div class="dropdown">
                                    <button class="btn btn-light px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <span class="material-symbols-outlined">filter_list</span>
                                    </button>
                                    <div class="dropdown-menu rounded-3 p-6">
                                        <h4 class="fs-lg mb-4">Filter</h4>
                                        <form style="width: 350px" id="filterForm">
                                            <div class="row align-items-center">
                                                <div class="row align-items-center mb-3">
                                                    <div class="col-3">
                                                        <label class="form-label mb-0" for="filterFromDate">From</label>
                                                    </div>
                                                    <div class="col-9">
                                                        <input type="date" class="form-control" id="filterFromDate">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-3">
                                                <div class="col-3">
                                                    <label class="form-label mb-0" for="filterToDate">To</label>
                                                </div>
                                                <div class="col-9">
                                                    <input type="date" class="form-control" id="filterToDate">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">filter</button>
                                            <br><br>
                                            <a href="javascript:;" id="clearFilter" class="text-sm">clear filter</a>
                                        </form>
                                    </div>
                                </div>
                                <div class="dropdown ms-1">
                                    <button class="btn btn-light px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
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

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-round mb-0">
                            <thead>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Price</th>
                            </thead>
                            <tbody>
                            <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                                <td class="text-body-secondary">#3456</td>
                                <td>Apple MacBook Pro</td>
                                <td>2021-08-12</td>
                                <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                                <td>$2,499</td>
                                </tr>
                                <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                                <td class="text-body-secondary">#3455</td>
                                <td>Apple iPhone 12 Pro</td>
                                <td>2021-08-11</td>
                                <td><span class="badge bg-secondary-subtle text-secondary">Pending</span></td>
                                <td>$1,099</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <script>

                $(document).ready(function() {

                    // SEARCH AND PAGINATION FOR LIST
                    function load_data(page, query = ''<?= admin_has_permission() ? ', filters = {}' : ''; ?>) {
                        $.ajax({
                            url : "<?= PROOT; ?>app/controller/list.collector.transactions.php",
                            method : "POST",
                            data : {
                                page : page, 
                                query : query, 
                                date_from: filters.date_from || '',
                                date_to: filters.date_to || '',
                            },
                            success : function(data) {
                                $("#load-content").html(data);
                            }, 
                            error: function(error) {
                                console.log(error);
                            }
                        });
                    }

                    function getFilters() {
                        return {
                            date_from: $('input[type="date"]').eq(0).val(),
                            date_to: $('input[type="date"]').eq(1).val(),
                        }
                    }

                    load_data(1);

                    $('#search').keyup(function() {
                        var query = $('#search').val();
                        load_data(1, query, getFilters());
                    });

                    // Filter change
                    $('#filterForm input, #filterForm select').on('change', function() {
                        load_data(1, $('#search').val(), getFilters());
                    });

                    // Optional: Add a submit button for filters
                    $('#filterForm').on('submit', function(e) {
                        e.preventDefault();
                        load_data(1, $('#search').val(), getFilters());
                    });

                    // Clear filter functionality
                    $('#clearFilter').on('click', function() {

                        // Clear date inputs
                        $('#filterFromDate').val('');
                        $('#filterToDate').val('');

                        // Clear search input
                        $('#search').val('');

                        // Reload data with cleared filters
                        load_data(1, '', {
                            date_from: '',
                            date_to: ''
                        });
                    });

                    $(document).on('click', '.page-link-go', function() {
                        var page = $(this).data('page_number');
                        var query = $('#search').val();
                        load_data(page, query, getFilters());
                    });
                        
                });
            </script>
            <?php else: ?>

            <!-- Page header -->
            <div class="row align-items-center mb-7">
                <div class="col-auto">
                    <!-- Avatar -->
                    <div class="avatar avatar-xl rounded text-primary">
                        <i class="fs-2" data-duoicon="user"></i>
                    </div>
                </div>
                <div class="col">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Collectors</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Collectors</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">Collectors</h1>
                </div>
                    <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                        <div class="row gx-2">
                            <div class="col-6 col-sm-auto">
                                <!-- Action -->
                                <a class="btn btn-secondary d-block" href="<?= PROOT; ?>app/collector-new">
                                    <span class="material-symbols-outlined me-1">add</span> New collector
                                </a>
                            </div>
                        <div class="col-6 col-sm-auto">
                            <a class="btn btn-light d-block" href="<?= goBack(); ?>"><span class="material-symbols-outlined me-1">arrow_back_ios</span> Go back </a>
                        </div>
                    </div>
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
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="text-body-secondary">No customers selected</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg">
                                    <div class="row gx-3  ">
                                        <div class="col col-lg-auto ms-auto">
                                            <div class="input-group bg-body">
                                                <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" id="search" />
                                                <span class="input-group-text">
                                                    <span class="material-symbols-outlined">search</span>
                                                </span>
                                            </div>
                                        </div>
                                        <!-- <div class="col-auto">
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
                                        </div> -->
                                        <!-- <div class="col-auto ms-n2">
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
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="load-content"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

<?php include ('../system/inc/footer.php'); ?>

<script>

    $(document).ready(function() {

        // SEARCH AND PAGINATION FOR LIST
        function load_data(page, query = '') {
            $.ajax({
                url : "<?= PROOT; ?>app/controller/list.collectors.php",
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

    });
</script>