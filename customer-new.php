<?php 
    require ('system/DatabaseConnector.php');
    
	// Check if the user is logged in
	if (!admin_is_logged_in()) {
		admin_login_redirect();
	}
    $body_class = '';
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
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Customers</a></li>
                            <li class="breadcrumb-item active" aria-current="page">New customer</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">New customer</h1>

                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">

                    <!-- Action -->
                    <button class="btn btn-light w-100" type="button">
                        Save draft
                    </button>

                </div>
            </div>

            <!-- Page content -->
            <div class="row">
                <div class="col">

                    <!-- Form -->
                    <form>

                        <section class="card card-line bg-body-tertiary border-transparent mb-5">
                            <div class="card-body">
                                <h3 class="fs-5 mb-1">General</h3>
                                <p class="text-body-secondary mb-5">General information about the project.</p>
                                <hr>
                                <div class="mb-4">
                                    <label class="form-label" for="name">Full name</label>
                                    <input class="form-control bg-body" id="name" type="text" />
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" class="form-control bg-body mb-3" id="phone" placeholder="(___)___-____"
                                    data-inputmask="'mask': '(999)999-9999'">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control bg-body" id="email" placeholder="name@company.com" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="company">Location</label>
                                    <input class="form-control bg-body" id="company" type="text" />
                                </div>
                            </div>
                        </section>

                        <section class="card card-line bg-body-tertiary border-transparent mb-5">
                            <div class="card-body">
                                <h3 class="fs-5 mb-1">Saving plan</h3>
                                <p class="text-body-secondary mb-5">General information about the project.</p>
                                <hr>
                                <div class="mb-4">
                                    <label class="form-label" for="location">Daily amount</label>
                                    <input class="form-control bg-body" id="location" type="text" />
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="location">Target</label>
                                    <input class="form-control bg-body" id="location" type="text" />
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="location">Duration</label>
                                    <input class="form-control bg-body" id="location" type="text" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="projectStartDate">Start date</label>
                                    <input class="form-control bg-body bg-body flatpickr-input" id="projectStartDate" type="text" data-flatpickr="" readonly="readonly">
                                </div>
                            </div>
                        </section>
                        
                        <section class="card bg-body-tertiary border-transparent mb-7">
                            <div class="card-body">
                                <h3 class="fs-5 mb-1">ID details</h3>
                                <p class="text-body-secondary mb-5">Starting files for the project.</p>
                                <hr>
                                <div class="mb-4">
                                    <label class="form-label" for="location">ID</label>
                                    <select class="form-control bg-body" id="location" type="text">
                                        <option value=""></option>
                                        <option value="ghana-card">Ghana Card</option>
                                        <option value="driver-licence">Driver Licence</option>
                                        <option value="voters-id-card">Voters ID card</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="location">ID Number</label>
                                    <input class="form-control bg-body" id="location" type="text" />
                                </div>
                                <div class="row mb-4">
                                    <div class="col">
                                        <div class="mb-0">
                                            <label for="dropzone">Front card</label>
                                            <div class="form-text mt-0 mb-3">Attach files to this customer.</div>
                                            <div class="dropzone dz-clickable" id="dropzone"><div class="dz-default dz-message"><button class="dz-button" type="button">Drop files here to upload</button></div></div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-0">
                                            <label for="dropzone">Back Card</label>
                                            <div class="form-text mt-0 mb-3">Attach files to this customer.</div>
                                            <div class="dropzone dz-clickable" id="dropzone"><div class="dz-default dz-message"><button class="dz-button" type="button">Drop files here to upload</button></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <button type="submit" class="btn btn-secondary w-100">
                            Save customer
                        </button>
                        <button type="reset" class="btn btn-link w-100 mt-3">
                            Reset form
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </main>

<?php include ('system/inc/footer.php'); ?>
<script>
    $(document).ready(function() {

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