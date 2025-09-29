<?php 

    // change admin password
    require ('system/DatabaseConnector.php');
    
	// Check if the admin or collector is logged in
    if (!admin_is_logged_in()) {
        redirect(PROOT . 'auth/sign-in');
    }

    $body_class = '';
    $title = 'Acount Settings | ';
    include ('system/inc/head.php');
    include ('system/inc/modals.php');
    include ('system/inc/sidebar.php');
    include ('system/inc/topnav-base.php');
    include ('system/inc/topnav.php');


    $errors = '';
    $admin_fullname = ((isset($_POST['admin_name'])) ? sanitize($_POST['admin_name']) : $admin_data['admin_name']);
    $admin_email = ((isset($_POST['admin_email'])) ? sanitize($_POST['admin_email']) : $admin_data['admin_email']);
    $admin_phone = ((isset($_POST['admin_phone'])) ? sanitize($_POST['admin_phone']) : $admin_data['admin_phone']);

    if ($_POST) {
        if (empty($_POST['admin_email']) && empty($_POST['admin_email'])) {
            $errors = 'Fill out all empty fileds';
        }

        if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $errors = 'The email you provided is not valid';
        }

        if (!empty($errors)) {
            $errors;
        } else {
            $data = [$admin_fullname, $admin_email, $admin_phone, $admin_data['admin_id']];
            $query = "
                UPDATE susu_admins 
                SET admin_name = ?, admin_email = ?, admin_phone = ?  
                WHERE admin_id = ?
            ";
            $statement = $conn->prepare($query);
            $result = $statement->execute($data);
            if (isset($result)) {

                $log_message = 'Admin [' . $admin_id . '] has updated profile details!';
                add_to_log($message, $admin_data['admin_id'], 'admin');

                $_SESSION['flash_success'] = 'Admin has been updated!';
                redirect(PROOT . "account/profile");
            } else {
                echo js_alert("Something went wrong!");
                redirect(PROOT . "account/profile");
            }
        }
    }

?>

    <main class="main px-lg-6">

        <!-- Content -->
        <div class="container-lg">
            <!-- Page header -->
            <div class="row align-items-center mb-7">
                <div class="col-auto">
                    <!-- Avatar -->
                    <div class="avatar avatar-xl rounded text-warning">
                        <i class="fs-2" data-duoicon="user"></i>
                    </div>
                </div>
                <div class="col">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Account</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Account Settings</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-5 mb-0">Account</h1>
                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                    <!-- Action -->
                    <button class="btn btn-warning d-block" id="submitForm"> Save </button>
                </div>
            </div>

            <!-- Page content -->
            <div class="row">
                <div class="col-12 col-lg-3">
                    <!-- Nav -->
                    <nav class="nav nav-pills position-sticky flex-column mb-8" id="accountNav" style="top: 2rem">
                        <a class="nav-link" href="<?= PROOT; ?>account/profile">General</a>
                        <a class="nav-link" href="<?= PROOT; ?>account/settings">Update account</a>
                        <a class="nav-link" href="<?= PROOT; ?>account/change-password">Change password</a>
                        <a class="nav-link text-danger" href="<?= PROOT; ?>auth/logout">Logout</a>
                    </nav>
                </div>
                <div class="col-12 col-lg-9" data-bs-spy="scroll" data-bs-target="#accountNav" data-bs-smooth-scroll="true" tabindex="0">
                    
                    <!-- General -->
                    <section class="card bg-body-tertiary border-transparent mb-5" id="general">
                        <div class="card-body">
                            <h2 class="fs-5 mb-1">Settings</h2>
                            <p class="text-body-secondary">Update your general account information.</p>
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <label class="form-label">Avatar</label>
                                </div>
                                <div class="col-md-8 col-xl-5">
                                    <div class="" id="upload_profile">
                                        <div class="d-flex align-items-center">
                                            <a href="<?= (($admin_data['admin_profile'] != NULL) ? PROOT . $admin_data['admin_profile'] : 'javascript:;'); ?>" class="avatar avatar-lg bg-warning rounded-circle text-white">
                                                <img src="<?= PROOT . (($admin_data['admin_profile'] == NULL) ? 'assets/media/avatar.png' : 'assets/media/uploads/collectors-media/' . basename($admin_data['admin_profile'])); ?>" style="object-fit: cover; object-position: center; width: 35px; height: 35px" alt="<?=ucwords($admin_data['admin_name']); ?>'s profile.">
                                            </a>
                                            <div class="hstack gap-2 ms-5">
                                                <?php if ($admin_data['admin_profile'] == NULL): ?>
                                                <label for="file_upload" class="btn btn-sm btn-neutral">
                                                    <span>Upload</span> 
                                                    <input type="file" name="file_upload" id="file_upload" class="visually-hidden">
                                                </label>
                                                <?php else: ?>
                                                <a href="javascript:;" class="btn d-inline-flex btn-sm btn-neutral text-danger change-profile-picture" id="<?=  $admin_data['admin_profile']; ?>">
                                                    <span><i class="bi bi-trash"></i> </span>
                                                    <span class="d-none d-sm-block me-2">Remove</span>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <form method="POST" id="settingsForm">
                                <div class="text-danger mb-3"><?= $errors; ?></div>
                                <div class="mb-4">
                                    <label for="admin_fullname" class="form-label">Full Name</label>
                                    <input type="text" class="form-control bg-body" name="admin_fullname" id="admin_fullname" value="<?= $admin_fullname; ?>" required>
                                    <div class="text-sm text-muted">Change your full name in this field</div>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="form-label">Position / Title</label>
                                    <input type="text" class="form-control bg-body" disabled value="<?= strtoupper($admin_data['admin_permissions']); ?>">
                                </div>
                                <div class="mb-4">
                                    <label for="admin_email" class="form-label">Email</label>
                                    <input type="email" class="form-control bg-body" name="admin_email" id="admin_email" value="<?= $admin_email; ?>" required>
                                    <div class="text-sm text-muted">Change your email in this field</div>
                                </div>
                                <div class="mb-4">
                                    <label for="admin_phone" class="form-label">Phone number</label>
                                    <input type="text" class="form-control bg-body" name="admin_phone" id="admin_phone" value="<?= $admin_phone; ?>" data-inputmask="'mask': '(999)999-9999'" required>
                                    <div class="text-sm text-muted">Change your phone number in this field</div>
                                </div>
                            </form>
                        </div>
                    </section>

                </div>

            </div>
        </div>

<?php include ('system/inc/footer.php'); ?>

<script type="text/javascript">
    $(document).ready(function() {

        // Upload IMAGE Temporary
        $(document).on('change','#file_upload', function() {

            var property = document.getElementById("file_upload").files[0];
            var image_name = property.name;

            var image_extension = image_name.split(".").pop().toLowerCase();
            if (jQuery.inArray(image_extension, ['jpeg', 'png', 'jpg']) == -1) {
                alert("The file extension must be .jpg, .png, .jpeg");
                $('#file_upload').val('');
                return false;
            }

            var image_size = property.size;
            if (image_size > 15000000) {
                alert('The file size must be under 15MB');
                return false;
            } else {

                var form_data = new FormData();
                form_data.append("file_upload", property);
                $.ajax({
                    url: "<?= PROOT; ?>auth/upload.admin.profile.picture.php",
                    method: "POST",
                    data: form_data,
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function() {
                        $("#upload_profile").html("<div class='text-success font-weight-bolder'>Uploading profile picture ...</div>");
                    },
                    success: function(data) {
                        if (data == '') {
                            location.reload();
                        } else {
                            alert(data);
                        }
                    }
                });
            }
        });

        // DELETE TEMPORARY UPLOADED IMAGE
        $(document).on('click', '.change-profile-picture', function() {
            var tempuploded_file_id = $(this).attr('id');

            $.ajax ({
                url : "<?= PROOT; ?>auth/delete.admin.profile.picture.php",
                method : "POST",
                data : {
                    tempuploded_file_id : tempuploded_file_id
                },
                success: function(data) {
                    location.reload();
                }
            });
        });

        // save account details
        $('#submitForm').on('click', function() {

            $('#submitForm').attr('disabled', true);
            $('#submitForm').text('Saving ...');

            setTimeout(function () {
                $('#settingsForm').submit();

                $('#submitForm').attr('disabled', false);
                $('#submitForm').text('Save');
            }, 2000)

        })
    });
    
</script>
