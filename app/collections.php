<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
	if (!admin_is_logged_in()) {
		admin_login_redirect();
	}
    // if (admin_has_permission('admin')) {
    //     redirect(PROOT . 'auth/sign-in');
    // }
    $view = 0;


    $body_class = '';
    $title = 'Collections | ';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');

    //
    // Deposite status
    if (isset($_GET['c']) && !empty($_GET['c'])) {
        // verify collection
        if (isset($_GET['verify']) && !empty($_GET['verify'])) {
            $verify_id = sanitize($_GET['verify']);

            $sql = $dbConnection->query("UPDATE daily_collections SET daily_status = 'Verified' WHERE daily_id = '".$verify_id."'")->execute();
            if ($sql) {
                $log_message =  'Admin [' . $admin_id . '] has set collection [' . $verify_id . '] record status to Verified!';
                add_to_log($log_message, $admin_id, 'admin');
                $_SESSION['flash_success'] = $log_message;
                redirect(PROOT . 'app/collections');
            } else {
                $_SESSION['flash_success'] = 'Could\'nt update collection file status to Verified!';
                redirect(PROOT . 'app/collections');
            }
        }

        // delete collection
        if (isset($_GET['delete']) && !empty($_GET['delete'])) {
            $delete_id = sanitize($_GET['delete']);

            $sql = $dbConnection->query("DELETE FROM daily_collections WHERE daily_id = '".$delete_id."'")->execute();
            if ($sql) {

                // delete file from directory
                $filename = $_GET['f'];
                $filepath = BASEURL . 'assets/media/uploads/collection-files/' . $filename;
                $unlink = unlink($filepath);

                $log_message =  'Admin [' . $admin_id . '] has delete collection [' . $delete_id . '] record!';
                add_to_log($log_message, $admin_id, 'admin');
                $_SESSION['flash_success'] = $log_message;
                redirect(PROOT . 'app/collections');
            } else {
                $_SESSION['flash_success'] = 'Could\'nt delete collection file!';
                redirect(PROOT . 'app/collections');
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
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Collections</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Collections</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">Collections</h1>
                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                    <div class="row gx-2">
                        <div class="col-6 col-sm-auto">

                            <!-- Action -->
                            <?php if (admin_has_permission('collector') && !admin_has_permission('admin')): ?>
                            <button class="btn btn-secondary d-block" href="javascript:;" type="button" data-bs-toggle="modal" data-bs-target="#todayUploadModal">
                                <span class="material-symbols-outlined me-1">add_a_photo</span> Upload new collection file
                            </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" type="button" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                                <span class="material-symbols-outlined me-1">payment_arrow_down</span> Make new withdrawal
                            </button>
                            <?php endif; ?>

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
                                            <div class="text-body-secondary">No collection selected</div>
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
                url : "<?= PROOT; ?>app/controller/list.collections.php",
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