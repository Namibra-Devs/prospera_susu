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
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Collector</a></li>
                            <li class="breadcrumb-item active" aria-current="page">New collector</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">New collector</h1>

                </div>
                <div class="col-12 col-sm-auto mt-4 mt-sm-0">

                    <!-- Action -->
                    <button class="btn btn-light w-100" href="<?= goback(); ?>">
                        Go back
                    </button>

                </div>
            </div>

            <!-- Page content -->
            <div class="row">
                <div class="col">

                    <!-- Form -->
                    <form>
                        <div class="mb-4">
                            <label class="form-label" for="name">Full name</label>
                            <input class="form-control" id="name" type="text" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" id="email" name="email" type="email" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="text" class="form-control mb-3" id="phone" name="phone" placeholder="(___)___-____"
                            data-inputmask="'mask': '(999)999-9999'">
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="address">Address</label>
                            <input class="form-control" id="address" name="address" type="text" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="region">Region</label>
                            <input class="form-control" id="region" name="region" type="text" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="city">City</label>
                            <input class="form-control" id="city" name="city" type="text" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="address">Address</label>
                            <input class="form-control" id="address" type="text" />
                        </div>
                        <!-- <div class="mb-4">
                            <label class="form-label mb-0" for="tiptapExample">About</label>
                            <div class="form-text mt-0 mb-3">
                                A brief description of the customer.
                            </div>
                            <di class="form-control" id="tiptapExample"></di>
                        </div> -->
                        <div class="mb-7">
                            <label for="dropzone">Photo</label>
                            <div class="form-text mt-0 mb-3">
                                Attach photo to this collector.
                            </div>
                            <div class="dropzone" id="dropzone"></div>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">
                            Save collector
                        </button>
                        <button type="reset" class="btn btn-link w-100 mt-3">
                            Reset form
                        </button>
                    </form>

                </div>
            </div>
        </div>

<?php include ('../system/inc/footer.php'); ?>

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