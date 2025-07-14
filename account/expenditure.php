<?php 

    // expenditure
    require_once ("../db_connection/conn.php");

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    if ($admin_permission == 'supervisor') {
        redirect(PROOT . 'index');
    }

    //
	if (is_array(capital_mover($admin_id)) && capital_mover($admin_id)["msg"] == "touched") {
		redirect(PROOT . 'auth/end-trade-checker');
	}

    $today = date("Y-m-d");
    $where = '';
    if (!admin_has_permission()) {
        $where = ' AND sale_by = "' . $admin_id . '" AND CAST(giltmarket_sales.createdAt AS date) = "' . $today . '" ';
    }
    $total_exp = $conn->query("SELECT * FROM giltmarket_sales INNER JOIN giltmarket_admin ON giltmarket_admin.admin_id = giltmarket_sales.sale_by WHERE giltmarket_sales.sale_status = 0 AND giltmarket_sales.sale_type = 'exp' $where")->rowCount();
    $count_exp = '';
    if ($total_exp > 0) {
        $count_exp = ' (' . $total_exp . ')';
    }

    include ("../includes/header.inc.php");
    include ("../includes/aside.inc.php");
    include ("../includes/left.nav.inc.php");
    include ("../includes/top.nav.inc.php");

    $by = $admin_data['admin_id'];
    $for_amount = ((isset($_POST['for_amount']) && !empty($_POST['for_amount'])) ? sanitize($_POST['for_amount']) : '');
    $what_for = ((isset($_POST['what_for']) && !empty($_POST['what_for'])) ? sanitize($_POST['what_for']) : '');

    if (isset($_GET['edit']) && !empty($_GET['edit'])) {
        $id = sanitize($_GET['edit']);

        $sql = "
            SELECT * FROM giltmarket_sales 
            WHERE sale_id = ? 
            AND sale_by = ?
            LIMIT 1 
        ";
        $statement = $conn->prepare($sql);
        $statement->execute([$id, $by]);
        $_row = $statement->fetchAll();

        if ($statement->rowCount()) {
            $for_amount = ((isset($_POST['for_amount']) && !empty($_POST['for_amount'])) ? sanitize($_POST['for_amount']) : $_row[0]['sale_total_amount']);
            $what_for = ((isset($_POST['what_for']) && !empty($_POST['what_for'])) ? sanitize($_POST['what_for']) : $_row[0]['sale_comment']);
        } else {
            $_SESSION['flash_error'] = 'Cannot find expenditure!';
            redirect(PROOT . "account/expenditure");
        }
    }

    if ($_POST) {
        $e_id = guidv4();
        $createdAt = date("Y-m-d H:i:s");

        if ((!empty($for_amount) || $for_amount != '') && (!empty($what_for) || $what_for != '')) {

            if (is_capital_given()) {
                if ($for_amount > 0) {
                    if ($admin_data['admin_pin'] == $_POST['pin']) {

                        $today_balance = _capital($by)['today_balance'];
                        if ($for_amount <= $today_balance) {
                            $data = [$e_id, $for_amount, $what_for, 'exp', $by, _capital($by)['today_capital_id'], $createdAt];

                            $sql = "
                                INSERT INTO giltmarket_sales (sale_id, sale_total_amount, sale_comment, sale_type, sale_by, sale_daily, createdAt) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)
                            ";
                            
                            if (isset($_GET['edit']) && !empty($_GET['edit'])) {
                                $data = [$what_for, $for_amount, $id];
                                $sql = "
                                    UPDATE giltmarket_sales 
                                    SET sale_comment = ?, sale_total_amount = ?
                                    WHERE sale_id = ?
                                ";
                            }

                            $statement = $conn->prepare($sql);
                            $result = $statement->execute($data);
                            if (isset($result)) {
                                
                                $today = date("Y-m-d");
                                $balance = (float)(_capital($by)['today_balance'] - $for_amount);
                                if (isset($_GET['edit']) && !empty($_GET['edit'])) {
                                    if ($for_amount < $_row[0]['sale_total_amount']) {
                                        $balance = (float)($_row[0]['sale_total_amount'] - $for_amount);
                                        $balance = (float)(_capital($by)['today_balance'] + $balance);
                                    } elseif ($for_amount > $_row[0]['sale_total_amount']) {
                                        $balance = (float)($for_amount - $_row[0]['sale_total_amount']);
                                        $balance = (float)(_capital($by)['today_balance'] - $balance);
                                    } else {
                                        $balance = _capital($by)['today_balance'];
                                    }
                                }

                                $update_comma = '';
                                if ($balance <= 0) {
                                    $update_comma .= " , daily_capital_status = 1";
                                }

                                $query = "
                                    UPDATE giltmarket_daily 
                                    SET daily_balance = ? $update_comma
                                    WHERE daily_date = ? 
                                    AND daily_to = ?
                                ";
                                $statement = $conn->prepare($query);
                                $statement->execute([$balance, $today, $by]);

                                $message = "added new expenditure: " . $what_for . " and amount of: " . money($for_amount);
                                add_to_log($message, $by);
                                
                                $_SESSION['flash_success'] = 'Expenditure has been saved!';
                            } else {
                                echo js_alert("Something went wrong!");
                            }
                        } else {
                            $_SESSION['flash_error'] = 'Today\'s remaining balance cannot complete this expenditure!';
                        }
                    } else {
                        $_SESSION['flash_error'] = 'Invalid pin code provided!';
                    }
                }
            } else {
                $_SESSION['flash_error'] = 'Today\'s capital has not been given so, you can not create an expenditure!';
            }
        } else {
            $_SESSION['flash_error'] = 'Empty fields are required!';
        }
        redirect(PROOT . "account/expenditure");
    }

    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $id = sanitize($_GET['delete']);

        $sql = "
            SELECT * FROM giltmarket_sales 
            WHERE sale_id = ? 
            AND sale_by = ?
            LIMIT 1 
        ";
        $statement = $conn->prepare($sql);
        $statement->execute([$id, $by]);
        $_row = $statement->fetchAll();

        if ($statement->rowCount()) {
            $updateQuery = "
                UPDATE giltmarket_sales 
                SET sale_status = ? 
                WHERE sale_id = ?
            ";
            $statement = $conn->prepare($updateQuery);
            $result = $statement->execute([1, $id]);

            if (isset($result)) {
                $for_amount = $_row[0]['sale_total_amount'];
                $today = date("Y-m-d");
                $balance = (float)(_capital($by)['today_balance'] + $for_amount);

                $query = "
                    UPDATE giltmarket_daily 
                    SET daily_balance = ?
                    WHERE daily_date = ? 
                    AND daily_to = ?
                ";
                $statement = $conn->prepare($query);
                $statement->execute([$balance, $today, $by]);

                $message = "deleted expenditure: " . $what_for . " and amount of: " . money($for_amount);
                add_to_log($message, $by);

                $_SESSION['flash_success'] = 'Expenditure has been deleted!';
                redirect(PROOT . "account/expenditure");
            }
        } else {
            $_SESSION['flash_error'] = 'Cannot find expenditure!';
            redirect(PROOT . "account/expenditure");
        }

    }

?>

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
                        <li class="breadcrumb-item"><a class="text-body-secondary" href="javascript:;">Trade</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= ((isset($_GET['add']) && !empty($_GET['add'])) ? 'New e' : 'E'); ?>xpenditure</li>
                    </ol>
                </nav>

                <!-- Heading -->
                <h1 class="fs-4 mb-0">Expenditure</h1>
            </div>
            <?php if (!admin_has_permission()): ?>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <a class="btn btn-warning d-block" href="<?= ((isset($_GET['add']) && !empty($_GET['add'])) ? goBack() : PROOT . 'account/expenditure?add=1'); ?>"> <?= ((isset($_GET['add']) && !empty($_GET['add'])) ? 'Go back' : 'Add expenditure'); ?></a>
            </div>
            <?php endif; ?>
        </div>

    <?php if (isset($_GET['add']) || isset($_GET['edit'])) : ?>
        <?php if (!admin_has_permission()): ?>
            <?php if (is_capital_given()): ?>
       
            <form method="POST" id="expenditureForm">
                <section class="card bg-body-tertiary border-transparent mb-5">
                    <div class="card-body">
                        <h3 class="fs-5 mb-1">Create an expenditure</h3>
                        <p class="text-body-secondary mb-5">Fill in the below fields to make an expenditure</p>
                        <hr />
                        <div class="mb-4">
                            <label class="form-label" for="projectTitle">Reason</label>
                            <textarea class="form-control bg-body" type="text" name="what_for" rows="3" id="what_for" placeholder="Enter description" required><?= $what_for; ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="projectTitle">Amount</label>
                            <input class="form-control bg-body" name="for_amount" id="for_amount" type="number" min="0.00" step="0.01" value="<?= $for_amount; ?>" placeholder="0.00" required />
                        </div>
                        <button type="button" data-bs-target="#expenditureModal" data-bs-toggle="modal" class="btn btn-dark"><?=((isset($_GET['add'])) ? 'Add' : 'Edit'); ?> expenditure</button>
                        <?php if (isset($_GET['edit'])): ?>
                            <a href="<?= PROOT; ?>account/expenditure" class="btn">Cancel</a>
                        <?php endif; ?>
                    </div>
                </section>
                <div class="modal fade" id="expenditureModal" tabindex="-1" aria-labelledby="expenditureModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" style="backdrop-filter: blur(5px);">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content overflow-hidden">
                            <div class="modal-header pb-0 border-0">
                                <h1 class="modal-title h4" id="expenditureModalLabel">Verify add expenditure!</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="inputpin mb-3">
                                    <div>
                                        <?php if (is_capital_given()): ?>
                                            <?php if (is_capital_exhausted($conn, $admin_data['admin_id'])): ?>
                                            <label class="form-label">Enter pin</label>
                                            <div class="d-flex justify-content-between p-4 bg-body-tertiary rounded">
                                                <input type="password" class="form-control form-control-flush text-xl fw-bold w-rem-40 bg-transparent" placeholder="0000" name="pin" id="pin" autocomplete="off" inputmode="numeric" data-maxlength="4" oninput="this.value=this.value.slice(0,this.dataset.maxlength)" required>
                                                <button type="button" class="btn btn-sm btn-light rounded-pill shadow-none flex-none d-flex align-items-center gap-2 p-2" style="border: 1px solid #cbd5e1;">
                                                    <img src="<?= PROOT; ?>assets/media/pin.jpg" class="w-rem-6 h-rem-6 rounded-circle" alt="..."> <span>PIN</span>
                                                </button>
                                            </div>
                                            <?php else: ?>
                                                <p class="h4">
                                                    Trade ended: the capital given for today's trade has been exhausted!
                                                </p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="h4">
                                                Please you are to provide today's capital given before you can complete a trade!
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (is_capital_given()): ?>
                                    <?php if (is_capital_exhausted($conn, $admin_data['admin_id'])): ?>
                                        <button type="button" id="submitExpenditure" class="btn btn-warning mt-4">Submit</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-info">
                    Please you are to provide today's capital given before you can complete a trade!
                </div>
            <?php endif; ?>
        </div>
   
    <?php endif; ?>

    <?php else: ?>

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
                                    <a class="nav-link bg-dark active" aria-current="page" href="<?= PROOT; ?>account/expenditure">All data<?= $count_exp; ?></a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-12 col-lg">
                            <div class="row gx-3">
                                <div class="col col-lg-auto ms-auto">
                                    <div class="input-group bg-body">
                                        <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" id="search" />
                                        <span class="input-group-text" id="search">
                                            <span class="material-symbols-outlined">search</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto ms-n2">
                                    <div class="dropdown">
                                    <button class="btn btn-dark px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <span class="material-symbols-outlined">export_notes</span>
                                    </button>
                                    <div class="dropdown-menu rounded-3 p-6">
                                        <h4 class="fs-lg mb-4">Export expenditure</h4>
                                        <form style="width: 350px" id="exportForm" action="<?= PROOT; ?>auth/export.expenditure.php">
                                            <div class="row gx-3">
                                                <div class="col-sm-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_type" type="radio" name="export-status" id="inlineRadioStatus1" required value="zero" checked>
                                                        <label class="form-check-label" for="inlineRadioStatus1">All</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_type" type="radio" name="export-status" id="inlineRadioStatus2" value="one" required>
                                                        <label class="form-check-label" for="inlineRadioStatus2">Archived</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_type" type="radio" name="export-status" id="inlineRadioStatus3" value="1and0" required>
                                                        <label class="form-check-label" for="inlineRadioStatus3">Both</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio1" required value="date">
                                                        <label class="form-check-label" for="inlineRadio1">Date</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio2" required value="month">
                                                        <label class="form-check-label" for="inlineRadio2">Month</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio3" required value="year">
                                                        <label class="form-check-label" for="inlineRadio3">Year</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input export_class" type="radio" name="exp_with" id="inlineRadio4" required value="all">
                                                        <label class="form-check-label" for="inlineRadio4">All</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 mb-3 d-none" id="check-date">
                                                    <input type="date" class="form-control form-control-sm" id="export-date" name="export-date" value="<?= date("Y-m-d"); ?>">
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
    
        <div id="load-content"></div>
    </div>
    <?php endif; ?>

<?php include ("../includes/footer.inc.php"); ?>

<script>
    $(document).ready(function() {

        // submit expenditure to db
        $('#submitExpenditure').on('click', function() {
            if ($('#pin').val() == '') {
				alert("PIN is required!");
                $('#pin').focus()
				return false;
			} else {
                if (confirm("By clicking on ok, this expenditure will be recorded!")) {
                    $('#submitExpenditure').attr('disabled', true);
                    $('#submitExpenditure').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Adding ...</span>');

                    setTimeout(function () {
                        $('#expenditureForm').submit()
                    }, 2000)
                } else {
                    $('#expenditureForm')[0].reset()
                    $('#expenditureModal').modal('hide');
                    return false
                }
            }
        });

        //
        $('.btn-close').on('click', function() {
			$('#expenditureForm')[0].reset()
		})

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

                $('#submit-export').attr('disabled', true);
                $('#submit-export').text('Exporting ...');
                setTimeout(function () {
                    $('#exportForm').submit();

                    $('#submit-export').attr('disabled', false);
                    $('#submit-export').text('Export1');
                    // location.reload();
                }, 2000)
            } else {
                return false;
            }
        });


    });
    
    // SEARCH AND PAGINATION FOR LIST
    function load_data(page, query = '') {
        $.ajax({
            url : "<?= PROOT; ?>auth/expenditure.list.php",
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
</script>
