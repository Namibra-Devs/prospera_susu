<?php 

    // Logs page 
    
    require_once ("../db_connection/conn.php");

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    //
	if (is_array(capital_mover($admin_id)) && capital_mover($admin_id)["msg"] == "touched") {
		redirect(PROOT . 'auth/end-trade-checker');
	}

    include ("../includes/header.inc.php");
    include ("../includes/aside.inc.php");
    include ("../includes/left.nav.inc.php");
    include ("../includes/top.nav.inc.php");

    $today = date("Y-m-d");
    $where = '';
    if (!admin_has_permission()) {
        $where = ' WHERE giltmarket_admin.admin_id = "' . $admin_id . '" AND CAST(giltmarket_logs.createdAt AS date) = "' . $today . '" ';
    }

    $sql = "
        SELECT * FROM giltmarket_logs 
        INNER JOIN giltmarket_admin 
        ON giltmarket_admin.admin_id = giltmarket_logs.log_admin
        $where 
        ORDER BY giltmarket_logs.createdAt DESC
    ";
    $statement = $conn->prepare($sql);
    $statement->execute();
    $count_row = $statement->rowCount();
    $rows = $statement->fetchAll();

    $c_logs = '';
    if ($count_row > 0) {
        $c_logs = '(' . $count_row . ')' ;
    }

?>
    
   <!-- Content -->
   <div class="container-lg">
        <!-- Page header -->
        <div class="row align-items-center mb-7">
            <div class="col-auto">
                <!-- Avatar -->
                <div class="avatar avatar-xl rounded text-warning">
                    <i class="fs-2" data-duoicon="credit-card"></i>
                </div>
            </div>
            <div class="col">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a class="text-body-secondary" href="#">System</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Logs</li>
                    </ol>
                </nav>

                <!-- Heading -->
                <h1 class="fs-4 mb-0">Logs</h1>
            </div>
            <?php if (!admin_has_permission()): ?>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <a class="btn btn-secondary d-block" href="javascript:;" data-bs-target="#buyModal" data-bs-toggle="modal"> <span class="material-symbols-outlined me-1">add</span> New Trade </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Page content -->
        <div class="row">
            <div class="col-12">
                <!-- Filters -->
                <div class="card card-line bg-body-tertiary border-transparent mb-7">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a class="nav-link bg-dark active" aria-current="page" href="<?= PROOT; ?>account/logs">Logs <?= $c_logs; ?></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-12 col-lg">
                                <div class="row gx-3">
                                    <!-- <div class="col col-lg-auto ms-auto">
                                        <div class="input-group bg-body">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" id="search" />
                                            <span class="input-group-text" id="search">
                                                <span class="material-symbols-outlined">search</span>
                                            </span>
                                        </div>
                                    </div> -->
                                    <div class="col col-auto ms-auto">
                                        <div class="dropdown">
                                        <button class="btn btn-dark px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                            <span class="material-symbols-outlined">export_notes</span>
                                        </button>
                                        <div class="dropdown-menu rounded-3 p-6">
                                            <h4 class="fs-lg mb-4">Export trades</h4>
                                            <form style="width: 350px" id="exportForm" action="<?= PROOT; ?>auth/export.logs.php">
                                                <div class="row gx-3">
                                                <div class="col-sm-12 mb-3">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input export_type" type="radio" name="export-status" id="inlineRadioStatus1" required value="all" checked>
                                                            <label class="form-check-label" for="inlineRadioStatus1">All</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 mb-3">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio1" required value="date">
                                                            <label class="form-check-label" for="inlineRadio1">Date</label>
                                                        </div>
                                                        <div class="form-check form-check-inline<?= ((admin_has_permission()) ? '' : ' d-none'); ?>">
                                                            <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio2" required value="month">
                                                            <label class="form-check-label" for="inlineRadio2">Month</label>
                                                        </div>
                                                        <div class="form-check form-check-inline<?= ((admin_has_permission()) ? '' : ' d-none'); ?>">
                                                            <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio3" required value="year">
                                                            <label class="form-check-label" for="inlineRadio3">Year</label>
                                                        </div>
                                                        <div class="form-check form-check-inline<?= ((admin_has_permission()) ? '' : ' d-none'); ?>">
                                                            <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio4" required value="all">
                                                            <label class="form-check-label" for="inlineRadio4">All</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 mb-3 d-none" id="check-date">
                                                        <input type="date" class="form-control form-control-sm" id="export-date" name="export-date" <?= ((admin_has_permission()) ? '' : 'readonly'); ?>  value="<?= date("Y-m-d"); ?>">
                                                    </div>
                                                    <div class="col-sm-12 mb-3 d-none" id="check-month">
                                                        <select id='export-month' name="export-month" class="form-control form-control-sm">
                                                            <option value=''>Select Month</option>
                                                            <option value="1"<?= ((date('m') == '1') ? ' selected' : ''); ?>>January</option>
                                                            <option value='2'<?= ((date('m') == '2') ? ' selected' : ''); ?>>February</option>
                                                            <option value='3'<?= ((date('m') == '3') ? ' selected' : ''); ?>>March</option>
                                                            <option value='4'<?= ((date('m') == '4') ? ' selected' : ''); ?>>April</option>
                                                            <option value='5'<?= ((date('m') == '5') ? ' selected' : ''); ?>>May</option>
                                                            <option value='6'<?= ((date('m') == '6') ? ' selected' : ''); ?>>June</option>
                                                            <option value='7'<?= ((date('m') == '7') ? ' selected' : ''); ?>>July</option>
                                                            <option value='8'<?= ((date('m') == '8') ? ' selected' : ''); ?>>August</option>
                                                            <option value='9'<?= ((date('m') == '9') ? ' selected' : ''); ?>>September</option>
                                                            <option value='10<?= ((date('m') == '10') ? ' selected' : ''); ?>'>October</option>
                                                            <option value='11<?= ((date('m') == '11') ? ' selected' : ''); ?>'>November</option>
                                                            <option value='12<?= ((date('m') == '12') ? ' selected' : ''); ?>'>December</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-12 mb-3 d-none" id="check-year">
                                                        <select id='export-year' name="export-year" class="form-control form-control-sm">
                                                            <option value=''>Select Year</option>
                                                            <?php for ($i = date('Y'); $i >= 2020; $i--) : ?>
                                                                <option value='<?= $i; ?>' <?= ((date('Y') == $i)? 'selected' : ''); ?>><?= $i; ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-12 mb-3">
                                                        <div class="btn-group w-100" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" name="export-type" id="export_xlsx" autocomplete="off" checked value="xlsx" required />
                                                            <label class="btn btn-light" for="export_xlsx" data-bs-toggle="tooltip" data-bs-title="XLSX">
                                                            <img src="<?= PROOT; ?>assets/media/XLSX.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                                            </label>
                                                            <input type="radio" class="btn-check" name="export-type" id="export_csv" autocomplete="off" value="csv" required />
                                                            <label class="btn btn-light" for="export_csv" data-bs-toggle="tooltip" data-bs-title="CSV">
                                                            <img src="<?= PROOT; ?>assets/media/CSV.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                                            </label>
                                                            <input type="radio" class="btn-check" name="export-type" id="export_pdf" autocomplete="off" value="pdf" required />
                                                            <label class="btn btn-light" for="export_pdf" data-bs-toggle="tooltip" data-bs-title="PDF">
                                                            <img src="<?= PROOT; ?>assets/media/PDF.png" class="w-rem-6 rh-rem-6 ounded-circle" alt="...">
                                                            </label>
                                                            <input type="radio" class="btn-check" name="export-type" id="export_xls" autocomplete="off" value="xls" required />
                                                            <label class="btn btn-light" for="export_xls" data-bs-toggle="tooltip" data-bs-title="XLS">
                                                            <img src="<?= PROOT; ?>assets/media/XLS.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <button id="submit-export" type="button" class="btn btn-warning">Export</button>
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
            
            <div class="card">
                <div class="card-header">
                    <h3 class="fs-6 mb-0">Activities</h3>
                </div>
                <div class="card-body">
                    <div class="list-group mb-7">
                        <?php foreach ($rows as $row): ?>
                        <div class="list-group-item bg-body">
                            <div class="row">
                                <div class="col fst-italic"><?= (($row["log_admin"] == $admin_data['admin_id']) ? 'You' : ucwords($row['admin_fullname'])); ?> - <?= $row['log_message']; ?></div>
                                <div class="col-auto fst-italic"><?= pretty_date($row['createdAt']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include ("../includes/footer.inc.php"); ?>

<script>
    $(document).ready(function() {

        $(".export_class").change(function(e) {
            event.preventDefault()
            var select_for = $(".export_class:checked").val();

            if (select_for == 'date') {
                $('#check-date').removeClass('d-none');

                // display none
                $('#check-month').addClass('d-none');
                $('#check-year').addClass('d-none');

                // empty values
                // $('#export-month').val('');
                // $('#export-year').val('');
            } else if (select_for == 'month') {
                $('#check-month').removeClass('d-none');

                // display none
                $('#check-date').addClass('d-none');
                $('#check-year').addClass('d-none');

                // empty values
                // $('#export-date').val('');
                // $('#export-year').val('');
            } else if (select_for == 'year') {
                $('#check-year').removeClass('d-none');

                // display none
                $('#check-month').addClass('d-none');
                $('#check-date').addClass('d-none');

                // empty values
                // $('#export-date').val('');
                // $('#export-month').val('');
            } else {
                // display none
                $('#check-date').addClass('d-none');
                $('#check-month').addClass('d-none');
                $('#check-year').addClass('d-none');

                // empty values
                // $('#export-date').val('');
                // $('#export-month').val('');
                // $('#export-year').val('');
            }
        });


        $('#submit-export').on('click', function() {

            if ($(".export_class:checked").val()) {
                var select_for = $(".export_class:checked").val();

                if (select_for == 'date' && $("#export-date").val() == '') {
                    alert("You will have to select date!");
                    $("#export-date").focus();
                    return false;
                } else if (select_for == 'month' && $("#export-month").val() == '') {
                    alert("You will have to select month!");
                    $("#export-month").focus();
                    return false;
                } else if (select_for == 'year' && $("#export-year").val() == '') {
                    alert("You will have to select year!");
                    $("#export-year").focus();
                    return false;
                }

                // var formData = $('#exportForm');
                // $.ajax({
                //     method : "GET",
                //     url : "<?= PROOT; ?>auth/export",
                //     data : formData.serialize(),
                //     beforeSend : function() {
                //         $('#submit-export').attr('disabled', true);
                //         $('#submit-export').text('Exporting ...');
                //     },
                //     success : function (data) {
                //         console.log(data)
                //         $('#submit-export').attr('disabled', false);
                //         $('#submit-export').text('Export');
                //         location.reload();
                //     },
                //     error : function () {

                //     }
                // })

                $('#submit-export').attr('disabled', true);
                $('#submit-export').text('Exporting ...');
                setTimeout(function () {
                    $('#exportForm').submit();

                    $('#submit-export').attr('disabled', false);
                    $('#submit-export').text('Export');
                    // location.reload();
                }, 2000)
            } else {
                return false;
            }
        });
    })
</script>
