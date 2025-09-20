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




?>

    <!-- Main -->
    <main class="main px-lg-6">
        <!-- Content -->
        <div class="container-lg">
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
                        <!-- Action -->
                        <a class="btn btn-secondary d-block" href="<?= PROOT; ?>app/customer-new">
                        <span class="material-symbols-outlined me-1">add</span> New customer
                    </a>
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
                    <div id="load-content"></div>
                </div>
            </div>
        </div>

<?php include ('../system/inc/footer.php'); ?>

<script>

    $(document).ready(function() {

        // SEARCH AND PAGINATION FOR LIST
        function load_data(page, query = '') {
            $.ajax({
                url : "<?= PROOT; ?>app/controller/list.customers.php",
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