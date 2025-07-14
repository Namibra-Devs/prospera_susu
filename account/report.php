<?php 

    // change admin password
    require_once ("../db_connection/conn.php");

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    include ("../includes/header.inc.php");
    include ("../includes/aside.inc.php");
    include ("../includes/left.nav.inc.php");
    include ("../includes/top.nav.inc.php");

    // get all supervisors
    $supQuery = "SELECT * FROM giltmarket_admin where admin_permissions = ? AND admin_id != ?";
    $satement = $conn->prepare($supQuery);
    $satement->execute(['supervisor', $admin_id]);
    $sup_count = $satement->rowCount();
    $sup_rows = $satement->fetchAll();
    $sup_output = '';
    if ($sup_count > 0) {
        foreach ($sup_rows as $sup_row) {
            $sup_output .= '
                <option value="' . $sup_row["admin_id"] . '">' . ucwords($sup_row["admin_fullname"]) . '</option>
            ';
        }
    }

    // get all supervisors
    $salQuery = "SELECT * FROM giltmarket_admin where admin_permissions = ? AND admin_id != ?";
    $satement = $conn->prepare($salQuery);
    $satement->execute(['salesperson', $admin_id]);
    $sal_count = $satement->rowCount();
    $sal_rows = $satement->fetchAll();
    $sal_output = '';
    if ($sal_count > 0) {
        foreach ($sal_rows as $sal_row) {
            $sal_output .= '
                <option value="' . $sal_row["admin_id"] . '">' . ucwords($sal_row["admin_fullname"]) . '</option>
            ';
        }
    }


?>

    <!-- Content -->
    <div class="container-lg">
        <!-- Page header -->
        <div class="row align-items-center mb-7">
            <div class="col-auto">
                <!-- Avatar -->
                <div class="avatar avatar-xl rounded text-warning">
                    <i class="fs-2" data-duoicon="clipboard"></i>
                </div>
            </div>
            <div class="col">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Market</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Report</li>
                    </ol>
                </nav>

                <!-- Heading -->
                <h1 class="fs-5 mb-0">Report</h1>
            </div>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <button class="btn btn-warning d-block" onclick="printPageArea('printableArea')"> Print </button>
            </div>
        </div>

        <!-- Page content -->
        <div class="row">
            <div class="col-12 col-lg-3">
                <form class="" id="reportForm">

                    <div class="form-check">
                        <input class="form-check-input role" name="role" type="radio" id="role-1" value="supervisor">
                        <label class="form-check-label" for="role">
                            Supervisor
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input role" name="role" type="radio" id="role-2" value="salesperson">
                        <label class="form-check-label" for="role">
                            Salespersonnel
                        </label>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input role" name="role" type="radio" id="role-3" value="all">
                        <label class="form-check-label" for="role">
                            All
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="admin">Admin:</label>
                        <select class="form-control sup d-none" id="sup_admin" name="sup_admin" type="text">
                            <option value=""></option>
                            <?= $sup_output; ?>
                        </select>
                        <select class="form-control sal d-none" id="sal_admin" name="sal_admin" type="text">
                            <option value=""></option>
                            <?= $sal_output; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="from">From:</label>
                        <input class="form-control" name="from" id="from" type="date">
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="to">To:</label>
                        <input class="form-control" name="to" id="to" type="date">
                    </div>
                    <button type="button" class="btn btn-dark" id="submitReport">Submit</button>
                    <button type="button" class="btn clear">Clear</button>
                </form>
            </div>

            <div class="col-12 col-lg-9" id="printableArea">
                <!-- General -->
                <section class="card bg-body-tertiary border-transparent mb-5" id="general">
                    <div class="card-body">
                        <h2 class="fs-5 mb-1">Report view</h2>
                        <div id="load-data"></div>
                    </div>
                </section>
             </div>
        </div>
    </div>

<?php include ("../includes/footer.inc.php"); ?>

<script type="text/javascript">

    function printPageArea(areaID) {
        var printContent = document.getElementById(areaID).innerHTML;
        var originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        $('head').append(`
            <style>
                @page {
                    size: landscape;
                }

                @media print {
                    @page {
                        margin: 0 !important;
                    }

                    body {
                        /* padding: 75px;  This will act as your margin. Originally, the margin will hide the header and footer text. */
                    }
                }
            </style>
        `);
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }

    $(document).ready(function() {
        // sclear form
        $('.clear').on('click', function(event) {
            event.preventDefault()

            $('.sal').addClass('d-none')
            $('.sup').addClass('d-none')

            $('#submitReport').attr('disabled', false);
            $('#submitReport').text('Submit');

            $('#reportForm')[0].reset();
        })


        $('input[name="role"]').click(function(event) {
            var role = $('input[name="role"]:checked').val();
            if ($('input[name="role"]').is(':checked')) {
                if (role == 'supervisor') {
                    $('.sup').removeClass('d-none')
                    $('.sal').addClass('d-none')
                } else if (role == 'salesperson') {
                    $('.sal').removeClass('d-none')
                    $('.sup').addClass('d-none')
                }
            } else {
                return false;
            }
        });
        
        $('#submitReport').on('click', function() {

            var formData = $('#reportForm');
            var from = $('#from').val();
            var to = $('#to').val();
            var role = $('input[name="role"]:checked').val();

            const firstDate = new Date(from)
            const secondDate = new Date(to)

            if ($('input[name="role"]').is(':checked')) {
                if (from != '' && to != '') {
                    if (firstDate > secondDate) {
                        alert('From date is later than To date!');
                        return false;
                    }
                }
                
                $.ajax({
                    method : "POST",
                    url : "<?= PROOT; ?>auth/generate.report.php",
                    data : formData.serialize(),
                    beforeSend : function() {
                        $('#submitReport').attr('disabled', true);
                        $('#submitReport').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Loading ...</span>');;
                    },
                    success : function (data) {
                        console.log(data)
                        $('#load-data').html(data);

                        $('#submitReport').attr('disabled', false);
                        $('#submitReport').text('Submit');

                        // $('#reportForm')[0].reset();

                        // location.reload();
                    },
                    error : function () {

                    }
                })
                
            } else {
                alert('Select role!');
                return false;
            }

        })

    });
</script>
